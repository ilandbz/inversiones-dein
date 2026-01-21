<?php

use App\Http\Controllers\ClienteController;

use Illuminate\Support\Facades\Route;
Route::group(['prefix' => 'cliente', 'middleware' => 'auth'], function () {
    Route::get('todos', [ClienteController::class, 'todos']);
    Route::get('mostrar', [ClienteController::class, 'show']);
    Route::post('actualizar', [ClienteController::class, 'update']);
    Route::post('eliminar', [ClienteController::class, 'destroy']);
    Route::post('guardar', [ClienteController::class, 'store']);
    Route::get('listar', [ClienteController::class, 'listar']);
    Route::get('listar-clientes-posicion', [ClienteController::class, 'listarClientesPosicion']);
    Route::get('mostrar-dni', [ClienteController::class, 'mostrarPorDni']);
    Route::get('mostrar-con-registros-dni', [ClienteController::class, 'datosCreditoJuntaPorDni']);
    Route::get('mostrar-dni-nuevo-credito', [ClienteController::class, 'getDatosParaNuevoCredito']);
    Route::post('asignar-asesor-masivo', [ClienteController::class, 'asignarAsesorMasivo']);
});