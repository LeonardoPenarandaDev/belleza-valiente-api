<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MiPerfilProfesionalController;
use App\Http\Controllers\Api\V1\ProfesionalController;
use App\Http\Controllers\Api\V1\ReservaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Autenticación pública
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Catálogo público
    Route::get('categorias', [CatalogoController::class, 'categorias']);
    Route::get('zonas', [CatalogoController::class, 'zonas']);
    Route::get('profesionales', [ProfesionalController::class, 'index']);
    Route::get('profesionales/{id}', [ProfesionalController::class, 'show']);

    // Rutas protegidas con token Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        // Sesión y cuenta
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [MeController::class, 'show']);
        Route::post('me/foto', [MeController::class, 'subirFoto']);
        Route::delete('me/foto', [MeController::class, 'borrarFoto']);
        Route::delete('me', [MeController::class, 'destroy']);

        // Perfil propio de la profesional
        Route::get('mi-perfil-profesional', [MiPerfilProfesionalController::class, 'show']);
        Route::put('mi-perfil-profesional', [MiPerfilProfesionalController::class, 'update']);
        Route::post('mi-perfil-profesional/fotos', [MiPerfilProfesionalController::class, 'subirFoto']);
        Route::delete('mi-perfil-profesional/fotos/{id}', [MiPerfilProfesionalController::class, 'borrarFoto']);

        // Reservas (Mis citas y flujo de agendamiento)
        Route::get('reservas', [ReservaController::class, 'index']);
        Route::get('reservas/{id}', [ReservaController::class, 'show']);
        Route::post('reservas', [ReservaController::class, 'store']);
        Route::post('reservas/{id}/aceptar', [ReservaController::class, 'aceptar']);
        Route::post('reservas/{id}/rechazar', [ReservaController::class, 'rechazar']);
        Route::post('reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);
        Route::post('reservas/{id}/completar', [ReservaController::class, 'completar']);
        Route::post('reservas/{id}/calificar', [ReservaController::class, 'calificar']);
    });
});
