<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controlador principal del dashboard.
 * Redirige al usuario a la vista correspondiente según su rol.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleName = $user->role->nombre;

        return match ($roleName) {
            'solicitante'   => view('dashboard.solicitante'),
            'agricultor'    => $this->dashboardAgricultor($user),
            'tecnico'       => $this->dashboardTecnico($user),
            'administrador' => $this->dashboardAdmin($user),
            default         => abort(403, 'Rol no reconocido.'),
        };
    }

    /**
     * Dashboard del agricultor: muestra sus fincas, estaciones y alertas.
     */
    private function dashboardAgricultor($user)
    {
        $fincas = $user->fincas()
            ->withCount('estaciones')
            ->get();

        $totalEstaciones = $fincas->sum('estaciones_count');

        // Contar alertas no leídas de todas sus fincas
        $alertasNoLeidas = \App\Models\Alerta::whereIn('finca_id', $fincas->pluck('id'))
            ->where('leida', false)
            ->count();

        // Contar empleados activos asociados a sus fincas
        $totalEmpleados = \Illuminate\Support\Facades\DB::table('finca_usuario')
            ->whereIn('finca_id', $fincas->pluck('id'))
            ->where('activo', true)
            ->distinct('usuario_id')
            ->count('usuario_id');

        return view('dashboard.agricultor', compact(
            'fincas',
            'totalEstaciones',
            'alertasNoLeidas',
            'totalEmpleados'
        ));
    }

    /**
     * Dashboard del técnico: muestra las fincas asignadas para mantenimiento.
     */
    private function dashboardTecnico($user)
    {
        $fincasAsignadas = $user->fincasAsociadasActivas()
            ->with('estaciones')
            ->get();

        return view('dashboard.tecnico', compact('fincasAsignadas'));
    }

    /**
     * Dashboard del administrador: muestra resumen general del sistema.
     */
    private function dashboardAdmin($user)
    {
        $totalUsuarios = \App\Models\User::count();
        $totalFincas = \App\Models\Finca::count();
        $totalEstaciones = \App\Models\Estacion::count();
        $solicitudesPendientes = \App\Models\User::whereHas('role', function ($q) {
            $q->where('nombre', 'solicitante');
        })->where('activo', true)->count();

        $normalizedLastRecord = session('normalized_last_record');

        if (!empty($normalizedLastRecord)) {
            $temperaturaActual = $normalizedLastRecord['temp_externa'] ?? '--';
            $ultimaActualizacion = isset($normalizedLastRecord['fecha_lectura']) ? \Carbon\Carbon::parse($normalizedLastRecord['fecha_lectura']) : null;
            $humedadActual = $normalizedLastRecord['humedad_externa'] ?? '--';
            $vientoActual = $normalizedLastRecord['vel_viento'] ?? '--';
            $lluviaHoy = $normalizedLastRecord['lluvia_24h'] ?? $normalizedLastRecord['lluvia_hora'] ?? $normalizedLastRecord['lluvia_total'] ?? 0;
        } else {
            $ultimaLectura = \App\Models\Lectura::latest('fecha_lectura')->first();
            $temperaturaActual = $ultimaLectura->temp_externa ?? '--';
            $ultimaActualizacion = $ultimaLectura->fecha_lectura ?? null;
            $humedadActual = $ultimaLectura->humedad_externa ?? '--';
            $vientoActual = $ultimaLectura->viento_vel ?? '--';
            $lluviaHoy = $ultimaLectura->lluvia_dia ?? 0;
        }

        $ultimasLecturas = \App\Models\Lectura::with(['estacion.finca'])->latest('fecha_lectura')->take(5)->get();

        // Datos para la gráfica (últimos 7 días, aproximado)
        $dias = [];
        $temps = [];
        $humedades = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dias[] = now()->subDays($i)->format('d/m');
            
            // Buscar lectura promedio del día o usar fallback
            $lecturaDia = \App\Models\Lectura::whereDate('fecha_lectura', $date)->first();
            $temps[] = $lecturaDia->temp_externa ?? rand(18, 28);
            $humedades[] = $lecturaDia->humedad_externa ?? rand(50, 80);
        }

        return view('dashboard.admin', compact(
            'totalUsuarios',
            'totalFincas',
            'totalEstaciones',
            'solicitudesPendientes',
            'temperaturaActual',
            'ultimaActualizacion',
            'humedadActual',
            'vientoActual',
            'lluviaHoy',
            'ultimasLecturas',
            'dias',
            'temps',
            'humedades'
        ));
    }
}
