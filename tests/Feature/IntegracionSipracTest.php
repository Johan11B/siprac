<?php

namespace Tests\Feature;

use App\Http\Controllers\DataNormalizationController;
use App\Models\Estacion;
use App\Models\Finca;
use App\Models\Lectura;
use App\Models\User;
use App\Services\WeatherPredictionService;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class IntegracionSipracTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesTableSeeder::class);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function crearFinca(User $propietario, array $extra = []): Finca
    {
        return Finca::create(array_merge([
            'propietario_id'   => $propietario->id,
            'nombre_finca'     => 'Finca ' . fake()->word(),
            'vereda'           => 'Vereda Test',
            'municipio'        => 'Ubaté',
            'latitud'          => 5.312300,
            'longitud'         => -73.816700,
            'altitud_msnm'     => 2556,
            'area_hectareas'   => 10.5,
            'cultivo_principal' => 'Papa',
        ], $extra));
    }

    private function crearEstacion(Finca $finca, array $extra = []): Estacion
    {
        return Estacion::create(array_merge([
            'mac_address'      => fake()->unique()->macAddress(), // 17 chars
            'nombre_estacion'  => 'Estación ' . fake()->word(),
            'tipo_estacion'    => 'meteorologica_principal',
            'finca_id'         => $finca->id,
            'activo'           => true,
        ], $extra));
    }

    // ─────────────────────────────────────────────
    // CP-01  Acceso por rol
    // ─────────────────────────────────────────────
    /** @test */
    public function cp01_acceso_por_rol(): void
    {
        // CP-01
        $solicitante  = User::factory()->solicitante()->create();
        $agricultor   = User::factory()->agricultor()->create();
        $tecnico      = User::factory()->tecnico()->create();
        $administrador = User::factory()->administrador()->create();

        // Solicitante recibe 403 en /normalizacion, /fincas, /agricultor/empleados y /admin/dashboard
        $this->actingAs($solicitante)->get('/normalizacion')->assertStatus(403);
        $this->actingAs($solicitante)->get('/fincas')->assertStatus(403);
        $this->actingAs($solicitante)->get('/agricultor/empleados')->assertStatus(403);
        $this->actingAs($solicitante)->get('/admin/dashboard')->assertStatus(403);

        // Agricultor recibe 200 en /fincas y 403 en /admin/dashboard
        $this->actingAs($agricultor)->get('/fincas')->assertStatus(200);
        $this->actingAs($agricultor)->get('/admin/dashboard')->assertStatus(403);

        // Tecnico recibe 403 en /fincas
        $this->actingAs($tecnico)->get('/fincas')->assertStatus(403);

        // Administrador recibe 200 en /admin/dashboard
        $this->actingAs($administrador)->get('/admin/dashboard')->assertStatus(200);
    }

    // ─────────────────────────────────────────────
    // CP-02  Usuario inactivo
    // ─────────────────────────────────────────────
    /** @test */
    public function cp02_usuario_inactivo_no_se_autentica(): void
    {
        // CP-02
        $user = User::factory()->agricultor()->inactivo()->create([
            'password' => 'secreto123',
        ]);

        // POST /login should NOT authenticate an inactive user
        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'secreto123',
        ]);
        $this->assertGuest();

        // actingAs: GET /dashboard should NOT respond 200 (must redirect to login)
        $response = $this->actingAs($user)->get('/dashboard');
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    // ─────────────────────────────────────────────
    // CP-03  Estaciones ajenas
    // ─────────────────────────────────────────────
    /** @test */
    public function cp03_estaciones_ajenas(): void
    {
        // CP-03
        $agricultorA = User::factory()->agricultor()->create();
        $agricultorB = User::factory()->agricultor()->create();

        $fincaA = $this->crearFinca($agricultorA);
        $fincaB = $this->crearFinca($agricultorB);

        $estacionB = $this->crearEstacion($fincaB);

        // Agricultor A intenta editar estación de B → 403
        $this->actingAs($agricultorA)
            ->get("/estaciones/{$estacionB->id}/edit")
            ->assertStatus(403);

        // Agricultor A intenta actualizar estación de B → 403
        $this->actingAs($agricultorA)
            ->put("/estaciones/{$estacionB->id}", [
                'mac_address'     => $estacionB->mac_address,
                'nombre_estacion' => 'Hackeada',
                'tipo_estacion'   => 'meteorologica_principal',
                'finca_id'        => $fincaB->id,
            ])
            ->assertStatus(403);

        // Agricultor A intenta eliminar estación de B → 403
        $this->actingAs($agricultorA)
            ->delete("/estaciones/{$estacionB->id}")
            ->assertStatus(403);

        // Agricultor A actualiza su propia estación enviando finca_id de B → 403
        $estacionA = $this->crearEstacion($fincaA);

        $response = $this->actingAs($agricultorA)
            ->put("/estaciones/{$estacionA->id}", [
                'mac_address'     => $estacionA->mac_address,
                'nombre_estacion' => $estacionA->nombre_estacion,
                'tipo_estacion'   => 'meteorologica_principal',
                'finca_id'        => $fincaB->id,  // finca ajena!
            ]);

        $response->assertStatus(403);

        // La finca de la estación NO debe haber cambiado
        $this->assertEquals($fincaA->id, $estacionA->fresh()->finca_id);
    }

    // ─────────────────────────────────────────────
    // CP-04  Carga sin duplicados
    // ─────────────────────────────────────────────
    /** @test */
    public function cp04_carga_sin_duplicados(): void
    {
        // CP-04
        $agricultor = User::factory()->agricultor()->create();
        $finca      = $this->crearFinca($agricultor);
        $estacion   = $this->crearEstacion($finca);

        $csvFile = base_path('tests/fixtures/lecturas_5.csv');

        // Invoke private method importarCsv via Reflection
        $controller = new DataNormalizationController();
        $method = new ReflectionMethod($controller, 'importarCsv');
        $method->setAccessible(true);

        // First import
        $result1 = $method->invoke($controller, $csvFile, $estacion->id);
        $this->assertEquals(5, $result1);
        $this->assertEquals(5, Lectura::count());

        // Second import (same file) — should NOT duplicate
        $result2 = $method->invoke($controller, $csvFile, $estacion->id);
        $this->assertEquals(5, $result2);
        $this->assertEquals(5, Lectura::count());
    }

    // ─────────────────────────────────────────────
    // CP-05  Asistente sin fincas
    // ─────────────────────────────────────────────
    /** @test */
    public function cp05_asistente_sin_fincas(): void
    {
        // CP-05
        $agricultorSinFincas = User::factory()->agricultor()->create();

        // Otro agricultor con finca, estación y lecturas
        $otroAgricultor = User::factory()->agricultor()->create();
        $fincaOtro      = $this->crearFinca($otroAgricultor);
        $estacionOtro   = $this->crearEstacion($fincaOtro);

        for ($i = 0; $i < 8; $i++) {
            Lectura::create([
                'estacion_id'    => $estacionOtro->id,
                'fecha_lectura'  => now()->subHours($i),
                'temp_externa'   => 15.0 + $i,
                'humedad_externa' => 70,
            ]);
        }

        config(['services.gemini.key' => 'x']);

        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Hola, soy SIPRAC Asistente.']],
                    ],
                ]],
            ], 200),
        ]);

        $this->actingAs($agricultorSinFincas)
            ->postJson('/chatbot/message', ['message' => 'hola'])
            ->assertOk();

        // Verify the systemInstruction sent to Gemini does NOT contain forecast data from other farms
        Http::assertSent(function ($request) {
            $body = $request->data();
            $systemText = $body['systemInstruction']['parts'][0]['text'] ?? '';

            return ! str_contains($systemText, 'Estimación local de SIPRAC');
        });
    }

    // ─────────────────────────────────────────────
    // CP-06  Pronóstico del usuario
    // ─────────────────────────────────────────────
    /** @test */
    public function cp06_pronostico_del_usuario(): void
    {
        // CP-06
        $userA = User::factory()->agricultor()->create();
        $userB = User::factory()->agricultor()->create();

        $fincaA     = $this->crearFinca($userA);
        $fincaB     = $this->crearFinca($userB);
        $estacionA  = $this->crearEstacion($fincaA);
        $estacionB  = $this->crearEstacion($fincaB);

        // Estación A: 8 lecturas recientes, temp 4-6 °C
        for ($i = 0; $i < 8; $i++) {
            Lectura::create([
                'estacion_id'    => $estacionA->id,
                'fecha_lectura'  => now()->subHours($i),
                'temp_externa'   => fake()->randomFloat(1, 4, 6),
                'humedad_externa' => 85,
            ]);
        }

        // Estación B: 8 lecturas recientes, temp 18-20 °C
        for ($i = 0; $i < 8; $i++) {
            Lectura::create([
                'estacion_id'    => $estacionB->id,
                'fecha_lectura'  => now()->subHours($i),
                'temp_externa'   => fake()->randomFloat(1, 18, 20),
                'humedad_externa' => 60,
            ]);
        }

        // Call forecast filtering only by estacionA
        $service  = new WeatherPredictionService();
        $forecast = $service->forecast(3, [$estacionA->id]);

        $this->assertEquals(8, $forecast['data_points']);

        // All estimated temperatures should be < 10 (since only cold station data)
        foreach ($forecast['items'] as $item) {
            $this->assertLessThan(10, $item['temperature'],
                "Temperatura estimada {$item['temperature']} debería ser < 10 (solo estación fría).");
        }
    }
}
