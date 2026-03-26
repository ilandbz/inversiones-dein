<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ahorro;
use App\Models\AhorroMovimiento;
use App\Models\CajaMovimiento;
use App\Services\CajaService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AhorroService
{
    public function __construct(
        private readonly CajaService $cajaService,
        private readonly AuditoriaService $auditoriaService
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Abrir cuenta de ahorro (con depósito inicial)
    // ──────────────────────────────────────────────────────────────────────────

    public function abrir(array $datos, int $userId): Ahorro
    {
        return DB::transaction(function () use ($datos, $userId): Ahorro {
            $montoInicial = (float) ($datos['monto'] ?? 0);

            $ahorro = Ahorro::create([
                'cliente_id'      => $datos['cliente_id'],
                'asesor_id'       => $datos['asesor_id'] ?? null,
                'agencia_id'      => $datos['agencia_id'] ?? null,
                'tipo_ahorro'     => $datos['tipo_ahorro'] ?? Ahorro::TIPO_LIBRE,
                'monto'           => $montoInicial,
                'saldo'           => $montoInicial,
                'tasa_interes'    => $datos['tasa_interes'] ?? 0.00,
                'metodo_pago'     => $datos['metodo_pago'] ?? 'EFECTIVO',
                'estado'          => Ahorro::ESTADO_ACTIVO,
                'notas'           => $datos['notas'] ?? null,
                'fecha_apertura'  => Carbon::today()->toDateString(),
                'fecha_movimiento'=> Carbon::today()->toDateString(),
            ]);

            $this->auditoriaService->registrar('AHORRO', 'ABRIR_CUENTA', Ahorro::class, (int) $ahorro->id, null, $ahorro->toArray(), "Apertura de cuenta de ahorro");

            // Registrar movimiento de apertura si hay depósito inicial
            if ($montoInicial > 0) {
                $this->crearMovimiento($ahorro, AhorroMovimiento::TIPO_DEPOSITO, $montoInicial, 0.00, $userId, $datos);
            }

            return $ahorro->fresh(['cliente', 'agencia']);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Depositar
    // ──────────────────────────────────────────────────────────────────────────

    public function depositar(Ahorro $ahorro, float $monto, int $userId, array $extras = []): AhorroMovimiento
    {
        return DB::transaction(function () use ($ahorro, $monto, $userId, $extras): AhorroMovimiento {
            if (!$ahorro->estaActivo()) {
                throw new \LogicException('No se puede depositar en una cuenta inactiva o cerrada.');
            }
            if ($monto <= 0) {
                throw new \InvalidArgumentException('El monto del depósito debe ser mayor a cero.');
            }

            return $this->crearMovimiento($ahorro, AhorroMovimiento::TIPO_DEPOSITO, $monto, (float)$ahorro->saldo, $userId, $extras);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Retirar
    // ──────────────────────────────────────────────────────────────────────────

    public function retirar(Ahorro $ahorro, float $monto, int $userId, array $extras = []): AhorroMovimiento
    {
        return DB::transaction(function () use ($ahorro, $monto, $userId, $extras): AhorroMovimiento {
            if (!$ahorro->estaActivo()) {
                throw new \LogicException('No se puede retirar de una cuenta inactiva o cerrada.');
            }
            if ($monto <= 0) {
                throw new \InvalidArgumentException('El monto del retiro debe ser mayor a cero.');
            }
            if ((float)$ahorro->saldo < $monto) {
                throw new \LogicException(
                    sprintf('Saldo insuficiente. Disponible: S/ %.2f | Solicitado: S/ %.2f', $ahorro->saldo, $monto)
                );
            }

            return $this->crearMovimiento($ahorro, AhorroMovimiento::TIPO_RETIRO, $monto, (float)$ahorro->saldo, $userId, $extras);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Aplicar interés acumulado (puede llamarse diariamente via Job/Schedule)
    // ──────────────────────────────────────────────────────────────────────────

    public function aplicarInteres(Ahorro $ahorro, int $userId, int $dias = 1): ?AhorroMovimiento
    {
        if (!$ahorro->estaActivo() || (float)$ahorro->tasa_interes <= 0) {
            return null;
        }

        $interes = $ahorro->calcularInteresDiario($dias);

        if ($interes <= 0) {
            return null;
        }

        return DB::transaction(function () use ($ahorro, $interes, $userId): AhorroMovimiento {
            return $this->crearMovimiento($ahorro, AhorroMovimiento::TIPO_INTERES, $interes, (float)$ahorro->saldo, $userId, [
                'descripcion' => 'Interés automático acumulado',
            ]);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cerrar cuenta
    // ──────────────────────────────────────────────────────────────────────────

    public function cerrar(Ahorro $ahorro, int $userId, array $extras = []): Ahorro
    {
        return DB::transaction(function () use ($ahorro, $userId, $extras): Ahorro {
            if (!$ahorro->estaActivo()) {
                throw new \LogicException('La cuenta ya está cerrada o bloqueada.');
            }

            // Si tiene saldo, retira todo
            if ((float)$ahorro->saldo > 0) {
                $this->retirar($ahorro, (float)$ahorro->saldo, $userId, array_merge($extras, [
                    'descripcion' => 'Retiro por cierre de cuenta',
                ]));
                $ahorro->refresh();
            }

            $ahorro->update([
                'estado'       => Ahorro::ESTADO_CERRADO,
                'fecha_cierre' => Carbon::today()->toDateString(),
            ]);

            return $ahorro->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Listado paginado con filtros
    // ──────────────────────────────────────────────────────────────────────────

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Ahorro::with([
            'cliente:id,persona_id',
            'cliente.persona:id,dni,ape_pat,ape_mat,primernombre',
            'asesor.user:id,name',
            'agencia:id,nombre',
        ]);

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }
        if (!empty($filtros['agencia_id'])) {
            $query->where('agencia_id', $filtros['agencia_id']);
        }
        if (!empty($filtros['tipo_ahorro'])) {
            $query->where('tipo_ahorro', $filtros['tipo_ahorro']);
        }
        if (!empty($filtros['buscar'])) {
            $buscar = mb_strtoupper($filtros['buscar']);
            $query->whereHas('cliente.persona', fn($q) => $q
                ->whereRaw('UPPER(dni) LIKE ?', ["%$buscar%"])
                ->orWhereRaw('UPPER(ape_pat) LIKE ?', ["%$buscar%"])
                ->orWhereRaw("UPPER(CONCAT(ape_pat,' ',ape_mat,' ',primernombre)) LIKE ?", ["%$buscar%"])
            );
        }

        $perPage = is_numeric($filtros['paginacion'] ?? null) ? (int) $filtros['paginacion'] : 15;

        return $query->orderByDesc('id')->paginate($perPage);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Privado — crea movimiento y actualiza saldo de la cuenta
    // ──────────────────────────────────────────────────────────────────────────

    private function crearMovimiento(
        Ahorro $ahorro,
        string $tipo,
        float $monto,
        float $saldoAnterior,
        int $userId,
        array $extras = []
    ): AhorroMovimiento {
        $esIngreso   = in_array($tipo, [AhorroMovimiento::TIPO_DEPOSITO, AhorroMovimiento::TIPO_INTERES], true);
        $saldoPost   = $esIngreso
            ? round($saldoAnterior + $monto, 2)
            : round($saldoAnterior - $monto, 2);

        $movimiento = AhorroMovimiento::create([
            'ahorro_id'        => $ahorro->id,
            'caja_id'          => $extras['caja_id'] ?? null,
            'user_id'          => $userId,
            'fecha'            => Carbon::today()->toDateString(),
            'hora'             => Carbon::now()->toTimeString(),
            'tipo'             => $tipo,
            'monto'            => $monto,
            'saldo_anterior'   => $saldoAnterior,
            'saldo_posterior'  => $saldoPost,
            'metodo_pago'      => $extras['metodo_pago'] ?? 'EFECTIVO',
            'referencia'       => $extras['referencia'] ?? null,
            'descripcion'      => $extras['descripcion'] ?? null,
        ]);

        $this->auditoriaService->registrar('AHORRO', 'MOVIMIENTO', Ahorro::class, (int) $ahorro->id, ['saldo' => $saldoAnterior], ['saldo' => $saldoPost], "Movimiento de tipo {$tipo} por S/ {$monto}");

        // Actualizar saldo en la cuenta
        $ahorro->update(['saldo' => $saldoPost]);

        // Registrar en caja si corresponde
        if (!empty($extras['caja_id'])) {
            $conceptoCaja = $esIngreso
                ? CajaMovimiento::CONCEPTO_DEPOSITO_AHORRO
                : CajaMovimiento::CONCEPTO_RETIRO_AHORRO;

            $tipoCaja = $esIngreso
                ? CajaMovimiento::TIPO_INGRESO
                : CajaMovimiento::TIPO_EGRESO;

            $this->cajaService->registrarMovimiento(
                (int) $extras['caja_id'],
                $userId,
                $tipoCaja,
                $conceptoCaja,
                $monto,
                Ahorro::class,
                $ahorro->id,
                "Ahorro #{$ahorro->id} — $tipo",
            );
        }

        return $movimiento;
    }
}
