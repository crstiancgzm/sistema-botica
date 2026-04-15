<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    $roles = $request->user()->roles;
    $permisos_direc = $request->user()->getPermissionNames()->toArray();
    $permisos = [];
    foreach ($roles as $rol) {
        $permisos = array_merge($permisos, $rol->permissions->pluck('name')->toArray());
    }
    $resultado = array_merge($permisos, $permisos_direc);
    $permisos = array_values(array_unique($resultado));

    return response()->json([
        'user' => $request->user(),
        'permisos' => $permisos,
        'roles' => $request->user()->roles->pluck('name'),
    ]);
});

Route::get('inventarios/{inventario}/relacionados', [InventarioController::class, 'relacionados']);
Route::apiResource('inventarios', InventarioController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('areas', AreaController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('categorias', CategoriaController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('laboratorios', LaboratorioController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('subcategorias', SubcategoriaController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('proveedores', ProveedorController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('presentaciones', PresentacionController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('roles', RolController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('permisos', PermisoController::class)->middleware([HandlePrecognitiveRequests::class]);
Route::apiResource('usuarios', UserController::class)->middleware([HandlePrecognitiveRequests::class]);
