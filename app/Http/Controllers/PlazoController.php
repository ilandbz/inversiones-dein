<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePlazoRequest;
use App\Http\Requests\UpdatePlazoRequest;
use App\Models\Plazo;
use Illuminate\Http\Request;

class PlazoController extends Controller
{
    public function store(StorePlazoRequest $request)
    {
        $data = $request->validated();

        $registro = Plazo::create($data);

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
        $data = $request->validated();

        $registro = Plazo::findOrFail($request->id);
        $registro->update($data);

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
        $buscar = mb_strtoupper((string) $request->buscar);
        $paginacion = $request->paginacion ?? 10;
        return Plazo::whereRaw('UPPER(frecuencia) LIKE ?', ['%' . $buscar . '%'])
            ->paginate((int) $paginacion);
    }
}
