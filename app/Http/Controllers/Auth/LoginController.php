<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login'); // Retorna la vista del formulario de inicio de sesión
    }

    public function store(Request $_request)
    {
        $credentials = $_request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if (! auth()->attempt($credentials)) { // Intenta autenticar al usuario con las credenciales proporcionadas
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }
        $_request->session()->regenerate();

        return redirect()->intended('/dashboard'); // Redirige al usuario al dashboard
    }

    public function destroy(Request $_request)
    {
        auth()->logout(); // Cierra la sesión del usuario
        $_request->session()->invalidate(); // Invalida la sesión actual
        $_request->session()->regenerateToken(); // Regenera el token CSRF para seguridad

        return redirect('/'); // Redirige al usuario a la página de inicio después de cerrar sesión
    }
}
