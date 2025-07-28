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

// 
// 1. Ruta específica para productos filtrados
Route::get('/products/filtered', [ProductController::class, 'getFilteredProducts']);

// 2. Rutas genéricas
// Obtener un producto
Route::get('/products/{product}', [ProductController::class, 'show']);
// Actualizar un producto 
Route::put('/products/{product}', [ProductController::class, 'update']);
// Elimina un producto 
Route::delete('/products/{product}', [ProductController::class, 'destroy']);

// 3. Otras rutas específica
// Crear un nuevo producto
Route::post('/products/create', [ProductController::class, 'store']);

// 4. Ruta general
// Obtener todos los productos
Route::get('/products', [ProductController::class, 'index']);




