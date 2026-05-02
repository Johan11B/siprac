<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activacion extends Model
{
    protected $table = 'activaciones';

    protected $fillable = [
        'alerta_id',
        'estacion_id',
        'timestamp',
        'mecanismo',
        'duracion_segundos',
        'resultado',
        'codigo_error',
        'consumo_agua',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
            'consumo_agua' => 'float',
        ];
    }

    /**
     * Alerta que originó esta activación.
     */
    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class);
    }

    /**
     * Estación donde se ejecutó la activación.
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }
}
