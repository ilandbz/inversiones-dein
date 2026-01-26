<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRolRequest;
use App\Http\Requests\UpdateRolRequest;
use App\Models\Asesor;
use Illuminate\Http\Request;

class AsesorController extends Controller
{
    public function store(StoreRolRequest $request)
    {
        $request->validated();
        $role = Asesor::create([
            'nombre'    => $request->nombre,
        ]);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Role Registrado satisfactoriamente'
        ],200);
    }

    public function show(Request $request)
    {
        $role = Asesor::with([
            'user',
            'user.persona'
        ])->where('id', $request->id)->first();
        return $role;
    }
    
    public function update(UpdateRolRequest $request)
    {
        $request->validated();

        $role = Asesor::where('id',$request->id)->first();

        $role->nombre           = $request->nombre;
        $role->save();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Rol modificado satisfactoriamente'
        ],200);
    }

    public function destroy(Request $request)
    {
        $role = Asesor::where('id', $request->id)->first();
        $role->delete();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Rol eliminado satisfactoriamente'
        ],200);
    }

    public function todos(){
        $roles = Asesor::with([
            'user',
            'user.persona'
        ])->get();
        return $roles;
    }
    public function listar(Request $request){
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion;
        return Asesor::whereRaw('UPPER(nombre) LIKE ?', ['%'.$buscar.'%'])
            ->paginate($paginacion);
    }
}
