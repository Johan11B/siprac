<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Usuarios que tienen este rol.
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
