<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lecturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_id')->constrained('estaciones')->onDelete('cascade');
            $table->datetime('fecha_lectura');

            // Datos internos de la estación
            $table->integer('intervalo')->nullable();            // minutos
            $table->float('temp_interna')->nullable();
            $table->integer('humedad_interna')->nullable();

            // Datos externos (clima)
            $table->float('temp_externa')->nullable();                       // Temperatura Externa
            $table->integer('humedad_externa')->nullable();
            $table->float('presion_relativa')->nullable();       // mmHg
            $table->float('presion_absoluta')->nullable();

            // Viento
            $table->float('viento_vel')->nullable();                         // m/s
            $table->float('viento_rafaga')->nullable();
            $table->string('viento_dir', 10)->nullable();        // N, NE, etc.

            // Sensación térmica y punto de rocío
            $table->float('punto_rocio')->nullable();
            $table->float('sensacion_termica')->nullable();

            // Lluvia
            $table->float('lluvia_hora')->nullable();                        // mm última hora
            $table->float('lluvia_dia')->nullable();                        // mm últimas 24h
            $table->float('lluvia_semana')->nullable();          // mm semana
            $table->float('lluvia_mes')->nullable();             // mm mes
            $table->float('lluvia_total')->nullable();           // mm acumulado

            // Opcionales para suelo (si algún sensor se agrega)
            $table->float('humedad_suelo')->nullable();
            $table->float('temperatura_suelo')->nullable();

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['estacion_id', 'fecha_lectura']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturas');
    }
};
