<?php

use App\Http\Controllers\ActividadNegocioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\AsesorController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\OrigenFinanciamientoController;
use App\Http\Controllers\PlazoController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EvaluacionPrestamoController;
use App\Http\Controllers\DesembolsoController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'cliente', 'middleware' => 'auth'], function () {
    Route::get('todos', [ClienteController::class, 'todos']);
    Route::get('mostrar', [ClienteController::class, 'show']);
    Route::post('actualizar', [ClienteController::class, 'update']);
    Route::post('eliminar', [ClienteController::class, 'destroy']);
    Route::post('guardar', [ClienteController::class, 'store']);
    Route::get('listar', [ClienteController::class, 'listar']);
    Route::get('clientes-por-estado', [ClienteController::class, 'clientesPorEstado']);
    Route::get('listar-clientes-posicion', [ClienteController::class, 'listarClientesPosicion']);
    Route::get('mostrar-dni', [ClienteController::class, 'mostrarPorDni']);
    Route::get('mostrar-con-registros-dni', [ClienteController::class, 'datosCreditoJuntaPorDni']);
    Route::get('mostrar-dni-nuevo-credito', [ClienteController::class, 'getDatosParaNuevoCredito']);
    Route::post('asignar-asesor-masivo', [ClienteController::class, 'asignarAsesorMasivo']);
    Route::get('obtener-cliente-reciente', [ClienteController::class, 'obtenerClienteReciente']);
    Route::post('obtener-cliente-reciente-pdf', [ClienteController::class, 'obtenerClienteRecientePdf']);
});

Route::group(['prefix' => 'actividadnegocio', 'middleware' => 'auth'], function () {
    Route::get('listar', [ActividadNegocioController::class, 'listar']);
    Route::get('mostrar', [ActividadNegocioController::class, 'show']);
    Route::post('actualizar', [ActividadNegocioController::class, 'update']);
    Route::post('eliminar', [ActividadNegocioController::class, 'destroy']);
    Route::post('guardar', [ActividadNegocioController::class, 'store']);
    Route::get('todos', [ActividadNegocioController::class, 'todos']);
    Route::get('detalleactividadnegocio', [ActividadNegocioController::class, 'todosPorActividad']);
});


Route::group(['prefix' => 'ubigeo', 'middleware' => 'auth'], function () {
    Route::get('obtener', [UbicacionController::class, 'obtenerPorUbigeo']);
    Route::get('lista-distritos', [UbicacionController::class, 'listarDistritos']);
    Route::get('lista-provincias', [UbicacionController::class, 'listarProvincias']);
    Route::get('departamentos', [UbicacionController::class, 'obtenerDepartamentos']);
    Route::get('provincias', [UbicacionController::class, 'obtenerProvincias']);
    Route::get('distritos', [UbicacionController::class, 'obtenerDistritos']);
    Route::post('guardar', [UbicacionController::class, 'store']);
    Route::post('eliminar', [UbicacionController::class, 'destroy']);
});


//PERSONA
Route::group(['prefix' => 'persona', 'middleware' => 'auth'], function () {
    Route::get('mostrar-por-dni', [PersonaController::class, 'mostrarPorDniconApi']);
    Route::get('todos', [PersonaController::class, 'todos']);
    Route::get('mostrar', [PersonaController::class, 'show']);
    Route::post('actualizar', [PersonaController::class, 'update']);
    Route::post('eliminar', [PersonaController::class, 'destroy']);
    Route::post('guardar', [PersonaController::class, 'store']);
    Route::get('listar', [PersonaController::class, 'listar']);
    Route::post('actualizar-celular', [PersonaController::class, 'actualizarCelular']);
});


Route::group(['prefix' => 'asesor', 'middleware' => 'auth'], function () {
    Route::get('mostrar', [AsesorController::class, 'show']);
    Route::post('guardar', [AsesorController::class, 'store']);
    Route::post('eliminar', [AsesorController::class, 'destroy']);
    Route::get('todos', [AsesorController::class, 'todos']);
    Route::get('listar', [AsesorController::class, 'listar']);
});

Route::group(['prefix' => 'origen_financiamiento', 'middleware' => 'auth'], function () {
    Route::get('mostrar', [OrigenFinanciamientoController::class, 'show']);
    Route::post('guardar', [OrigenFinanciamientoController::class, 'store']);
    Route::post('eliminar', [OrigenFinanciamientoController::class, 'destroy']);
    Route::get('todos', [OrigenFinanciamientoController::class, 'todos']);
    Route::get('listar', [OrigenFinanciamientoController::class, 'listar']);
});

Route::group(['prefix' => 'plazo', 'middleware' => 'auth'], function () {
    Route::get('mostrar', [PlazoController::class, 'show']);
    Route::post('guardar', [PlazoController::class, 'store']);
    Route::post('eliminar', [PlazoController::class, 'destroy']);
    Route::get('todos', [PlazoController::class, 'todos']);
    Route::get('listar', [PlazoController::class, 'listar']);
});

Route::group(['prefix' => 'credito', 'middleware' => 'auth'], function () {
    Route::post('guardar', [CreditoController::class, 'store']);
    Route::post('actualizar', [CreditoController::class, 'update']);
    Route::post('listar', [CreditoController::class, 'listar']);
    Route::get('mostrar', [CreditoController::class, 'show']);
    Route::post('eliminar', [CreditoController::class, 'destroy']);
    Route::get('tipo-credito-cliente', [CreditoController::class, 'obtenerTiposCreditoPorCiente']);
    Route::post('replicar-evaluacion-anterior', [CreditoController::class, 'cargarEvaluacionAnterior']);
    Route::get('validar-evaluacion-asesor', [CreditoController::class, 'validarParaEvaluacion']);
    Route::post('cambiar-estado', [CreditoController::class, 'cambiarEstado']);
    Route::post('generar-pdf', [CreditoController::class, 'generarPDF']);
    Route::post('generar-pdf-desembolso', [DesembolsoController::class, 'generarPDF']);
    Route::post('listar-estado-agencia', [CreditoController::class, 'listarCreditosPorEstado']);
    Route::get('obtener-creditos-cancelar', [CreditoController::class, 'obtenerSolicitudesCancelar']);
    Route::get('todos-tipo-creditos', [CreditoController::class, 'todosTipoCreditos']);
    Route::get('datos-evaluar', [CreditoController::class, 'obtenerDatosCreditoEvaluar']);
});

Route::group(['prefix' => 'balance', 'middleware' => 'auth'], function () {
    Route::post('guardar', [BalanceController::class, 'store']);
    Route::post('actualizar', [BalanceController::class, 'update']);
    Route::get('listar', [BalanceController::class, 'listar']);
    Route::get('mostrar', [BalanceController::class, 'show']);
    Route::post('eliminar', [BalanceController::class, 'destroy']);
});

Route::group(['prefix' => 'propiedad', 'middleware' => 'auth'], function () {
    Route::post('guardar', [PropiedadController::class, 'store']);
    Route::post('actualizar', [PropiedadController::class, 'update']);
    Route::post('eliminar', [PropiedadController::class, 'destroy']);
    Route::get('mostrar', [PropiedadController::class, 'show']);
    Route::get('todos', [PropiedadController::class, 'todos']);
    Route::get('listar', [PropiedadController::class, 'listar']);
});


Route::group(['prefix' => 'rol', 'middleware' => 'auth'], function () {
    Route::get('todos', [RoleController::class, 'todos']);
    Route::get('mostrar', [RoleController::class, 'show']);
    Route::post('actualizar', [RoleController::class, 'update']);
    Route::post('eliminar', [RoleController::class, 'destroy']);
    Route::post('guardar', [RoleController::class, 'store']);
    Route::get('listar', [RoleController::class, 'listar']);
    Route::get('menu-roles', [RoleController::class, 'mostrarRoleMenus']);
    Route::get('mostrar-menus', [RoleController::class, 'mostrarMenus']);
    Route::post('menu-role-guardar', [RoleController::class, 'guardarRoleMenu']);
});


//USUARIOS
Route::group(['prefix' => 'usuario', 'middleware' => 'auth'], function () {
    Route::post('reset-password', [UserController::class, 'resetclave']);
    Route::get('listar-habilitados', [UserController::class, 'habilitados']);
    Route::get('listar-inactivos', [UserController::class, 'inactivos']);
    Route::get('listar-todos', [UserController::class, 'todos']);
    Route::get('mostrar', [UserController::class, 'show']);
    Route::post('actualizar', [UserController::class, 'update']);
    Route::post('eliminar', [UserController::class, 'destroy']);
    Route::post('guardar', [UserController::class, 'store']);
    Route::get('cambiar-estado', [UserController::class, 'cambiarEstado']);
    Route::post('cambiar-clave', [UserController::class, 'cambiarclaveperfil']);
    Route::post('eliminar-role', [UserController::class, 'eliminarRole']);
    Route::post('eliminar-agencia', [UserController::class, 'eliminarAgencia']);
    Route::post('agregar-role', [UserController::class, 'agregarRole']);
    Route::post('agregar-agencia', [UserController::class, 'agregarAgencia']);
    Route::get('roles-disponibles', [UserController::class, 'rolesDisponibles']);
    Route::get('agencias-disponibles', [UserController::class, 'agenciasDisponibles']);
    Route::get('users-tipo-agencia', [UserController::class, 'obtenerPorTipo']);
    Route::post('cambiar-imagen', [UserController::class, 'cambiarImagen']);
    Route::get('obtener-usuarios-operaciones', [UserController::class, 'obtenerUsuariosOperadores']);
});


Route::group(['prefix' => 'evaluacion-prestamo', 'middleware' => 'auth'], function () {
    Route::post('guardar', [EvaluacionPrestamoController::class, 'store']);
    Route::post('actualizar', [EvaluacionPrestamoController::class, 'update']);
    Route::post('eliminar', [EvaluacionPrestamoController::class, 'destroy']);
    Route::get('mostrar', [EvaluacionPrestamoController::class, 'show']);
    Route::get('todos', [EvaluacionPrestamoController::class, 'todos']);
    Route::post('listar', [EvaluacionPrestamoController::class, 'listar']);
    Route::get('por-credito', [EvaluacionPrestamoController::class, 'porCredito']);
});

Route::group(['prefix' => 'desembolso', 'middleware' => 'auth'], function () {
    Route::get('todos', [DesembolsoController::class, 'todos']);
    Route::get('mostrar', [DesembolsoController::class, 'show']);
    Route::post('actualizar', [DesembolsoController::class, 'update']);
    Route::post('eliminar', [DesembolsoController::class, 'destroy']);
    Route::post('guardar', [DesembolsoController::class, 'store']);
    Route::get('listar', [DesembolsoController::class, 'listar']);
    Route::post('generar-pdf', [DesembolsoController::class, 'generarPDF']);
});
