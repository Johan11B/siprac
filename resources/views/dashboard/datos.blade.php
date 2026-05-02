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
    <div class="filter-bar">
        <div class="d-flex align-items-center gap-2 me-auto">
            <i class="bi bi-funnel text-muted"></i>
            <span style="font-size: 0.82rem; font-weight: 600; color: #334155;">Filtros:</span>
        </div>
        <select class="form-select" id="filter-estacion" aria-label="Filtrar por estación">
            <option selected>Todas las estaciones</option>
            <option>Estación Ubaté Centro</option>
            <option>Estación Laguna Suesca</option>
            <option>Estación Cucunubá</option>
        </select>
        <select class="form-select" id="filter-periodo" aria-label="Filtrar por período">
            <option>Últimas 24 horas</option>
            <option selected>Última semana</option>
            <option>Último mes</option>
        </select>
        <button class="btn btn-filter" type="button">
            <i class="bi bi-search me-1"></i> Aplicar
        </button>
    </div>

    <!-- Summary Mini Cards -->
    <div class="section-label">Resumen del Período</div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #3b82f6;">
                    <i class="bi bi-thermometer-high"></i>
                </div>
                <div class="mini-value">18.2°C</div>
                <div class="mini-label">Temp. Máxima</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #cffafe, #a5f3fc); color: #06b6d4;">
                    <i class="bi bi-thermometer-low"></i>
                </div>
                <div class="mini-value">2.1°C</div>
                <div class="mini-label">Temp. Mínima</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #10b981;">
                    <i class="bi bi-moisture"></i>
                </div>
                <div class="mini-value">82%</div>
                <div class="mini-label">Humedad Promedio</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #8b5cf6;">
                    <i class="bi bi-cloud-rain-heavy"></i>
                </div>
                <div class="mini-value">14.7 mm</div>
                <div class="mini-label">Lluvia Acumulada</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Temp + Humidity Chart -->
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
                    <canvas id="weeklyChart" aria-label="Gráfica de temperatura y humedad semanal" role="img"></canvas>
                </div>
            </div>
        </div>

        <!-- Wind Rose / Precipitation -->
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
                    <canvas id="rainChart" aria-label="Gráfica de precipitación diaria" role="img"></canvas>
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
                Últimos Registros — Estación Ubaté Centro
            </h6>
            <button class="btn btn-outline-secondary btn-sm" style="border-radius: 8px; font-size: 0.78rem;">
                <i class="bi bi-download me-1"></i> Exportar CSV
            </button>
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
                    <tr>
                        <td><i class="bi bi-clock me-1 text-muted"></i>08/04/2026 22:00</td>
                        <td><strong>9.5°C</strong></td>
                        <td>85%</td>
                        <td>12 km/h</td>
                        <td>0.0 mm</td>
                        <td><span class="status-badge online"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Normal</span></td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-clock me-1 text-muted"></i>08/04/2026 21:00</td>
                        <td><strong>10.2°C</strong></td>
                        <td>82%</td>
                        <td>14 km/h</td>
                        <td>0.2 mm</td>
                        <td><span class="status-badge online"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Normal</span></td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-clock me-1 text-muted"></i>08/04/2026 20:00</td>
                        <td><strong>11.0°C</strong></td>
                        <td>78%</td>
                        <td>16 km/h</td>
                        <td>0.5 mm</td>
                        <td><span class="status-badge online"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Normal</span></td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-clock me-1 text-muted"></i>08/04/2026 19:00</td>
                        <td><strong>12.3°C</strong></td>
                        <td>75%</td>
                        <td>18 km/h</td>
                        <td>1.0 mm</td>
                        <td><span class="status-badge online"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Normal</span></td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-clock me-1 text-muted"></i>08/04/2026 18:00</td>
                        <td><strong>13.5°C</strong></td>
                        <td>72%</td>
                        <td>20 km/h</td>
                        <td>0.8 mm</td>
                        <td><span class="status-badge online"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Normal</span></td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-clock me-1 text-muted"></i>08/04/2026 05:00</td>
                        <td><strong>3.2°C</strong></td>
                        <td>95%</td>
                        <td>5 km/h</td>
                        <td>0.0 mm</td>
                        <td><span class="status-badge warning"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Precaución</span></td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-clock me-1 text-muted"></i>08/04/2026 04:00</td>
                        <td><strong>1.8°C</strong></td>
                        <td>98%</td>
                        <td>3 km/h</td>
                        <td>0.0 mm</td>
                        <td><span class="status-badge offline"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Helada</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // ============================================
    // Weekly Temperature + Humidity Chart
    // ============================================
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyDays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    const gradientTemp = weeklyCtx.createLinearGradient(0, 0, 0, 300);
    gradientTemp.addColorStop(0, 'rgba(59, 130, 246, 0.12)');
    gradientTemp.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    const gradientHum = weeklyCtx.createLinearGradient(0, 0, 0, 300);
    gradientHum.addColorStop(0, 'rgba(16, 185, 129, 0.12)');
    gradientHum.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: weeklyDays,
            datasets: [
                {
                    label: 'Temperatura (°C)',
                    data: [11.2, 10.5, 12.8, 14.0, 9.3, 13.1, 12.5],
                    borderColor: '#3b82f6',
                    backgroundColor: gradientTemp,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y',
                },
                {
                    label: 'Humedad (%)',
                    data: [78, 82, 75, 70, 88, 74, 80],
                    borderColor: '#10b981',
                    backgroundColor: gradientHum,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
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

    // ============================================
    // Daily Precipitation Chart
    // ============================================
    const rainCtx = document.getElementById('rainChart').getContext('2d');

    new Chart(rainCtx, {
        type: 'bar',
        data: {
            labels: weeklyDays,
            datasets: [{
                label: 'Precipitación (mm)',
                data: [2.4, 0.8, 5.1, 0.0, 3.7, 1.2, 1.5],
                backgroundColor: [
                    'rgba(6, 182, 212, 0.7)',
                    'rgba(6, 182, 212, 0.5)',
                    'rgba(6, 182, 212, 0.9)',
                    'rgba(6, 182, 212, 0.2)',
                    'rgba(6, 182, 212, 0.8)',
                    'rgba(6, 182, 212, 0.5)',
                    'rgba(6, 182, 212, 0.6)',
                ],
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
                    callbacks: { label: (item) => `${item.parsed.y} mm` }
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
