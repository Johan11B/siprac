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
        Schema::create('activaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alerta_id')->constrained();
            $table->foreignId('estacion_id')->constrained('estaciones');
            $table->datetime('timestamp');
            $table->enum('mecanismo', ['riego_antiheladas', 'drenaje', 'cobertura', 'ventilacion']);
            $table->integer('duracion_segundos')->nullable();
            $table->enum('resultado', ['exito', 'fallo', 'parcial', 'cancelado'])->default('exito');
            $table->string('codigo_error', 50)->nullable();
            $table->float('consumo_agua')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activaciones');
    }
};
