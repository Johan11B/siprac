<!doctype html>
<html lang="es">
    <head>
        <title>SIPRAC — Nueva contraseña</title>
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
            </div>
        </nav>
        <main class="container-fluid d-flex align-items-center justify-content-center flex-grow-1">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-body p-4">
                    <header class="text-center">
                        <img src="{{ asset('asset/images/icono.webp') }}" alt="Logo" class="img-fluid logo-login mb-2">
                        <h1 class="titulo-login">Nueva contraseña</h1>
                        <p class="text-muted small">Elige una clave segura para volver a entrar a SIPRAC.</p>
                    </header>
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <label for="email">Correo electrónico:</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" placeholder="Correo electrónico" required>

                        <label for="password">Nueva contraseña:</label>
                        <input id="password" type="password" name="password" placeholder="Mínimo 8 caracteres" required>

                        <label for="password_confirmation">Confirmar contraseña:</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirma la contraseña" required>

                        <button class="btn btn-login" type="submit">Guardar contraseña</button>

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
