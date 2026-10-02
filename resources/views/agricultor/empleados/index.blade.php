{{-- SIPRAC - Listado de Empleados del Agricultor --}}
@extends('dashboard.layout')
@section('page-title', 'Mis Empleados')
@section('navbar-title', 'Gestión de Empleados')
@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="section-label mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i>Empleados por Finca
        </div>
        <a href="{{ route('agricultor.empleados.create') }}" class="btn btn-sm px-3 py-2" style="background: #2563eb; color: white; border-radius: 10px; font-size: 0.85rem;">
            <i class="bi bi-person-plus me-1"></i> Agregar Empleado
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px; font-size: 0.88rem;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @forelse($fincas as $finca)
        <div class="chart-card mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-house-door text-success me-2"></i>{{ $finca->nombre_finca }}</h6>
                <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 0.78rem;">
                    {{ $finca->usuarios->count() }} empleado(s)
                </span>
            </div>
            <div class="p-3">
                @if($finca->usuarios->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Permiso</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($finca->usuarios as $emp)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #dbeafe, #bfdbfe); display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-person-fill" style="color: #3b82f6; font-size: 0.9rem;"></i>
                                                </div>
                                                <strong style="font-size: 0.88rem;">{{ $emp->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $emp->telefono }}</td>
                                        <td>{{ $emp->email }}</td>
                                        <td>
                                            @php $nivel = $emp->pivot->nivel_permiso; @endphp
                                            <span class="badge {{ $nivel == 3 ? 'bg-danger' : ($nivel == 2 ? 'bg-warning text-dark' : 'bg-info text-dark') }}" style="font-size: 0.7rem;">
                                                {{ $nivel == 3 ? 'Control' : ($nivel == 2 ? 'Alertas' : 'Solo ver') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $emp->pivot->activo ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                                                {{ $emp->pivot->activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td>
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
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-person-slash fs-2 d-block mb-2"></i>
                        No hay empleados asignados a esta finca.
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="chart-card">
            <div class="p-5 text-center">
                <i class="bi bi-house-add fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold text-dark">No tienes fincas registradas</h5>
                <p class="text-muted">Necesitas tener fincas para poder gestionar empleados.</p>
                <a href="{{ route('fincas.create') }}" class="btn mt-2" style="background: #10b981; color: white; border-radius: 10px;">
                    <i class="bi bi-plus-circle me-1"></i> Crear mi primera finca
                </a>
            </div>
        </div>
    @endforelse
@endsection
