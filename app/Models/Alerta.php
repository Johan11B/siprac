<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alerta extends Model
{
    protected $fillable = [
        'estacion_id',
        'finca_id',
        'timestamp',
        'tipo_alerta',
        'nivel_riesgo',
        'probabilidad',
        'temperatura_actual',
        'mensaje',
        'recomendacion',
        'leida',
        'fecha_lectura',
        'accion_automatica',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
            'fecha_lectura' => 'datetime',
            'leida' => 'boolean',
            'accion_automatica' => 'boolean',
            'probabilidad' => 'float',
        ];
    }

    /**
     * Estación que generó la alerta.
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }

    /**
     * Finca asociada a la alerta.
     */
    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class);
    }

    /**
     * Activaciones generadas por esta alerta.
     */
    public function activaciones(): HasMany
    {
        return $this->hasMany(Activacion::class);
    }
}
