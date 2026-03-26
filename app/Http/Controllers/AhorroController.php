<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\UserFilters;
use App\Models\Ahorro;
use App\Models\AhorroMovimiento;
use App\Services\AhorroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AhorroController extends Controller
{
    use UserFilters;

    public function __construct(
        private readonly AhorroService $ahorroService
    ) {}

    /**
     * Listar ahorros (paginado y con filtros)
     */
    public function listar(Request $request): mixed
    {
        $filters = $this->getUserFilters();
        return $this->ahorroService->listar([
            'estado'      => $request->estado,
            'cliente_id'  => $request->cliente_id,
            'agencia_id'  => $request->agencia_id ?? $filters['agencia_id'] ?? null,
            'tipo_ahorro' => $request->tipo_ahorro,
            'buscar'      => $request->buscar,
            'paginacion'  => $request->paginacion,
        ]);
    }

    /**
     * Abrir una nueva cuenta de ahorro
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cliente_id'   => 'required|integer|exists:clientes,id',
            'tipo_ahorro'  => 'required|string|in:LIBRE,PLAZO_FIJO,PROGRAMADO',
            'monto'        => 'nullable|numeric|min:0',
            'tasa_interes' => 'nullable|numeric|min:0|max:1',
            'metodo_pago'  => 'nullable|string|max:50',
            'agencia_id'   => 'nullable|integer|exists:agencias,id',
            'asesor_id'    => 'nullable|integer|exists:asesors,id',
            'caja_id'      => 'nullable|integer|exists:cajas,id',
            'notas'        => 'nullable|string|max:500',
        ]);

        try {
            $filters = $this->getUserFilters();
            $ahorro  = $this->ahorroService->abrir($request->all(), (int) $filters['user_id']);

            return response()->json([
                'ok'     => 1,
                'mensaje'=> 'Cuenta de ahorro abierta correctamente',
                'ahorro' => $ahorro,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar un depósito
     */
    public function depositar(Request $request): JsonResponse
    {
        $request->validate([
            'ahorro_id'   => 'required|integer|exists:ahorros,id',
            'monto'       => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string|max:50',
            'caja_id'     => 'nullable|integer|exists:cajas,id',
            'referencia'  => 'nullable|string|max:100',
        ]);

        try {
            $filters    = $this->getUserFilters();
            $ahorro     = Ahorro::findOrFail($request->ahorro_id);
            $movimiento = $this->ahorroService->depositar($ahorro, (float) $request->monto, (int) $filters['user_id'], $request->all());

            return response()->json([
                'ok'          => 1,
                'mensaje'     => 'Depósito registrado correctamente',
                'movimiento'  => $movimiento,
                'saldo_actual'=> $ahorro->fresh()->saldo,
            ], 201);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar un retiro
     */
    public function retirar(Request $request): JsonResponse
    {
        $request->validate([
            'ahorro_id'   => 'required|integer|exists:ahorros,id',
            'monto'       => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string|max:50',
            'caja_id'     => 'nullable|integer|exists:cajas,id',
        ]);

        try {
            $filters    = $this->getUserFilters();
            $ahorro     = Ahorro::findOrFail($request->ahorro_id);
            $movimiento = $this->ahorroService->retirar($ahorro, (float) $request->monto, (int) $filters['user_id'], $request->all());

            return response()->json([
                'ok'          => 1,
                'mensaje'     => 'Retiro registrado correctamente',
                'movimiento'  => $movimiento,
                'saldo_actual'=> $ahorro->fresh()->saldo,
            ], 201);
        } catch (\LogicException|\InvalidArgumentException $e) {
            return response()->json(['ok' => 0, 'mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        $ahorro = Ahorro::with([
            'cliente.persona:id,dni,ape_pat,ape_mat,primernombre',
            'asesor.user:id,name',
            'agencia:id,nombre',
        ])->findOrFail($request->id);

        return response()->json($ahorro);
    }

    public function movimientos(Request $request): JsonResponse
    {
        $movimientos = AhorroMovimiento::with('user:id,name')
            ->where('ahorro_id', $request->ahorro_id)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return response()->json($movimientos);
    }

    public function cerrar(Request $request): JsonResponse
    {
        $request->validate(['ahorro_id' => 'required|integer|exists:ahorros,id']);

        try {
            $filters = $this->getUserFilters();
            $ahorro  = Ahorro::findOrFail($request->ahorro_id);
            $ahorro  = $this->ahorroService->cerrar($ahorro, (int) $filters['user_id'], $request->all());

            return response()->json(['ok' => 1, 'mensaje' => 'Cuenta cerrada correctamente', 'ahorro' => $ahorro]);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }
}
