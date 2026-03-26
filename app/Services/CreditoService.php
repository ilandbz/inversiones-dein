<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agencia;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\CronogramaPago;
use App\Models\Desembolso;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CreditoService
{
    public function __construct(
        private readonly AuditoriaService $auditoriaService
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Registro de una nueva solicitud de crédito
    // ──────────────────────────────────────────────────────────────────────────

    public function registrar(array $datos): Credito
    {
        return DB::transaction(function () use ($datos): Credito {
            $fechaVenc = $this->calcularFechaVencimiento(
                Carbon::now(),
                $datos['frecuencia'],
                (int) $datos['plazo']
            );

            $credito = Credito::create([
                'cliente_id'              => $datos['cliente_id'],
                'asesor_id'               => $datos['asesor_id'],
                'agencia_id'              => $datos['agencia_id'] ?? null,
                'aval_id'                 => $datos['aval_id'] ?? null,
                'tipo'                    => $datos['tipo'],
                'monto'                   => $datos['monto'],
                'origen_financiamiento_id'=> $datos['origen_financiamiento_id'],
                'frecuencia'              => strtoupper($datos['frecuencia']),
                'plazo'                   => $datos['plazo'],
                'tasainteres'             => $datos['tasainteres'] ?? 0.00,
                'interes'                 => $datos['interes'] ?? 0.00,
                'costomora'               => $datos['costomora'] ?? 0.00,
                'total'                   => $datos['total'] ?? 0.00,
                'fecha_reg'               => now()->toDateString(),
                'fecha_venc'              => $fechaVenc,
                'estado'                  => Credito::ESTADO_PENDIENTE,
                'mencion'                 => $datos['mencion'] ?? null,
            ]);

            $this->auditoriaService->registrar('CREDITO', 'CREAR', Credito::class, (int) $credito->id, null, $credito->toArray(), "Nueva solicitud registrada");

            Cliente::where('id', $datos['cliente_id'])->update([
                'estado' => 'PENDIENTE',
            ]);

            return $credito;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Actualizar solicitud (solo cuando está en PENDIENTE/OBSERVADO)
    // ──────────────────────────────────────────────────────────────────────────

    public function actualizar(Credito $credito, array $datos): Credito
    {
        $anteriores = $credito->toArray();
        $fechaVenc = $this->calcularFechaVencimiento(
            Carbon::now(),
            $datos['frecuencia'],
            (int) $datos['plazo']
        );

        $credito->fill([
            'cliente_id'               => $datos['cliente_id'],
            'asesor_id'                => $datos['asesor_id'],
            'agencia_id'               => $datos['agencia_id'] ?? $credito->agencia_id,
            'aval_id'                  => $datos['aval_id'] ?? null,
            'tipo'                     => $datos['tipo'],
            'monto'                    => $datos['monto'],
            'origen_financiamiento_id' => $datos['origen_financiamiento_id'],
            'frecuencia'               => strtoupper($datos['frecuencia']),
            'plazo'                    => $datos['plazo'],
            'tasainteres'              => $datos['tasainteres'] ?? 0.00,
            'interes'                  => $datos['interes'] ?? 0.00,
            'costomora'                => $datos['costomora'] ?? 0.00,
            'total'                    => $datos['total'] ?? 0.00,
            'fecha_venc'               => $fechaVenc,
            'mencion'                  => $datos['mencion'] ?? $credito->mencion,
        ]);

        $credito->save();

        $this->auditoriaService->registrar('CREDITO', 'ACTUALIZAR', Credito::class, (int) $credito->id, $anteriores, $credito->toArray());

        return $credito;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cambiar estado
    // ──────────────────────────────────────────────────────────────────────────

    public function cambiarEstado(Credito $credito, string $estado): Credito
    {
        $anteriores = ['estado' => $credito->estado];
        $credito->update(['estado' => $estado]);
        $this->auditoriaService->registrar('CREDITO', 'CAMBIAR_ESTADO', Credito::class, (int) $credito->id, $anteriores, ['estado' => $estado]);
        return $credito->fresh();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Eliminar (solo PENDIENTE)
    // ──────────────────────────────────────────────────────────────────────────

    public function eliminar(Credito $credito): void
    {
        if ($credito->estaDesembolsado()) {
            throw new \LogicException('No se puede eliminar un crédito ya desembolsado.');
        }
        $datos = $credito->toArray();
        $id = (int) $credito->id;
        $credito->delete();
        $this->auditoriaService->registrar('CREDITO', 'ELIMINAR', Credito::class, $id, $datos, null);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Consulta de tipo de crédito disponible para un cliente
    // ──────────────────────────────────────────────────────────────────────────

    public function tiposDisponibles(int $clienteId): array
    {
        $cliente = Cliente::with('creditosVigentesCliente')->findOrFail($clienteId);
        $estados = Credito::where('cliente_id', $clienteId)
            ->whereIn('estado', [Credito::ESTADO_DESEMBOLSADO, Credito::ESTADO_FINALIZADO])
            ->pluck('estado')
            ->toArray();

        if ($cliente->creditosVigentesConMora()?->isNotEmpty()) {
            return ['Recurrente Con Saldo', 'Paralelo'];
        }
        if (in_array(Credito::ESTADO_DESEMBOLSADO, $estados, true)) {
            return ['Recurrente Con Saldo', 'Paralelo'];
        }
        if (in_array(Credito::ESTADO_FINALIZADO, $estados, true)) {
            return ['Recurrente Sin Saldo'];
        }
        return ['Nuevo'];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Listado paginado con filtros
    // ──────────────────────────────────────────────────────────────────────────

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Credito::with([
            'cliente:id,estado,persona_id',
            'asesor.user:id,name',
            'agencia:id,nombre',
            'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,celular,ruc',
        ]);

        if (!empty($filtros['estado'])) {
            is_array($filtros['estado'])
                ? $query->whereIn('estado', $filtros['estado'])
                : $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['buscar'])) {
            $buscar = mb_strtoupper($filtros['buscar']);
            $query->where(function ($q) use ($buscar): void {
                $q->whereHas('cliente.persona', fn($sq) => $sq
                    ->whereRaw('UPPER(dni) LIKE ?', ["%$buscar%"])
                    ->orWhereRaw('UPPER(ape_pat) LIKE ?', ["%$buscar%"])
                    ->orWhereRaw('UPPER(ape_mat) LIKE ?', ["%$buscar%"])
                    ->orWhereRaw('UPPER(primernombre) LIKE ?', ["%$buscar%"])
                    ->orWhereRaw("UPPER(CONCAT(ape_pat,' ',ape_mat,' ',primernombre)) LIKE ?", ["%$buscar%"])
                )->orWhere('id', $buscar);
            });
        }

        // Filtro por asesor si rol = ASESOR
        if (!empty($filtros['asesor_id'])) {
            $query->where('asesor_id', $filtros['asesor_id']);
        }

        if (!empty($filtros['agencia_id'])) {
            $query->where('agencia_id', $filtros['agencia_id']);
        }

        $perPage = is_numeric($filtros['paginacion'] ?? null) ? (int) $filtros['paginacion'] : 15;

        return $query->orderByDesc('id')->paginate($perPage);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Privados
    // ──────────────────────────────────────────────────────────────────────────

    private function calcularFechaVencimiento(Carbon $desde, string $frecuencia, int $plazo): Carbon
    {
        return match (strtoupper($frecuencia)) {
            'DIARIA'    => $desde->copy()->addDays($plazo),
            'SEMANAL'   => $desde->copy()->addWeeks($plazo),
            'QUINCENAL' => $desde->copy()->addDays($plazo * 15),
            'MENSUAL'   => $desde->copy()->addMonths($plazo),
            default     => throw new \InvalidArgumentException("Frecuencia inválida: $frecuencia"),
        };
    }
}
