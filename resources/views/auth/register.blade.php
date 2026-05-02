<!doctype html>
<html lang="es">
<head>
    <title>Registro de Usuario - SIPRAC</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('asset/css/app.css') }}">
</head>

<body id="pagina-register">
    <main class="container-fluid min-vh-100 p-0">
        <div class="row g-0 min-vh-100">
            <div class="col-lg-6 d-none d-lg-block">
                <img src="{{ asset('asset/images/fondoregistro.webp') }}" alt="Fondo SIPRAC" class="img-fluid h-100 w-100" style="object-fit: cover;">
            </div>
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4">
                <div class="contenedor-compacto" style="max-width: 450px; width: 100%;">
                    <div class="text-center mb-4">
                        <img src="{{ asset('asset/images/icono.webp') }}" alt="SIPRAC" style="height: 150px; width: auto;">
                        <h1 class="h2 mt-3">Registro de Usuario</h1>
                        <p class="text-muted">Crea tu cuenta en SIPRAC</p>
                    </div>
                    <form action="{{ route('register.store') }}" method="post">
                        @csrf
                        <label for="name">Nombre:</label>
                        <input type="text" name="name" placeholder="Nombre" value="{{ old('name') }}"/>
                        <label for="telefono">Telefono</label>
                        <input type="text" name="telefono" placeholder="Telefono" value="{{ old('telefono') }}"/>
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}"/>

                        <label for="password">Contraseña:</label>
                        <input type="password" name="password" placeholder="Contraseña"/> 
                        <label for="password_confirmation">Confirmar Contraseña:</label>
                        <input type="password" name="password_confirmation" placeholder="Confirmar Contraseña"/>
                        <button id="botonR" class="btn" type="submit">Registrar</button>
                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <p style="color: red">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                        <a href="{{ route('login') }}">¿Ya tienes una cuenta? Inicia sesión</a>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
