<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pivote para asociar usuarios (trabajadores/técnicos) a fincas.
     * Cada registro define qué nivel de permiso tiene el usuario en esa finca.
     *
     * Niveles de permiso:
     *   1 = ver     → Datos climáticos, gráficas, estado de estaciones
     *   2 = alertas → Nivel 1 + recibir y ver alertas de helada/lluvia
     *   3 = control → Nivel 2 + activar riego antihelada o drenaje
     */
    public function up(): void
    {
        Schema::create('finca_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')->constrained('fincas')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('nivel_permiso')->default(1)->comment('1=ver, 2=alertas, 3=control');
            $table->foreignId('asignado_por')->constrained('users')->onDelete('cascade');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Un usuario solo puede estar asociado una vez a cada finca
            $table->unique(['finca_id', 'usuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finca_usuario');
    }
};
