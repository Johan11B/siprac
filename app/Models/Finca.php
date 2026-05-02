<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finca extends Model
{
    protected $fillable = [
        'propietario_id',
        'nombre_finca',
        'vereda',
        'municipio',
        'latitud',
        'longitud',
        'altitud_msnm',
        'area_hectareas',
        'cultivo_principal',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:8',
            'longitud' => 'decimal:8',
            'area_hectareas' => 'float',
        ];
    }

    /**
     * Propietario (agricultor) de la finca.
     */
    public function propietario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'propietario_id');
    }

    /**
     * Usuarios asociados a la finca (trabajadores y técnicos).
     * La tabla pivote incluye nivel_permiso y asignado_por.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'finca_usuario', 'finca_id', 'usuario_id')
            ->withPivot(['nivel_permiso', 'asignado_por', 'activo'])
            ->withTimestamps();
    }

    /**
     * Solo los usuarios activos asociados a esta finca.
     */
    public function usuariosActivos(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('activo', true);
    }

    /**
     * Estaciones meteorológicas de la finca.
     */
    public function estaciones(): HasMany
    {
        return $this->hasMany(Estacion::class);
    }

    /**
     * Alertas generadas para esta finca.
     */
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }
}
