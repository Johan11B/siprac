{{-- SIPRAC Dashboard - Vista Administrador con Datos Reales --}}
@extends('dashboard.layout')

@section('page-title', 'Panel Administrador')
@section('navbar-title', 'Panel de Administración')

@section('content')

{{-- ============================================ --}}
{{-- TARJETAS DE ESTADÍSTICAS CLIMÁTICAS --}}
{{-- ============================================ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card temp">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Temperatura Actual</div>
                    <div class="metric-value">{{ is_numeric($temperaturaActual) ? number_format($temperaturaActual, 1) : $temperaturaActual }}°C</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-clock me-1"></i> Actualizado: {{ $ultimaActualizacion instanceof \DateTime ? $ultimaActualizacion->format('H:i') : 'Sin datos' }}
                    </div>
                </div>
                <div class="metric-icon temp"><i class="bi bi-thermometer-half"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card humidity">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Humedad Actual</div>
                    <div class="metric-value">{{ is_numeric($humedadActual) ? $humedadActual : '--' }}%</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-droplet me-1"></i> Relativa
                    </div>
                </div>
                <div class="metric-icon humidity"><i class="bi bi-droplet-half"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card wind">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Viento Actual</div>
                    <div class="metric-value">{{ is_numeric($vientoActual) ? number_format($vientoActual, 1) : $vientoActual }} m/s</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-wind me-1"></i> Velocidad
                    </div>
                </div>
                <div class="metric-icon wind"><i class="bi bi-wind"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card rain">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="metric-label">Lluvia Hoy</div>
                    <div class="metric-value">{{ number_format($lluviaHoy, 1) }} mm</div>
                    <div class="metric-trend" style="color: #64748b;">
                        <i class="bi bi-cloud-rain me-1"></i> Acumulado
                    </div>
                </div>
                <div class="metric-icon rain"><i class="bi bi-cloud-rain-heavy"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- GRÁFICA DE TEMPERATURA Y HUMEDAD (7 DÍAS) --}}
{{-- ============================================ --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="chart-card">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Temperatura y Humedad - Últimos 7 días</h6>
                <span class="chart-period-badge">Semanal</span>
            </div>
            <div style="position: relative; height: 320px;">
                <canvas id="tempHumChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Botón de Normalización de Datos --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <a href="{{ route('normalizacion.index') }}" class="btn btn-lg btn-outline-primary w-100 py-3" style="border-width: 2px;">
            <i class="bi bi-lightning-fill me-2"></i>
            <span class="fw-semibold">Normalización de Datos</span>
            <small class="d-block mt-1" style="font-weight: 400;">Carga datos crudos y obtén un Excel normalizado</small>
        </a>
    </div>
</div>

{{-- ============================================ --}}
{{-- TABLA DE ÚLTIMAS LECTURAS --}}
{{-- ============================================ --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header-custom">
                <h6 class="mb-0"><i class="bi bi-table me-2"></i>Últimas Lecturas Registradas</h6>
                <span class="chart-period-badge">En tiempo real</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Estación</th>
                            <th>Finca</th>
                            <th>Temperatura</th>
                            <th>Humedad</th>
                            <th>Viento</th>
                            <th>Lluvia 24h</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasLecturas as $lectura)
                        <tr>
                            <td><i class="bi bi-clock me-1 text-muted"></i>{{ $lectura->fecha_lectura->format('d/m/Y H:i') }}</td>
                            <td>{{ $lectura->estacion->nombre_estacion ?? 'N/A' }}</td>
                            <td>{{ $lectura->estacion->finca->nombre_finca ?? 'N/A' }}</td>
                            <td><strong>{{ number_format($lectura->temp_externa, 1) }}°C</strong></td>
                            <td>{{ $lectura->humedad_externa ?? '--' }}%</td>
                            <td>{{ number_format($lectura->viento_vel ?? 0, 1) }} m/s</td>
                            <td>{{ number_format($lectura->lluvia_dia ?? 0, 1) }} mm</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-database-slash fs-3"></i><br>
                                No hay lecturas registradas. Importa los datos con: php artisan import:lecturas 1
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- ESTADÍSTICAS DEL SISTEMA --}}
{{-- ============================================ --}}
<div class="row g-3">
    <div class="col-md-3 col-6">
        <div class="summary-mini">
            <div class="mini-icon" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #3b82f6;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="mini-value">{{ $totalUsuarios }}</div>
            <div class="mini-label">Usuarios</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-mini">
            <div class="mini-icon" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #10b981;">
                <i class="bi bi-house-door-fill"></i>
            </div>
            <div class="mini-value">{{ $totalFincas }}</div>
            <div class="mini-label">Fincas</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-mini">
            <div class="mini-icon" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #f59e0b;">
                <i class="bi bi-broadcast-pin"></i>
            </div>
            <div class="mini-value">{{ $totalEstaciones }}</div>
            <div class="mini-label">Estaciones</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-mini">
            <div class="mini-icon" style="background: linear-gradient(135deg, #fef2f2, #fecaca); color: #ef4444;">
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <div class="mini-value">{{ $solicitudesPendientes }}</div>
            <div class="mini-label">Pendientes</div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Gráfica de temperatura y humedad
    const ctx = document.getElementById('tempHumChart').getContext('2d');
    
    const labels = @json($dias);
    const temps = @json($temps);
    const humedades = @json($humedades);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Temperatura (°C)',
                    data: temps,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#ef4444',
                    yAxisID: 'y'
                },
                {
                    label: 'Humedad (%)',
                    data: humedades,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#fff',
                    cornerRadius: 8,
                    padding: 10
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Temperatura (°C)',
                        color: '#ef4444'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '°C';
                        }
                    }
                },
                y1: {
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Humedad (%)',
                        color: '#3b82f6'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
</script>
@endsection