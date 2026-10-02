{{-- SIPRAC - Formulario Crear Empleado --}}
@extends('dashboard.layout')
@section('page-title', 'Agregar Empleado')
@section('navbar-title', 'Agregar Empleado')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Nuevo Empleado</h6>
                    <a href="{{ route('agricultor.empleados.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
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

                    <form action="{{ route('agricultor.empleados.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium" style="font-size: 0.85rem;">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Ej: Juan Pérez" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label fw-medium" style="font-size: 0.85rem;">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror"
                                       value="{{ old('telefono') }}" placeholder="Ej: 3001234567" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium" style="font-size: 0.85rem;">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="empleado@ejemplo.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium" style="font-size: 0.85rem;">Contraseña temporal <span class="text-danger">*</span></label>
                            <input type="text" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                   value="{{ old('password', 'Siprac2026') }}" required minlength="8">
                            <div class="form-text" style="font-size: 0.75rem;"><i class="bi bi-info-circle me-1"></i>El empleado deberá cambiarla al iniciar sesión. Mínimo 8 caracteres.</div>
                        </div>

                        <hr class="my-4" style="border-color: #e2e8f0;">

                        <h6 class="fw-bold mb-3" style="color: #475569; font-size: 0.9rem;">Asignación de Finca y Permisos</h6>

                        <div class="mb-3">
                            <label for="finca_id" class="form-label fw-medium" style="font-size: 0.85rem;">Asignar a finca <span class="text-danger">*</span></label>
                            <select name="finca_id" id="finca_id" class="form-select @error('finca_id') is-invalid @enderror" required>
                                <option value="">Selecciona una finca</option>
                                @foreach($fincas as $finca)
                                    <option value="{{ $finca->id }}" {{ old('finca_id') == $finca->id ? 'selected' : '' }}>
                                        {{ $finca->nombre_finca }} — {{ $finca->vereda }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="nivel_permiso" class="form-label fw-medium" style="font-size: 0.85rem;">Nivel de permiso <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="form-check p-3 rounded" style="border: 1px solid #e2e8f0; background: #f8fafc;">
                                        <input class="form-check-input ms-1 mt-1" type="radio" name="nivel_permiso" id="nivel_1" value="1" {{ old('nivel_permiso', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2 d-block" for="nivel_1">
                                            <strong style="color: #0ea5e9;">Nivel 1 — Solo Lectura</strong>
                                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">El empleado solo podrá ver datos climáticos y gráficas.</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check p-3 rounded" style="border: 1px solid #e2e8f0; background: #f8fafc;">
                                        <input class="form-check-input ms-1 mt-1" type="radio" name="nivel_permiso" id="nivel_2" value="2" {{ old('nivel_permiso') == '2' ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2 d-block" for="nivel_2">
                                            <strong style="color: #f59e0b;">Nivel 2 — Alertas</strong>
                                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">Ver datos climáticos + recibir notificaciones de alertas.</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check p-3 rounded" style="border: 1px solid #e2e8f0; background: #f8fafc;">
                                        <input class="form-check-input ms-1 mt-1" type="radio" name="nivel_permiso" id="nivel_3" value="3" {{ old('nivel_permiso') == '3' ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2 d-block" for="nivel_3">
                                            <strong style="color: #ef4444;">Nivel 3 — Control Total</strong>
                                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">Alertas + capacidad de activar actuadores (ej. riego antihelada).</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 mt-2" style="background: #2563eb; color: white; border-radius: 10px; font-weight: 600;">
                            <i class="bi bi-person-check me-1"></i> Crear y Asignar Empleado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
