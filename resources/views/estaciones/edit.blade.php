{{-- SIPRAC - Formulario Editar Estación --}}
@extends('dashboard.layout')
@section('page-title', 'Editar Estación')
@section('navbar-title', 'Editar Estación')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="chart-card">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-pencil-square text-warning me-1"></i>Editar: {{ $estacione->nombre_estacion }}</h6>
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

                <form action="{{ route('estaciones.update', $estacione) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- MAC Address --}}
                    <div class="mb-3">
                        <label for="mac_address" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Dirección MAC <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="mac_address" id="mac_address"
                               class="form-control @error('mac_address') is-invalid @enderror"
                               value="{{ old('mac_address', $estacione->mac_address) }}"
                               maxlength="17" required>
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
                               value="{{ old('nombre_estacion', $estacione->nombre_estacion) }}"
                               maxlength="50" required>
                        @error('nombre_estacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo --}}
                    <div class="mb-3">
                        <label for="tipo_estacion" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Tipo de estación <span class="text-danger">*</span>
                        </label>
                        <select name="tipo_estacion" id="tipo_estacion"
                                class="form-select @error('tipo_estacion') is-invalid @enderror" required>
                            <option value="meteorologica_principal" {{ old('tipo_estacion', $estacione->tipo_estacion) == 'meteorologica_principal' ? 'selected' : '' }}>
                                Meteorológica Principal
                            </option>
                            <option value="meteorologica_secundaria" {{ old('tipo_estacion', $estacione->tipo_estacion) == 'meteorologica_secundaria' ? 'selected' : '' }}>
                                Meteorológica Secundaria
                            </option>
                            <option value="suelo" {{ old('tipo_estacion', $estacione->tipo_estacion) == 'suelo' ? 'selected' : '' }}>
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
                            Finca asignada <span class="text-danger">*</span>
                        </label>
                        <select name="finca_id" id="finca_id"
                                class="form-select @error('finca_id') is-invalid @enderror" required>
                            @foreach($fincas as $finca)
                                <option value="{{ $finca->id }}" {{ old('finca_id', $estacione->finca_id) == $finca->id ? 'selected' : '' }}>
                                    {{ $finca->nombre_finca }} — {{ $finca->municipio }}
                                </option>
                            @endforeach
                        </select>
                        @error('finca_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fechas --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="fecha_instalacion" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Fecha de instalación
                            </label>
                            <input type="date" name="fecha_instalacion" id="fecha_instalacion"
                                   class="form-control @error('fecha_instalacion') is-invalid @enderror"
                                   value="{{ old('fecha_instalacion', $estacione->fecha_instalacion?->format('Y-m-d')) }}">
                            @error('fecha_instalacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ultimo_mantenimiento" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Último mantenimiento
                            </label>
                            <input type="date" name="ultimo_mantenimiento" id="ultimo_mantenimiento"
                                   class="form-control @error('ultimo_mantenimiento') is-invalid @enderror"
                                   value="{{ old('ultimo_mantenimiento', $estacione->ultimo_mantenimiento?->format('Y-m-d')) }}">
                            @error('ultimo_mantenimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Estado activo --}}
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                                   {{ old('activo', $estacione->activo) ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="activo" style="font-size: 0.85rem;">
                                Estación activa
                            </label>
                        </div>
                    </div>

                    {{-- Info adicional --}}
                    <div class="mb-4 p-3 rounded-2" style="background: #f8fafc; font-size: 0.8rem;">
                        <div class="row g-2">
                            <div class="col-4">
                                <span class="text-muted">Batería:</span>
                                <strong>{{ $estacione->bateria_nivel !== null ? $estacione->bateria_nivel . '%' : 'N/A' }}</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted">Señal:</span>
                                <strong>{{ $estacione->senal_radio !== null ? $estacione->senal_radio . ' dBm' : 'N/A' }}</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted">Creada:</span>
                                <strong>{{ $estacione->created_at->format('d/m/Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 py-2"
                            style="background: #f59e0b; color: white; border-radius: 10px; font-weight: 600; font-size: 0.9rem;">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
