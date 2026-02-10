<?php

namespace App\Http\Controllers;

use App\Models\ActividadNegocio;
use App\Models\DetalleActividadNegocio;
use Illuminate\Http\Request;
use App\Http\Requests\ActividadNegocio\StoreActividadNegocioRequest;
use App\Http\Requests\ActividadNegocio\UpdateActividadNegocioRequest;

class ActividadNegocioController extends Controller
{
    public function store(StoreActividadNegocioRequest $request)
    {
        $request->validated();

        $actividad = ActividadNegocio::create([
            'nombre' => $request->nombre,
        ]);

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Actividad de negocio registrada satisfactoriamente'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $actividad = ActividadNegocio::where('id', $request->id)->first();
        return $actividad;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActividadNegocioRequest $request)
    {
        $request->validated();

        $actividad = ActividadNegocio::where('id', $request->id)->first();

        $actividad->nombre = $request->nombre;
        $actividad->save();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Actividad de negocio modificada satisfactoriamente'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $actividad = ActividadNegocio::where('id', $request->id)->first();
        $actividad->delete();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Actividad de negocio eliminada satisfactoriamente'
        ], 200);
    }

    /**
     * Listar todos sin paginar
     */
    public function todos()
    {
        $actividades = ActividadNegocio::orderBy('nombre', 'ASC')->get();
        return $actividades;
    }

    /**
     * Listar con búsqueda y paginación
     */
    public function listar(Request $request)
    {
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion;

        return ActividadNegocio::whereRaw('UPPER(nombre) LIKE ?', ['%' . $buscar . '%'])
            ->orderBy('nombre', 'ASC')
            ->paginate($paginacion);
    }

    public function todosPorActividad(Request $request)
    {
        $actividad_negocio_id = $request->actividad_negocio_id;
        return DetalleActividadNegocio::where('actividad_negocio_id', $actividad_negocio_id)->get();
    }
}
