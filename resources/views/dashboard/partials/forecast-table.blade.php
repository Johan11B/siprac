@php($forecast = session('forecast', session('normalization_forecast', [])))
@if(!empty($forecast))
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="chart-card">
                <div class="card-header-custom">
                    <div>
                        <h6 class="mb-1"><i class="bi bi-cloud-sun text-primary me-2"></i>Predicción agroclimática</h6>
                        <small class="text-muted">Próximas 12 horas según el último archivo reprocesado</small>
                    </div>
                    <span class="chart-period-badge">{{ $forecast['data_points'] ?? 0 }} lecturas</span>
                </div>
                <div class="table-responsive">
                    @if(($forecast['available'] ?? false) && !empty($forecast['items']))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Temperatura</th>
                                    <th>Humedad</th>
                                    <th>Viento</th>
                                    <th>Lluvia</th>
                                    <th>Riesgo</th>
                                    <th>Confianza</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forecast['items'] as $item)
                                    <tr>
                                        <td><strong>{{ $item['label'] ?? '--' }}</strong> <span class="text-muted small">({{ $item['date_label'] ?? '--' }})</span></td>
                                        <td>{{ $item['temperature'] ?? '--' }} °C</td>
                                        <td>{{ $item['humidity'] ?? '--' }}%</td>
                                        <td>{{ $item['wind'] ?? '--' }} m/s</td>
                                        <td>{{ $item['rain'] ?? '--' }} mm</td>
                                        <td>
                                            @if(($item['risk'] ?? '') === 'Riesgo de helada')
                                                <span class="status-badge warning">Riesgo de helada</span>
                                            @elseif(($item['risk'] ?? '') === 'Lluvia intensa')
                                                <span class="status-badge warning">Lluvia intensa</span>
                                            @else
                                                <span class="status-badge online">Condición estable</span>
                                            @endif
                                        </td>
                                        <td>{{ $item['confidence'] ?? '--' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3 text-muted small">
                            <i class="bi bi-info-circle me-1"></i>{{ $forecast['message'] ?? 'No hay suficientes lecturas para generar la predicción.' }}
                        </div>
                    @endif
                </div>
                <div class="px-3 pb-3 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Estimación orientativa; valida las condiciones locales antes de tomar decisiones.
                </div>
            </div>
        </div>
    </div>
@endif
