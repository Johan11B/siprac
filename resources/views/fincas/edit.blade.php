{{-- SIPRAC - Formulario Editar Finca (Anexo M) --}}
@extends('dashboard.layout')
@section('page-title', 'Editar Finca')
@section('navbar-title', 'Editar Finca')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-pencil-square text-warning me-1"></i>Editar: {{ $finca->nombre_finca }}</h6>
                <a href="{{ route('fincas.index') }}" class="chart-period-badge" style="text-decoration: none;">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
            <div class="p-4">
                {{-- Errores de validación --}}
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

                <form action="{{ route('fincas.update', $finca) }}" method="POST" id="formEditarFinca">
                    @csrf
                    @method('PUT')

                    {{-- Nombre de la finca --}}
                    <div class="mb-3">
                        <label for="nombre_finca" class="form-label fw-medium" style="font-size: 0.85rem;">
                            Nombre de la finca <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_finca" id="nombre_finca"
                               class="form-control @error('nombre_finca') is-invalid @enderror"
                               value="{{ old('nombre_finca', $finca->nombre_finca) }}"
                               maxlength="80" required>
                        @error('nombre_finca')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ubicación --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="vereda" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Vereda <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="vereda" id="vereda"
                                   class="form-control @error('vereda') is-invalid @enderror"
                                   value="{{ old('vereda', $finca->vereda) }}"
                                   maxlength="50" required>
                            @error('vereda')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="municipio" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Municipio <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="municipio" id="municipio"
                                   class="form-control @error('municipio') is-invalid @enderror"
                                   value="{{ old('municipio', $finca->municipio) }}"
                                   maxlength="50" required>
                            @error('municipio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Coordenadas GPS --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="latitud" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Latitud <small class="text-muted">(opcional)</small>
                            </label>
                            <input type="number" name="latitud" id="latitud"
                                   class="form-control @error('latitud') is-invalid @enderror"
                                   value="{{ old('latitud', $finca->latitud) }}"
                                   step="0.00000001" min="-90" max="90">
                            @error('latitud')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="longitud" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Longitud <small class="text-muted">(opcional)</small>
                            </label>
                            <input type="number" name="longitud" id="longitud"
                                   class="form-control @error('longitud') is-invalid @enderror"
                                   value="{{ old('longitud', $finca->longitud) }}"
                                   step="0.00000001" min="-180" max="180">
                            @error('longitud')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="altitud_msnm" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Altitud (msnm) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="altitud_msnm" id="altitud_msnm"
                                   class="form-control @error('altitud_msnm') is-invalid @enderror"
                                   value="{{ old('altitud_msnm', $finca->altitud_msnm) }}"
                                   min="-500" max="9000" required>
                            @error('altitud_msnm')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Área y Cultivo --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="area_hectareas" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Área (hectáreas) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="area_hectareas" id="area_hectareas"
                                   class="form-control @error('area_hectareas') is-invalid @enderror"
                                   value="{{ old('area_hectareas', $finca->area_hectareas) }}"
                                   step="0.01" min="0.01" max="99999" required>
                            @error('area_hectareas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="cultivo_principal" class="form-label fw-medium" style="font-size: 0.85rem;">
                                Cultivo principal <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="cultivo_principal" id="cultivo_principal"
                                   class="form-control @error('cultivo_principal') is-invalid @enderror"
                                   value="{{ old('cultivo_principal', $finca->cultivo_principal) }}"
                                   maxlength="50" required>
                            @error('cultivo_principal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Info adicional de la finca --}}
                    <div class="mb-4 p-3 rounded-2" style="background: #f8fafc; font-size: 0.8rem;">
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="text-muted">Propietario:</span>
                                <strong>{{ $finca->propietario->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Creada:</span>
                                <strong>{{ $finca->created_at->format('d/m/Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Botón de guardar --}}
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
