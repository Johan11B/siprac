<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\Finca;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Panel de Administración del Sistema (Anexo N).
 *
 * Centraliza la navegación administrativa con tarjetas hacia
 * los módulos de Fincas, Estaciones y Empleados.
 */
class AdminDashboardController extends Controller
{
    /**
     * Vista principal del panel administrativo con estadísticas
     * y accesos rápidos a los módulos del sistema.
     */
    public function index()
    {
        $stats = [
            'totalFincas'      => Finca::count(),
            'totalEstaciones'  => Estacion::count(),
            'totalUsuarios'    => User::count(),
            'totalAgricultores' => User::whereHas('role', fn ($q) => $q->where('nombre', 'agricultor'))->count(),
            'totalTecnicos'    => User::whereHas('role', fn ($q) => $q->where('nombre', 'tecnico'))->count(),
            'totalEmpleados'   => \Illuminate\Support\Facades\DB::table('finca_usuario')
                ->where('activo', true)
                ->distinct('usuario_id')
                ->count('usuario_id'),
            'estacionesActivas' => Estacion::where('activo', true)->count(),
            'solicitudesPendientes' => User::whereHas('role', fn ($q) => $q->where('nombre', 'solicitante'))
                ->where('activo', true)
                ->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
