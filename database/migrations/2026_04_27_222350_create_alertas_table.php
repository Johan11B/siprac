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
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_id')->constrained('estaciones');
            $table->foreignId('finca_id')->constrained();
            $table->datetime('timestamp');
            $table->enum('tipo_alerta', ['helada', 'lluvia_intensa', 'bajo_voltaje', 'error_sensor']);
            $table->enum('nivel_riesgo', ['bajo', 'medio', 'alto', 'critico'])->default('bajo');
            $table->float('probabilidad', 5, 4); 
            $table->float('temperatura_actual')->nullable();
            $table->string('mensaje', 255);
            $table->string('recomendacion', 255)->nullable();
            $table->boolean('leida')->default(false);
            $table->datetime('fecha_lectura')->nullable();
            $table->boolean('accion_automatica')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
