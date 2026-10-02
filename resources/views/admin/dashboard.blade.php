{{-- SIPRAC - Panel de Administración del Sistema (Anexo N) --}}
@extends('dashboard.layout')
@section('page-title', 'Administración')
@section('navbar-title', 'Panel de Administración del Sistema')

@section('content')

{{-- ============================================ --}}
{{-- TARJETAS DE ESTADÍSTICAS DEL SISTEMA        --}}
{{-- ============================================ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card" style="border-left: 4px solid #10b981;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Total Fincas</div>
                    <div class="metric-value">{{ $stats['totalFincas'] }}</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-house-door me-1"></i> Registradas en el sistema
                    </div>
                </div>
                <div class="metric-icon" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669;">
                    <i class="bi bi-house-door-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card" style="border-left: 4px solid #3b82f6;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Estaciones</div>
                    <div class="metric-value">{{ $stats['totalEstaciones'] }}</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-check-circle me-1"></i> {{ $stats['estacionesActivas'] }} activas
                    </div>
                </div>
                <div class="metric-icon" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #3b82f6;">
                    <i class="bi bi-broadcast-pin"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card" style="border-left: 4px solid #8b5cf6;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Usuarios</div>
                    <div class="metric-value">{{ $stats['totalUsuarios'] }}</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-people me-1"></i>
                        {{ $stats['totalAgricultores'] }} agricultores, {{ $stats['totalTecnicos'] }} técnicos
                    </div>
                </div>
                <div class="metric-icon" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #8b5cf6;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card" style="border-left: 4px solid #f59e0b;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Solicitudes Pendientes</div>
                    <div class="metric-value">{{ $stats['solicitudesPendientes'] }}</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-person-check me-1"></i> {{ $stats['totalEmpleados'] }} empleados activos
                    </div>
                </div>
                <div class="metric-icon" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #f59e0b;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MENÚ DE MÓDULOS CON TARJETAS INTERACTIVAS   --}}
{{-- ============================================ --}}
<div class="section-label mb-3">
    <i class="bi bi-grid-3x3-gap-fill me-1"></i> Módulos del Sistema
</div>

<div class="row g-3 mb-4">
    {{-- Tarjeta: Gestión de Fincas --}}
    <div class="col-md-4">
        <a href="{{ route('fincas.index') }}" class="text-decoration-none">
            <div class="chart-card h-100" style="transition: var(--transition-smooth); cursor: pointer; border: 2px solid transparent;"
                 onmouseover="this.style.borderColor='#10b981'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--card-shadow-hover)'"
                 onmouseout="this.style.borderColor='transparent'; this.style.transform='translateY(0)'; this.style.boxShadow='var(--card-shadow)'">
                <div class="p-4 text-center">
                    <div class="mb-3 mx-auto" style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #d1fae5, #a7f3d0); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-house-door-fill" style="font-size: 1.8rem; color: #059669;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: #1e293b;">Gestión de Fincas</h5>
                    <p class="text-muted mb-2" style="font-size: 0.83rem;">
                        Registrar, editar y administrar las fincas del sistema con sus coordenadas y cultivos.
                    </p>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; font-size: 0.78rem; padding: 5px 12px; border-radius: 8px;">
                        {{ $stats['totalFincas'] }} fincas registradas
                    </span>
                </div>
            </div>
        </a>
    </div>

    {{-- Tarjeta: Gestión de Estaciones --}}
    <div class="col-md-4">
        <a href="{{ route('estaciones.index') }}" class="text-decoration-none">
            <div class="chart-card h-100" style="transition: var(--transition-smooth); cursor: pointer; border: 2px solid transparent;"
                 onmouseover="this.style.borderColor='#3b82f6'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--card-shadow-hover)'"
                 onmouseout="this.style.borderColor='transparent'; this.style.transform='translateY(0)'; this.style.boxShadow='var(--card-shadow)'">
                <div class="p-4 text-center">
                    <div class="mb-3 mx-auto" style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #dbeafe, #bfdbfe); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-broadcast-pin" style="font-size: 1.8rem; color: #3b82f6;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: #1e293b;">Estaciones Meteorológicas</h5>
                    <p class="text-muted mb-2" style="font-size: 0.83rem;">
                        Registrar y monitorear estaciones meteorológicas, mantenimiento y estado.
                    </p>
                    <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.78rem; padding: 5px 12px; border-radius: 8px;">
                        {{ $stats['estacionesActivas'] }} / {{ $stats['totalEstaciones'] }} activas
                    </span>
                </div>
            </div>
        </a>
    </div>

    {{-- Tarjeta: Gestión de Empleados --}}
    <div class="col-md-4">
        <a href="{{ route('agricultor.empleados.index') }}" class="text-decoration-none">
            <div class="chart-card h-100" style="transition: var(--transition-smooth); cursor: pointer; border: 2px solid transparent;"
                 onmouseover="this.style.borderColor='#8b5cf6'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--card-shadow-hover)'"
                 onmouseout="this.style.borderColor='transparent'; this.style.transform='translateY(0)'; this.style.boxShadow='var(--card-shadow)'">
                <div class="p-4 text-center">
                    <div class="mb-3 mx-auto" style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #ede9fe, #ddd6fe); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people-fill" style="font-size: 1.8rem; color: #8b5cf6;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: #1e293b;">Gestión de Empleados</h5>
                    <p class="text-muted mb-2" style="font-size: 0.83rem;">
                        Administrar empleados, asignar permisos por finca y controlar accesos.
                    </p>
                    <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; font-size: 0.78rem; padding: 5px 12px; border-radius: 8px;">
                        {{ $stats['totalEmpleados'] }} empleados activos
                    </span>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ============================================ --}}
{{-- ACCESOS RÁPIDOS ADICIONALES                  --}}
{{-- ============================================ --}}
<div class="row g-3">
    <div class="col-md-6">
        <a href="{{ route('normalizacion.index') }}" class="text-decoration-none">
            <div class="chart-card" style="transition: var(--transition-smooth); cursor: pointer; border: 2px solid transparent;"
                 onmouseover="this.style.borderColor='#06b6d4'; this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.borderColor='transparent'; this.style.transform='translateY(0)'">
                <div class="p-3 d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #cffafe, #a5f3fc); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi bi-lightning-fill" style="font-size: 1.3rem; color: #0891b2;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.9rem;">Preprocesamiento de Datos</h6>
                        <small class="text-muted">Normalizar, imputar y limpiar lecturas climáticas</small>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6">
        <a href="{{ route('dashboard.datos') }}" class="text-decoration-none">
            <div class="chart-card" style="transition: var(--transition-smooth); cursor: pointer; border: 2px solid transparent;"
                 onmouseover="this.style.borderColor='#ef4444'; this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.borderColor='transparent'; this.style.transform='translateY(0)'">
                <div class="p-3 d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #fee2e2, #fecaca); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi bi-graph-up-arrow" style="font-size: 1.3rem; color: #ef4444;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.9rem;">Datos Climáticos</h6>
                        <small class="text-muted">Explorar gráficas, lecturas y análisis climático</small>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </div>
            </div>
        </a>
    </div>
</div>

@endsection
