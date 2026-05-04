{{-- SIPRAC Dashboard - Normalización de Datos --}}
@extends('dashboard.layout')

@section('page-title', 'Normalización de Datos')
@section('navbar-title', 'Normalización de Datos')

@section('content')

<div class="section-label">Normalización de Datos Meteorológicos</div>

<!-- Descripción -->
<div class="chart-card mb-4">
    <div class="p-4">
        <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
                <i class="bi bi-info-circle text-info" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-2">¿Qué es la normalización de datos?</h6>
                <p class="text-muted mb-0">
                    Carga un archivo Excel con datos meteorológicos crudos de tu estación. El sistema normalizará automáticamente 
                    estos datos, calculando valores derivados como punto de rocío y sensación térmica. 
                    Luego podrás descargar el archivo con todos los datos normalizados.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de Estado -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Error en la validación:</strong>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Formulario de Carga -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-upload text-primary me-2"></i>Subir Archivo de Datos</h6>
            </div>
            <div class="p-4">
                <form action="{{ route('normalizacion.normalize') }}" method="POST" enctype="multipart/form-data" id="form-normalizacion">
                    @csrf

                    <!-- Zona de Drop -->
                    <div class="drop-zone mb-4" id="drop-zone">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <i class="bi bi-cloud-upload" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                            <p class="mb-1 fw-semibold">Arrastra un archivo aquí o haz clic para seleccionar</p>
                            <p class="text-muted small">Formatos: .xlsx, .xls, .csv | Máximo: 10MB</p>
                        </div>
                        <input type="file" name="file" id="file-input" class="d-none" accept=".xlsx,.xls,.csv">
                    </div>

                    <!-- Preview del archivo seleccionado -->
                    <div id="file-preview" class="mb-4 d-none">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <i class="bi bi-file-earmark-spreadsheet text-success" style="font-size: 2rem;"></i>
                            <div class="flex-grow-1">
                                <p class="mb-1 fw-semibold" id="file-name">archivo.xlsx</p>
                                <p class="text-muted small mb-0" id="file-size">0 KB</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-file">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btn-normalize" disabled>
                            <i class="bi bi-lightning-fill me-2"></i>Normalizar Datos
                        </button>
                        <span class="text-muted small d-flex align-items-center">
                            Selecciona un archivo para continuar
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel de Información -->
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-lightbulb text-warning me-2"></i>Recomendaciones</h6>
            </div>
            <div class="p-4">
                <div class="mb-4">
                    <h6 class="fw-semibold small mb-2">✓ Columnas Requeridas</h6>
                    <ul class="small text-muted mb-0" style="padding-left: 1.2rem;">
                        <li>Fecha y Hora</li>
                        <li>Temp. Externa</li>
                        <li>Humedad Externa</li>
                        <li>Velocidad del Viento</li>
                    </ul>
                </div>
                <div class="mb-4">
                    <h6 class="fw-semibold small mb-2">✓ Columnas Opcionales</h6>
                    <ul class="small text-muted mb-0" style="padding-left: 1.2rem;">
                        <li>Temp. Interna</li>
                        <li>Humedad Interna</li>
                        <li>Presión</li>
                        <li>Ráfaga de Viento</li>
                        <li>Dirección del Viento</li>
                        <li>Lluvia</li>
                    </ul>
                </div>
                <div class="p-3 bg-info bg-opacity-10 rounded">
                    <p class="small mb-0">
                        <i class="bi bi-info-circle text-info me-1"></i>
                        El sistema calculará automáticamente el punto de rocío y la sensación térmica.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Normalización (si existe) -->
@if(session('normalization_summary'))
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="chart-card">
                <div class="card-header-custom">
                    <h6 class="mb-0"><i class="bi bi-graph-up text-success me-2"></i>Resumen de Normalización</h6>
                    <span class="chart-period-badge">Completado</span>
                </div>
                <div class="p-4">
                    <div class="row g-3 mb-4">
                        <!-- Cards de Resumen -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="summary-card">
                                <div class="summary-label">Total de Registros</div>
                                <div class="summary-value">{{ session('normalization_summary.total_registros') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="summary-card">
                                <div class="summary-label">Temp. Promedio</div>
                                <div class="summary-value">{{ session('normalization_summary.temp_promedio') }}°C</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="summary-card">
                                <div class="summary-label">Humedad Promedio</div>
                                <div class="summary-value">{{ session('normalization_summary.humedad_promedio') }}%</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="summary-card">
                                <div class="summary-label">Lluvia Total</div>
                                <div class="summary-value">{{ session('normalization_summary.lluvia_total') }} m</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla Detallada de Resumen -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Métrica</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Fecha de Inicio</td>
                                    <td class="text-end">{{ \Carbon\Carbon::parse(session('normalization_summary.fecha_inicio'))->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Fecha de Fin</td>
                                    <td class="text-end">{{ \Carbon\Carbon::parse(session('normalization_summary.fecha_fin'))->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Temperatura Mínima</td>
                                    <td class="text-end">{{ session('normalization_summary.temp_minima') }}°C</td>
                                </tr>
                                <tr>
                                    <td>Temperatura Máxima</td>
                                    <td class="text-end">{{ session('normalization_summary.temp_maxima') }}°C</td>
                                </tr>
                                <tr>
                                    <td>Velocidad del Viento Promedio</td>
                                    <td class="text-end">{{ session('normalization_summary.vel_viento_promedio') }} m/s</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Botón de Descarga -->
                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('normalizacion.download') }}" class="btn btn-success">
                            <i class="bi bi-download me-2"></i>Descargar Datos Normalizados
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Normalizar Otro Archivo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<style>
    .drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #f8fafc;
    }

    .drop-zone:hover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }

    .drop-zone.drag-over {
        border-color: #10b981;
        background-color: #f0fdf4;
    }

    .summary-card {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #3b82f6;
    }

    .summary-label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .summary-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1e293b;
    }
</style>

<script>
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const btnNormalize = document.getElementById('btn-normalize');
    const btnRemoveFile = document.getElementById('btn-remove-file');

    // Click para seleccionar archivo
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            updateFilePreview();
        }
    });

    // Cambio en input de archivo
    fileInput.addEventListener('change', updateFilePreview);

    function updateFilePreview() {
        const file = fileInput.files[0];
        if (file) {
            document.getElementById('file-name').textContent = file.name;
            const sizeKB = (file.size / 1024).toFixed(1);
            document.getElementById('file-size').textContent = `${sizeKB} KB`;
            filePreview.classList.remove('d-none');
            btnNormalize.disabled = false;
        }
    }

    btnRemoveFile.addEventListener('click', () => {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        btnNormalize.disabled = true;
    });

    // Prevenir envío duplicado
    document.getElementById('form-normalizacion').addEventListener('submit', function() {
        btnNormalize.disabled = true;
        btnNormalize.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
    });
</script>
@endsection
