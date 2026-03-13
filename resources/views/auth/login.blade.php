<!doctype html>
<html lang="en">
    <head>
        <title>Inicio de Sesión</title>
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
        <link rel="stylesheet" href="{{ asset('asset/css/app.css') }}">
    </head>

    <body id="pagina-login">
        <main class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-body p-4">
                <header>
                    <div> 
                        <img src="asset/images/icono.webp" alt="" class="img-fluid" id= "logo-login">
                    </div>
                    <h1 class="titulo-login">Inicio de Sesión</h1>
                </header>
                    <form action="{{ route('login.store') }}" method="POST">
                        @csrf
                    
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" name="email" placeholder="Escribe el correo"/>
                    
                        <label for="password">Contraseña:</label>
                        <input type="password" name="password" placeholder="Contraseña"/> 
                    
                        <button class="btn btn-login" type="submit">Iniciar Sesión</button>
                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <p style="color: red">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                        <a href="{{ route('register') }}">¿No tienes una cuenta? Regístrate</a>
                    </form>
                </div>
            </div>
        </main>
        
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
