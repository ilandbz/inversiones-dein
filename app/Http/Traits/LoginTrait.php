<?php
namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\DispositivoAutorizado;
use App\Models\Role;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

trait LoginTrait
{


    public function validarLogin(LoginRequest $request)
    {
        $credenciales = [
            'name' => $request->name,
            'password' => $request->password,
            'es_activo' => 1
        ];

        // Primero obtenemos el usuario real
        $user = User::where('name', $request->name)
            ->with('roles')
            ->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => ['password' => 'Credenciales incorrectas']
            ], 422);
        }

        if (!auth()->attempt($credenciales)) {
            return response()->json([
                'errors' => ['username' => 'Usuario suspendido o no activo']
            ], 422);
        }

        // Usuario autenticado y correcto
        $user = auth()->user();
        $user->load('roles');

        if (!$user->role_id || !$user->existeRole($user->role_id)) {
            $user->role_id = $user->roles->first()->id ?? 10;
        }


        // $rolesRestringidos = Role::whereIn('nombre', [
        //     'ASESOR', 'COBRANZA', 'COBRANZA HUANUCO', 'GERENTE AGENCIA'
        // ])->pluck('id');


        // $requiereValidacionDispositivo = $rolesRestringidos->contains($user->role_id);

        // if ($requiereValidacionDispositivo && !$this->validarDispositivo($request)) {
        //     $user->role_id = 10;
        // }

        $user->save();

        $payload = [
            'user' => $user->id,
            'roleid' => $user->role_id,
        ];

        $token = JWT::encode($payload, env('VITE_SECRET_KEY'), 'HS256');

        return response()->json($token, 200);
    }


    public function cambiarRole(Request $request){
        $user = auth()->user();
        $user->role_id = $request->id;
        $user->save();
    
        $success = [
            'user' => $user->id,
            'roleid' => $user->role_id,
            'agenciaid' => $user->agencia_id
        ];
        
        $success = JWT::encode($success, env('VITE_SECRET_KEY'), 'HS256');
        return response()->json($success, 200);
    }
    public function cambiarAgencia(Request $request){
        $user = auth()->user();
        $user->agencia_id = $request->id;
        $user->save();
    
        $success = [
            'user' => $user->id,
            'roleid' => $user->role_id,
        ];
        
        $success = JWT::encode($success, env('VITE_SECRET_KEY'), 'HS256');
        return response()->json($success, 200);
    }
    public function validarDispositivo(Request $request): bool
    {
        $fingerprint = $request->fingerprint;

        if (!$fingerprint) {
            return false;
        }

        return DispositivoAutorizado::where('fingerprint', $fingerprint)
            ->where('activo', true)
            ->exists();
    }
}