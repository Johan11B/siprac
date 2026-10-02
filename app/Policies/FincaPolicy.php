<?php

namespace App\Policies;

use App\Models\Finca;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FincaPolicy
{
    use HandlesAuthorization;

    /** Administrador tiene acceso global en todos los métodos. */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->esAdmin()) {
            return true;
        }
        return null;
    }

    /** Listar fincas: agricultor y admin. */
    public function viewAny(User $user): bool
    {
        return $user->esAgricultor() || $user->esAdmin();
    }

    /** Ver una finca: solo el propietario (o admin via before). */
    public function view(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }

    /** Crear finca: solo agricultores (o admin via before). */
    public function create(User $user): bool
    {
        return $user->esAgricultor();
    }

    /** Editar finca: solo el propietario (o admin via before). */
    public function update(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }

    /** Eliminar finca: solo el propietario (o admin via before). */
    public function delete(User $user, Finca $finca): bool
    {
        return $finca->propietario_id === $user->id;
    }
}
