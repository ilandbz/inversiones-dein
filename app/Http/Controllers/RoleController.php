<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rol\StoreRolRequest;
use App\Http\Requests\Rol\UpdateRolRequest;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function store(StoreRolRequest $request)
    {
        $request->validated();
        $role = Role::create([
            'nombre'    => $request->nombre,
        ]);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Role Registrado satisfactoriamente'
        ],200);
    }

    public function show(Request $request)
    {
        $role = Role::where('id', $request->id)->first();
        return $role;
    }
    
    public function obtener(Request $request)
    {
        $role = Role::where('id', $request->id)->first();
        return response()->json([
            'role' => $role,
        ],200);     
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRolRequest $request)
    {
        $request->validated();

        $role = Role::where('id',$request->id)->first();

        $role->nombre           = $request->nombre;
        $role->save();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Rol modificado satisfactoriamente'
        ],200);
    }

    public function destroy(Request $request)
    {
        $role = Role::where('id', $request->id)->first();
        $role->delete();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Rol eliminado satisfactoriamente'
        ],200);
    }
    public function mostrarRoleMenus(Request $request){
        $request->validate([
            'role_id' => 'required',
        ]);
       
        $role = Role::with('menus')->where('id',$request->role_id)->first();
        return response()->json([
            'role' => $role
        ]);
    }
    public function mostrarMenus(){
        return Menu::with('grupo')->select('id','nombre','slug','icono')
                    ->orderBy('orden','asc')
                    ->get();
    }
    public function guardarRoleMenu(Request $request){
        $role = Role::where('id',$request->role_id)->first();
        $role->asignarMenus($request->menu_id);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Menús Asignados Satisfactoriamente'
        ],200);
    }
    public function todos(){
        $roles = Role::get();
        return $roles;
    }
    public function listar(Request $request){
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion;
        return Role::whereRaw('UPPER(nombre) LIKE ?', ['%'.$buscar.'%'])
            ->paginate($paginacion);
    }
}
