<!doctype html>
<html lang="es">
    <head>
        <title>SIPRAC — Recuperar contraseña</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        @include('partials.head-icons')

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="{{ asset('asset/css/app.css') }}">
    </head>
    <body id="pagina-login" class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
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
                    </ul>
                </div>
            </div>
        </nav>
        <main class="container-fluid d-flex align-items-center justify-content-center flex-grow-1">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-body p-4">
                    <header class="text-center">
                        <img src="{{ asset('asset/images/icono.webp') }}" alt="Logo" class="img-fluid logo-login mb-2">
                        <h1 class="titulo-login">Recuperar contraseña</h1>
                        <p class="text-muted small">Te enviaremos un enlace al correo registrado en SIPRAC.</p>
                    </header>
                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <label for="email">Correo electrónico:</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Escribe el correo" required autofocus>

                        <button class="btn btn-login" type="submit">Enviar enlace</button>

                        @if (session('status'))
                            <div class="alert alert-success mt-3 mb-0">{{ session('status') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <p class="mb-0" style="color: red">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                        <a href="{{ route('login') }}">Volver al inicio de sesión</a>
                    </form>
                </div>
            </div>
        </main>
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
