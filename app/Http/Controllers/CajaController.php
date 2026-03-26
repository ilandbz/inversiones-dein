<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\UserFilters;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Services\CajaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    use UserFilters;

    public function __construct(
        private readonly CajaService $cajaService
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Apertura
    // ──────────────────────────────────────────────────────────────────────────

    public function abrir(Request $request): JsonResponse
    {
        $request->validate([
            'agencia_id'    => 'required|integer|exists:agencias,id',
            'saldo_inicial' => 'nullable|numeric|min:0',
        ]);

        try {
            $filters = $this->getUserFilters();
            $caja    = $this->cajaService->abrir(
                (int) $request->agencia_id,
                (int) $filters['user_id'],
                (float) ($request->saldo_inicial ?? 0),
            );

            return response()->json([
                'ok'   => 1,
                'msg'  => 'Caja abierta correctamente',
                'caja' => $caja,
            ], 201);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cierre con arqueo
    // ──────────────────────────────────────────────────────────────────────────

    public function cerrar(Request $request): JsonResponse
    {
        $request->validate([
            'caja_id'            => 'required|integer|exists:cajas,id',
            'efectivo_declarado' => 'required|numeric|min:0',
            'observacion'        => 'nullable|string|max:500',
        ]);

        try {
            $caja = $this->cajaService->cerrar(
                (int) $request->caja_id,
                (float) $request->efectivo_declarado,
                $request->observacion
            );

            $diferencia = (float) $caja->diferencia;
            $msg = match (true) {
                $diferencia > 0  => "✅ Caja cerrada. Sobrante: S/ " . number_format($diferencia, 2),
                $diferencia < 0  => "⚠️ Caja cerrada. Faltante: S/ " . number_format(abs($diferencia), 2),
                default          => '✅ Caja cerrada sin diferencias.',
            };

            return response()->json(['ok' => 1, 'msg' => $msg, 'caja' => $caja]);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Resumen del día
    // ──────────────────────────────────────────────────────────────────────────

    public function resumen(Request $request): JsonResponse
    {
        $request->validate(['caja_id' => 'required|integer|exists:cajas,id']);
        $resumen = $this->cajaService->resumenDia((int) $request->caja_id);
        return response()->json($resumen);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Caja activa del día
    // ──────────────────────────────────────────────────────────────────────────

    public function activa(Request $request): JsonResponse
    {
        $filters = $this->getUserFilters();
        $caja    = $this->cajaService->cajaActivaHoy(
            (int) $filters['user_id'],
            (int) $request->agencia_id
        );

        return $caja
            ? response()->json(['ok' => 1, 'caja' => $caja])
            : response()->json(['ok' => 0, 'msg' => 'No hay caja abierta hoy'], 404);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Listado paginado
    // ──────────────────────────────────────────────────────────────────────────

    public function listar(Request $request): mixed
    {
        $filters = $this->getUserFilters();
        return $this->cajaService->listar([
            'agencia_id' => $request->agencia_id ?? $filters['agencia_id'] ?? null,
            'estado'     => $request->estado,
            'fecha'      => $request->fecha,
            'user_id'    => $filters['role'] === 'CAJERO' ? $filters['user_id'] : null,
            'paginacion' => $request->paginacion,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Movimientos manuales (egresos de gastos, ingresos extraordinarios)
    // ──────────────────────────────────────────────────────────────────────────

    public function registrarMovimiento(Request $request): JsonResponse
    {
        $request->validate([
            'caja_id'    => 'required|integer|exists:cajas,id',
            'tipo'       => 'required|in:INGRESO,EGRESO',
            'concepto'   => 'required|string|max:60',
            'monto'      => 'required|numeric|min:0.01',
            'descripcion'=> 'nullable|string|max:500',
        ]);

        try {
            $filters    = $this->getUserFilters();
            $movimiento = $this->cajaService->registrarMovimiento(
                (int) $request->caja_id,
                (int) $filters['user_id'],
                $request->tipo,
                $request->concepto,
                (float) $request->monto,
                null,
                null,
                $request->descripcion,
            );

            return response()->json([
                'ok'          => 1,
                'msg'         => 'Movimiento registrado',
                'movimiento'  => $movimiento,
            ], 201);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Movimientos de una caja
    // ──────────────────────────────────────────────────────────────────────────

    public function movimientos(Request $request): JsonResponse
    {
        $movimientos = CajaMovimiento::with('user:id,name')
            ->where('caja_id', $request->caja_id)
            ->orderBy('hora')
            ->get();

        return response()->json($movimientos);
    }
}
