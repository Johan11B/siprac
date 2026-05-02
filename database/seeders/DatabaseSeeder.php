<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ============================================
        // 1. Crear roles (solo si no existen)
        // ============================================
        // Intentamos ejecutar RolesTableSeeder, pero si falla, lo ignoramos
        try {
            $this->call(RolesTableSeeder::class);
        } catch (\Exception $e) {
            echo "Roles ya existían. Continuamos...\n";
        }

        // ============================================
        // 2. Crear usuario administrador (si no existe)
        // ============================================
        $admin = User::where('email', 'admin@siprac.com')->first();
        if (! $admin) {
            User::create([
                'name' => 'Administrador SIPRAC',
                'email' => 'admin@siprac.com',
                'telefono' => '3000000001',
                'password' => Hash::make('admin123'),
                'role_id' => 4, // administrador
                'activo' => true,
            ]);
            echo "Admin creado: admin@siprac.com / admin123\n";
        } else {
            echo "Admin ya existe\n";
        }

        // ============================================
        // 3. Crear agricultor de prueba (si no existe)
        // ============================================
        $agricultor = User::where('email', 'carlos@ejemplo.com')->first();
        if (! $agricultor) {
            User::create([
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos@ejemplo.com',
                'telefono' => '3001234567',
                'password' => Hash::make('agricultor123'),
                'role_id' => 2, // agricultor
                'activo' => true,
            ]);
            echo " Agricultor creado: carlos@ejemplo.com / agricultor123\n";
        } else {
            echo "Agricultor ya existe\n";
        }

        // ============================================
        // 4. Crear técnico de prueba (si no existe)
        // ============================================
        $tecnico = User::where('email', 'marta@siprac.com')->first();
        if (! $tecnico) {
            User::create([
                'name' => 'Ing. Marta Gómez',
                'email' => 'marta@siprac.com',
                'telefono' => '3009876543',
                'password' => Hash::make('tecnico123'),
                'role_id' => 3, // tecnico
                'activo' => true,
            ]);
            echo "Técnico creado: marta@siprac.com / tecnico123\n";
        } else {
            echo "Técnico ya existe\n";
        }

        // ============================================
        // 5. Crear solicitante de prueba (si no existe)
        // ============================================
        $solicitante = User::where('email', 'solicitante@ejemplo.com')->first();
        if (! $solicitante) {
            User::create([
                'name' => 'Pedro Solicitante',
                'email' => 'solicitante@ejemplo.com',
                'telefono' => '3005555555',
                'password' => Hash::make('solicitante123'),
                'role_id' => 1, // solicitante
                'activo' => true,
            ]);
            echo "Solicitante creado: solicitante@ejemplo.com / solicitante123\n";
        } else {
            echo "Solicitante ya existe\n";
        }
    }
}
