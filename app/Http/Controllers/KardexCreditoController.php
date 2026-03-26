<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\KardexCredito\StoreKardexCreditoRequest;
use App\Http\Traits\UserFilters;
use App\Models\KardexCredito;
use App\Services\KardexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KardexCreditoController extends Controller
{
    use UserFilters;

    public function __construct(
        private readonly KardexService $kardexService
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Registrar pago de cuotas
    // ──────────────────────────────────────────────────────────────────────────

    public function store(StoreKardexCreditoRequest $request): JsonResponse
    {
        try {
            $filters = $this->getUserFilters();
            $kardex  = $this->kardexService->registrarPago(
                $request->validated(),
                (int) $filters['user_id']
            );

            return response()->json([
                'ok'     => 1,
                'mensaje'=> 'Pago registrado correctamente',
                'pago'   => $kardex,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok'     => 0,
                'errors' => $e->errors(),
            ], 422);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => 0,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Anular último pago
    // ──────────────────────────────────────────────────────────────────────────

    public function destroy(Request $request): JsonResponse
    {
        try {
            $kardex = KardexCredito::where('credito_id', $request->credito_id)
                ->where('nro', $request->nro)
                ->firstOrFail();

            $this->kardexService->anularPago($kardex);

            return response()->json(['ok' => 1, 'mensaje' => 'Pago anulado correctamente']);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Consultas
    // ──────────────────────────────────────────────────────────────────────────

    public function show(Request $request): JsonResponse
    {
        $kardex = KardexCredito::with(['detalles.cuota', 'user:id,name'])
            ->where('credito_id', $request->credito_id)
            ->where('nro', $request->nro)
            ->firstOrFail();

        return response()->json($kardex);
    }

    public function listar(Request $request): JsonResponse
    {
        $query = KardexCredito::with(['user:id,name', 'detalles.cuota']);

        if ($request->has('credito_id')) {
            $query->where('credito_id', $request->credito_id);
        }

        return response()->json(
            $query->orderByDesc('fecha')->orderByDesc('hora')->get()
        );
    }
}
