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
        Schema::create('estaciones', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address', 17)->unique();
            $table->string('nombre_estacion', 50);
            $table->enum('tipo_estacion', ['meteorologica_principal', 'meteorologica_secundaria', 'suelo'])->default('meteorologica_principal');
            $table->foreignId('finca_id')->constrained()->onDelete('cascade');
            $table->timestamp('fecha_instalacion')->nullable();
            $table->timestamp('ultimo_mantenimiento')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('bateria_nivel')->nullable(); // %
            $table->integer('senal_radio')->nullable();   // RSSI dBm
            $table->json('configuracion')->nullable();    // para personalización
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estaciones');
    }
};
