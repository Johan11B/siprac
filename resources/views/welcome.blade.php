<!doctype html>
<html lang="es">
    <head>
        <title>SIPRAC — Inicio</title>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.head-icons')

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="{{ asset('asset/css/app.css') }}">
    </head>

    <body>
        <header>
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                        <img src="{{ asset('asset/images/icono.webp') }}" alt="Logo SIPRAC" class="logo-navbar">
                        <span class="fw-bold">SIPRAC</span>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                        <ul class="navbar-nav align-items-center">
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Inicio</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#nosotros">Nosotros</a></li>
                            <li class="nav-item me-3"><a class="nav-link" href="{{ route('home') }}#tecnologia">Tecnología</a></li>
                            <li class="nav-item">
                                <a class="btn btn-outline-dark btn-sm px-4 py-2" href="{{ route('login') }}">Iniciar Sesión</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-register btn-outline-dark btn-sm px-4 py-2" href="{{ route('register') }}">Registrarse</a>
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
                                <a href="{{ route('register') }}" class="btn btn-custom-primary">Registrar mi Finca</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </header>

        <main>
            <section class="features-section text-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-3 mb-4 mb-md-0">
                            <img src="{{ asset('asset/images/iconosol.webp') }}" alt="Sensor de temperatura">
                            <h3 class="feature-title">Sensor de Temp.</h3>
                            <p class="feature-desc">Precisión rural</p>
                        </div>
                        <div class="col-12 col-md-3 mb-4 mb-md-0">
                            <img src="{{ asset('asset/images/iconolluvia.webp') }}" alt="Alertas de lluvia">
                            <h3 class="feature-title">Alertas de Lluvia</h3>
                            <p class="feature-desc">Datos en vivo</p>
                        </div>
                        <div class="col-12 col-md-3 mb-4 mb-md-0">
                            <img src="{{ asset('asset/images/iconoia.webp') }}" alt="Predicción de heladas">
                            <h3 class="feature-title">Predicción Heladas</h3>
                            <p class="feature-desc">Modelos Avanzados</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="nosotros" class="nosotros-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 text-center">
                            <p class="section-kicker mb-2">Provincia de Ubaté</p>
                            <h2 class="section-title">Nosotros</h2>
                            <p class="nosotros-text mb-0">
                                SIPRAC es un sistema inteligente de protección agroclimática pensado para los agricultores
                                de la provincia de Ubaté. Su propósito es mitigar los riesgos de heladas y lluvias intensas
                                que amenazan los cultivos, combinando monitoreo en campo, análisis de datos y alertas
                                oportunas para cuidar la producción rural.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="tecnologia" class="tecnologia-section">
                <div class="container">
                    <div class="text-center mb-5">
                        <p class="section-kicker mb-2">Cómo protegemos los cultivos</p>
                        <h2 class="section-title">Tecnología</h2>
                        <p class="text-muted mx-auto" style="max-width: 640px;">
                            Tres pilares trabajan juntos para observar el clima, anticipar riesgos y poner la información
                            al alcance del agricultor.
                        </p>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <article class="card h-100 border-0 shadow-sm tecnologia-card">
                                <div class="card-body p-4">
                                    <div class="tecnologia-icon mb-3">
                                        <i class="bi bi-broadcast-pin"></i>
                                    </div>
                                    <h3 class="h5 fw-bold">Estaciones Meteorológicas IoT</h3>
                                    <p class="mb-0 text-muted">
                                        Sensores en finca registran temperatura, humedad, lluvia, viento y humedad del suelo
                                        para conocer las condiciones reales del cultivo en tiempo cercano.
                                    </p>
                                </div>
                            </article>
                        </div>
                        <div class="col-md-4">
                            <article class="card h-100 border-0 shadow-sm tecnologia-card">
                                <div class="card-body p-4">
                                    <div class="tecnologia-icon mb-3">
                                        <i class="bi bi-cpu"></i>
                                    </div>
                                    <h3 class="h5 fw-bold">Modelos de Machine Learning (Python)</h3>
                                    <p class="mb-0 text-muted">
                                        El preprocesamiento y los modelos en Python identifican patrones, valores atípicos
                                        y señales de helada o precipitación intensa a partir de las lecturas.
                                    </p>
                                </div>
                            </article>
                        </div>
                        <div class="col-md-4">
                            <article class="card h-100 border-0 shadow-sm tecnologia-card">
                                <div class="card-body p-4">
                                    <div class="tecnologia-icon mb-3">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <h3 class="h5 fw-bold">Plataforma Web (Laravel)</h3>
                                    <p class="mb-0 text-muted">
                                        El dashboard en Laravel concentra lecturas, alertas y gestión de fincas para que
                                        agricultores, técnicos y administradores tomen decisiones a tiempo.
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <div class="container-fluid text-center">
                <p class="mt-2">© 2026 SIPRAC | Ingeniería de Sistemas y computación</p>
            </div>
        </footer>

        @include('partials.chatbot', [
            'endpoint'    => route('chatbot.public'),
            'mode'        => 'public',
            'greeting'    => 'Hola, soy el asistente de SIPRAC. Puedo contarte sobre el proyecto. Para consultar las lecturas de tu finca, inicia sesión.',
            'suggestions' => [
                '¿Qué es SIPRAC?',
                '¿Cómo se integra la Declaración de la Persona Transhumana?',
                'What does SIPRAC monitor?',
            ],
        ])

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
