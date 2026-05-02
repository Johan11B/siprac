<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estacion extends Model
{
    protected $table = 'estaciones';

    protected $fillable = [
        'mac_address',
        'nombre_estacion',
        'tipo_estacion',
        'finca_id',
        'fecha_instalacion',
        'ultimo_mantenimiento',
        'activo',
        'bateria_nivel',
        'senal_radio',
        'configuracion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_instalacion' => 'datetime',
            'ultimo_mantenimiento' => 'datetime',
            'activo' => 'boolean',
            'configuracion' => 'array',
        ];
    }

    /**
     * Finca a la que pertenece la estación.
     */
    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class);
    }

    /**
     * Todas las lecturas de la estación.
     */
    public function lecturas(): HasMany
    {
        return $this->hasMany(Lectura::class);
    }

    /**
     * Última lectura registrada por la estación.
     */
    public function ultimaLectura(): HasOne
    {
        return $this->hasOne(Lectura::class)->latestOfMany('fecha_lectura');
    }

    /**
     * Alertas generadas por esta estación.
     */
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }
}
