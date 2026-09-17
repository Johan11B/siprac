{{-- ============================================
    SIPRAC Dashboard - Layout Base
    Todas las páginas del dashboard extienden este layout.
    ============================================ --}}
<!doctype html>
<html lang="es">
<head>
    <title>SIPRAC - @yield('page-title', 'Dashboard')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SIPRAC - Sistema de Prevención y Alerta Climática." />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS v5.3.2 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous"
    />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('asset/css/dashboard.css') }}">

    <!-- Extra head content (Chart.js, etc.) -->
    @yield('head')
</head>

<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <div class="d-flex" id="wrapper">

        <!-- ============================
             SIDEBAR
             ============================ -->
        <aside id="sidebar-wrapper" role="navigation" aria-label="Menú principal">
            <!-- Brand -->
            <div class="sidebar-brand text-center">
                <img src="{{ asset('asset/images/icono.webp') }}" width="48" alt="Logo SIPRAC" class="mb-2">
                <h5 class="mb-0">SIPRAC</h5>
                <span class="d-block mt-1" style="font-size: 0.65rem; color: #475569; letter-spacing: 1px;">MONITOREO CLIMÁTICO</span>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    {{-- Todos los roles ven el Dashboard --}}
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-inicio">
                            <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                            Inicio
                        </a>
                    </li>

                    {{-- Solo agricultor, tecnico y admin ven Datos Climáticos --}}
                    @if(auth()->user()->tieneAlgunRol(['agricultor', 'tecnico', 'administrador']))
                    <li class="nav-item">
                        <a href="{{ route('dashboard.datos') }}" class="nav-link {{ request()->routeIs('dashboard.datos') ? 'active' : '' }}" id="nav-datos">
                            <span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span>
                            Datos Climáticos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dashboard.alertas') }}" class="nav-link {{ request()->routeIs('dashboard.alertas') ? 'active' : '' }}" id="nav-alertas">
                            <span class="nav-icon"><i class="bi bi-bell-fill"></i></span>
                            Alertas
                            <span class="badge bg-danger ms-auto" style="font-size: 0.65rem; padding: 3px 8px; border-radius: 6px;">3</span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->tieneAlgunRol(['agricultor', 'administrador']))
                    <li class="nav-item">
                        <a href="{{ route('normalizacion.index') }}" class="nav-link {{ request()->routeIs('normalizacion.*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="bi bi-lightning-fill"></i></span>
                            Preprocesamiento
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->tieneAlgunRol(['agricultor', 'administrador']))
                    <li class="nav-item">
                        <a href="{{ route('agricultor.empleados.index') }}" class="nav-link {{ request()->routeIs('agricultor.empleados.*') ? 'active' : '' }}" id="nav-empleados">
                            <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
                            Empleados
                        </a>
                    </li>
                    @endif

                    {{-- Configuración para agricultor, tecnico y admin --}}
                    @if(auth()->user()->tieneAlgunRol(['agricultor', 'tecnico', 'administrador']))
                    <li class="nav-item">
                        <a href="{{ route('dashboard.configuracion') }}" class="nav-link {{ request()->routeIs('dashboard.configuracion') ? 'active' : '' }}" id="nav-config">
                            <span class="nav-icon"><i class="bi bi-gear-fill"></i></span>
                            Configuración
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer text-center">
                <div class="version-badge">v1.0.0 &bull; SIPRAC</div>
            </div>
        </aside>

        <!-- ============================
             MAIN CONTENT
             ============================ -->
        <div id="page-content-wrapper">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg top-navbar sticky-top" aria-label="Barra superior">
                <div class="container-fluid">
                    <!-- Hamburger button (mobile) -->
                    <button class="btn btn-hamburger d-lg-none me-3" id="menu-toggle" aria-label="Abrir menú lateral" type="button">
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <!-- Title -->
                    <div>
                        <h1 class="navbar-title mb-0">@yield('navbar-title', 'Panel Principal')</h1>
                        <div class="d-flex flex-column">
                            <p class="navbar-subtitle mb-0" style="line-height: 1.2;">Bienvenido, <span class="fw-medium text-dark">{{ auth()->user()->name ?? 'Usuario' }}</span></p>
                            @if(auth()->check() && auth()->user()->role)
                                <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; width: fit-content; font-size: 0.7rem; padding: 3px 8px; border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 4px; margin-top: 2px;">
                                    <i class="bi bi-shield-lock-fill me-1"></i>{{ auth()->user()->nombre_rol }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <!-- Date badge -->
                        <div class="date-badge d-none d-md-flex">
                            <i class="bi bi-clock"></i>
                            <span id="currentDateTime"></span>
                        </div>

                        <!-- Logout -->
                        <a href="{{ route('logout') }}" class="btn btn-logout"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           id="btn-logout" aria-label="Cerrar sesión">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-sm-inline">Salir</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="post" class="d-none">@csrf</form>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="dashboard-content">
                <div class="container-fluid"> {{-- Agregamos este contenedor --}}
        @yield('content')
    </div>
            </main>
        </div>
    </div>

    <!-- Asistente agroclimático -->
    <section class="siprac-chatbot" aria-label="Asistente SIPRAC">
        <button type="button" class="siprac-chatbot__toggle" id="chatbotToggle" aria-expanded="false" aria-controls="chatbotPanel" title="Abrir asistente SIPRAC">
            <i class="bi bi-stars" aria-hidden="true"></i>
            <span class="siprac-chatbot__toggle-label">Asistente IA</span>
        </button>

        <div class="siprac-chatbot__panel" id="chatbotPanel" hidden>
            <header class="siprac-chatbot__header">
                <div>
                    <span class="siprac-chatbot__eyebrow">SIPRAC</span>
                    <h2>Asistente agroclimático</h2>
                    <p>Pregúntame sobre el proyecto, clima y cultivos.</p>
                </div>
                <button type="button" class="siprac-chatbot__close" id="chatbotClose" aria-label="Cerrar asistente">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>

            <div class="siprac-chatbot__messages" id="chatbotMessages" aria-live="polite">
                <div class="siprac-chatbot__message siprac-chatbot__message--assistant">
                    Hola, soy el asistente de SIPRAC. Puedo explicarte sensores, lecturas, heladas, lluvias y alertas.
                </div>
            </div>

            <div class="siprac-chatbot__suggestions" aria-label="Preguntas sugeridas">
                <button type="button" data-chatbot-suggestion="¿Cómo detecta SIPRAC una helada?">Riesgo de heladas</button>
                <button type="button" data-chatbot-suggestion="¿Qué variables monitorean los sensores?">Variables monitoreadas</button>
                <button type="button" data-chatbot-suggestion="¿Cómo funciona SIPRAC?">Cómo funciona</button>
            </div>

            <form class="siprac-chatbot__form" id="chatbotForm">
                <label class="visually-hidden" for="chatbotInput">Escribe tu pregunta</label>
                <input id="chatbotInput" name="message" type="text" maxlength="1000" autocomplete="off" placeholder="Escribe una pregunta..." required>
                <button type="submit" aria-label="Enviar pregunta" title="Enviar pregunta">
                    <i class="bi bi-arrow-up" aria-hidden="true"></i>
                </button>
            </form>
            <p class="siprac-chatbot__disclaimer">La información es orientativa. Verifica las alertas y lecturas de tu finca.</p>
        </div>
    </section>

    <!-- Scripts -->
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

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar-wrapper');
        const overlay = document.getElementById('sidebarOverlay');
        const menuToggle = document.getElementById('menu-toggle');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        // Current local date and time for the operation area.
        const dateEl = document.getElementById('currentDateTime');
        const updateCurrentDateTime = () => {
            dateEl.textContent = new Intl.DateTimeFormat('es-CO', {
                timeZone: @json(config('app.timezone')),
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(new Date());
        };
        updateCurrentDateTime();
        setInterval(updateCurrentDateTime, 60000);
    </script>

    <script>
        (() => {
            const toggle = document.getElementById('chatbotToggle');
            const close = document.getElementById('chatbotClose');
            const panel = document.getElementById('chatbotPanel');
            const form = document.getElementById('chatbotForm');
            const input = document.getElementById('chatbotInput');
            const messages = document.getElementById('chatbotMessages');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const setOpen = (isOpen) => {
                panel.hidden = !isOpen;
                toggle.setAttribute('aria-expanded', String(isOpen));
                if (isOpen) input.focus();
            };

            const addMessage = (text, type) => {
                const message = document.createElement('div');
                message.className = `siprac-chatbot__message siprac-chatbot__message--${type}`;
                message.textContent = text;
                messages.appendChild(message);
                messages.scrollTop = messages.scrollHeight;
                return message;
            };

            toggle.addEventListener('click', () => setOpen(panel.hidden));
            close.addEventListener('click', () => setOpen(false));

            document.querySelectorAll('[data-chatbot-suggestion]').forEach((button) => {
                button.addEventListener('click', () => {
                    input.value = button.dataset.chatbotSuggestion;
                    form.requestSubmit();
                });
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const message = input.value.trim();
                if (!message) return;

                addMessage(message, 'user');
                input.value = '';
                input.disabled = true;
                const loading = addMessage('Estoy consultando la información de SIPRAC...', 'assistant siprac-chatbot__message--loading');

                try {
                    const response = await fetch('{{ route('chatbot.message') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ message }),
                    });
                    const data = await response.json();
                    loading.remove();

                    if (!response.ok) {
                        throw new Error(data.message || 'No se pudo procesar la pregunta.');
                    }

                    addMessage(data.reply, 'assistant');
                } catch (error) {
                    loading.remove();
                    addMessage(error.message || 'No hay conexión con el asistente. Inténtalo de nuevo.', 'assistant siprac-chatbot__message--error');
                } finally {
                    input.disabled = false;
                    input.focus();
                }
            });
        })();
    </script>

    <!-- Extra scripts per page -->
    @yield('scripts')
</body>
</html>
