<?php

namespace App\Http\Controllers;

use App\Http\Traits\UserFilters;
use Illuminate\Http\Request;
use App\Models\OrigenFinanciamiento;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class OrigenFinanciamientoController extends Controller
{
    use UserFilters;
    public function store(StoreUserRequest $request){
        $file = $request->file('foto');
        Persona::firstOrcreate([
            'dni'           => $request->dni,
            ],[
            'ape_pat'       => $request->apepat,
            'ape_mat'       => $request->apemat,
            'primernombre'  => $request->primernombre,
            'otrosnombres'  => $request->otrosnombres,
            'celular'       => $request->celular,
        ]);
        $usuario = User::create([
            'name'          => $request->username,
            'dni'           => $request->dni,
            'password'      => Hash::make($request->username),
        ]);
        if ($file) {
            $errores = [];
            if ($file->getSize() > 2048 * 1024) {
                $errores['foto'][] = 'El tamaño máximo permitido es 2MB.';
            }
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            if ($image->width() > 800 || $image->height() > 1000) {
                $image->resize(800, 1000, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            if (!empty($errores)) {
                return response()->json(['errors' => $errores], 422);
            }
            $nombre_archivo = $request->username . ".webp";
            Storage::disk('fotos')->makeDirectory('usuarios');
            Storage::disk('fotos')->put('usuarios/' . $nombre_archivo, File::get($file));
        }
        $usuario->roles()->sync([$request->role_id]);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Usuario Registrado satisfactoriamente'
        ],200);
    }
    public function cambiarclaveperfil(UpdatePasswordRequest $request){
        $filters = $this->getUserFilters();
        $user_id = $filters['user_id'];
        $user = Auth::user();
        if(Hash::check($request->current_password,$user->password)){
            $user = User::find($user_id);
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'ok' => 1,
                'mensaje' => 'Clave Cambiado con Exito'
            ],200);
        }
        else {
            return response()->json([
                'errors' => [
                    'current_password' => [
                        'Contraseña Incorrecta'
                    ]
                ]
            ],422);
        }
    }
    public function cambiarEstado(Request $request){
        $user = User::find($request->id);
        if($user->es_activo==0){
            $user->es_activo=1;
        }else{
            $user->es_activo=0;
        }
        $user->save();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Estado Cambiado'
        ],200);
    }
    public function resetclave(Request $request){
        $user = User::where('id', $request->id)->first();
        $user->password = Hash::make($user->name);
        $user->save();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Clave Reseteado con Exito'
        ],200);
    }
    public function show(Request $request){
        $user = User::with(
            'persona:dni,ape_pat,ape_mat,primernombre,otrosnombres,celular'
            )->where('id', $request->id)->first();
        return $user;
    }
    public function cambiarImagen(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,webp|max:2048',
            'username' => 'required|string',
        ]);
        $file = $request->file('foto');
        $errores = [];
        if ($file->getSize() > 2048 * 1024) {
            $errores['foto'][] = 'El tamaño máximo permitido es 2MB.';
        }
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);
        if ($image->width() > 800 || $image->height() > 1000) {
            $image->resize(800, 1000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        if (!empty($errores)) {
            return response()->json(['errors' => $errores], 422);
        }
        $nombre_archivo = $request->username . ".webp";
        Storage::disk('fotos')->makeDirectory('usuarios');
        Storage::disk('fotos')->put('usuarios/' . $nombre_archivo, File::get($file));
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Imagen Cambiado con Exito'
        ],200);
    }
    public function update(UpdateUserRequest $request){
        $file = $request->file('foto');
        $user = User::findOrFail($request->id);
        $user->update([
            'name'      => $request->username,
            'dni'       => $request->dni,
        ]);
        $user->save();
        Persona::updateOrCreate([
            'dni'           => $request->dni,
        ],[
            'ape_pat'       => $request->apepat,
            'ape_mat'       => $request->apemat,
            'primernombre'  => $request->primernombre,
            'otrosnombres'  => $request->otrosnombres,
            'celular'       => $request->celular,
        ]);


        if ($file) {
            $errores = [];
            if ($file->getSize() > 2048 * 1024) {
                $errores['foto'][] = 'El tamaño máximo permitido es 2MB.';
            }
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            if ($image->width() > 800 || $image->height() > 1000) {
                $image->resize(800, 1000, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            if (!empty($errores)) {
                return response()->json(['errors' => $errores], 422);
            }
            $nombre_archivo = $request->username . ".webp";
            Storage::disk('fotos')->makeDirectory('usuarios');
            Storage::disk('fotos')->put('usuarios/' . $nombre_archivo, File::get($file));
        }
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Se guardo Exito'
        ],200);
    }
    public function habilitados(Request $request)
    {
        $filters = $this->getUserFilters();
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion;
        $usuarios = User::query()
        ->whereRaw("upper(name) like ?", ['%' . $buscar . '%'])
        ->when(is_numeric($buscar), function ($query) use ($buscar) {
            return $query->orWhere('dni', $buscar);
        })
        ->with(['roles', 'agencias'])
        ->where('es_activo', 1);
        if ($filters['role'] === 'GERENTE AGENCIA') {
            $usuarios->whereHas('agencias', function ($query) use ($filters) {
                $query->where('agencia_id', $filters['agencia_id']);
            });
        }
        return $usuarios->paginate($paginacion);
    }
    public function inactivos(Request $request)
    {
        $filters = $this->getUserFilters();
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion;
        $usuarios = User::query()
        ->whereRaw("upper(name) like ?", ['%' . $buscar . '%'])
        ->when(is_numeric($buscar), function ($query) use ($buscar) {
            return $query->orWhere('dni', $buscar);
        })
        ->with(['roles', 'agencias'])
        ->where('es_activo', 0);
        if ($filters['role'] === 'GERENTE AGENCIA') {
            $usuarios->whereHas('agencias', function ($query) use ($filters) {
                $query->where('agencia_id', $filters['agencia_id']);
            });
        }
        return $usuarios->paginate($paginacion);
    }
    public function todos(Request $request)
    {
        return OrigenFinanciamiento::all(); 
    }
    public function todosAsesores()
    {  
        $filters = $this->getUserFilters();
        $usuarios = User::query()
        ->with(['roles', 'agencias', 'persona', 'agencia'])
        ->where('es_activo', 1)
        ->whereHas('roles', function ($query) {
            $query->where('roles.id', 5);
        });
        if ($filters['role'] === 'GERENTE AGENCIA' || $filters['role'] === 'COBRANZA') {
            $usuarios->whereHas('agencias', function ($query) use ($filters) {
                $query->where('agencia_id', $filters['agencia_id']);
            });
        }else if($filters['role'] === 'COBRANZA HUANUCO'){
            $usuarios->whereHas('agencias', function ($query) use ($filters) {
                $query->whereIn('agencia_id', [1,3]);
            });
        }
        return $usuarios->get();
    }
    public function obtenerAsesor(Request $request)
    {
        return User::with('persona')->where('id', $request->id)->first();
    }
    public function destroy(Request $request){
        $user = User::where('id', $request->id)->first();
    
        if ($user && Storage::disk('fotos')->exists('usuarios/'.$user->dni.'.webp')) {
            Storage::disk('fotos')->delete('usuarios/'.$user->name.'.webp');
        }
        $user->delete();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Usuario eliminado satisfactoriamente'
        ],200);
    }
    public function obtenerUsuariosOperadores(Request $request)//incluye cobranza y operaciones
    {
        $filters = $this->getUserFilters();
        $usuarios = User::query()
            ->where('es_activo', 1)
            ->whereHas('roles', function ($query) {
                $query->whereIn('roles.id', [3, 4, 2]); // ID de los roles de Operador y COBRANZA
            });
        if ($filters['role'] === 'GERENTE AGENCIA' || $filters['role'] === 'OPERACIONES') {
            $usuarios->whereHas('agencias', function ($query) use ($filters) {
                $query->where('agencia_id', $filters['agencia_id']);
            });
        }
        return response()->json($usuarios->get());
    }
    public function asesoresPorAgencia(Request $request)
    {
        $filters = $this->getUserFilters();
        $agencia_id = $request->agencia_id;
        $asesores = User::query()
            ->where('es_activo', 1)
            ->whereHas('roles', function ($query) {
                $query->where('roles.id', 5); // ID del rol de Asesor
            })
            ->when($agencia_id != 0, function ($query) use ($agencia_id) {
                $query->whereHas('agencias', function ($query) use ($agencia_id) {
                    $query->where('agencias.id', $agencia_id);
                });
            });

        if ($filters['role'] === 'ASESOR') {
            $asesores->where('id', $filters['user_id']);
        }

        return response()->json($asesores->get());
    }
    public function mostrarDatoUsuario(Request $request) {
        $usuario = User::with([
            'roles',
            'persona',
        ])->where('id', $request->id)->first();
    
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    
        $menu = [];
        $roles = $usuario->roles->makeHidden('pivot'); // Oculta pivot de roles
    
        // Ocultar created_at, updated_at y pivot del usuario
        $usuario->makeHidden(['created_at', 'updated_at', 'pivot']);
    
        if ($roles->count() == 1) {
            $role = $roles->first();
            $usuario->setAttribute('role', $role);
            $role->makeHidden(['created_at', 'updated_at', 'pivot']);
    
            $menusPorRol = GrupoMenu::with(['menus' => function ($query) use ($role) {
                $query->select('id','nombre','slug','icono','grupo_menu_id','padre_menu_id','orden','url')
                    ->whereHas('roles', function ($roleQuery) use ($role) {
                        $roleQuery->where('roles.id', $role->id);
                    })->orderBy('orden');
            }])
            ->whereHas('menus.roles', function ($query) use ($role) {
                $query->where('roles.id', $role->id);
            })
            ->get();
    
            $menu = array_merge($menu, $menusPorRol->toArray());
    
            return response()->json([
                'usuario' => $usuario,
                'menus' => $menu,
            ], 200);
        } else {
            return response()->json([
                'usuario' => $usuario,
            ], 200);
        }
    }
    
    public function obtenerMenusPorRole(Request $request){
        $menu = [];
        $role = Role::where('id',$request->role_id)->first();
        $menusPorRol = GrupoMenu::with(['menus' => function ($query) use ($role) {
            $query->select('id','nombre','slug','icono','grupo_menu_id','padre_menu_id','orden')
                ->whereHas('roles', function ($roleQuery) use ($role) {
                    $roleQuery->where('roles.id', $role->id);
                })
                ->orderBy('orden');
        }])
        ->whereHas('menus.roles', function ($query) use ($role) {
            $query->where('roles.id', $role->id);
        })
        ->orderBy('id')
        ->get();
        $menu = array_merge($menu, $menusPorRol->toArray());
        return response()->json([
            'menus' => $menu
        ],200); 
    }
    public function eliminarRole(Request $request){
        $user = User::find($request->user_id);
        $user->roles()->detach($request->role_id);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Rol eliminado satisfactoriamente'
        ],200);
    }
    public function agregarRole(StoreRoleUserRequest $request){
        $user = User::find($request->user_id);
        $user->roles()->attach($request->role_id);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Rol agregado satisfactoriamente'
        ],200);
    }

    public function obtenerPorTipo(Request $request){
        $filters = $this->getUserFilters();
        $role_id=$request->role_id;
        $agencia_id=$request->agencia_id;

        $users = User::where('es_activo', 1)
        ->whereHas('roles', function ($query) use ($role_id) {
            $query->where('roles.id', $role_id);
        })
        ->when($agencia_id != 0, function ($query) use ($agencia_id) {
            $query->whereHas('agencias', function ($query) use ($agencia_id) {
                $query->where('agencias.id', $agencia_id);
            });
        });
        if ($filters['role'] === 'ASESOR') {
            $users->where('id', $filters['user_id']);
        }
        return $users->get();
        //return response()->json($users);
    }
    public function rolesDisponibles(Request $request)
    {
        $user_id = $request->user_id;
        $roles = Role::whereDoesntHave('users', function ($query) use ($user_id) {
            $query->where('users.id', $user_id);
        })->get();
        return response()->json($roles);
    }
}
