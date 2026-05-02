{{-- ============================================
    SIPRAC Dashboard - Vista Solicitante
    Muestra un dashboard de demostración con datos
    climáticos generales de la región de Ubaté.
    ============================================ --}}
@extends('dashboard.layout')

@section('page-title', 'Bienvenido')
@section('navbar-title', 'Panel de Bienvenida')

@section('content')

    {{-- ============================
         Mensaje de estado de cuenta
         ============================ --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="p-4 rounded-3" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b;">
                <div class="d-flex align-items-start gap-3">
                    <div style="background: #f59e0b; border-radius: 12px; padding: 12px; color: white;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1" style="color: #92400e;">
                            <i class="bi bi-info-circle me-1"></i> Tu cuenta está pendiente de verificación
                        </h5>
                        <p class="mb-2" style="color: #78350f; font-size: 0.9rem;">
                            Un administrador revisará tu solicitud y te asignará acceso como agricultor.
                            Mientras tanto, puedes explorar los datos climáticos generales de la región.
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge" style="background: #92400e; font-size: 0.75rem; padding: 6px 12px;">
                                <i class="bi bi-person-check me-1"></i> Estado: Pendiente
                            </span>
                            <span class="badge" style="background: rgba(146, 64, 14, 0.6); font-size: 0.75rem; padding: 6px 12px;">
                                <i class="bi bi-telephone me-1"></i> Contacto: admin@siprac.com
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================
         Clima general de Ubaté (Demo)
         ============================ --}}
    <div class="section-label">Clima General — Provincia de Ubaté</div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card temp" id="card-temperatura-demo">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Temperatura Promedio</div>
                        <div class="metric-value">13.2°C</div>
                        <div class="metric-trend" style="color: #64748b;">
                            <i class="bi bi-geo-alt me-1"></i> Ubaté, Cundinamarca
                        </div>
                    </div>
                    <div class="metric-icon temp">
                        <i class="bi bi-thermometer-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card humidity" id="card-humedad-demo">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Humedad Promedio</div>
                        <div class="metric-value">75%</div>
                        <div class="metric-trend" style="color: #64748b;">
                            <i class="bi bi-droplet me-1"></i> Región general
                        </div>
                    </div>
                    <div class="metric-icon humidity">
                        <i class="bi bi-droplet-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card wind" id="card-altitud-demo">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Altitud Promedio</div>
                        <div class="metric-value">2.556 <span class="metric-unit">msnm</span></div>
                        <div class="metric-trend" style="color: #64748b;">
                            <i class="bi bi-mountains me-1"></i> Valle de Ubaté
                        </div>
                    </div>
                    <div class="metric-icon wind">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card rain" id="card-heladas-demo">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Riesgo Heladas</div>
                        <div class="metric-value" style="font-size: 1.4rem;">Medio</div>
                        <div class="metric-trend" style="color: #64748b;">
                            <i class="bi bi-snow me-1"></i> Época actual
                        </div>
                    </div>
                    <div class="metric-icon rain">
                        <i class="bi bi-snow2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================
         Información del sistema
         ============================ --}}
    <div class="section-label">¿Qué es SIPRAC?</div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="chart-card h-100">
                <div class="text-center p-3">
                    <div style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 16px; padding: 20px; display: inline-block; margin-bottom: 12px;">
                        <i class="bi bi-thermometer-sun fs-1" style="color: #2563eb;"></i>
                    </div>
                    <h6 class="fw-bold">Monitoreo Climático</h6>
                    <p class="text-muted small mb-0">Estaciones meteorológicas que registran temperatura, humedad, viento y precipitación en tiempo real.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-card h-100">
                <div class="text-center p-3">
                    <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 16px; padding: 20px; display: inline-block; margin-bottom: 12px;">
                        <i class="bi bi-bell-fill fs-1" style="color: #d97706;"></i>
                    </div>
                    <h6 class="fw-bold">Alertas Tempranas</h6>
                    <p class="text-muted small mb-0">Notificaciones automáticas cuando se detecta riesgo de helada, para que puedas proteger tus cultivos.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-card h-100">
                <div class="text-center p-3">
                    <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); border-radius: 16px; padding: 20px; display: inline-block; margin-bottom: 12px;">
                        <i class="bi bi-graph-up-arrow fs-1" style="color: #059669;"></i>
                    </div>
                    <h6 class="fw-bold">Predicción Inteligente</h6>
                    <p class="text-muted small mb-0">Modelos de inteligencia artificial que predicen condiciones climáticas adversas con horas de anticipación.</p>
                </div>
            </div>
        </div>
    </div>

@endsection
