<!doctype html>
<html lang="en">
    <head>
        <title>SIPRAC</title>
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
        <link rel="stylesheet" href="{{asset('asset/css/app.css')}}">
    </head>

    <body>
        <header>
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <a class="navbar-brand d-flex align-items-center">
                        <img src="{{ asset('asset/images/icono.webp') }}" alt="Logo SIPRAC" class="logo-navbar">
                        <span class="fw-bold">SIPRAC</span>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                        <ul class="navbar-nav align-items-center">
                            <li class="nav-item"><a class="nav-link" href="#">Inicio</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Nosotros</a></li>
                            <li class="nav-item me-3"><a class="nav-link" href="#">Tecnología</a></li>
                            <li class="nav-item">
                                <a class="btn btn-outline-dark btn-sm px-4 py-2" href="{{route('login')}}">Iniciar Sesión</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-register btn-outline-dark btn-sm px-4 py-2 " href="{{route('register')}}">Registrarse</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        
            <section class="hero-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <h1 class="hero-title">SIPRAC</h1>
                            <p class="hero-subtitle">Sistema Inteligente de protección rural agroclimática</p>
                            <div class="d-flex justify-content-center mt-4">
                                <a href="{{route('register')}}" class="btn btn-custom-primary">Registrar mi Finca</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        
            <section class="features-section text-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-3 mb-4 mb-md-0">
                            <img src="{{ asset('asset/images/iconosol.webp') }}">
                            <h3 class="feature-title">Sensor de Temp.</h3>
                            <p class="feature-desc">Precisión rural</p>
                        </div>
                        <div class="col-12 col-md-3 mb-4 mb-md-0">
                            <img src="{{ asset('asset/images/iconolluvia.webp') }}">
                            <h3 class="feature-title">Alertas de Lluvia</h3>
                            <p class="feature-desc">Datos en vivo</p>
                        </div>
                        <div class="col-12 col-md-3 mb-4 mb-md-0">
                            <img src="{{ asset('asset/images/iconoia.webp') }}">
                            <h3 class="feature-title">Predicción Heladas</h3>
                            <p class="feature-desc">Modelos Avanzados</p>
                        </div>
                    </div>
                </div>
            </section>
        </header>
        
        <footer>
            <div class="container-fluid text-center">
                <p class= "mt-2">© 2026 SIPRAC | Ingeniería de Sistemas y computación</p>
            </div>
        </footer>
        <!-- Bootstrap JavaScript Libraries -->
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
