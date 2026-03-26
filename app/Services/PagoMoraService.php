<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Credito;
use App\Models\KardexCredito;
use App\Models\PagoMora;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagoMoraService
{
    // ──────────────────────────────────────────────────────────────────────────
    // Registrar pago de mora
    // ──────────────────────────────────────────────────────────────────────────

    public function registrar(array $datos, int $userId): PagoMora
    {
        return DB::transaction(function () use ($datos, $userId): PagoMora {
            $credito = Credito::findOrFail($datos['credito_id']);

            if (!$credito->estaDesembolsado() && $credito->estado !== Credito::ESTADO_EN_MORA) {
                throw new \LogicException('Solo se puede pagar mora de créditos DESEMBOLSADOS o EN_MORA.');
            }

            $diasMora  = (int) $datos['diasmora'];
            $costoMora = (float) ($datos['costomora'] ?? $credito->costomora);
            $montoMora = round($diasMora * $costoMora, 2);

            return PagoMora::create([
                'credito_id'       => $credito->id,
                'kardex_credito_id'=> $datos['kardex_credito_id'] ?? null,
                'caja_id'          => $datos['caja_id'] ?? null,
                'user_id'          => $userId,
                'fecha'            => Carbon::now()->toDateString(),
                'hora'             => Carbon::now()->toTimeString(),
                'diasmora'         => $diasMora,
                'costomora'        => $costoMora,
                'montomora'        => $montoMora,
                'metodo_pago'      => $datos['metodo_pago'] ?? 'EFECTIVO',
                'observacion'      => $datos['observacion'] ?? null,
            ]);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Resumen de mora por crédito
    // ──────────────────────────────────────────────────────────────────────────

    public function resumenPorCredito(int $creditoId): array
    {
        $pagos = PagoMora::where('credito_id', $creditoId)->get();

        return [
            'total_dias_pagados'  => $pagos->sum('diasmora'),
            'total_monto_pagado'  => $pagos->sum('montomora'),
            'cantidad_pagos'      => $pagos->count(),
            'ultimo_pago'         => $pagos->sortByDesc('fecha')->first(),
        ];
    }
}
