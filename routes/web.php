<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
Route::get('/', function () {
    return view('app');
});

Route::post('/login',[LoginController::class,'login']);

Route::group(['middleware' => ['auth:sanctum']],function(){
    Route::post('/logout',[LoginController::class,'logout']);
    Route::get('/usuario-session-data',[UserController::class,'mostrarDatoUsuario']);
    Route::get('/mostrar-role', [RoleController::class, 'obtener']);
    Route::post('/cambiar-role',[LoginController::class,'cambiarRol']);
    Route::get('/obtener-menus-role',[UserController::class,'obtenerMenusPorRole']);
});



require __DIR__."/entidades.php";
require __DIR__.'/settings.php';
Route::get('/{path}',function(){   return view('app'); })->where('path','.*');