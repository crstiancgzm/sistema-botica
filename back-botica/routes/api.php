<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'check.active'])->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $roles = $user->roles;
        $permisos_direc = $user->getPermissionNames()->toArray();
        $permisos = [];
        foreach ($roles as $rol) {
            $permisos = array_merge($permisos, $rol->permissions->pluck('name')->toArray());
        }
        $resultado = array_merge($permisos, $permisos_direc);
        $permisos = array_values(array_unique($resultado));

        return response()->json([
            'user'     => $user,
            'permisos' => $permisos,
            'roles'    => $user->roles->pluck('name'),
        ]);
    });

    Route::get('inventarios/{inventario}/relacionados', [InventarioController::class, 'relacionados']);
    Route::patch('inventarios/{inventario}/stock', [InventarioController::class, 'updateStock']);
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
    Route::patch('usuarios/{usuario}/toggle-active', [UserController::class, 'toggleActive']);

    // Caja
    Route::prefix('caja')->group(function () {
        Route::get('estado',          [CajaController::class, 'estado']);
        Route::get('hoy',             [CajaController::class, 'hoy']);
        Route::get('historial',       [CajaController::class, 'index']);
        Route::post('abrir',          [CajaController::class, 'abrir']);
        Route::get('{caja}',          [CajaController::class, 'show']);
        Route::put('{caja}/cerrar',   [CajaController::class, 'cerrar']);
    });

    // Ventas
    Route::post('ventas', [VentaController::class, 'store']);

    // Auditoría
    Route::get('auditoria', [AuditController::class, 'index']);
});
