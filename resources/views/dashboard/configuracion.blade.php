{{-- ============================================
    SIPRAC Dashboard - Configuración
    ============================================ --}}
@extends('dashboard.layout')

@section('page-title', 'Configuración')
@section('navbar-title', 'Configuración')

@section('content')

    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-gear-fill me-2 text-muted"></i>Configuración</h2>
        <p>Personaliza tu dashboard, estaciones y preferencias de notificación.</p>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">

            <!-- Profile Section -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h6><i class="bi bi-person-circle text-primary me-1"></i> Perfil de Usuario</h6>
                </div>
                <div class="config-section">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.8rem; font-weight: 700;">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                        <div class="col">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="config-name">Nombre</label>
                                    <input type="text" class="form-control" id="config-name" value="{{ auth()->user()->name ?? 'Usuario' }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="config-email">Correo electrónico</label>
                                    <input type="email" class="form-control" id="config-email" value="{{ auth()->user()->email ?? 'usuario@mail.com' }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Para cambiar tu información, contacta al administrador del sistema.</small>
                    </div>
                </div>
            </div>

            <!-- Station Management -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h6><i class="bi bi-broadcast-pin text-success me-1"></i> Estaciones de Monitoreo</h6>
                    <button class="btn btn-sm" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; border-radius: 8px; font-size: 0.78rem; padding: 0.4rem 1rem;">
                        <i class="bi bi-plus-lg me-1"></i> Agregar
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="tabla-estaciones">
                        <thead>
                            <tr>
                                <th>Estación</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Última Señal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #dbeafe, #bfdbfe); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-broadcast" style="color: #3b82f6; font-size: 0.9rem;"></i>
                                        </div>
                                        <strong>Ubaté Centro</strong>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem;">5.3123°N, -73.8167°W</td>
                                <td><span class="status-badge online"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Activa</span></td>
                                <td style="font-size: 0.85rem;">Hace 2 min</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.75rem;"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 0.75rem;"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #d1fae5, #a7f3d0); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-broadcast" style="color: #10b981; font-size: 0.9rem;"></i>
                                        </div>
                                        <strong>Laguna Suesca</strong>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem;">5.1845°N, -73.7990°W</td>
                                <td><span class="status-badge online"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Activa</span></td>
                                <td style="font-size: 0.85rem;">Hace 5 min</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.75rem;"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 0.75rem;"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #fef2f2, #fecaca); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-broadcast" style="color: #ef4444; font-size: 0.9rem;"></i>
                                        </div>
                                        <strong>Cucunubá</strong>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem;">5.2500°N, -73.7667°W</td>
                                <td><span class="status-badge offline"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Sin señal</span></td>
                                <td style="font-size: 0.85rem;">Hace 35 min</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.75rem;"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 0.75rem;"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Alert Thresholds -->
            <div class="content-card">
                <div class="card-header-custom">
                    <h6><i class="bi bi-sliders text-warning me-1"></i> Umbrales de Alerta</h6>
                </div>
                <div class="config-section">
                    <h6>Temperatura</h6>
                    <p>Define las temperaturas que activan alertas de helada.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="umbral-helada">Umbral de helada (°C)</label>
                            <input type="number" class="form-control" id="umbral-helada" value="3" step="0.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="umbral-critico">Umbral crítico (°C)</label>
                            <input type="number" class="form-control" id="umbral-critico" value="0" step="0.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="umbral-calor">Temp. máxima alerta (°C)</label>
                            <input type="number" class="form-control" id="umbral-calor" value="35" step="0.5">
                        </div>
                    </div>
                </div>
                <div class="config-section">
                    <h6>Viento y Precipitación</h6>
                    <p>Umbrales para alertas de viento fuerte y lluvia intensa.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="umbral-viento">Viento máximo (km/h)</label>
                            <input type="number" class="form-control" id="umbral-viento" value="40">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="umbral-lluvia">Lluvia intensa (mm/h)</label>
                            <input type="number" class="form-control" id="umbral-lluvia" value="20">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="umbral-humedad">Humedad crítica (%)</label>
                            <input type="number" class="form-control" id="umbral-humedad" value="95">
                        </div>
                    </div>
                </div>
                <div class="config-section">
                    <button class="btn" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; border-radius: 10px; padding: 0.6rem 1.5rem; font-size: 0.88rem; font-weight: 600;">
                        <i class="bi bi-check-lg me-1"></i> Guardar Umbrales
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">

            <!-- Notification Preferences -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h6><i class="bi bi-bell text-info me-1"></i> Notificaciones</h6>
                </div>
                <div class="config-section">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 600; color: #0f172a;">Alertas por correo</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Recibir alertas críticas por email</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notif-email" checked>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 600; color: #0f172a;">Alertas SMS</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Notificaciones por mensaje de texto</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notif-sms">
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 600; color: #0f172a;">Resumen diario</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Reporte automático a las 7:00 AM</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notif-daily" checked>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 600; color: #0f172a;">Sonidos del sistema</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Sonidos para alertas en el dashboard</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notif-sound" checked>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Display Preferences -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h6><i class="bi bi-palette text-purple me-1" style="color: #8b5cf6;"></i> Visualización</h6>
                </div>
                <div class="config-section">
                    <div class="mb-3">
                        <label class="form-label" for="config-unidad-temp">Unidad de temperatura</label>
                        <select class="form-select" id="config-unidad-temp">
                            <option selected>Celsius (°C)</option>
                            <option>Fahrenheit (°F)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="config-unidad-viento">Unidad de viento</label>
                        <select class="form-select" id="config-unidad-viento">
                            <option selected>km/h</option>
                            <option>m/s</option>
                            <option>mph</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="config-refresh">Intervalo de actualización</label>
                        <select class="form-select" id="config-refresh">
                            <option>Cada 1 minuto</option>
                            <option selected>Cada 5 minutos</option>
                            <option>Cada 15 minutos</option>
                            <option>Cada 30 minutos</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="config-zona">Zona horaria</label>
                        <select class="form-select" id="config-zona">
                            <option selected>América/Bogotá (UTC-5)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="content-card">
                <div class="card-header-custom">
                    <h6><i class="bi bi-cpu text-muted me-1"></i> Sistema</h6>
                </div>
                <div class="config-section">
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted">Versión</span>
                        <span class="fw-medium">v1.0.0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted">Framework</span>
                        <span class="fw-medium">Laravel 12</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted">PHP</span>
                        <span class="fw-medium">{{ phpversion() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted">Estaciones activas</span>
                        <span class="fw-medium">2 / 3</span>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size: 0.85rem;">
                        <span class="text-muted">Última sincronización</span>
                        <span class="fw-medium">Hace 2 min</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
