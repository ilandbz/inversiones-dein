<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Credito;
use App\Models\CronogramaPago;
use App\Models\DetalleKardexCredito;
use App\Models\KardexCredito;
use App\Models\PagoMora;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KardexService
{
    public function __construct(
        private readonly AuditoriaService $auditoriaService
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Registrar un pago de crédito con sus detalles por cuota
    // ──────────────────────────────────────────────────────────────────────────

    public function registrarPago(array $datos, int $userId): KardexCredito
    {
        return DB::transaction(function () use ($datos, $userId): KardexCredito {
            $creditoId = (int) $datos['credito_id'];
            $monto     = (float) $datos['montopagado'];
            $detalles  = $this->normalizarDetalles($datos['detalles']);

            // Validaciones financieras
            $this->validarDetallesUnicos($detalles);
            $this->validarCronogramasDelCredito($creditoId, $detalles);
            $this->validarSumaDetalles($monto, $detalles);
            $nextNro = $this->calcularSiguienteNro($creditoId);

            // Crear registro de pago (kardex)
            $kardex = KardexCredito::create([
                'credito_id'  => $creditoId,
                'nro'         => $nextNro,
                'fecha'       => Carbon::now()->toDateString(),
                'hora'        => Carbon::now()->toTimeString(),
                'montopagado' => $monto,
                'user_id'     => $userId,
                'mediopago'   => $datos['mediopago'],
            ]);

            // Crear detalles y actualizar cronograma
            foreach ($detalles as $detalle) {
                $detalle['kardex_credito_id'] = $kardex->id;
                DetalleKardexCredito::create($detalle);

                // Actualizar la cuota del cronograma
                $this->actualizarCuota((int) $detalle['cronograma_id'], $detalle);
            }

            // Verificar si el crédito ha sido pagado completamente
            $this->verificarCreditoFinalizado($creditoId);

            $this->auditoriaService->registrar('PAGO_CREDITO', 'PAGAR', KardexCredito::class, (int) $kardex->id, null, $kardex->toArray(), "Pago registrado por S/ {$monto}");

            return $kardex->load('detalles.cuota');
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Anular pago (elimina kardex y revierte cronograma)
    // ──────────────────────────────────────────────────────────────────────────

    public function anularPago(KardexCredito $kardex): void
    {
        DB::transaction(function () use ($kardex): void {
            // Solo se puede anular el último pago (integridad correlativa)
            $ultimoNro = KardexCredito::where('credito_id', $kardex->credito_id)->max('nro');
            if ($kardex->nro !== $ultimoNro) {
                throw new \LogicException('Solo se puede anular el último pago registrado.');
            }

            // Revertir cronograma
            foreach ($kardex->detalles as $detalle) {
                $cuota = CronogramaPago::find($detalle->cronograma_id);
                if ($cuota) {
                    $cuota->decrement('capital_pagado', $detalle->capital_pagado);
                    $cuota->decrement('interes_pagado', $detalle->interes_pagado);
                    $cuota->decrement('mora_pagada', $detalle->mora_pagada);
                    $cuota->update(['estado' => 'PENDIENTE']);
                }
            }

            $datosAnulacion = $kardex->toArray();
            $kardexId = (int) $kardex->id;
            $kardex->delete();

            $this->auditoriaService->registrar('PAGO_CREDITO', 'ANULAR', KardexCredito::class, $kardexId, $datosAnulacion, null, "Pago anulado");

            // Reactivar crédito si estaba finalizado
            $credito = Credito::find($kardex->credito_id);
            if ($credito && $credito->estaFinalizado()) {
                $credito->update(['estado' => Credito::ESTADO_DESEMBOLSADO]);
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Privados — Validaciones
    // ──────────────────────────────────────────────────────────────────────────

    private function normalizarDetalles(array $rawDetalles): Collection
    {
        return collect($rawDetalles)->map(fn($d) => [
            'cronograma_id'  => (int) $d['cronograma_id'],
            'capital_pagado' => (float) ($d['capital_pagado'] ?? 0),
            'interes_pagado' => (float) ($d['interes_pagado'] ?? 0),
            'mora_pagada'    => (float) ($d['mora_pagada'] ?? 0),
        ]);
    }

    private function validarDetallesUnicos(Collection $detalles): void
    {
        $ids = $detalles->pluck('cronograma_id');
        if ($ids->count() !== $ids->unique()->count()) {
            throw ValidationException::withMessages([
                'detalles' => ['No se permiten cuotas repetidas en un mismo pago.'],
            ]);
        }
    }

    private function validarCronogramasDelCredito(int $creditoId, Collection $detalles): void
    {
        $ids     = $detalles->pluck('cronograma_id')->all();
        $count   = CronogramaPago::where('credito_id', $creditoId)->whereIn('id', $ids)->count();

        if ($count !== count($ids)) {
            throw ValidationException::withMessages([
                'detalles' => ['Hay cuotas inválidas o que no pertenecen a este crédito.'],
            ]);
        }
    }

    private function validarSumaDetalles(float $monto, Collection $detalles): void
    {
        $suma = $detalles->sum(fn($d) => $d['capital_pagado'] + $d['interes_pagado'] + $d['mora_pagada']);

        if (abs($suma - $monto) > 0.01) {
            throw ValidationException::withMessages([
                'montopagado' => [
                    sprintf('El monto (S/ %.2f) no coincide con la suma de detalles (S/ %.2f).', $monto, $suma),
                ],
            ]);
        }
    }

    private function calcularSiguienteNro(int $creditoId): int
    {
        $max = KardexCredito::where('credito_id', $creditoId)->max('nro');
        return $max ? ($max + 1) : 1;
    }

    private function actualizarCuota(int $cronogramaId, array $detalle): void
    {
        $cuota = CronogramaPago::findOrFail($cronogramaId);
        $cuota->increment('capital_pagado', $detalle['capital_pagado']);
        $cuota->increment('interes_pagado', $detalle['interes_pagado']);
        $cuota->increment('mora_pagada', $detalle['mora_pagada']);

        // Determinar si la cuota quedó pagada
        $totalPagado = (float)$cuota->capital_pagado + (float)$cuota->interes_pagado;
        $totalProg   = (float)$cuota->capital_programado + (float)$cuota->interes_programado;

        if ($totalPagado >= $totalProg - 0.01) {
            $cuota->update(['estado' => 'PAGADO']);
        }
    }

    private function verificarCreditoFinalizado(int $creditoId): void
    {
        $pendientes = CronogramaPago::where('credito_id', $creditoId)
            ->where('estado', '!=', 'PAGADO')
            ->count();

        if ($pendientes === 0) {
            Credito::where('id', $creditoId)->update([
                'estado'        => Credito::ESTADO_FINALIZADO,
                'saldo_capital' => 0,
                'saldo_interes' => 0,
                'saldo_total'   => 0,
            ]);
        }
    }
}
