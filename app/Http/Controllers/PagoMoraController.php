<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\UserFilters;
use App\Models\PagoMora;
use App\Services\PagoMoraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagoMoraController extends Controller
{
    use UserFilters;

    public function __construct(
        private readonly PagoMoraService $pagoMoraService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'credito_id'        => 'required|integer|exists:creditos,id',
            'diasmora'          => 'required|integer|min:1',
            'kardex_credito_id' => 'nullable|integer|exists:kardex_creditos,id',
            'caja_id'           => 'nullable|integer|exists:cajas,id',
            'metodo_pago'       => 'nullable|string|max:50',
            'observacion'       => 'nullable|string|max:500',
        ]);

        try {
            $filters = $this->getUserFilters();
            $pago    = $this->pagoMoraService->registrar(
                $request->all(),
                (int) $filters['user_id']
            );

            return response()->json([
                'ok'   => 1,
                'msg'  => 'Pago de mora registrado',
                'pago' => $pago,
            ], 201);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    public function listar(Request $request): JsonResponse
    {
        $pagos = PagoMora::with(['user:id,name', 'kardex:id,nro'])
            ->where('credito_id', $request->credito_id)
            ->orderByDesc('fecha')
            ->get();

        return response()->json($pagos);
    }

    public function resumen(Request $request): JsonResponse
    {
        $resumen = $this->pagoMoraService->resumenPorCredito((int) $request->credito_id);
        return response()->json($resumen);
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $pago = PagoMora::findOrFail($request->id);
            $pago->delete();
            return response()->json(['ok' => 1, 'msg' => 'Pago de mora eliminado']);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }
}
