<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Estacion;
use App\Models\Finca;
use App\Models\Lectura;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleName = $user->role->nombre;

        return match ($roleName) {
            'solicitante' => view('dashboard.solicitante'),
            'agricultor' => $this->dashboardAgricultor($user),
            'tecnico' => $this->dashboardTecnico($user),
            'administrador' => $this->dashboardAdmin($user),
            default => abort(403, 'Rol no reconocido.'),
        };
    }

    public function datos(Request $request)
    {
        $user = $request->user();
        $estacionesQuery = Estacion::with('finca')->orderBy('nombre_estacion');

        if ($user->esAgricultor() && ! $user->esAdmin()) {
            $estacionesQuery->whereIn('finca_id', $user->fincas()->pluck('id'));
        } elseif ($user->esTecnico() && ! $user->esAdmin()) {
            $estacionesQuery->whereIn('finca_id', $user->fincasAsociadasActivas()->pluck('fincas.id'));
        }

        $estaciones = $estacionesQuery->get();
        $estacionId = $request->integer('estacion_id') ?: $estaciones->first()?->id;
        $periodo = $request->get('periodo', 'semana');

        $desde = match ($periodo) {
            '24h' => now()->subDay(),
            'mes' => now()->subMonth(),
            default => now()->subDays(7),
        };

        $lecturasQuery = Lectura::with(['estacion.finca'])->orderByDesc('fecha_lectura');
        if ($estacionId) {
            $lecturasQuery->where('estacion_id', $estacionId);
        } elseif ($estaciones->isNotEmpty()) {
            $lecturasQuery->whereIn('estacion_id', $estaciones->pluck('id'));
        }

        $ultimasLecturas = (clone $lecturasQuery)->take(20)->get();
        $periodoLecturas = (clone $lecturasQuery)->where('fecha_lectura', '>=', $desde)->get();

        $tempMax = $periodoLecturas->max('temp_externa');
        $tempMin = $periodoLecturas->min('temp_externa');
        $humedadPromedio = $periodoLecturas->avg('humedad_externa');
        $lluviaAcumulada = $periodoLecturas->max('lluvia_dia');

        $dias = [];
        $temps = [];
        $humedades = [];
        $lluvias = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dias[] = $date->format('d/m');
            $delDia = Lectura::query()
                ->when($estacionId, fn ($q) => $q->where('estacion_id', $estacionId))
                ->when(! $estacionId && $estaciones->isNotEmpty(), fn ($q) => $q->whereIn('estacion_id', $estaciones->pluck('id')))
                ->whereDate('fecha_lectura', $date->toDateString())
                ->selectRaw('AVG(temp_externa) as t, AVG(humedad_externa) as h, MAX(lluvia_dia) as l')
                ->first();
            $temps[] = $delDia?->t !== null ? round((float) $delDia->t, 1) : null;
            $humedades[] = $delDia?->h !== null ? round((float) $delDia->h, 1) : null;
            $lluvias[] = $delDia?->l !== null ? round((float) $delDia->l, 1) : 0;
        }

        $estacionNombre = $estaciones->firstWhere('id', $estacionId)?->nombre_estacion ?? 'Todas las estaciones';

        return view('dashboard.datos', compact(
            'estaciones',
            'estacionId',
            'periodo',
            'ultimasLecturas',
            'tempMax',
            'tempMin',
            'humedadPromedio',
            'lluviaAcumulada',
            'dias',
            'temps',
            'humedades',
            'lluvias',
            'estacionNombre'
        ));
    }

    private function dashboardAgricultor($user)
    {
        $fincas = $user->fincas()->withCount('estaciones')->get();
        $totalEstaciones = $fincas->sum('estaciones_count');
        $alertasNoLeidas = Alerta::whereIn('finca_id', $fincas->pluck('id'))->where('leida', false)->count();
        $totalEmpleados = DB::table('finca_usuario')
            ->whereIn('finca_id', $fincas->pluck('id'))
            ->where('activo', true)
            ->distinct('usuario_id')
            ->count('usuario_id');

        $estacionIds = Estacion::whereIn('finca_id', $fincas->pluck('id'))->pluck('id');
        $ultimaLectura = Lectura::whereIn('estacion_id', $estacionIds)->latest('fecha_lectura')->first();
        $clima = $this->metricasDesdeLectura($ultimaLectura);

        $labels24h = [];
        $temps24h = [];
        if ($estacionIds->isNotEmpty()) {
            $desde = now()->subDay();
            $series = Lectura::whereIn('estacion_id', $estacionIds)
                ->where('fecha_lectura', '>=', $desde)
                ->orderBy('fecha_lectura')
                ->get(['fecha_lectura', 'temp_externa']);

            if ($series->isEmpty()) {
                $series = Lectura::whereIn('estacion_id', $estacionIds)
                    ->latest('fecha_lectura')
                    ->take(24)
                    ->get(['fecha_lectura', 'temp_externa'])
                    ->reverse();
            }

            foreach ($series as $lectura) {
                $labels24h[] = $lectura->fecha_lectura?->format('H:i');
                $temps24h[] = $lectura->temp_externa;
            }
        }

        return view('dashboard.agricultor', compact(
            'fincas',
            'totalEstaciones',
            'alertasNoLeidas',
            'totalEmpleados',
            'clima',
            'labels24h',
            'temps24h'
        ));
    }

    private function dashboardTecnico($user)
    {
        $fincasAsignadas = $user->fincasAsociadasActivas()->with(['estaciones.ultimaLectura'])->get();

        return view('dashboard.tecnico', compact('fincasAsignadas'));
    }

    private function dashboardAdmin($user)
    {
        $totalUsuarios = User::count();
        $totalFincas = Finca::count();
        $totalEstaciones = Estacion::count();
        $solicitudesPendientes = User::whereHas('role', fn ($q) => $q->where('nombre', 'solicitante'))
            ->where('activo', true)
            ->count();

        $ultimaLectura = Lectura::latest('fecha_lectura')->first();
        $clima = $this->metricasDesdeLectura($ultimaLectura);

        $ultimasLecturas = Lectura::with(['estacion.finca'])->latest('fecha_lectura')->take(8)->get();

        $dias = [];
        $temps = [];
        $humedades = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dias[] = $date->format('d/m');
            $aggs = Lectura::whereDate('fecha_lectura', $date->toDateString())
                ->selectRaw('AVG(temp_externa) as t, AVG(humedad_externa) as h')
                ->first();
            $temps[] = $aggs?->t !== null ? round((float) $aggs->t, 1) : null;
            $humedades[] = $aggs?->h !== null ? round((float) $aggs->h, 1) : null;
        }

        return view('dashboard.admin', compact(
            'totalUsuarios',
            'totalFincas',
            'totalEstaciones',
            'solicitudesPendientes',
            'clima',
            'ultimasLecturas',
            'dias',
            'temps',
            'humedades'
        ));
    }

    private function metricasDesdeLectura(?Lectura $lectura): array
    {
        $sessionRecord = session('normalized_last_record');
        if ($lectura) {
            return [
                'temperatura' => $lectura->temp_externa,
                'humedad' => $lectura->humedad_externa,
                'viento' => $lectura->viento_vel,
                'lluvia' => $lectura->lluvia_dia ?? 0,
                'actualizado' => $lectura->fecha_lectura,
            ];
        }

        if (! empty($sessionRecord)) {
            return [
                'temperatura' => $sessionRecord['temp_externa'] ?? null,
                'humedad' => $sessionRecord['humedad_externa'] ?? null,
                'viento' => $sessionRecord['viento_vel'] ?? $sessionRecord['vel_viento'] ?? null,
                'lluvia' => $sessionRecord['lluvia_dia'] ?? $sessionRecord['lluvia_24h'] ?? 0,
                'actualizado' => isset($sessionRecord['fecha_lectura']) ? \Carbon\Carbon::parse($sessionRecord['fecha_lectura']) : null,
            ];
        }

        return [
            'temperatura' => null,
            'humedad' => null,
            'viento' => null,
            'lluvia' => 0,
            'actualizado' => null,
        ];
    }
}
