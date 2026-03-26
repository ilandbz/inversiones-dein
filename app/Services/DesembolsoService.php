<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Agencia;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\CronogramaPago;
use App\Models\Desembolso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DesembolsoService
{
    public function __construct(
        private readonly CronogramaService $cronogramaService,
        private readonly AuditoriaService $auditoriaService
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Desembolsar: crea el desembolso, actualiza crédito y genera cronograma
    // ──────────────────────────────────────────────────────────────────────────

    public function desembolsar(array $datos): Desembolso
    {
        return DB::transaction(function () use ($datos): Desembolso {
            $credito = Credito::findOrFail($datos['credito_id']);

            if (!$credito->estaPendiente() && $credito->estado !== Credito::ESTADO_APROBADO) {
                throw new \LogicException('Solo se pueden desembolsar créditos en estado PENDIENTE o APROBADO.');
            }

            $desembolso = Desembolso::create([
                'credito_id'     => $credito->id,
                'fecha'          => $datos['fecha'],
                'hora'           => $datos['hora'],
                'user_id'        => $datos['user_id'],
                'descontado'     => $datos['descontado'] ?? 0.00,
                'totalentregado' => $datos['totalentregado'],
            ]);

            // Actualizar estado y saldos del crédito
            $credito->update([
                'estado'        => Credito::ESTADO_DESEMBOLSADO,
                'fecha_inicio'  => $datos['fecha'],
                'saldo_capital' => $credito->monto,
                'saldo_interes' => round((float)$credito->total - (float)$credito->monto, 2),
                'saldo_total'   => $credito->total,
            ]);

            $this->auditoriaService->registrar('DESEMBOLSO', 'DESEMBOLSAR', Credito::class, (int) $credito->id, null, $credito->toArray(), "Desembolso realizado por S/ {$credito->monto}");

            // Actualizar estado del cliente
            Cliente::where('id', $credito->cliente_id)->update(['estado' => 'VIGENTE']);

            // Generar cronograma
            $this->cronogramaService->generar($credito, Carbon::parse($datos['fecha']));

            return $desembolso->fresh(['credito']);
        });
    }
}
