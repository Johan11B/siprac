{{-- SIPRAC Dashboard - Vista Agricultor --}}
@extends('dashboard.layout')
@section('page-title', 'Panel Principal')
@section('navbar-title', 'Panel Principal')
@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endsection
@section('content')
    <div class="section-label">Resumen General</div>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card temp" id="card-fincas">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Mis Fincas</div>
                        <div class="metric-value">{{ $fincas->count() }}</div>
                        <div class="metric-trend" style="color: #64748b;"><i class="bi bi-geo-alt me-1"></i> Registradas</div>
                    </div>
                    <div class="metric-icon temp"><i class="bi bi-house-door-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card humidity" id="card-estaciones">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Estaciones</div>
                        <div class="metric-value">{{ $totalEstaciones }}</div>
                        <div class="metric-trend" style="color: #64748b;"><i class="bi bi-broadcast me-1"></i> Activas</div>
                    </div>
                    <div class="metric-icon humidity"><i class="bi bi-broadcast-pin"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card rain" id="card-alertas">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Alertas Pendientes</div>
                        <div class="metric-value">{{ $alertasNoLeidas }}</div>
                        <div class="metric-trend {{ $alertasNoLeidas > 0 ? 'up' : '' }}">
                            @if($alertasNoLeidas > 0)
                                <i class="bi bi-exclamation-triangle me-1"></i> Requieren atención
                            @else
                                <i class="bi bi-check-circle me-1"></i> Todo en orden
                            @endif
                        </div>
                    </div>
                    <div class="metric-icon rain"><i class="bi bi-bell-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card wind" id="card-empleados">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Empleados</div>
                        <div class="metric-value">{{ $totalEmpleados }}</div>
                        <div class="metric-trend" style="color: #64748b;"><i class="bi bi-people me-1"></i> Activos</div>
                    </div>
                    <div class="metric-icon wind"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-label">Mis Fincas</div>
    <div class="row g-3 mb-4">
        @forelse($fincas as $finca)
            <div class="col-md-6 col-xl-4">
                <div class="chart-card h-100">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="bi bi-house-door text-success me-1"></i>{{ $finca->nombre_finca }}</h6>
                        <span class="chart-period-badge">{{ $finca->estaciones_count }} estación(es)</span>
                    </div>
                    <div class="p-3">
                        <div class="d-flex flex-column gap-2" style="font-size: 0.85rem;">
                            <div><i class="bi bi-geo-alt text-muted me-2"></i>{{ $finca->vereda }}, {{ $finca->municipio }}</div>
                            <div><i class="bi bi-arrow-up-circle text-muted me-2"></i>{{ $finca->altitud_msnm }} msnm</div>
                            <div><i class="bi bi-flower1 text-muted me-2"></i>{{ $finca->cultivo_principal }}</div>
                            <div><i class="bi bi-rulers text-muted me-2"></i>{{ $finca->area_hectareas }} hectáreas</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="chart-card">
                    <div class="p-4 text-center">
                        <i class="bi bi-house-add fs-1 text-muted"></i>
                        <h6 class="fw-bold mt-2">No tienes fincas registradas</h6>
                        <p class="text-muted small">Contacta al administrador para agregar tus fincas al sistema.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="chart-card" id="card-grafica-temp">
                <div class="card-header-custom">
                    <h6 class="mb-0"><i class="bi bi-graph-up text-primary"></i> Temperatura — Últimas 24 Horas</h6>
                    <span class="chart-period-badge"><i class="bi bi-clock-history me-1"></i> Datos de ejemplo</span>
                </div>
                <div style="position: relative; height: 280px;">
                    <canvas id="tempChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <a href="{{ route('normalizacion.index') }}" class="btn btn-lg btn-outline-primary w-100 py-3" style="border-width: 2px;">
                <i class="bi bi-lightning-fill me-2"></i>
                <span class="fw-semibold">Normalización de Datos</span>
                <small class="d-block mt-1" style="font-weight: 400;">Carga datos crudos y obtén una versión normalizada con cálculos avanzados</small>
            </a>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('tempChart').getContext('2d');
    const temps = [12,11.5,10.8,10.2,9.5,8.7,8.2,7.8,7.5,8,9.2,10.5,12,13.2,14.5,15,14.8,13.5,12,11,10.5,10,9.8,9.5];
    const labels = Array.from({length:24},(_,i)=>`${String(i).padStart(2,'0')}:00`);
    const grad = ctx.createLinearGradient(0,0,0,280);
    grad.addColorStop(0,'rgba(59,130,246,0.15)');
    grad.addColorStop(1,'rgba(59,130,246,0)');
    new Chart(ctx,{type:'line',data:{labels,datasets:[{label:'Temperatura (°C)',data:temps,borderColor:'#3b82f6',backgroundColor:grad,borderWidth:2.5,fill:true,tension:0.4,pointRadius:0,pointHoverRadius:6}]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{font:{size:10},color:'#94a3b8',maxRotation:0,callback:function(v,i){return i%3===0?this.getLabelForValue(v):''}},border:{display:false}},y:{grid:{color:'rgba(0,0,0,0.04)'},ticks:{font:{size:11},color:'#94a3b8',callback:v=>v+'°C',stepSize:2},border:{display:false},min:4,max:18}}}});
</script>
@endsection
