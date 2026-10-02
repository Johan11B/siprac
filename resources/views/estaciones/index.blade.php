{{-- SIPRAC - Listado de Estaciones Meteorológicas --}}
@extends('dashboard.layout')
@section('page-title', 'Estaciones')
@section('navbar-title', 'Gestión de Estaciones')

@section('content')

{{-- Encabezado --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="section-label mb-0">
        <i class="bi bi-broadcast-pin text-primary me-1"></i>
        @if(auth()->user()->esAdmin())
            Todas las Estaciones
        @else
            Mis Estaciones
        @endif
    </div>
    <a href="{{ route('estaciones.create') }}" class="btn btn-sm px-3 py-2"
       style="background: #3b82f6; color: white; border-radius: 10px; font-size: 0.85rem; font-weight: 600;">
        <i class="bi bi-plus-circle me-1"></i> Nueva Estación
    </a>
</div>

{{-- Mensajes --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert"
         style="border-radius: 10px; font-size: 0.88rem; border: 1px solid #a7f3d0; background: #ecfdf5;">
        <i class="bi bi-check-circle-fill me-1 text-success"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Tabla de estaciones --}}
<div class="content-card">
    <div class="card-header-custom">
        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Registro de Estaciones</h6>
        <span class="chart-period-badge">{{ $estaciones->total() }} estación(es)</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>MAC Address</th>
                    <th>Tipo</th>
                    <th>Finca</th>
                    <th>Instalación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estaciones as $estacion)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #dbeafe, #bfdbfe); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-broadcast" style="color: #3b82f6; font-size: 0.85rem;"></i>
                            </div>
                            <strong style="font-size: 0.88rem;">{{ $estacion->nombre_estacion }}</strong>
                        </div>
                    </td>
                    <td><code style="font-size: 0.78rem;">{{ $estacion->mac_address }}</code></td>
                    <td>
                        @php
                            $tipoLabel = match($estacion->tipo_estacion) {
                                'meteorologica_principal'   => ['Principal', 'bg-primary'],
                                'meteorologica_secundaria'  => ['Secundaria', 'bg-info'],
                                'suelo'                     => ['Suelo', 'bg-warning text-dark'],
                                default                     => [$estacion->tipo_estacion, 'bg-secondary'],
                            };
                        @endphp
                        <span class="badge {{ $tipoLabel[1] }}" style="font-size: 0.7rem;">{{ $tipoLabel[0] }}</span>
                    </td>
                    <td>
                        <small>{{ $estacion->finca->nombre_finca ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <small class="text-muted">
                            {{ $estacion->fecha_instalacion ? $estacion->fecha_instalacion->format('d/m/Y') : '—' }}
                        </small>
                    </td>
                    <td>
                        <span class="badge {{ $estacion->activo ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                            {{ $estacion->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('estaciones.edit', $estacion) }}" class="btn btn-sm btn-outline-primary"
                               style="font-size: 0.75rem; padding: 3px 8px;" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('estaciones.destroy', $estacion) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar la estación «{{ $estacion->nombre_estacion }}»? Se borrarán todas sus lecturas.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        style="font-size: 0.75rem; padding: 3px 8px;" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-broadcast fs-2 d-block mb-2"></i>
                        No hay estaciones registradas.
                        <a href="{{ route('estaciones.create') }}" class="text-decoration-none fw-medium"> Registra tu primera estación</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($estaciones->hasPages())
        <div class="d-flex justify-content-center p-3">
            {{ $estaciones->links() }}
        </div>
    @endif
</div>

@endsection
