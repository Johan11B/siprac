<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed roles y usuario administrador para el entorno de producción.
     * Idempotente: usa firstOrCreate para no duplicar registros.
     */
    public function run(): void
    {
        // --- Roles (mismo orden e IDs que RolesTableSeeder) ---
        Role::firstOrCreate(
            ['nombre' => 'solicitante'],
            ['descripcion' => 'Usuario registrado pendiente de verificación. Ve un dashboard de demostración.']
        );

        Role::firstOrCreate(
            ['nombre' => 'agricultor'],
            ['descripcion' => 'Propietario de fincas. Gestiona empleados, ve alertas y datos climáticos.']
        );

        Role::firstOrCreate(
            ['nombre' => 'tecnico'],
            ['descripcion' => 'Ingeniero o técnico de mantenimiento de estaciones meteorológicas.']
        );

        Role::firstOrCreate(
            ['nombre' => 'administrador'],
            ['descripcion' => 'Acceso total al sistema. Gestiona usuarios, roles y configuración.']
        );

        // --- Usuario administrador ---
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (empty($adminEmail) || empty($adminPassword)) {
            $this->command->error('❌ Las variables ADMIN_EMAIL y ADMIN_PASSWORD son obligatorias. Abortando creación del admin.');

            return;
        }

        $adminRole = Role::where('nombre', 'administrador')->first();

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'telefono' => '3000000001',
                'password' => Hash::make($adminPassword),
                'role_id' => $adminRole->id,
                'activo' => true,
            ]
        );

        $this->command->info('✅ Roles y usuario administrador verificados/creados.');
    }
}
