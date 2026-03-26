<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Agencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgenciaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Agencia::where('es_activa', true)
                ->select('id', 'nombre', 'codigo', 'ciudad', 'telefono')
                ->orderBy('nombre')
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'    => 'required|string|max:100',
            'codigo'    => 'required|string|max:20|unique:agencias,codigo',
            'direccion' => 'nullable|string|max:200',
            'telefono'  => 'nullable|string|max:20',
            'ciudad'    => 'nullable|string|max:80',
        ]);

        $agencia = Agencia::create($request->only(['nombre', 'codigo', 'direccion', 'telefono', 'ciudad']));

        return response()->json(['ok' => 1, 'agencia' => $agencia], 201);
    }

    public function show(Agencia $agencia): JsonResponse
    {
        return response()->json($agencia);
    }

    public function update(Request $request, Agencia $agencia): JsonResponse
    {
        $request->validate([
            'nombre'    => 'sometimes|string|max:100',
            'codigo'    => "sometimes|string|max:20|unique:agencias,codigo,{$agencia->id}",
            'direccion' => 'nullable|string|max:200',
            'telefono'  => 'nullable|string|max:20',
            'ciudad'    => 'nullable|string|max:80',
            'es_activa' => 'sometimes|boolean',
        ]);

        $agencia->update($request->only(['nombre', 'codigo', 'direccion', 'telefono', 'ciudad', 'es_activa']));

        return response()->json(['ok' => 1, 'agencia' => $agencia]);
    }

    public function destroy(Agencia $agencia): JsonResponse
    {
        $agencia->update(['es_activa' => false]); // Soft-disable, no delete físico
        return response()->json(['ok' => 1, 'msg' => 'Agencia desactivada']);
    }
}
