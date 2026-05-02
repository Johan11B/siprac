<?php

namespace App\Http\Controllers\Agricultor;

use App\Http\Controllers\Controller;
use App\Models\Finca;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para que un agricultor gestione los empleados (trabajadores)
 * asociados a sus fincas.
 */
class EmpleadoController extends Controller
{
    /**
     * Muestra la lista de empleados del agricultor autenticado.
     * Agrupa por finca para mejor organización.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Obtener las fincas del agricultor con sus usuarios asociados
        $fincas = $user->fincas()
            ->with(['usuarios' => function ($query) {
                $query->withPivot(['nivel_permiso', 'asignado_por', 'activo']);
            }])
            ->get();

        return view('agricultor.empleados.index', compact('fincas'));
    }

    /**
     * Formulario para crear un nuevo empleado y asignarlo a una finca.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $fincas = $user->fincas()->get();

        return view('agricultor.empleados.create', compact('fincas'));
    }

    /**
     * Crea la cuenta del empleado y lo asocia a la finca seleccionada.
     */
    public function store(Request $request)
    {
        $agricultor = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:15', 'unique:users,telefono'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'finca_id' => ['required', 'exists:fincas,id'],
            'nivel_permiso' => ['required', 'integer', 'in:1,2,3'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'telefono.unique' => 'Este teléfono ya está registrado.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'finca_id.exists' => 'La finca seleccionada no existe.',
            'nivel_permiso.in' => 'El nivel de permiso debe ser 1, 2 o 3.',
        ]);

        // Verificar que la finca pertenece al agricultor
        $finca = Finca::where('id', $validated['finca_id'])
            ->where('propietario_id', $agricultor->id)
            ->firstOrFail();

        // Obtener el rol "solicitante" (los empleados creados por agricultor empiezan como solicitantes)
        $rolSolicitante = Role::where('nombre', 'solicitante')->firstOrFail();

        // Crear la cuenta del empleado
        $empleado = User::create([
            'name' => $validated['name'],
            'telefono' => $validated['telefono'],
            'email' => $validated['email'],
            'password' => $validated['password'], // El cast 'hashed' lo encripta
            'role_id' => $rolSolicitante->id,
            'activo' => true,
        ]);

        // Asociar el empleado a la finca con los permisos definidos
        $finca->usuarios()->attach($empleado->id, [
            'nivel_permiso' => $validated['nivel_permiso'],
            'asignado_por' => $agricultor->id,
            'activo' => true,
        ]);

        return redirect()
            ->route('agricultor.empleados.index')
            ->with('success', "Empleado {$empleado->name} creado y asignado a {$finca->nombre_finca}.");
    }

    /**
     * Actualiza el nivel de permiso o estado de un empleado en una finca.
     */
    public function update(Request $request, Finca $finca, User $empleado)
    {
        $agricultor = $request->user();

        // Verificar que la finca pertenece al agricultor
        if ($finca->propietario_id !== $agricultor->id) {
            abort(403, 'No tienes permiso para modificar empleados de esta finca.');
        }

        $validated = $request->validate([
            'nivel_permiso' => ['required', 'integer', 'in:1,2,3'],
            'activo' => ['required', 'boolean'],
        ]);

        // Actualizar la relación en la tabla pivote
        $finca->usuarios()->updateExistingPivot($empleado->id, [
            'nivel_permiso' => $validated['nivel_permiso'],
            'activo' => $validated['activo'],
        ]);

        $estado = $validated['activo'] ? 'activado' : 'desactivado';

        return redirect()
            ->route('agricultor.empleados.index')
            ->with('success', "Empleado {$empleado->name} {$estado} en {$finca->nombre_finca}.");
    }

    /**
     * Desasocia un empleado de una finca (no elimina la cuenta).
     */
    public function destroy(Request $request, Finca $finca, User $empleado)
    {
        $agricultor = $request->user();

        // Verificar que la finca pertenece al agricultor
        if ($finca->propietario_id !== $agricultor->id) {
            abort(403, 'No tienes permiso para eliminar empleados de esta finca.');
        }

        $finca->usuarios()->detach($empleado->id);

        return redirect()
            ->route('agricultor.empleados.index')
            ->with('success', "Empleado {$empleado->name} removido de {$finca->nombre_finca}.");
    }
}
