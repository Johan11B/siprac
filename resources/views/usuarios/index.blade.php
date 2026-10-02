{{-- SIPRAC - Vista para listar usuarios (Admin) --}}
@extends('dashboard.layout')
@section('page-title', 'Gestión de Usuarios')
@section('navbar-title', 'Gestión de Usuarios')
@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="section-label mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i>Todos los Usuarios
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-sm px-3 py-2" style="background: #2563eb; color: white; border-radius: 10px; font-size: 0.85rem;">
            <i class="bi bi-person-plus me-1"></i> Agregar Usuario
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px; font-size: 0.88rem;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px; font-size: 0.88rem;">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="content-card">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul text-primary me-2"></i>Directorio de Usuarios</h6>
            <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 0.78rem;">
                {{ $usuarios->total() }} usuario(s)
            </span>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #dbeafe, #bfdbfe); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-person-fill" style="color: #3b82f6; font-size: 0.9rem;"></i>
                                        </div>
                                        <strong style="font-size: 0.88rem;">{{ $user->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->telefono }}</td>
                                <td>
                                    @php
                                        $badgeColor = match($user->role->nombre) {
                                            'administrador' => 'bg-danger',
                                            'agricultor' => 'bg-success',
                                            'tecnico' => 'bg-warning text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }}" style="font-size: 0.7rem; text-transform: capitalize;">
                                        {{ $user->role->nombre }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->activo ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                                        {{ $user->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 3px 8px;" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('usuarios.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a {{ $user->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size: 0.75rem; padding: 3px 8px;" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary disabled" style="font-size: 0.75rem; padding: 3px 8px;" title="No puedes eliminarte a ti mismo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($usuarios->hasPages())
            <div class="d-flex justify-content-center p-3">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
@endsection
