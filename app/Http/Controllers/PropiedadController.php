<?php

namespace App\Http\Controllers;

use App\Http\Requests\Propiedad\StorePropiedadRequest;
use App\Http\Requests\Propiedad\UpdatePropiedadRequest;
use App\Models\Propiedad;
use Illuminate\Http\Request;

class PropiedadController extends Controller
{
    public function store(StorePropiedadRequest $request)
    {
        $request->validated();

        Propiedad::create([
            'nombre' => mb_strtoupper($request->nombre)
        ]);

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Propiedad registrada correctamente'
        ], 200);
    }

    public function show(Request $request)
    {
        return Propiedad::where('id', $request->id)->first();
    }

    public function update(UpdatePropiedadRequest $request)
    {
        $request->validated();

        $propiedad = Propiedad::where('id', $request->id)->first();
        $propiedad->nombre = mb_strtoupper($request->nombre);
        $propiedad->save();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Propiedad modificada correctamente'
        ], 200);
    }

    public function destroy(Request $request)
    {
        $propiedad = Propiedad::where('id', $request->id)->first();
        $propiedad->delete();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Propiedad eliminada correctamente'
        ], 200);
    }

    public function todos()
    {
        return Propiedad::all();
    }

    public function listar(Request $request)
    {
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion ?? 10;

        return Propiedad::whereRaw('UPPER(nombre) LIKE ?', ['%' . $buscar . '%'])
            ->orderBy('id', 'desc')
            ->paginate($paginacion);
    }
}
