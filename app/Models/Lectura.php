<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lectura extends Model
{
    protected $fillable = [
        'estacion_id',
        'fecha_lectura',
        'intervalo',
        'temp_interna',
        'humedad_interna',
        'temp_externa',
        'humedad_externa',
        'presion_relativa',
        'presion_absoluta',
        'viento_vel',
        'viento_rafaga',
        'viento_dir',
        'punto_rocio',
        'sensacion_termica',
        'lluvia_hora',
        'lluvia_dia',
        'lluvia_semana',
        'lluvia_mes',
        'lluvia_total',
        'humedad_suelo',
        'temperatura_suelo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_lectura' => 'datetime',
            'temp_externa' => 'float',
            'humedad_externa' => 'integer',
            'viento_vel' => 'float',
            'punto_rocio' => 'float',
            'lluvia_hora' => 'float',
            'lluvia_dia' => 'float',
        ];
    }

    /**
     * Estación que registró esta lectura.
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }
}
