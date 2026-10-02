{{-- SIPRAC - Listado de Fincas (Anexo M) --}}
@extends('dashboard.layout')
@section('page-title', 'Mis Fincas')
@section('navbar-title', 'Gestión de Fincas')

@section('content')

{{-- Encabezado con botón de crear --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="section-label mb-0">
        <i class="bi bi-house-door-fill text-success me-1"></i>
        @if(auth()->user()->esAdmin())
            Todas las Fincas del Sistema
        @else
            Mis Fincas
        @endif
    </div>
    <a href="{{ route('fincas.create') }}" class="btn btn-sm px-3 py-2"
       style="background: #10b981; color: white; border-radius: 10px; font-size: 0.85rem; font-weight: 600;">
        <i class="bi bi-plus-circle me-1"></i> Nueva Finca
    </a>
</div>

{{-- Mensaje de éxito --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert"
         style="border-radius: 10px; font-size: 0.88rem; border: 1px solid #a7f3d0; background: #ecfdf5;">
        <i class="bi bi-check-circle-fill me-1 text-success"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Tabla de fincas --}}
<div class="content-card">
    <div class="card-header-custom">
        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Registro de Fincas</h6>
        <span class="chart-period-badge">{{ $fincas->total() }} finca(s)</span>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tableFincas">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Vereda</th>
                    <th>Municipio</th>
                    <th>Altitud</th>
                    <th>Área (ha)</th>
                    <th>Cultivo</th>
                    <th>Estaciones</th>
                    @if(auth()->user()->esAdmin())
                        <th>Propietario</th>
                    @endif
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fincas as $finca)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #d1fae5, #a7f3d0); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-tree-fill" style="color: #059669; font-size: 0.9rem;"></i>
                            </div>
                            <strong style="font-size: 0.88rem;">{{ $finca->nombre_finca }}</strong>
                        </div>
                    </td>
                    <td>{{ $finca->vereda }}</td>
                    <td>{{ $finca->municipio }}</td>
                    <td>
                        <span style="font-weight: 500;">{{ number_format($finca->altitud_msnm) }}</span>
                        <small class="text-muted">msnm</small>
                    </td>
                    <td>{{ number_format($finca->area_hectareas, 2) }}</td>
                    <td>
                        <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 0.72rem; padding: 4px 8px; border-radius: 6px;">
                            {{ $finca->cultivo_principal }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $finca->estaciones_count > 0 ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.72rem;">
                            {{ $finca->estaciones_count }}
                        </span>
                    </td>
                    @if(auth()->user()->esAdmin())
                        <td>
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>{{ $finca->propietario->name ?? 'N/A' }}
                            </small>
                        </td>
                    @endif
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('fincas.edit', $finca) }}" class="btn btn-sm btn-outline-primary"
                               style="font-size: 0.75rem; padding: 3px 8px;" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('fincas.destroy', $finca) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar la finca «{{ $finca->nombre_finca }}»? Se borrarán sus estaciones, lecturas y asociaciones.')">
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
                    <td colspan="{{ auth()->user()->esAdmin() ? 9 : 8 }}" class="text-center text-muted py-4">
                        <i class="bi bi-house-add fs-2 d-block mb-2"></i>
                        No hay fincas registradas.
                        <a href="{{ route('fincas.create') }}" class="text-decoration-none fw-medium"> Registra tu primera finca</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Paginación --}}
    @if($fincas->hasPages())
        <div class="d-flex justify-content-center p-3">
            {{ $fincas->links() }}
        </div>
    @endif
</div>

{{-- Resumen de coordenadas (si hay fincas con latitud/longitud) --}}
@php $conCoordenadas = $fincas->filter(fn ($f) => $f->latitud && $f->longitud); @endphp
@if($conCoordenadas->isNotEmpty())
<div class="chart-card mt-3">
    <div class="card-header-custom">
        <h6 class="mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Coordenadas Registradas</h6>
        <span class="chart-period-badge">{{ $conCoordenadas->count() }} con GPS</span>
    </div>
    <div class="p-3">
        <div class="row g-2">
            @foreach($conCoordenadas as $f)
                <div class="col-md-4 col-sm-6">
                    <div class="p-2 rounded-2" style="background: #f8fafc; font-size: 0.8rem;">
                        <strong>{{ $f->nombre_finca }}</strong><br>
                        <small class="text-muted">
                            <i class="bi bi-geo me-1"></i>{{ $f->latitud }}, {{ $f->longitud }}
                        </small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection
