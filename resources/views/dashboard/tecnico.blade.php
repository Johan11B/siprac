{{-- SIPRAC Dashboard - Vista Técnico --}}
@extends('dashboard.layout')
@section('page-title', 'Panel Técnico')
@section('navbar-title', 'Panel Técnico')
@section('content')
    <div class="section-label">Fincas Asignadas para Mantenimiento</div>
    <div class="row g-3 mb-4">
        @forelse($fincasAsignadas as $finca)
            <div class="col-md-6 col-xl-4">
                <div class="chart-card h-100">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="bi bi-tools text-primary me-1"></i>{{ $finca->nombre_finca }}</h6>
                        <span class="chart-period-badge">{{ $finca->estaciones->count() }} estación(es)</span>
                    </div>
                    <div class="p-3">
                        <div class="d-flex flex-column gap-2" style="font-size: 0.85rem;">
                            <div><i class="bi bi-geo-alt text-muted me-2"></i>{{ $finca->vereda }}, {{ $finca->municipio }}</div>
                            <div><i class="bi bi-person text-muted me-2"></i>Propietario: {{ $finca->propietario->name }}</div>
                        </div>
                        @if($finca->estaciones->count() > 0)
                        <hr class="my-2">
                        <div class="small fw-bold text-muted mb-1">Estaciones:</div>
                        @foreach($finca->estaciones as $est)
                            <div class="d-flex align-items-center gap-2 mb-1" style="font-size: 0.8rem;">
                                <span class="badge {{ $est->activo ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.65rem;">
                                    {{ $est->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                                <span>{{ $est->nombre_estacion }}</span>
                                @if($est->bateria_nivel !== null)
                                    <span class="text-muted ms-auto">
                                        <i class="bi bi-battery-{{ $est->bateria_nivel > 50 ? 'full' : ($est->bateria_nivel > 20 ? 'half' : 'charging') }}"></i>
                                        {{ $est->bateria_nivel }}%
                                    </span>
                                @endif
                            </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="chart-card">
                    <div class="p-4 text-center">
                        <i class="bi bi-clipboard-check fs-1 text-muted"></i>
                        <h6 class="fw-bold mt-2">No tienes fincas asignadas</h6>
                        <p class="text-muted small">El administrador te asignará fincas para mantenimiento de estaciones.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
