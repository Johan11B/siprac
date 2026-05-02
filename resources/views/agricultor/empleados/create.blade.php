{{-- SIPRAC - Formulario Crear Empleado --}}
@extends('dashboard.layout')
@section('page-title', 'Agregar Empleado')
@section('navbar-title', 'Agregar Empleado')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="card-header-custom">
                    <h6 class="mb-0"><i class="bi bi-person-plus text-primary me-1"></i>Nuevo Empleado</h6>
                    <a href="{{ route('agricultor.empleados.index') }}" class="chart-period-badge" style="text-decoration: none;">
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
                        {{-- Nombre --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium" style="font-size: 0.85rem;">Nombre completo</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Nombre del empleado" required>
                        </div>

                        {{-- Teléfono --}}
                        <div class="mb-3">
                            <label for="telefono" class="form-label fw-medium" style="font-size: 0.85rem;">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror"
                                   value="{{ old('telefono') }}" placeholder="3001234567" required>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium" style="font-size: 0.85rem;">Correo electrónico</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="empleado@ejemplo.com" required>
                        </div>

                        {{-- Contraseña temporal --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium" style="font-size: 0.85rem;">Contraseña temporal</label>
                            <input type="text" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                   value="{{ old('password', 'Siprac2026') }}" placeholder="Mínimo 8 caracteres" required>
                            <div class="form-text" style="font-size: 0.75rem;">El empleado deberá cambiar esta contraseña al iniciar sesión.</div>
                        </div>

                        {{-- Finca --}}
                        <div class="mb-3">
                            <label for="finca_id" class="form-label fw-medium" style="font-size: 0.85rem;">Asignar a finca</label>
                            <select name="finca_id" id="finca_id" class="form-select @error('finca_id') is-invalid @enderror" required>
                                <option value="">Selecciona una finca</option>
                                @foreach($fincas as $finca)
                                    <option value="{{ $finca->id }}" {{ old('finca_id') == $finca->id ? 'selected' : '' }}>
                                        {{ $finca->nombre_finca }} — {{ $finca->vereda }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nivel de permiso --}}
                        <div class="mb-4">
                            <label for="nivel_permiso" class="form-label fw-medium" style="font-size: 0.85rem;">Nivel de permiso</label>
                            <select name="nivel_permiso" id="nivel_permiso" class="form-select @error('nivel_permiso') is-invalid @enderror" required>
                                <option value="1" {{ old('nivel_permiso', '1') == '1' ? 'selected' : '' }}>
                                    Nivel 1 — Solo ver (datos climáticos, gráficas)
                                </option>
                                <option value="2" {{ old('nivel_permiso') == '2' ? 'selected' : '' }}>
                                    Nivel 2 — Alertas (nivel 1 + recibir alertas)
                                </option>
                                <option value="3" {{ old('nivel_permiso') == '3' ? 'selected' : '' }}>
                                    Nivel 3 — Control (nivel 2 + activar riego/drenaje)
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn w-100 py-2" style="background: #2563eb; color: white; border-radius: 10px; font-weight: 600;">
                            <i class="bi bi-person-check me-1"></i> Crear y Asignar Empleado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
