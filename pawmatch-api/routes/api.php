<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Mascotas\Controllers\MascotaController;
use App\Modules\Solicitudes\Controllers\SolicitudController;

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas públicas de mascotas 
Route::get('/mascotas', [MascotaController::class, 'index']);
Route::get('/mascotas/{id}', [MascotaController::class, 'show']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Mascotas
    Route::post('/mascotas', [MascotaController::class, 'store']);
    Route::put('/mascotas/{id}', [MascotaController::class, 'update']);
    Route::delete('/mascotas/{id}', [MascotaController::class, 'destroy']);
    Route::get('/mascotas/trashed/list', [MascotaController::class, 'trashed']);
    Route::post('/mascotas/{id}/restore', [MascotaController::class, 'restore']);

    // Solicitudes - Usuario
     Route::post('/solicitudes', [SolicitudController::class, 'store']);
    Route::get('/solicitudes/mis-solicitudes', [SolicitudController::class, 'myIndex']);
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show']);

    // Solicitudes - Administrador
    Route::get('/solicitudes', [SolicitudController::class, 'index']);
    Route::post('/solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar']);
    Route::post('/solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar']);
});
