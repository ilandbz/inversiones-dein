<?php

namespace App\Http\Controllers;

use App\Models\Ahorro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AhorroController extends Controller
{
    /**
     * Listar ahorros por cliente
     */
    public function porCliente(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id'
        ]);

        $ahorros = Ahorro::where('cliente_id', $request->cliente_id)
            ->orderBy('fecha_movimiento', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json($ahorros);
    }

    /**
     * Guardar un nuevo ahorro
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'       => 'required|integer|exists:clientes,id',
            'tipo_ahorro'      => 'required|string|max:50',
            'monto'            => 'required|numeric|min:0',
            'fecha_movimiento' => 'required|date',
            'metodo_pago'      => 'required|string|max:50',
            'estado'           => 'nullable|string|max:20',
            'notas'            => 'nullable|string',
        ]);

        $ahorro = Ahorro::create($request->all());

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Ahorro registrado satisfactoriamente',
            'ahorro' => $ahorro
        ], 210); // Using 210 or 201 as per project convention (seen 200/210 in others)
    }

    /**
     * Mostrar un ahorro específico
     */
    public function show(Request $request)
    {
        $ahorro = Ahorro::findOrFail($request->id);
        return response()->json($ahorro);
    }

    /**
     * Actualizar un ahorro
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'               => 'required|integer|exists:ahorros,id',
            'tipo_ahorro'      => 'required|string|max:50',
            'monto'            => 'required|numeric|min:0',
            'fecha_movimiento' => 'required|date',
            'metodo_pago'      => 'required|string|max:50',
            'estado'           => 'nullable|string|max:20',
            'notas'            => 'nullable|string',
        ]);

        $ahorro = Ahorro::findOrFail($request->id);
        $ahorro->update($request->all());

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Ahorro actualizado satisfactoriamente',
            'ahorro' => $ahorro
        ]);
    }

    /**
     * Eliminar un ahorro
     */
    public function destroy(Request $request)
    {
        $ahorro = Ahorro::findOrFail($request->id);
        $ahorro->delete();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Ahorro eliminado satisfactoriamente'
        ]);
    }
}
