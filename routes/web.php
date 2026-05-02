<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Agricultor\EmpleadoController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ============================================
// Ruta pública — Landing page
// ============================================
Route::get('/', function () {
    return view('bienvenida');
})->name('home');

// ============================================
// Rutas de autenticación (solo para invitados)
// ============================================
Route::middleware('guest')->group(function () {
    // Registro
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Login
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

// Logout (solo para autenticados)
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ============================================
// Dashboard — Redirige según rol del usuario
// ============================================
Route::middleware('auth')->group(function () {
    // Dashboard principal (redirige según rol)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas existentes del dashboard (datos, alertas, configuración)
    Route::get('/dashboard/datos', function () {
        return view('dashboard.datos');
    })->name('dashboard.datos');

    Route::get('/dashboard/alertas', function () {
        return view('dashboard.alertas');
    })->name('dashboard.alertas');

    Route::get('/dashboard/configuracion', function () {
        return view('dashboard.configuracion');
    })->name('dashboard.configuracion');
});

// ============================================
// Rutas del Agricultor
// ============================================
Route::middleware(['auth', 'role:agricultor,administrador'])->prefix('agricultor')->group(function () {
    // Gestión de empleados
    Route::get('/empleados', [EmpleadoController::class, 'index'])->name('agricultor.empleados.index');
    Route::get('/empleados/crear', [EmpleadoController::class, 'create'])->name('agricultor.empleados.create');
    Route::post('/empleados', [EmpleadoController::class, 'store'])->name('agricultor.empleados.store');
    Route::put('/empleados/{finca}/{empleado}', [EmpleadoController::class, 'update'])->name('agricultor.empleados.update');
    Route::delete('/empleados/{finca}/{empleado}', [EmpleadoController::class, 'destroy'])->name('agricultor.empleados.destroy');
});
