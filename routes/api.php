<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareaController;
use Illuminate\Support\Facades\Route;

// Autenticación
Route::middleware('throttle:10,1')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'me'])->name('auth.me');
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

    // GET|POST /api/tareas · GET|PUT|PATCH|DELETE /api/tareas/{tarea}
    Route::apiResource('tareas', TareaController::class);
});
