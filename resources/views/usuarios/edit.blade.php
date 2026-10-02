{{-- SIPRAC - Editar Usuario --}}
@extends('dashboard.layout')
@section('page-title', 'Editar Usuario')
@section('navbar-title', 'Editar Usuario')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editar Usuario: {{ $usuario->name }}</h6>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
                <div class="p-4">
                    @if($errors->any())
                        <div class="alert alert-danger" style="border-radius: 10px; font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i> Corrige los siguientes errores:
                            <ul class="mb-0 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium" style="font-size: 0.85rem;">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $usuario->name) }}" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium" style="font-size: 0.85rem;">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $usuario->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label fw-medium" style="font-size: 0.85rem;">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror"
                                       value="{{ old('telefono', $usuario->telefono) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium" style="font-size: 0.85rem;">Nueva Contraseña <small class="text-muted">(Opcional)</small></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                   minlength="8" placeholder="Déjalo en blanco para mantener la actual">
                        </div>

                        <hr class="my-4" style="border-color: #e2e8f0;">

                        <div class="mb-3">
                            <label for="role_id" class="form-label fw-medium" style="font-size: 0.85rem;">Asignar Rol <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ old('role_id', $usuario->role_id) == $rol->id ? 'selected' : '' }}>
                                        {{ ucfirst($rol->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="activo" style="font-size: 0.85rem;">Usuario activo</label>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 mt-2" style="background: #f59e0b; color: white; border-radius: 10px; font-weight: 600;">
                            <i class="bi bi-save me-1"></i> Actualizar Usuario
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
