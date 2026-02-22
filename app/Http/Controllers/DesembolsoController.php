<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\Desembolso\StoreDesembolsoRequest;
use App\Http\Requests\Desembolso\UpdateDesembolsoRequest;
use App\Models\Desembolso;
use App\Models\Credito;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class DesembolsoController extends Controller
{
    public function store(StoreDesembolsoRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $desembolso = Desembolso::create([
                    'credito_id'     => $request->credito_id,
                    'fecha'          => $request->fecha,
                    'hora'           => $request->hora,
                    'user_id'        => $request->user_id,
                    'descontado'     => $request->descontado,
                    'totalentregado' => $request->totalentregado,
                ]);
                $credito = Credito::findOrFail($request->credito_id);
                $credito->update(['estado' => 'DESEMBOLSADO']);
                Cliente::where('id', $credito->cliente_id)->update(['estado' => 'VIGENTE']);
                return response()->json([
                    'ok' => 1,
                    'msg' => 'Desembolso realizado exitosamente',
                    'desembolso' => $desembolso
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error al realizar el desembolso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $desembolso = Desembolso::with(['credito.cliente.persona', 'user'])->findOrFail($request->id);
        return response()->json($desembolso);
    }

    public function update(UpdateDesembolsoRequest $request)
    {
        try {
            $desembolso = Desembolso::findOrFail($request->id);
            $desembolso->update([
                'credito_id'     => $request->credito_id,
                'fecha'          => $request->fecha,
                'hora'           => $request->hora,
                'user_id'        => $request->user_id,
                'descontado'     => $request->descontado,
                'totalentregado' => $request->totalentregado,
            ]);

            return response()->json([
                'ok' => 1,
                'msg' => 'Desembolso actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error al actualizar el desembolso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $desembolso = Desembolso::findOrFail($request->id);
            $desembolso->delete();

            return response()->json([
                'ok' => 1,
                'msg' => 'Desembolso eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error al eliminar el desembolso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function todos()
    {
        return response()->json(Desembolso::all());
    }

    public function listar(Request $request)
    {
        $buscar = mb_strtoupper($request->buscar ?? '');
        $paginacion = is_numeric($request->paginacion) ? $request->paginacion : 10;

        $query = Desembolso::with(['credito.cliente.persona', 'user']);

        if (!empty($buscar)) {
            $query->whereHas('credito.cliente.persona', function ($q) use ($buscar) {
                $q->whereRaw("UPPER(dni) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(ape_pat) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(ape_mat) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(primernombre) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(otrosnombres) LIKE ?", ["%$buscar%"]);
            })->orWhere('id', 'LIKE', "%$buscar%");
        }

        return $query->orderBy('id', 'DESC')->paginate($paginacion);
    }
}
