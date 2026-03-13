<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\User; //Importar el modelo User para crear nuevos usuarios
use Illuminate\Support\Facades\Hash; //Importar Hash para encriptar las contraseñas

class RegisterController extends Controller
{
    public function create(){
        return view('auth.register'); //Retorna la vista del formulario de registro
    }
    public function store(Request $_request){
    $validated = $_request->validate(
        [
            'name'=>['required','string','max:100'],
            'telefono' => ['required', 'string', 'max:15', 'unique:'.User::class],
            'email'=>['required','email','unique:users,email'],
            'password'=>['required','min:8','confirmed'],
            'tipo_usuario' => ['required', 'in:agricultor,administrador,tecnico'],
        ]
    );
    $user = User::create(
        [ 
            'name'=>$validated['name'],
            'telefono' => $validated['telefono'],
            'email'=>$validated['email'],
            'password'=> Hash::make($validated['password']),
            'tipo_usuario' => $validated['tipo_usuario'],
        ]
    );
    auth()->login($user);
    return redirect()->route('login');
    }
}
