{{-- ============================================
    SIPRAC Dashboard - Alertas
    ============================================ --}}
@extends('dashboard.layout')

@section('page-title', 'Alertas')
@section('navbar-title', 'Centro de Alertas')

@section('content')

    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-bell-fill me-2" style="color: #f59e0b;"></i>Centro de Alertas</h2>
        <p>Gestiona y revisa las alertas climáticas generadas por el sistema de monitoreo.</p>
    </div>

    <!-- Alert Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #fef2f2, #fecaca); color: #ef4444;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="mini-value" style="color: #ef4444;">1</div>
                <div class="mini-label">Críticas</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #fffbeb, #fde68a); color: #f59e0b;">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div class="mini-value" style="color: #f59e0b;">2</div>
                <div class="mini-label">Advertencias</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #eff6ff, #bfdbfe); color: #3b82f6;">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div class="mini-value" style="color: #3b82f6;">5</div>
                <div class="mini-label">Informativas</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="summary-mini">
                <div class="mini-icon" style="background: linear-gradient(135deg, #ecfdf5, #a7f3d0); color: #10b981;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="mini-value" style="color: #10b981;">12</div>
                <div class="mini-label">Resueltas (mes)</div>
            </div>
        </div>
    </div>

    <!-- Active Alerts -->
    <div class="section-label">Alertas Activas</div>

    <!-- Critical Alert Highlight -->
    <div class="frost-alert danger mb-4" role="alert">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="frost-badge">
                <i class="bi bi-exclamation-triangle-fill"></i>
                CRÍTICA
            </div>
            <span style="font-size: 0.75rem; opacity: 0.8;">
                <i class="bi bi-clock me-1"></i> Hace 12 min
            </span>
        </div>
        <h5 class="fw-bold mb-1">
            <i class="bi bi-snow me-1"></i> Helada Inminente — Estación Ubaté Centro
        </h5>
        <p class="mb-2 small" style="opacity: 0.85;">
            La temperatura ha descendido a <strong>1°C</strong> y se prevé que alcance <strong>-1°C</strong> en las próximas 2 horas.
            Se recomienda activar sistemas de protección de cultivos inmediatamente.
        </p>
        <div class="d-flex align-items-center gap-3 flex-wrap" style="font-size: 0.78rem; opacity: 0.8;">
            <div><i class="bi bi-geo-alt me-1"></i> Ubaté, Cundinamarca</div>
            <div><i class="bi bi-thermometer-low me-1"></i> 1°C actual</div>
            <div><i class="bi bi-moisture me-1"></i> 98% humedad</div>
        </div>
    </div>

    <!-- Alert List -->
    <div class="content-card mb-4">
        <div class="card-header-custom">
            <h6>
                <i class="bi bi-list-check text-muted"></i>
                Historial de Alertas Recientes
            </h6>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.78rem; width: auto;">
                    <option>Todas</option>
                    <option>Críticas</option>
                    <option>Advertencias</option>
                    <option>Informativas</option>
                </select>
            </div>
        </div>

        <!-- Alert Items -->
        <div class="alert-item">
            <div class="alert-icon critical">
                <i class="bi bi-snow2"></i>
            </div>
            <div class="alert-content flex-grow-1">
                <h6>Riesgo de helada — Temperatura bajo umbral</h6>
                <p>La Estación Ubaté Centro registró 1°C a las 04:00 AM. Pronóstico: -1°C.</p>
            </div>
            <div class="alert-time">Hace 12 min</div>
        </div>

        <div class="alert-item">
            <div class="alert-icon warning">
                <i class="bi bi-thermometer-low"></i>
            </div>
            <div class="alert-content flex-grow-1">
                <h6>Temperatura en descenso rápido</h6>
                <p>La temperatura bajó 4°C en las últimas 2 horas en la Estación Laguna Suesca.</p>
            </div>
            <div class="alert-time">Hace 45 min</div>
        </div>

        <div class="alert-item">
            <div class="alert-icon warning">
                <i class="bi bi-moisture"></i>
            </div>
            <div class="alert-content flex-grow-1">
                <h6>Humedad relativa alta — 96%</h6>
                <p>Condiciones favorables para formación de escarcha en zona agrícola de Cucunubá.</p>
            </div>
            <div class="alert-time">Hace 1h</div>
        </div>

        <div class="alert-item">
            <div class="alert-icon info">
                <i class="bi bi-wind"></i>
            </div>
            <div class="alert-content flex-grow-1">
                <h6>Cambio en dirección del viento</h6>
                <p>Viento del sureste a 22 km/h detectado. Posible cambio de condiciones climáticas.</p>
            </div>
            <div class="alert-time">Hace 2h</div>
        </div>

        <div class="alert-item">
            <div class="alert-icon info">
                <i class="bi bi-cloud-rain"></i>
            </div>
            <div class="alert-content flex-grow-1">
                <h6>Lluvia ligera registrada</h6>
                <p>Se registraron 2.4 mm de precipitación en la Estación Ubaté Centro.</p>
            </div>
            <div class="alert-time">Hace 3h</div>
        </div>

        <div class="alert-item">
            <div class="alert-icon success">
                <i class="bi bi-check-lg"></i>
            </div>
            <div class="alert-content flex-grow-1">
                <h6>Alerta de helada resuelta</h6>
                <p>La temperatura subió a 8°C en la Estación Cucunubá. Riesgo eliminado.</p>
            </div>
            <div class="alert-time">Hace 6h</div>
        </div>

        <div class="alert-item">
            <div class="alert-icon info">
                <i class="bi bi-broadcast"></i>
            </div>
            <div class="alert-content flex-grow-1">
                <h6>Estación reconectada</h6>
                <p>La Estación Laguna Suesca se reconectó después de 15 minutos sin señal.</p>
            </div>
            <div class="alert-time">Hace 8h</div>
        </div>
    </div>

    <!-- Alert Configuration Quick Access -->
    <div class="section-label">Configuración Rápida de Alertas</div>
    <div class="content-card">
        <div class="card-body-custom">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: #f8fafc;">
                        <div class="metric-icon temp" style="width: 44px; height: 44px; border-radius: 12px;">
                            <i class="bi bi-thermometer-low" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-size: 0.82rem; font-weight: 600; color: #0f172a;">Umbral de Helada</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Actual: 3°C</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="toggleHelada" checked>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: #f8fafc;">
                        <div class="metric-icon wind" style="width: 44px; height: 44px; border-radius: 12px;">
                            <i class="bi bi-wind" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-size: 0.82rem; font-weight: 600; color: #0f172a;">Viento Fuerte</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Actual: 40 km/h</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="toggleViento" checked>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: #f8fafc;">
                        <div class="metric-icon rain" style="width: 44px; height: 44px; border-radius: 12px;">
                            <i class="bi bi-cloud-rain-heavy" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-size: 0.82rem; font-weight: 600; color: #0f172a;">Lluvia Intensa</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Actual: 20 mm/h</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="toggleLluvia" checked>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
