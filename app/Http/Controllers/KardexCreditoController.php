<?php

namespace App\Http\Controllers;

use App\Http\Requests\KardexCredito\StoreKardexCreditoRequest;
use App\Models\KardexCredito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Http\Traits\UserFilters;
use App\Models\CronogramaPago;

class KardexCreditoController extends Controller
{
    use UserFilters;
    public function store(StoreKardexCreditoRequest $request)
    {
        $filters = $this->getUserFilters();

        // 1) Normaliza/asegura números
        $detalles = collect($request->detalles)->map(function ($d) {
            return [
                'cronograma_id'  => (int) $d['cronograma_id'],
                'capital_pagado' => (float) ($d['capital_pagado'] ?? 0),
                'interes_pagado' => (float) ($d['interes_pagado'] ?? 0),
                'mora_pagada'    => (float) ($d['mora_pagada'] ?? 0),
                'observacion'    => $d['observacion'] ?? null,
            ];
        });

        // 2) Valida que no vengan ids duplicados (evita doble cobro en una misma operación)
        $ids = $detalles->pluck('cronograma_id');
        if ($ids->count() !== $ids->unique()->count()) {
            throw ValidationException::withMessages([
                'detalles' => ['No se permiten cronograma_id repetidos en el mismo pago.']
            ]);
        }

        // 3) Verifica que esos cronograma_id existan y pertenezcan a ese crédito
        $cronogramas = CronogramaPago::query()
            ->where('credito_id', $request->credito_id)
            ->whereIn('id', $ids->all())
            ->get(['id', 'credito_id', 'nrocuota', 'cuota']);

        if ($cronogramas->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'detalles' => ['Hay cuotas inválidas o que no pertenecen a este crédito.']
            ]);
        }

        // 4) (Opcional pero recomendado) Verifica que no estén pagando cuotas ya pagadas
        // Esto depende de tu diseño. Ejemplo: si una cuota se considera pagada cuando
        // la suma capital_pagado >= cuota (simplificado).
        //
        // Si tienes un campo "estado" en cronograma, mejor aún.
        //
        // Aquí ejemplo genérico: no permitir pagar de nuevo una cuota con saldo 0 según tu cálculo.
        // (Si permites pagos parciales, esta validación debe ser más fina.)
        //
        // $yaPagadas = ... // tu lógica
        // if ($yaPagadas) throw ValidationException...

        // 5) Suma detalles y compara con montopagado (con tolerancia de redondeo)
        $sumDetalles = $detalles->sum(fn($d) => $d['capital_pagado'] + $d['interes_pagado'] + $d['mora_pagada']);
        $monto = (float) $request->montopagado;

        // tolerancia por redondeo (ej: 0.01)
        $tolerancia = 0.01;

        if (abs($sumDetalles - $monto) > $tolerancia) {
            throw ValidationException::withMessages([
                'montopagado' => ["El monto total (S/ {$monto}) no coincide con la suma de detalles (S/ " . number_format($sumDetalles, 2) . ")."]
            ]);
        }

        // 6) Valida correlativo nro (evita que envíen nro incorrecto o duplicado)
        $nextNro = KardexCredito::where('credito_id', $request->credito_id)->max('nro');
        $nextNro = $nextNro ? ($nextNro + 1) : 1;

        if ((int)$request->nro !== $nextNro) {
            throw ValidationException::withMessages([
                'nro' => ["El nro enviado no es válido. El siguiente nro debe ser {$nextNro}."]
            ]);
        }

        try {
            DB::beginTransaction();

            $kardex = KardexCredito::create([
                'credito_id'  => $request->credito_id,
                'nro'         => $request->nro,
                'fecha'       => Carbon::now()->toDateString(),
                'hora'        => Carbon::now()->toTimeString(),
                'montopagado' => $monto,
                'user_id'     => $filters['user_id'],
                'mediopago'   => $request->mediopago,
            ]);

            foreach ($detalles as $detalle) {
                $kardex->detalles()->create($detalle);
            }

            DB::commit();

            return response()->json([
                'ok' => 1,
                'mensaje' => 'Pago y detalle registrados correctamente',
                'pago' => $kardex->load('detalles')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
        $kardex = KardexCredito::with(['detalles.cuota', 'user:id,name'])
            ->where('credito_id', $request->credito_id)
            ->where('nro', $request->nro)
            ->first();

        if (!$kardex) {
            return response()->json(['message' => 'Kardex no encontrado'], 404);
        }

        return response()->json($kardex);
    }

    public function update(Request $request)
    {
        try {
            $kardex = KardexCredito::where('credito_id', $request->credito_id)
                ->where('nro', $request->nro)
                ->first();

            if (!$kardex) {
                return response()->json(['message' => 'Kardex no encontrado'], 404);
            }

            $kardex->update($request->only(['montopagado', 'mediopago']));

            return response()->json($kardex);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $kardex = KardexCredito::where('credito_id', $request->credito_id)
                ->where('nro', $request->nro)
                ->first();

            if (!$kardex) {
                return response()->json(['message' => 'Kardex no encontrado'], 404);
            }

            $kardex->delete();

            return response()->json(['message' => 'Kardex eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function todos()
    {
        return response()->json(KardexCredito::all());
    }

    public function listar(Request $request)
    {
        $query = KardexCredito::with(['user:id,name', 'detalles.cuota']);

        if ($request->has('credito_id')) {
            $query->where('credito_id', $request->credito_id);
        }

        return response()->json($query->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->get());
    }
}
