<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Credito\StoreCreditoRequest;
use App\Http\Requests\Credito\UpdateCreditoRequest;
use App\Http\Traits\UserFilters;
use App\Models\Credito;
use App\Services\CreditoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditoController extends Controller
{
    use UserFilters;

    public function __construct(
        private readonly CreditoService $creditoService
    ) {}

    public function store(StoreCreditoRequest $request): JsonResponse
    {
        try {
            $credito = $this->creditoService->registrar($request->validated());

            return response()->json([
                'ok'      => 1,
                'msg'     => 'Crédito registrado exitosamente',
                'credito' => $credito,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => 0,
                'msg'   => 'Error al registrar crédito',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        $credito = Credito::with([
            'cliente:id,estado,persona_id',
            'asesor.user:id,name',
            'agencia:id,nombre',
            'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
            'cliente.creditos' => fn($q) => $q->whereIn('estado', [
                Credito::ESTADO_DESEMBOLSADO,
                Credito::ESTADO_PAGAR_RCS,
            ]),
        ])->findOrFail($request->id);

        return response()->json($credito);
    }

    public function update(UpdateCreditoRequest $request): JsonResponse
    {
        try {
            $credito = Credito::findOrFail($request->id);
            $this->creditoService->actualizar($credito, $request->validated());

            return response()->json([
                'ok'  => 1,
                'msg' => 'Crédito modificado satisfactoriamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => 0,
                'msg'   => 'Error al modificar crédito',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cambiarEstado(Request $request): JsonResponse
    {
        $credito = Credito::findOrFail($request->id);
        $this->creditoService->cambiarEstado($credito, $request->estado);

        return response()->json(['ok' => 1, 'msg' => 'Estado actualizado']);
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $credito = Credito::findOrFail($request->id);
            $this->creditoService->eliminar($credito);

            return response()->json(['ok' => 1, 'msg' => 'Crédito eliminado satisfactoriamente']);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'msg' => 'Error al eliminar', 'error' => $e->getMessage()], 500);
        }
    }

    public function listar(Request $request): mixed
    {
        $filters = $this->getUserFilters();

        return $this->creditoService->listar([
            'estado'     => $request->estado,
            'buscar'     => $request->buscar,
            'paginacion' => $request->paginacion,
            'asesor_id'  => $filters['role'] === 'ASESOR' ? $filters['user_id'] : null,
            'agencia_id' => $filters['agencia_id'] ?? null,
        ]);
    }

    public function obtenerTiposCreditoPorCliente(Request $request): JsonResponse
    {
        $tipos = $this->creditoService->tiposDisponibles((int) $request->cliente_id);
        return response()->json($tipos);
    }

    public function todosTipoCreditos(): JsonResponse
    {
        return response()->json(['Nuevo', 'Recurrente Sin Saldo', 'Recurrente Con Saldo', 'Paralelo']);
    }

    public function cronograma(Request $request): JsonResponse
    {
        return response()->json(
            \App\Models\CronogramaPago::where('credito_id', $request->credito_id)
                ->orderBy('nrocuota')
                ->get()
        );
    }
}
