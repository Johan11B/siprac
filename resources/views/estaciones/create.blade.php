{{-- SIPRAC - Formulario Crear Estación --}}
@extends('dashboard.layout')
@section('page-title', 'Nueva Estación')
@section('navbar-title', 'Registrar Estación')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="chart-card">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-broadcast text-primary me-1"></i>Nueva Estación Meteorológica</h6>
                <a href="{{ route('estaciones.index') }}" class="chart-period-badge" style="text-decoration: none;">
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

                <form action="{{ route('estaciones.store') }}" method="POST">
                    @csrf

                    {{-- MAC Address --}}
                    <div class="mb-3">
                        <label for="mac_address" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Dirección MAC <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="mac_address" id="mac_address"
                               class="form-control @error('mac_address') is-invalid @enderror"
                               value="{{ old('mac_address') }}" placeholder="AA:BB:CC:DD:EE:FF"
                               maxlength="17" required>
                        <div class="form-text" style="font-size: 0.75rem;">Formato: AA:BB:CC:DD:EE:FF (17 caracteres)</div>
                        @error('mac_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="nombre_estacion" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Nombre de la estación <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_estacion" id="nombre_estacion"
                               class="form-control @error('nombre_estacion') is-invalid @enderror"
                               value="{{ old('nombre_estacion') }}" placeholder="Ej: Estación Norte - Finca La Esperanza"
                               maxlength="50" required>
                        @error('nombre_estacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo de estación --}}
                    <div class="mb-3">
                        <label for="tipo_estacion" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Tipo de estación <span class="text-danger">*</span>
                        </label>
                        <select name="tipo_estacion" id="tipo_estacion"
                                class="form-select @error('tipo_estacion') is-invalid @enderror" required>
                            <option value="">Selecciona un tipo</option>
                            <option value="meteorologica_principal" {{ old('tipo_estacion') == 'meteorologica_principal' ? 'selected' : '' }}>
                                Meteorológica Principal
                            </option>
                            <option value="meteorologica_secundaria" {{ old('tipo_estacion') == 'meteorologica_secundaria' ? 'selected' : '' }}>
                                Meteorológica Secundaria
                            </option>
                            <option value="suelo" {{ old('tipo_estacion') == 'suelo' ? 'selected' : '' }}>
                                Sensor de Suelo
                            </option>
                        </select>
                        @error('tipo_estacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Finca --}}
                    <div class="mb-3">
                        <label for="finca_id" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Asignar a finca <span class="text-danger">*</span>
                        </label>
                        <select name="finca_id" id="finca_id"
                                class="form-select @error('finca_id') is-invalid @enderror" required>
                            <option value="">Selecciona una finca</option>
                            @foreach($fincas as $finca)
                                <option value="{{ $finca->id }}" {{ old('finca_id') == $finca->id ? 'selected' : '' }}>
                                    {{ $finca->nombre_finca }} — {{ $finca->municipio }}
                                </option>
                            @endforeach
                        </select>
                        @error('finca_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha de instalación --}}
                    <div class="mb-4">
                        <label for="fecha_instalacion" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Fecha de instalación <small class="text-muted">(opcional)</small>
                        </label>
                        <input type="date" name="fecha_instalacion" id="fecha_instalacion"
                               class="form-control @error('fecha_instalacion') is-invalid @enderror"
                               value="{{ old('fecha_instalacion') }}">
                        @error('fecha_instalacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn w-100 py-2"
                            style="background: #3b82f6; color: white; border-radius: 10px; font-weight: 600; font-size: 0.9rem;">
                        <i class="bi bi-broadcast me-1"></i> Registrar Estación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
