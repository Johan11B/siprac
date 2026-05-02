{{-- SIPRAC - Lista de Empleados del Agricultor --}}
@extends('dashboard.layout')
@section('page-title', 'Mis Empleados')
@section('navbar-title', 'Gestión de Empleados')
@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="section-label mb-0">Empleados por Finca</div>
        <a href="{{ route('agricultor.empleados.create') }}" class="btn btn-sm px-3 py-2" style="background: #2563eb; color: white; border-radius: 10px; font-size: 0.85rem;">
            <i class="bi bi-person-plus me-1"></i> Agregar Empleado
        </a>
    </div>

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px; font-size: 0.88rem;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @forelse($fincas as $finca)
        <div class="chart-card mb-3">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-house-door text-success me-1"></i>{{ $finca->nombre_finca }}</h6>
                <span class="chart-period-badge">{{ $finca->usuarios->count() }} empleado(s)</span>
            </div>
            <div class="p-3">
                @if($finca->usuarios->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th style="border: 0; padding: 10px 12px; color: #64748b; font-weight: 600;">Nombre</th>
                                    <th style="border: 0; padding: 10px 12px; color: #64748b; font-weight: 600;">Teléfono</th>
                                    <th style="border: 0; padding: 10px 12px; color: #64748b; font-weight: 600;">Email</th>
                                    <th style="border: 0; padding: 10px 12px; color: #64748b; font-weight: 600;">Permiso</th>
                                    <th style="border: 0; padding: 10px 12px; color: #64748b; font-weight: 600;">Estado</th>
                                    <th style="border: 0; padding: 10px 12px; color: #64748b; font-weight: 600;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($finca->usuarios as $emp)
                                    <tr>
                                        <td style="padding: 10px 12px; vertical-align: middle;">
                                            <i class="bi bi-person-circle text-muted me-1"></i>{{ $emp->name }}
                                        </td>
                                        <td style="padding: 10px 12px; vertical-align: middle;">{{ $emp->telefono }}</td>
                                        <td style="padding: 10px 12px; vertical-align: middle;">{{ $emp->email }}</td>
                                        <td style="padding: 10px 12px; vertical-align: middle;">
                                            @php $nivel = $emp->pivot->nivel_permiso; @endphp
                                            <span class="badge {{ $nivel == 3 ? 'bg-danger' : ($nivel == 2 ? 'bg-warning text-dark' : 'bg-info') }}" style="font-size: 0.7rem;">
                                                {{ $nivel == 3 ? 'Control' : ($nivel == 2 ? 'Alertas' : 'Solo ver') }}
                                            </span>
                                        </td>
                                        <td style="padding: 10px 12px; vertical-align: middle;">
                                            <span class="badge {{ $emp->pivot->activo ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                                                {{ $emp->pivot->activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td style="padding: 10px 12px; vertical-align: middle;">
                                            <div class="d-flex gap-1">
                                                {{-- Toggle activo/inactivo --}}
                                                <form action="{{ route('agricultor.empleados.update', [$finca, $emp]) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="nivel_permiso" value="{{ $emp->pivot->nivel_permiso }}">
                                                    <input type="hidden" name="activo" value="{{ $emp->pivot->activo ? '0' : '1' }}">
                                                    <button type="submit" class="btn btn-sm {{ $emp->pivot->activo ? 'btn-outline-secondary' : 'btn-outline-success' }}" style="font-size: 0.75rem; padding: 3px 8px;" title="{{ $emp->pivot->activo ? 'Desactivar' : 'Activar' }}">
                                                        <i class="bi {{ $emp->pivot->activo ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                    </button>
                                                </form>
                                                {{-- Eliminar asociación --}}
                                                <form action="{{ route('agricultor.empleados.destroy', [$finca, $emp]) }}" method="POST" onsubmit="return confirm('¿Remover a {{ $emp->name }} de {{ $finca->nombre_finca }}?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size: 0.75rem; padding: 3px 8px;" title="Remover">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3 text-muted" style="font-size: 0.85rem;">
                        <i class="bi bi-person-slash me-1"></i> Sin empleados asignados a esta finca.
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="chart-card">
            <div class="p-4 text-center">
                <i class="bi bi-house-add fs-1 text-muted"></i>
                <h6 class="fw-bold mt-2">No tienes fincas registradas</h6>
                <p class="text-muted small">Necesitas tener fincas para poder gestionar empleados.</p>
            </div>
        </div>
    @endforelse
@endsection
