<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Agricultor\EmpleadoController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataNormalizationController;
use App\Http\Controllers\EstacionController;
use App\Http\Controllers\FincaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ============================================
// Ruta pública — Landing page
// ============================================
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ============================================
// Chatbot público — sin autenticación
// ============================================
Route::post('/chatbot/public', [ChatbotController::class, 'publicMessage'])
    ->middleware('throttle:10,1')
    ->name('chatbot.public');

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

    Route::get('/password/reset', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'store'])->name('password.update');
});

// Logout (solo para autenticados)
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ============================================
// Dashboard — Redirige según rol del usuario
// ============================================
Route::middleware(['auth', 'active'])->group(function () {
    // Dashboard principal (redirige según rol)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas existentes del dashboard (datos, alertas, configuración)
    Route::get('/dashboard/datos', [DashboardController::class, 'datos'])->middleware('role:agricultor,tecnico,administrador')->name('dashboard.datos');
    Route::post('/chatbot/message', [ChatbotController::class, 'message'])
        ->middleware('throttle:20,1')
        ->name('chatbot.message');

    // Keep-alive: resets the server session without a full page reload
    Route::get('/session/keep-alive', function () {
        return response()->json(['ok' => true]);
    })->name('session.keepalive');

    Route::get('/dashboard/alertas', function () {
        return view('dashboard.alertas');
    })->middleware('role:agricultor,tecnico,administrador')->name('dashboard.alertas');

    Route::get('/dashboard/configuracion', function () {
        return view('dashboard.configuracion');
    })->middleware('role:agricultor,tecnico,administrador')->name('dashboard.configuracion');

    // ========================================
    // Normalización de Datos (Admin y Agricultor)
    // ========================================
    Route::middleware('role:administrador,agricultor')->group(function () {
        Route::get('/normalizacion', [DataNormalizationController::class, 'index'])->name('normalizacion.index');
        Route::post('/normalizacion/normalizar', [DataNormalizationController::class, 'normalize'])->name('normalizacion.normalize');
        Route::get('/normalizacion/descargar', [DataNormalizationController::class, 'download'])->name('normalizacion.download');
    });
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

// ============================================
// Rutas del Módulo Fincas (Anexo M)
// ============================================
Route::middleware(['auth', 'role:agricultor,administrador'])->group(function () {
    Route::resource('fincas', FincaController::class);
    Route::resource('estaciones', EstacionController::class);
});

// ============================================
// Rutas del Módulo Admin Dashboard (Anexo N)
// ============================================
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('usuarios', UserController::class);
});
