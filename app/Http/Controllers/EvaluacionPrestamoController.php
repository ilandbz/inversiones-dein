<?php

namespace App\Http\Controllers;

use App\Http\Requests\EvaluacionPrestamo\StoreEvaluacionPrestamoRequest;
use App\Http\Requests\EvaluacionPrestamo\UpdateEvaluacionPrestamoRequest;
use App\Models\EvaluacionPrestamo;
use Illuminate\Http\Request;

class EvaluacionPrestamoController extends Controller
{
    public function store(StoreEvaluacionPrestamoRequest $request)
    {
        $request->validated();

        EvaluacionPrestamo::create([
            'credito_id'  => $request->credito_id,
            'user_id'     => $request->user_id,
            'cargo'       => $request->cargo,
            'estado'      => $request->estado,
            'observacion' => $request->observacion,
        ]);

        return response()->json([
            'ok'      => 1,
            'mensaje' => 'Registro creado satisfactoriamente',
        ], 200);
    }

    public function show(Request $request)
    {
        $registro = EvaluacionPrestamo::with(['credito', 'user'])
            ->where('id', $request->id)
            ->first();

        return $registro;
    }

    public function update(UpdateEvaluacionPrestamoRequest $request)
    {
        $request->validated();

        $registro = EvaluacionPrestamo::where('id', $request->id)->first();

        $registro->credito_id  = $request->credito_id;
        $registro->user_id     = $request->user_id;
        $registro->cargo       = $request->cargo;
        $registro->estado      = $request->estado;
        $registro->observacion = $request->observacion;
        $registro->save();

        return response()->json([
            'ok'      => 1,
            'mensaje' => 'Registro modificado satisfactoriamente',
        ], 200);
    }

    public function destroy(Request $request)
    {
        $registro = EvaluacionPrestamo::where('id', $request->id)->first();
        $registro->delete();

        return response()->json([
            'ok'      => 1,
            'mensaje' => 'Registro eliminado satisfactoriamente',
        ], 200);
    }

    public function todos()
    {
        return EvaluacionPrestamo::with(['credito', 'user'])->get();
    }

    public function listar(Request $request)
    {
        $buscar     = mb_strtoupper($request->buscar ?? '');
        $paginacion = $request->paginacion ?? 10;

        return EvaluacionPrestamo::with(['credito.cliente.persona', 'user'])
            ->whereHas('credito.cliente.persona', function ($q) use ($buscar) {
                $q->whereRaw('UPPER(apenom) LIKE ?', ['%' . $buscar . '%']);
            })
            ->orWhereRaw('UPPER(estado) LIKE ?', ['%' . $buscar . '%'])
            ->orWhereRaw('UPPER(cargo) LIKE ?', ['%' . $buscar . '%'])
            ->paginate($paginacion);
    }

    public function porCredito(Request $request)
    {
        return EvaluacionPrestamo::with(['user'])
            ->where('credito_id', $request->credito_id)
            ->get();
    }
}
