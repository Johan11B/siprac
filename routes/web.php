<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('bienvenida');
});

// Llamar los controladores para el registro e inicio de sesión que estan en App\Http\Controllers\Auth
use App\Http\Controllers\Auth\RegisterController; 
use App\Http\Controllers\Auth\LoginController;

//-----------Rutas para el registro de usuarios-------------//
Route::get('/register', [RegisterController::class, 'create'])->name('register');
//Registra y usa el controlador para manejar la lógica de registro de usuarios
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

//-----------Rutas para el inicio de sesión-----------------//
Route::get('/login', [LoginController::class, 'create'])->name('login');
//Parte de seguridad para evitar ataques de fuerza bruta, limitando a 5 intentos por minuto
Route::post('/login', [LoginController::class, 'store'])
->middleware('throttle:5,1') //Limita a 5 intentos por minuto
->name('login.store'); //Ruta para cerrar sesión, usando el método destroy del controlador de login

//-----------Ruta para cerrar sesión, usando el método destroy del controlador de login----//
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');


//-----------Ruta para el dashboard, protegida por el middleware de autenticación------//
Route::get('/dashboard', function(){
    return view ('dashboard.index'); 
})->middleware('auth')->name('dashboard.index');
