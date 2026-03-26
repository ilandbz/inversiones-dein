<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Credito;
use App\Models\CronogramaPago;
use Carbon\Carbon;

class CronogramaService
{
    // ──────────────────────────────────────────────────────────────────────────
    // Genera el cronograma de pagos completo para un crédito desembolsado
    // ──────────────────────────────────────────────────────────────────────────

    public function generar(Credito $credito, Carbon $fechaDesembolso): void
    {
        // Limpiar cronograma previo si existiera (re-desembolso)
        CronogramaPago::where('credito_id', $credito->id)->delete();

        $plazo     = (int) $credito->plazo;
        $monto     = (float) $credito->monto;
        $total     = (float) $credito->total;
        $frecuencia = strtoupper(trim($credito->frecuencia));

        $cuotaCapital = round($monto / $plazo, 2);
        $cuotaInteres = round(($total - $monto) / $plazo, 2);
        $cuotaTotal   = round($total / $plazo, 2);

        $fecha = $fechaDesembolso->copy();
        $saldo = $monto;

        $cuotas = [];
        for ($i = 1; $i <= $plazo; $i++) {
            $fecha = $this->avanzarFecha($fecha, $frecuencia);
            $saldo = round($saldo - $cuotaCapital, 2);

            // Ajuste de redondeo en última cuota
            $capitalCuota = ($i === $plazo) ? round($monto - ($cuotaCapital * ($plazo - 1)), 2) : $cuotaCapital;
            $interesCuota = ($i === $plazo) ? round(($total - $monto) - ($cuotaInteres * ($plazo - 1)), 2) : $cuotaInteres;

            $cuotas[] = [
                'credito_id'          => $credito->id,
                'nrocuota'            => $i,
                'fecha_prog'          => $fecha->toDateString(),
                'nombredia'           => ucfirst($fecha->locale('es')->dayName),
                'capital_programado'  => $capitalCuota,
                'interes_programado'  => $interesCuota,
                'mora_programada'     => 0.00,
                'capital_pagado'      => 0.00,
                'interes_pagado'      => 0.00,
                'mora_pagada'         => 0.00,
                'estado'              => 'PENDIENTE',
            ];
        }

        CronogramaPago::insert($cuotas);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Avanza la fecha según la frecuencia
    // ──────────────────────────────────────────────────────────────────────────

    private function avanzarFecha(Carbon $fecha, string $frecuencia): Carbon
    {
        return match ($frecuencia) {
            'DIARIA'    => $fecha->copy()->addDay(),
            'SEMANAL'   => $fecha->copy()->addWeek(),
            'QUINCENAL' => $fecha->copy()->addDays(15),
            'MENSUAL'   => $fecha->copy()->addMonth(),
            default     => throw new \InvalidArgumentException("Frecuencia inválida: $frecuencia"),
        };
    }
}
