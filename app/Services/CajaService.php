<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CajaService
{
    public function __construct(
        private readonly AuditoriaService $auditoriaService
    ) {}
    // ──────────────────────────────────────────────────────────────────────────
    // Abrir caja
    // ──────────────────────────────────────────────────────────────────────────

    public function abrir(int $agenciaId, int $userId, float $saldoInicial = 0.00): Caja
    {
        return DB::transaction(function () use ($agenciaId, $userId, $saldoInicial): Caja {
            $hoy = Carbon::today()->toDateString();

            // Verificar que no haya ya una caja abierta hoy para este usuario/agencia
            $existente = Caja::where('agencia_id', $agenciaId)
                ->where('user_id', $userId)
                ->where('fecha', $hoy)
                ->where('estado', Caja::ESTADO_ABIERTA)
                ->first();

            if ($existente) {
                throw new \LogicException('Ya existe una caja abierta para hoy en esta agencia.');
            }

            $caja = Caja::create([
                'agencia_id'    => $agenciaId,
                'user_id'       => $userId,
                'fecha'         => $hoy,
                'hora_apertura' => Carbon::now()->toTimeString(),
                'saldo_inicial' => $saldoInicial,
                'estado'        => Caja::ESTADO_ABIERTA,
            ]);

            $this->auditoriaService->registrar('CAJA', 'ABRIR_CAJA', Caja::class, (int) $caja->id, null, $caja->toArray(), "Apertura de caja con S/ {$saldoInicial}");

            return $caja;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cerrar caja con arqueo
    // ──────────────────────────────────────────────────────────────────────────

    public function cerrar(int $cajaId, float $efectivoDeclarado, ?string $observacion = null): Caja
    {
        return DB::transaction(function () use ($cajaId, $efectivoDeclarado, $observacion): Caja {
            $caja = Caja::with('movimientos')->findOrFail($cajaId);

            if (!$caja->estaAbierta()) {
                throw new \LogicException('Esta caja ya está cerrada.');
            }

            $saldoFinal = $caja->saldoCalculado();
            $diferencia = round($efectivoDeclarado - $saldoFinal, 2);

            $caja->update([
                'hora_cierre'        => Carbon::now()->toTimeString(),
                'saldo_final'        => $saldoFinal,
                'efectivo_declarado' => $efectivoDeclarado,
                'diferencia'         => $diferencia,
                'estado'             => Caja::ESTADO_CERRADA,
                'observacion'        => $observacion,
            ]);

            $this->auditoriaService->registrar('CAJA', 'CERRAR_CAJA', Caja::class, (int) $caja->id, null, $caja->toArray(), "Cierre de caja. Diferencia: S/ {$diferencia}");

            return $caja->fresh('movimientos');
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Registrar movimiento automático (llamado por otros Services)
    // ──────────────────────────────────────────────────────────────────────────

    public function registrarMovimiento(
        int $cajaId,
        int $userId,
        string $tipo,
        string $concepto,
        float $monto,
        ?string $entidadTipo = null,
        ?int $entidadId = null,
        ?string $descripcion = null
    ): CajaMovimiento {
        $caja = Caja::findOrFail($cajaId);

        if (!$caja->estaAbierta()) {
            throw new \LogicException("No hay caja abierta para registrar el movimiento de '$concepto'.");
        }

        return CajaMovimiento::create([
            'caja_id'     => $cajaId,
            'user_id'     => $userId,
            'hora'        => Carbon::now()->toTimeString(),
            'tipo'        => $tipo,
            'concepto'    => $concepto,
            'monto'       => $monto,
            'entidad_tipo'=> $entidadTipo,
            'entidad_id'  => $entidadId,
            'descripcion' => $descripcion,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Obtener caja activa del usuario hoy
    // ──────────────────────────────────────────────────────────────────────────

    public function cajaActivaHoy(int $userId, int $agenciaId): ?Caja
    {
        return Caja::where('user_id', $userId)
            ->where('agencia_id', $agenciaId)
            ->where('fecha', Carbon::today()->toDateString())
            ->where('estado', Caja::ESTADO_ABIERTA)
            ->first();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Resumen del día (para dashboard de caja)
    // ──────────────────────────────────────────────────────────────────────────

    public function resumenDia(int $cajaId): array
    {
        $caja = Caja::with('movimientos')->findOrFail($cajaId);

        $ingresos = $caja->movimientos->where('tipo', CajaMovimiento::TIPO_INGRESO);
        $egresos  = $caja->movimientos->where('tipo', CajaMovimiento::TIPO_EGRESO);

        return [
            'caja'                 => $caja,
            'saldo_calculado'      => $caja->saldoCalculado(),
            'total_ingresos'       => round($ingresos->sum('monto'), 2),
            'total_egresos'        => round($egresos->sum('monto'), 2),
            'cant_movimientos'     => $caja->movimientos->count(),
            'ingresos_por_concepto'=> $ingresos->groupBy('concepto')->map(fn($g) => round($g->sum('monto'), 2)),
            'egresos_por_concepto' => $egresos->groupBy('concepto')->map(fn($g) => round($g->sum('monto'), 2)),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Listar cajas con filtros
    // ──────────────────────────────────────────────────────────────────────────

    public function listar(array $filtros)
    {
        $query = Caja::with(['agencia:id,nombre', 'user:id,name'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora_apertura');

        if (!empty($filtros['agencia_id'])) {
            $query->where('agencia_id', $filtros['agencia_id']);
        }
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha'])) {
            $query->where('fecha', $filtros['fecha']);
        }
        if (!empty($filtros['user_id'])) {
            $query->where('user_id', $filtros['user_id']);
        }

        $perPage = is_numeric($filtros['paginacion'] ?? null) ? (int) $filtros['paginacion'] : 15;

        return $query->paginate($perPage);
    }
}
