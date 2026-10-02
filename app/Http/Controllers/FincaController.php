<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFincaRequest;
use App\Http\Requests\UpdateFincaRequest;
use App\Models\Finca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * CRUD de Fincas (Anexo M).
 *
 * Seguridad:
 *  - Middleware 'role:agricultor,administrador' en las rutas.
 *  - FincaPolicy para autorización granular (propietario/admin).
 */
class FincaController extends Controller
{
    /**
     * Listado de fincas.
     * Agricultor ve solo las suyas; admin ve todas.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Finca::class);

        $user = $request->user();

        if ($user->esAdmin()) {
            $fincas = Finca::with('propietario')
                ->withCount(['estaciones', 'usuarios'])
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            $fincas = $user->fincas()
                ->withCount(['estaciones', 'usuarios'])
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('fincas.index', compact('fincas'));
    }

    /**
     * Formulario para crear una nueva finca.
     */
    public function create()
    {
        Gate::authorize('create', Finca::class);

        return view('fincas.create');
    }

    /**
     * Almacena una nueva finca en la base de datos.
     */
    public function store(StoreFincaRequest $request)
    {
        $user = $request->user();

        // Si es admin y se pasa un propietario_id (para asignar a otro usuario), se respeta.
        // De lo contrario, se asigna al usuario autenticado.
        $propietarioId = $user->esAdmin() && $request->filled('propietario_id')
            ? $request->input('propietario_id')
            : $user->id;

        $finca = Finca::create(array_merge(
            $request->validated(),
            ['propietario_id' => $propietarioId]
        ));

        return redirect()
            ->route('fincas.index')
            ->with('success', "Finca «{$finca->nombre_finca}» creada exitosamente.");
    }

    /**
     * Formulario de edición de una finca.
     */
    public function edit(Finca $finca)
    {
        Gate::authorize('update', $finca);

        return view('fincas.edit', compact('finca'));
    }

    /**
     * Actualiza los datos de una finca.
     */
    public function update(UpdateFincaRequest $request, Finca $finca)
    {
        Gate::authorize('update', $finca);

        $finca->update($request->validated());

        return redirect()
            ->route('fincas.index')
            ->with('success', "Finca «{$finca->nombre_finca}» actualizada exitosamente.");
    }

    /**
     * Elimina una finca y sus relaciones en cascada (estaciones, lecturas, etc.).
     */
    public function destroy(Finca $finca)
    {
        Gate::authorize('delete', $finca);

        $nombre = $finca->nombre_finca;
        $finca->delete();

        return redirect()
            ->route('fincas.index')
            ->with('success', "Finca «{$nombre}» eliminada correctamente.");
    }
}
