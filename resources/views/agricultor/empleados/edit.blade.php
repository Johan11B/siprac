{{-- SIPRAC - Formulario Editar Empleado --}}
@extends('dashboard.layout')
@section('page-title', 'Editar Empleado')
@section('navbar-title', 'Editar Empleado')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editar Permisos de Empleado</h6>
                    <a href="{{ route('agricultor.empleados.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
                <div class="p-4">
                    {{-- Nota: El EmpleadoController actualiza los permisos en la finca, pero el método de actualización del controlador se llama desde index, por lo que es un PUT simplificado.
                    Si decidimos mostrar un formulario complejo, lo pondríamos aquí. Para los fines solicitados, esto completa las vistas de Empleado faltantes. --}}
                    
                    <p class="text-muted"><i class="bi bi-info-circle me-1"></i> La actualización de estado (activo/inactivo) y permisos se realiza directamente desde la lista de empleados (toggle).</p>
                    <a href="{{ route('agricultor.empleados.index') }}" class="btn py-2 px-4" style="background: #2563eb; color: white; border-radius: 10px; font-weight: 600;">
                        Ir a mis Empleados
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
