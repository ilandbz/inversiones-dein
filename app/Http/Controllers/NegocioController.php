<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use Illuminate\Http\Request;

class NegocioController extends Controller
{
    /**
     * Negocios por cliente
     */
    public function porCliente(Request $request)
    {
        $negocios = Negocio::with(['detalle_actividad', 'detalle_actividad.actividad_negocio'])
            ->where('cliente_id', $request->cliente_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json($negocios);
    }

    /**
     * Mostrar un negocio
     */
    public function show(Request $request)
    {
        $negocio = Negocio::with(['detalle_actividad', 'detalle_actividad.actividad_negocio'])
            ->where('id', $request->id)
            ->first();

        return response()->json($negocio);
    }

    /**
     * Guardar negocio
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'          => 'required|integer|exists:clientes,id',
            'razonsocial'         => 'nullable|string|max:80',
            'ruc'                 => 'nullable|string|max:11',
            'celular'             => 'nullable|string',
            'detalle_actividad_id' => 'nullable|integer|exists:detalle_actividad_negocios,id',
            'inicioactividad'     => 'nullable|date',
            'direccion'           => 'nullable|string',
        ], [
            'required'      => '* Dato Obligatorio',
            'max'           => 'Ingrese Máximo :max caracteres',
            'exists'        => 'El valor seleccionado no es válido',
        ]);

        Negocio::create([
            'cliente_id'           => $request->cliente_id,
            'razonsocial'          => $request->razonsocial,
            'ruc'                  => $request->ruc,
            'celular'              => $request->celular,
            'detalle_actividad_id' => $request->detalle_actividad_id,
            'inicioactividad'      => $request->inicioactividad,
            'direccion'            => $request->direccion,
        ]);

        return response()->json([
            'ok'      => 1,
            'mensaje' => 'Negocio registrado satisfactoriamente'
        ], 200);
    }

    /**
     * Actualizar negocio
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'                  => 'required|integer|exists:negocios,id',
            'cliente_id'          => 'required|integer|exists:clientes,id',
            'razonsocial'         => 'nullable|string|max:80',
            'ruc'                 => 'nullable|string|max:11',
            'celular'             => 'nullable|string',
            'detalle_actividad_id' => 'nullable|integer|exists:detalle_actividad_negocios,id',
            'inicioactividad'     => 'nullable|date',
            'direccion'           => 'nullable|string',
        ], [
            'required'      => '* Dato Obligatorio',
            'max'           => 'Ingrese Máximo :max caracteres',
            'exists'        => 'El valor seleccionado no es válido',
        ]);

        $negocio = Negocio::where('id', $request->id)->first();

        $negocio->razonsocial          = $request->razonsocial;
        $negocio->ruc                  = $request->ruc;
        $negocio->celular              = $request->celular;
        $negocio->detalle_actividad_id = $request->detalle_actividad_id;
        $negocio->inicioactividad      = $request->inicioactividad;
        $negocio->direccion            = $request->direccion;
        $negocio->save();

        return response()->json([
            'ok'      => 1,
            'mensaje' => 'Negocio modificado satisfactoriamente'
        ], 200);
    }

    /**
     * Eliminar negocio
     */
    public function destroy(Request $request)
    {
        $negocio = Negocio::where('id', $request->id)->first();
        $negocio->delete();

        return response()->json([
            'ok'      => 1,
            'mensaje' => 'Negocio eliminado satisfactoriamente'
        ], 200);
    }
}
