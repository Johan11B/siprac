<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\Finca;
use Illuminate\Http\Request;

/**
 * CRUD de Estaciones Meteorológicas.
 *
 * Solo accesible por administradores y agricultores.
 * El agricultor ve las estaciones de sus fincas; el admin ve todas.
 */
class EstacionController extends Controller
{
    /**
     * Listado de estaciones.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->esAdmin()) {
            $estaciones = Estacion::with('finca.propietario')
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            $fincaIds = $user->fincas()->pluck('id');
            $estaciones = Estacion::with('finca')
                ->whereIn('finca_id', $fincaIds)
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('estaciones.index', compact('estaciones'));
    }

    /**
     * Formulario para registrar una nueva estación.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        $fincas = $user->esAdmin()
            ? Finca::orderBy('nombre_finca')->get()
            : $user->fincas()->orderBy('nombre_finca')->get();

        return view('estaciones.create', compact('fincas'));
    }

    /**
     * Almacena una nueva estación.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'mac_address'       => ['required', 'string', 'size:17', 'unique:estaciones,mac_address'],
            'nombre_estacion'   => ['required', 'string', 'max:50'],
            'tipo_estacion'     => ['required', 'in:meteorologica_principal,meteorologica_secundaria,suelo'],
            'finca_id'          => ['required', 'exists:fincas,id'],
            'fecha_instalacion' => ['nullable', 'date'],
        ], [
            'mac_address.required'     => 'La dirección MAC es obligatoria.',
            'mac_address.size'         => 'La MAC debe tener 17 caracteres (AA:BB:CC:DD:EE:FF).',
            'mac_address.unique'       => 'Esta dirección MAC ya está registrada.',
            'nombre_estacion.required' => 'El nombre de la estación es obligatorio.',
            'tipo_estacion.required'   => 'El tipo de estación es obligatorio.',
            'finca_id.exists'          => 'La finca seleccionada no existe.',
        ]);

        // Verificar que el agricultor es dueño de la finca
        if (!$user->esAdmin()) {
            $finca = Finca::where('id', $validated['finca_id'])
                ->where('propietario_id', $user->id)
                ->firstOrFail();
        }

        Estacion::create($validated);

        return redirect()
            ->route('estaciones.index')
            ->with('success', "Estación «{$validated['nombre_estacion']}» registrada exitosamente.");
    }

    /**
     * Formulario de edición de una estación.
     */
    public function edit(Request $request, Estacion $estacione)
    {
        $user = $request->user();

        // Verificar acceso
        if (!$user->esAdmin() && $estacione->finca->propietario_id !== $user->id) {
            abort(403, 'No tienes permiso para editar esta estación.');
        }

        $fincas = $user->esAdmin()
            ? Finca::orderBy('nombre_finca')->get()
            : $user->fincas()->orderBy('nombre_finca')->get();

        return view('estaciones.edit', compact('estacione', 'fincas'));
    }

    /**
     * Actualiza los datos de una estación.
     */
    public function update(Request $request, Estacion $estacione)
    {
        $user = $request->user();

        if (!$user->esAdmin() && $estacione->finca->propietario_id !== $user->id) {
            abort(403, 'No tienes permiso para editar esta estación.');
        }

        $validated = $request->validate([
            'mac_address'          => ['required', 'string', 'size:17', 'unique:estaciones,mac_address,' . $estacione->id],
            'nombre_estacion'      => ['required', 'string', 'max:50'],
            'tipo_estacion'        => ['required', 'in:meteorologica_principal,meteorologica_secundaria,suelo'],
            'finca_id'             => ['required', 'exists:fincas,id'],
            'fecha_instalacion'    => ['nullable', 'date'],
            'ultimo_mantenimiento' => ['nullable', 'date'],
            'activo'               => ['sometimes', 'boolean'],
        ]);

        $estacione->update($validated);

        return redirect()
            ->route('estaciones.index')
            ->with('success', "Estación «{$estacione->nombre_estacion}» actualizada.");
    }

    /**
     * Elimina una estación y sus lecturas asociadas.
     */
    public function destroy(Request $request, Estacion $estacione)
    {
        $user = $request->user();

        if (!$user->esAdmin() && $estacione->finca->propietario_id !== $user->id) {
            abort(403, 'No tienes permiso para eliminar esta estación.');
        }

        $nombre = $estacione->nombre_estacion;
        $estacione->delete();

        return redirect()
            ->route('estaciones.index')
            ->with('success', "Estación «{$nombre}» eliminada correctamente.");
    }
}
