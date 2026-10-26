{{-- ============================================
    SIPRAC Dashboard - Datos Climáticos
    ============================================ --}}
@extends('dashboard.layout')

@section('page-title', 'Datos Climáticos')
@section('navbar-title', 'Datos Climáticos')

@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endsection

@section('content')

    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Datos Climáticos</h2>
        <p>Historial y análisis de las condiciones meteorológicas registradas por las estaciones.</p>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('dashboard.datos') }}" id="form-filtros">
        <div class="filter-bar">
            <div class="d-flex align-items-center gap-2 me-auto">
                <i class="bi bi-funnel text-muted"></i>
                <span style="font-size: 0.82rem; font-weight: 600; color: #334155;">Filtros:</span>
            </div>

            <select class="form-select" id="filter-estacion" name="estacion_id" aria-label="Filtrar por estación">
                <option value="">Todas las estaciones</option>
                @foreach ($estaciones as $est)
                    <option value="{{ $est->id }}" {{ $estacionId == $est->id ? 'selected' : '' }}>
                        {{ $est->nombre_estacion }}
                    </option>
                @endforeach
            </select>

            <select class="form-select" id="filter-periodo" name="periodo" aria-label="Filtrar por período">
                <option value="24h"    {{ $periodo === '24h'    ? 'selected' : '' }}>Últimas 24 horas</option>
                <option value="semana" {{ $periodo === 'semana' ? 'selected' : '' }}>Última semana</option>
                <option value="mes"   {{ $periodo === 'mes'    ? 'selected' : '' }}>Último mes</option>
            </select>

            <button class="btn btn-filter" type="submit" id="btn-aplicar-filtro">
                <i class="bi bi-search me-1"></i> Aplicar
            </button>
        </div>
    </form>

    <!-- Summary Mini Cards -->
    <div class="section-label">Resumen del Período</div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #3b82f6;">
                    <i class="bi bi-thermometer-high"></i>
                </div>
                <div class="mini-value" id="val-temp-max">
                    {{ $tempMax !== null ? number_format($tempMax, 1) . '°C' : '—' }}
                </div>
                <div class="mini-label">Temp. Máxima</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #cffafe, #a5f3fc); color: #06b6d4;">
                    <i class="bi bi-thermometer-low"></i>
                </div>
                <div class="mini-value" id="val-temp-min">
                    {{ $tempMin !== null ? number_format($tempMin, 1) . '°C' : '—' }}
                </div>
                <div class="mini-label">Temp. Mínima</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #10b981;">
                    <i class="bi bi-moisture"></i>
                </div>
                <div class="mini-value" id="val-humedad">
                    {{ $humedadPromedio !== null ? number_format($humedadPromedio, 1) . '%' : '—' }}
                </div>
                <div class="mini-label">Humedad Promedio</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #8b5cf6;">
                    <i class="bi bi-cloud-rain-heavy"></i>
                </div>
                <div class="mini-value" id="val-lluvia">
                    {{ $lluviaAcumulada !== null ? number_format($lluviaAcumulada, 1) . ' mm' : '—' }}
                </div>
                <div class="mini-label">Lluvia Acumulada</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="card-header-custom">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart-line text-primary"></i>
                        Temperatura y Humedad — Últimos 7 días
                    </h6>
                    <span class="chart-period-badge">Semanal</span>
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="weeklyChart" aria-label="Grafica de temperatura y humedad semanal" role="img"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="card-header-custom">
                    <h6 class="mb-0">
                        <i class="bi bi-cloud-rain text-info"></i>
                        Precipitación Diaria
                    </h6>
                    <span class="chart-period-badge">7 días</span>
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="rainChart" aria-label="Grafica de precipitacion diaria" role="img"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="section-label">Registros Detallados</div>
    <div class="content-card">
        <div class="card-header-custom">
            <h6>
                <i class="bi bi-table text-muted"></i>
                Últimos Registros — {{ $estacionNombre }}
            </h6>
            <a href="{{ route('dashboard.datos', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="btn btn-outline-secondary btn-sm"
               style="border-radius: 8px; font-size: 0.78rem;"
               id="btn-exportar-csv">
                <i class="bi bi-download me-1"></i> Exportar CSV
            </a>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="tabla-datos">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Temperatura</th>
                        <th>Humedad</th>
                        <th>Viento</th>
                        <th>Precipitación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimasLecturas as $lectura)
                        @php
                            $estado     = $lectura->estadoClima();
                            $badgeClass = match ($estado) {
                                'Helada'     => 'offline',
                                'Precaución' => 'warning',
                                'Atípico'    => 'warning',
                                default      => 'online',
                            };
                        @endphp
                        <tr>
                            <td>
                                <i class="bi bi-clock me-1 text-muted"></i>
                                {{ $lectura->fecha_lectura?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td>
                                <strong>{{ $lectura->temp_externa !== null ? number_format($lectura->temp_externa, 1) . '°C' : '—' }}</strong>
                            </td>
                            <td>{{ $lectura->humedad_externa !== null ? $lectura->humedad_externa . '%' : '—' }}</td>
                            <td>{{ $lectura->viento_vel !== null ? number_format($lectura->viento_vel, 1) . ' km/h' : '—' }}</td>
                            <td>{{ $lectura->lluvia_dia !== null ? number_format($lectura->lluvia_dia, 1) . ' mm' : '0.0 mm' }}</td>
                            <td>
                                <span class="status-badge {{ $badgeClass }}">
                                    <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                    {{ $estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox me-2"></i>No hay registros disponibles para el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($ultimasLecturas, 'links'))
            <div class="d-flex justify-content-center mt-3">
                {{ $ultimasLecturas->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection

@section('scripts')
<script>
    const chartLabels = {!! json_encode($dias) !!};
    const chartTemps  = {!! json_encode($temps) !!};
    const chartHums   = {!! json_encode($humedades) !!};
    const chartRains  = {!! json_encode($lluvias) !!};

    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');

    const gradientTemp = weeklyCtx.createLinearGradient(0, 0, 0, 300);
    gradientTemp.addColorStop(0, 'rgba(59, 130, 246, 0.12)');
    gradientTemp.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    const gradientHum = weeklyCtx.createLinearGradient(0, 0, 0, 300);
    gradientHum.addColorStop(0, 'rgba(16, 185, 129, 0.12)');
    gradientHum.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Temperatura (°C)',
                    data: chartTemps,
                    borderColor: '#3b82f6',
                    backgroundColor: gradientTemp,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    spanGaps: true,
                    yAxisID: 'y',
                },
                {
                    label: 'Humedad (%)',
                    data: chartHums,
                    borderColor: '#10b981',
                    backgroundColor: gradientHum,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    spanGaps: true,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11, family: 'Inter' }, usePointStyle: true, padding: 20 }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#fff',
                    cornerRadius: 10,
                    padding: 12,
                    displayColors: true,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8' },
                    border: { display: false },
                },
                y: {
                    position: 'left',
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 11, family: 'Inter' }, color: '#3b82f6', callback: v => v + '°C' },
                    border: { display: false },
                },
                y1: {
                    position: 'right',
                    grid: { display: false },
                    ticks: { font: { size: 11, family: 'Inter' }, color: '#10b981', callback: v => v + '%' },
                    border: { display: false },
                }
            }
        }
    });

    const rainCtx = document.getElementById('rainChart').getContext('2d');
    const maxRain = Math.max(...chartRains.map(v => v ?? 0), 0.01);
    const rainColors = chartRains.map(v => {
        const alpha = v !== null ? 0.3 + 0.6 * (v / maxRain) : 0.2;
        return 'rgba(6, 182, 212, ' + alpha.toFixed(2) + ')';
    });

    new Chart(rainCtx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Precipitación (mm)',
                data: chartRains,
                backgroundColor: rainColors,
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 28,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    cornerRadius: 10,
                    padding: 10,
                    callbacks: { label: (item) => (item.parsed.y ?? 0) + ' mm' }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8' },
                    border: { display: false },
                },
                y: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8', callback: v => v + ' mm' },
                    border: { display: false },
                    beginAtZero: true,
                }
            }
        }
    });
</script>
@endsection
