<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Roles del sistema SIPRAC.
     * El orden de inserción define los IDs:
     *   1 = solicitante (default al registrarse)
     *   2 = agricultor
     *   3 = tecnico
     *   4 = administrador
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'nombre' => 'solicitante',
                'descripcion' => 'Usuario registrado pendiente de verificación. Ve un dashboard de demostración.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'agricultor',
                'descripcion' => 'Propietario de fincas. Gestiona empleados, ve alertas y datos climáticos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'tecnico',
                'descripcion' => 'Ingeniero o técnico de mantenimiento de estaciones meteorológicas.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'administrador',
                'descripcion' => 'Acceso total al sistema. Gestiona usuarios, roles y configuración.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
