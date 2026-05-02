<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro público.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Registra un nuevo usuario como "solicitante" (siempre).
     * El rol no es seleccionable por el usuario — es asignado automáticamente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:15', 'unique:users,telefono'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.unique' => 'Este teléfono ya está registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Siempre se asigna el rol "solicitante" (ID 1)
        $rolSolicitante = Role::where('nombre', 'solicitante')->firstOrFail();

        $user = User::create([
            'name' => $validated['name'],
            'telefono' => $validated['telefono'],
            'email' => $validated['email'],
            'password' => $validated['password'], // El cast 'hashed' lo encripta automáticamente
            'role_id' => $rolSolicitante->id,
        ]);

        auth()->login($user);

        return redirect()->route('dashboard');
    }
}
