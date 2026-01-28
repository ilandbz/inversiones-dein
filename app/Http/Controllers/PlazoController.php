<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlazoRequest;
use App\Http\Requests\UpdatePlazoRequest;
use App\Models\Plazo;
use Illuminate\Http\Request;

class PlazoController extends Controller
{
    public function store(StorePlazoRequest $request)
    {
        $request->validated();

        $registro = Plazo::create([
            'frecuencia'   => $request->frecuencia,
            'plazo'        => $request->plazo,
            'tasainteres'  => $request->tasainteres,
            'costomora'    => $request->costomora,
        ]);

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Registro creado satisfactoriamente'
        ], 200);
    }

    public function show(Request $request)
    {
        $registro = Plazo::where('id', $request->id)->first();
        return $registro;
    }

    public function update(UpdatePlazoRequest $request)
    {
        $request->validated();

        $registro = Plazo::where('id', $request->id)->first();

        $registro->frecuencia   = $request->frecuencia;
        $registro->plazo        = $request->plazo;
        $registro->tasainteres  = $request->tasainteres;
        $registro->costomora    = $request->costomora;
        $registro->save();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Registro modificado satisfactoriamente'
        ], 200);
    }

    public function destroy(Request $request)
    {
        $registro = Plazo::where('id', $request->id)->first();
        $registro->delete();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Registro eliminado satisfactoriamente'
        ], 200);
    }

    public function todos()
    {
        $registros = Plazo::all();
        return $registros;
    }

    public function listar(Request $request)
    {
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion;
        return Plazo::whereRaw('UPPER(frecuencia) LIKE ?', ['%' . $buscar . '%'])
            ->paginate($paginacion);
    }
}
