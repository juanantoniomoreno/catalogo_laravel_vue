<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Ruta para obtener información del usuario autenticado
// Esto es importante para tu Navbar y la lógica de permisos del frontend.
// Requiere que el usuario esté autenticado con Laravel Sanctum.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Rutas para la gestión de productos ---

// 1. Obtener todos los productos (Listado)
Route::get('/products', [ProductController::class, 'index']);

// 2. Obtener un producto específico por ID
Route::get('/products/{product}', [ProductController::class, 'show']);

// 3. Crear un nuevo producto (Protegido por Policy)
// El ProductController tiene $this->authorize('create', Product::class) en el método store,
// que usa ProductPolicy.php para verificar si el usuario es administrador.
Route::post('/products/create', [ProductController::class, 'store']);

// Route::put('/products/{product}', [ProductController::class, 'update']);
// Route::delete('/products/{product}', [ProductController::class, 'destroy']);