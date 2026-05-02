<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar el rol del usuario autenticado.
 *
 * Uso en rutas:
 *   ->middleware('role:agricultor')
 *   ->middleware('role:agricultor,administrador')  // acepta múltiples roles
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Si no está autenticado, redirigir al login
        if (! $user) {
            return redirect()->route('login');
        }

        // Si no está activo, cerrar sesión
        if (! $user->activo) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.']);
        }

        // Verificar que el usuario tiene alguno de los roles permitidos
        if (! $user->tieneAlgunRol($roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
