<!doctype html>
<html lang="en">
    <head>
        <title>DashBoard</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>

    <body>
        <div class="d-flex" id="wrapper">
            <div class="bg-white border-end" id="sidebar-wrapper">
                <div class="sidebar-heading text-center py-4">
                    <img src="{{ asset('asset/images/icono.webp') }}" width="50" alt="Logo">
                    <h5 class="fw-bold text-success mt-2">SIPRAC</h5>
                </div>
                <div class="list-group list-group-flush px-3">
                    <a href="#" class="list-group-item list-group-item-action active rounded-3 mb-2">
                        <i class="bi bi-house-door me-2"></i> Inicio
                    </a>
                    <a href="#" class="list-group-item list-group-item-action rounded-3 mb-2">
                        <i class="bi bi-graph-up me-2"></i> Datos Climáticos
                    </a>
                    <a href="#" class="list-group-item list-group-item-action rounded-3 mb-2">
                        <i class="bi bi-bell me-2"></i> Alertas
                    </a>
                    <a href="#" class="list-group-item list-group-item-action rounded-3 mb-2">
                        <i class="bi bi-gear me-2"></i> Configuración
                    </a>
                </div>
            </div>

            <div id="page-content-wrapper" class="bg-light w-100">
                <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 px-4 border-bottom shadow-sm">
                    <div class="container-fluid">
                        <button class="btn btn-light d-md-none me-3" id="menu-toggle">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                        <div>
                            <h4 class="fw-bold mb-0" style="font-size: 40px">Panel Principal</h4>
                            <p class=" mb-0 small text-capitalize fs-5">Bienvenido, {{ auth()->user()->name ?? 'Usuario' }}</p>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Salir
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="post" class="d-none">@csrf</form>
                        </div>
                    </div>
                </nav>

                <div class="container-fluid p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="icon-shape bg-warning-subtle text-warning rounded-3 me-3">
                                        <i class="bi bi-thermometer-half fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0 small">Temperatura</h6>
                                        <h3 class="fw-bold mb-0">+0.9°C</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card alerta-helada-card text-black rounded-4 p-4 h-100 shadow-sm bg-primary">
                                <h5 class="fw-bold"><i class="bi bi-snow me-2"></i> Alerta de Helada</h5>
                                <p class="small mb-4 opacity-75">A partir de las 3:00 AM</p>
                                <div class="display-5 fw-bold">1°C</div>
                                <p class="mb-0 mt-3 small">Pronóstico: -1°C</p>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4" style="min-height: 400px;">
                                <h6 class="fw-bold mb-3">Mapa de Microzonas (Ubaté)</h6>
                                <div class="bg-light rounded-4 w-100 h-100 d-flex align-items-center justify-content-center border">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-geo-alt fs-1"></i>
                                        <p class="small mt-2">Cargando monitoreo satelital...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
        
    </body>
</html>
