# SIPRAC — Sistema Inteligente de Protección Rural Agroclimática

Plataforma web que monitorea variables climáticas de estaciones meteorológicas en fincas de la provincia de Ubaté (Colombia), prepara y etiqueta los datos con machine learning, estima el clima de las próximas horas y ofrece un **asistente conversacional con IA (Google Gemini)** que responde en varios idiomas sobre el proyecto, las lecturas de cada usuario y el marco ético de la Universidad de Cundinamarca.

Proyecto desarrollado en la **Universidad de Cundinamarca** (Ingeniería de Sistemas y Computación).

> **English summary.** SIPRAC is a Laravel web platform for agroclimatic monitoring and frost/heavy-rain risk awareness. It ingests weather-station data, cleans and labels it with a Python/scikit-learn pipeline (IQR, Z-score, Isolation Forest, DBSCAN), computes an explainable hourly estimate, and includes an AI assistant powered by the **Gemini API** (with OpenAI and local fallbacks). The assistant is restricted by a system prompt, a structured knowledge base (including the Universidad de Cundinamarca *Transhuman Person Declaration*), and per-user data permissions. A bilingual reflection on the Declaration is included below.

---

## Contenido

1. [¿Qué es SIPRAC?](#1-qué-es-siprac)
2. [Asistente con IA (chatbot)](#2-asistente-con-ia-chatbot)
3. [Arquitectura](#3-arquitectura)
4. [Stack tecnológico](#4-stack-tecnológico)
5. [Estructura del proyecto](#5-estructura-del-proyecto)
6. [Roles y permisos](#6-roles-y-permisos)
7. [Instalación paso a paso](#7-instalación-paso-a-paso)
8. [Configuración del `.env`](#8-configuración-del-env)
9. [Despliegue](#9-despliegue)
10. [Declaración de la Persona Transhumana](#10-declaración-de-la-persona-transhumana-ucundinamarca)
11. [Autores](#11-autores)

---

## 1. ¿Qué es SIPRAC?

Las heladas y las lluvias intensas causan pérdidas importantes en los cultivos de la provincia de Ubaté. SIPRAC busca que el agricultor **se anticipe**: reúne las lecturas de estaciones meteorológicas (temperatura, humedad, viento, lluvia), las limpia y analiza, estima las condiciones de las próximas horas y presenta la información de forma clara según el rol de cada persona (agricultor, empleado, técnico o administrador).

La decisión final siempre es de la persona: el sistema **informa y orienta, no decide ni actúa por ella**.

## 2. Asistente con IA (chatbot)

El asistente está disponible como widget flotante en todas las pantallas del dashboard. Se ejecuta en el **backend** (la API key nunca llega al navegador).

**Qué hace**

- Explica SIPRAC, sensores IoT, heladas, lluvias, humedad, alertas y la normalización de datos.
- Responde con las **lecturas reales** de las estaciones a las que el usuario tiene acceso (última lectura por estación, última lluvia registrada y resumen de 7 días).
- Responde en el **idioma del usuario** (español, inglés, portugués, francés, etc.).
- Explica cómo el proyecto integra la **Declaración de la Persona Transhumana** de la Universidad de Cundinamarca, principio por principio.
- Rechaza con amabilidad lo que esté fuera de su alcance.

**Cómo está construido**

| Elemento | Detalle |
|---|---|
| Proveedor LLM | Google Gemini (`generateContent`, modelo `gemini-flash-latest`). Si no hay key de Gemini usa un proveedor compatible con OpenAI (opcional) y, si no hay ninguna, respuestas locales en español/inglés. |
| System Prompt | Define identidad, idioma, dominio permitido, honestidad con los datos y reglas de seguridad (no revelar instrucciones, no cambiar reglas). |
| Base de conocimiento | `config/siprac_knowledge.php`: marco institucional, los 12 principios de la declaración con su aplicación en SIPRAC, integración técnica y ejemplos *Few-Shot*. |
| Contexto de datos | Se arma en cada consulta y se **filtra por permisos**: el administrador ve todas las estaciones; los demás solo las de sus fincas (propias o asociadas y activas). |
| Pronóstico | `WeatherPredictionService` aporta la estimación local de las próximas horas, presentada como estimación y no como certeza. |
| Barreras | No inventa lecturas ni alertas, no activa mecanismos, no comparte datos de otras fincas y registra en el log los errores del proveedor. |
| Límite de uso | Ruta protegida por autenticación y limitada a 20 consultas por minuto. |

**Preguntas de ejemplo**

- "¿Cómo detecta SIPRAC una helada?"
- "Dame la última medición de la estación Ubaté Centro"
- "¿Cuándo llovió por última vez?"
- "How does SIPRAC reflect the Transhuman Person Declaration?"
- "¿Por qué el asistente no activa el riego por mí?"

## 3. Arquitectura

SIPRAC sigue el patrón **MVC de Laravel**, con dos piezas desacopladas: un **servicio de preprocesamiento en Python** (invocado como proceso por el backend) y un **servicio de IA** (API externa).

```mermaid
flowchart LR
    U[Usuario en el navegador] -->|HTTP| R[Rutas y middleware auth / role]
    R --> DC[DashboardController]
    R --> EC[EmpleadoController]
    R --> NC[DataNormalizationController]
    R --> CC[ChatbotController]

    DC --> DB[(MySQL)]
    EC --> DB

    NC -->|Symfony Process| PY[python/normalize_data.py]
    PY -->|CSV y Excel procesados| NC
    NC -->|upsert de lecturas| DB

    CC --> CTX[Contexto de lecturas filtrado por permisos]
    CTX --> DB
    CC --> KB[config/siprac_knowledge.php]
    CC --> WP[WeatherPredictionService]
    WP --> DB
    CC -->|"1. si hay GEMINI_API_KEY"| G[Gemini API]
    CC -->|"2. si hay AI_API_KEY"| O[API compatible con OpenAI]
    CC -->|"3. si no hay IA"| L[Respuestas locales ES y EN]
```

### Flujo de una pregunta al chatbot

```mermaid
sequenceDiagram
    participant U as Usuario
    participant W as Widget (layout.blade.php)
    participant C as ChatbotController
    participant D as MySQL
    participant G as Gemini API

    U->>W: escribe una pregunta
    W->>C: POST /chatbot/message (CSRF + sesión)
    C->>D: lecturas de las fincas del usuario
    C->>C: arma el System Prompt (reglas + base de conocimiento + contexto + pronóstico)
    C->>G: generateContent
    G-->>C: respuesta en el idioma del usuario
    C-->>W: JSON con reply y source
    W-->>U: muestra la respuesta
```

### Pipeline de preprocesamiento (machine learning)

1. **Lectura flexible** de Excel, CSV estándar o la exportación `utf-16` de la estación.
2. **Centinelas** del sensor (`--`, `--.-`, `---`) convertidos a nulos.
3. **Imputación:** interpolación temporal limitada (~1 hora), mediana en rachas largas y viento, moda en la dirección del viento; cálculo de punto de rocío y sensación térmica si faltan.
4. **Detección de valores atípicos** con IQR, Z-score, Isolation Forest y DBSCAN. **No se eliminan observaciones:** se etiquetan, y `outlier_consenso` marca la coincidencia de los métodos disponibles (viento y lluvia extremos pueden ser eventos reales).
5. **Salidas:** Excel descargable (datos, resumen y outliers), CSV alineado con la tabla `lecturas` y una estimación de las próximas 12 horas.
6. Laravel importa el CSV con `upsert` sobre la llave única `(estacion_id, fecha_lectura)`, por lo que reprocesar un archivo no duplica lecturas.

## 4. Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Base de datos | MySQL / MariaDB (SQLite también soportado por defecto en `.env.example`) |
| Frontend | Blade, HTML, CSS, JavaScript, Bootstrap 5.3, Bootstrap Icons, Chart.js 4 (por CDN) |
| ML / datos | Python 3, pandas, NumPy, scikit-learn, openpyxl |
| IA conversacional | Google Gemini API (con respaldo OpenAI-compatible y modo local) |
| Utilidades PHP | `league/csv` para importar lecturas, `symfony/process` para ejecutar Python |
| Despliegue | Docker, Render (aplicación) y Aiven (MySQL) |
| Calidad | Laravel Pint (formato), PHPUnit |

## 5. Estructura del proyecto

```
SIPRAC/
├── app/
│   ├── Console/Commands/ImportLecturasCommand.php   # php artisan import:lecturas {estacion_id}
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/ (LoginController, RegisterController)
│   │   │   ├── Agricultor/EmpleadoController.php
│   │   │   ├── ChatbotController.php                # integración con el LLM
│   │   │   ├── DashboardController.php
│   │   │   └── DataNormalizationController.php
│   │   └── Middleware/CheckRole.php                  # control de acceso por rol
│   ├── Models/ (User, Role, Finca, Estacion, Lectura, Alerta, Activacion)
│   └── Services/WeatherPredictionService.php        # estimación de las próximas horas
├── config/
│   ├── services.php                                  # credenciales de Gemini / OpenAI
│   └── siprac_knowledge.php                          # base de conocimiento del chatbot
├── database/ (migrations, seeders, factories)
├── docker/ · Dockerfile                              # imagen para el despliegue en Render
├── public/asset/ (css, js, imágenes)
├── python/normalize_data.py                          # preprocesamiento y outliers
├── resources/views/
│   ├── auth/ · agricultor/empleados/ · dashboard/
│   └── dashboard/layout.blade.php                    # layout + widget del chatbot
├── routes/web.php
└── .env.example
```

## 6. Roles y permisos

| Rol | Cómo se obtiene | Acceso |
|---|---|---|
| Solicitante | Se asigna automáticamente al registrarse | Vista de demostración con datos generales mientras es verificado |
| Agricultor | Lo asigna un administrador | Sus fincas, datos, alertas, preprocesamiento y gestión de empleados |
| Técnico | Lo asigna un administrador | Fincas asignadas, datos y estado de estaciones |
| Administrador | Usuario semilla | Todo el sistema |

Además del rol global, cada usuario puede estar asociado a una finca con un **nivel de permiso** (tabla `finca_usuario`): `1 = ver`, `2 = alertas`, `3 = control`. El middleware `CheckRole` valida el rol y cierra la sesión de cuentas desactivadas. El chatbot usa estos mismos permisos para decidir qué lecturas puede ver cada persona.

## 7. Instalación paso a paso

### Requisitos

- PHP 8.2 o superior (extensiones `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `curl`)
- [Composer](https://getcomposer.org/)
- MySQL 8 o MariaDB
- Python 3.10 o superior
- Una API key gratuita de [Google AI Studio](https://aistudio.google.com/)

### Pasos

**1. Clonar el repositorio**

```bash
git clone <URL-DEL-REPOSITORIO>
cd SIPRAC
```

**2. Instalar dependencias de PHP**

```bash
composer install
```

**3. Crear el archivo de entorno y la llave de la aplicación**

```bash
cp .env.example .env        # en Windows (CMD): copy .env.example .env
php artisan key:generate
```

**4. Crear la base de datos y configurarla en `.env`**

```sql
CREATE DATABASE siprac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siprac
DB_USERNAME=root
DB_PASSWORD=
```

**5. Instalar las dependencias de Python**

```bash
python -m venv venv
source venv/bin/activate          # Windows: venv\Scripts\activate
pip install -r python/requirements.txt
```

Si usas un entorno virtual, indica su intérprete en `.env` con `PYTHON_BIN` (ver sección 8).

**6. Ejecutar migraciones y datos iniciales (roles y usuarios de prueba)**

```bash
php artisan migrate --seed
```

**7. Configurar la API key de Gemini** en `.env` (ver sección 8) y limpiar la caché de configuración:

```bash
php artisan config:clear
```

**8. Iniciar el servidor**

```bash
php artisan serve
```

Abre `http://127.0.0.1:8000`.

> Las vistas usan Bootstrap y Chart.js por CDN, por lo que **no es necesario ejecutar `npm install`** para usar la aplicación.

## 8. Configuración del `.env`

Nunca subas tu `.env` ni pegues keys reales en el repositorio (el archivo ya está en `.gitignore`).

| Variable | Obligatoria | Descripción |
|---|---|---|
| `APP_KEY` | Sí | Se genera con `php artisan key:generate` |
| `APP_TIMEZONE` | Sí | `America/Bogota` |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Sí | Conexión a MySQL |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Sí | `database` (las tablas las crean las migraciones) |
| `GEMINI_API_KEY` | Para el chatbot con IA | Key de Google AI Studio |
| `GEMINI_API_URL` | No | Por defecto `https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent`. Cámbiala solo si necesitas otro modelo |
| `AI_API_KEY`, `AI_API_URL`, `AI_MODEL` | No | Proveedor alternativo compatible con OpenAI (se usa solo si no hay `GEMINI_API_KEY`) |
| `PYTHON_BIN` | No | Intérprete de Python para el preprocesamiento. Por defecto `python` (Windows) o `python3` (Linux/Mac) |

Ejemplo (sin valores reales):

```env
GEMINI_API_KEY=
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent

# Opcional
AI_API_KEY=
AI_API_URL=https://api.openai.com/v1/chat/completions
AI_MODEL=gpt-4o-mini
PYTHON_BIN=
```

**Prioridad de proveedores del chatbot:** Gemini → OpenAI-compatible → respuestas locales. Si el proveedor falla, el error queda registrado en `storage/logs/laravel.log` y el asistente responde con el modo local.

## 9. Despliegue

- **Enlace en vivo:** [https://siprac.onrender.com/dashboard](https://siprac.onrender.com/dashboard) (redirige al inicio de sesión si no hay una sesión activa)
- **Servicio de despliegue:** Render (Web Service con Docker)
- **Base de datos:** MySQL gestionada en Aiven, con conexión SSL
- **Despliegue continuo:** cada `git push` a la rama `main` reconstruye la imagen y redespliega la aplicación automáticamente; el contenedor ejecuta las migraciones al arrancar.

> Al usar el plan gratuito de Render, el servicio se suspende tras un periodo de inactividad, por lo que la primera carga puede tardar unos segundos.

Las variables de entorno se definen en el panel de Render (nunca en el código): `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, las `DB_*` de Aiven, `MYSQL_ATTR_SSL_CA`, `PYTHON_BIN` y `GEMINI_API_KEY`. La imagen Docker incluye PHP y Python 3 con las dependencias de `python/requirements.txt`, porque el preprocesamiento se ejecuta como proceso desde Laravel.

---

## 10. Declaración de la Persona Transhumana (UCundinamarca)

El proyecto integra la **Declaración de la Persona Transhumana de la Universidad de Cundinamarca** de forma **conceptual** (principios de diseño: la persona decide, la tecnología es un medio, no se hace daño) y **técnica** (permisos por finca, restricción de dominio en el System Prompt, base de conocimiento con los 12 principios en `config/siprac_knowledge.php`, honestidad con los datos, outliers etiquetados y no eliminados, pronóstico explicable y asistente multiidioma). El propio chatbot puede explicar esta integración principio por principio.

A continuación se responden las dos preguntas reflexivas de la actividad, en español y en inglés.

### Español

**¿De qué manera el agente/chatbot respeta y fomenta los principios de la Declaración de la Persona Transhumana de la Universidad de Cundinamarca en el contexto del aprendizaje y la interacción tecnológica?**

La Declaración abre con la idea de que la persona organiza y dirige su propia vida, y se reconoce libre, autónoma y responsable, capaz de dictarse sus propias reglas a través del diálogo. En SIPRAC, ese principio se tradujo en una decisión de diseño concreta: el asistente nunca decide ni actúa en lugar del agricultor. Explica, orienta y presenta estimaciones indicando su nivel de confianza, pero la decisión de proteger un cultivo sigue en manos de quien conoce su finca. El asistente no activa mecanismos como el riego antiheladas ni afirma haberlos activado, y el sistema de permisos por finca (solo ver, recibir alertas o controlar) distribuye la responsabilidad según el rol de cada persona. Así, la autonomía va acompañada de responsabilidad, tal como lo plantea la Declaración.

La Declaración también habla de aprender de la propia vida y de las buenas prácticas de los semejantes, y de mejorarse y reinventarse cada día. En el plano del aprendizaje, el chatbot funciona como un acompañante pedagógico: responde en el idioma de quien pregunta y explica conceptos como las heladas, la humedad relativa o la lluvia acumulada, de modo que el agricultor no recibe solo una alerta, sino también el porqué detrás de ella. Para que ese aprendizaje sea confiable, el asistente responde únicamente con base en su base de conocimiento y en las lecturas reales de las estaciones a las que el usuario tiene acceso, y reconoce cuando un dato no está disponible en lugar de inventarlo. La misma lógica atraviesa el componente de machine learning: en el preprocesamiento los valores atípicos se etiquetan pero no se eliminan, porque una ráfaga o una lluvia extrema pueden ser eventos reales y relevantes, y el pronóstico horario es una estimación estadística explicable que muestra su confianza, de modo que cualquier persona puede revisar cómo se llegó a la conclusión.

La Declaración pide ir más allá de uno mismo, tomar la posición del otro, de los seres vivos y de la naturaleza, y elegir actos que no hagan daño a la sociedad, a la convivencia ni al entorno. SIPRAC nace precisamente para proteger los cultivos de familias agricultoras de la provincia de Ubaté frente a heladas y lluvias intensas, es decir, para cuidar el sustento de una comunidad y la vida vegetal de la que depende. El modelo de datos registra además el consumo de agua de cada activación de protección, lo que permite evaluar si el recurso se usa de forma responsable. El asistente aplica el mismo principio de no hacer daño: no emite alertas inventadas que lleven a gastar recursos o, peor aún, a confiarse sin motivo; no revela datos de fincas ajenas; y, si le preguntan algo fuera de su alcance, lo reconoce con honestidad.

Finalmente, la Declaración afirma que la persona se perfecciona no necesariamente a través de la tecnología y la ciencia. SIPRAC adopta esa humildad: los sensores y los modelos amplían la capacidad de observación del agricultor, pero no sustituyen su conocimiento del terreno ni el criterio de un agrónomo, y el asistente lo dice de forma explícita. El respeto por la dignidad, la diversidad y la no discriminación que subraya el texto institucional se refleja en que el asistente atiende a todas las personas con el mismo trato, en varios idiomas, sin requerir información personal sensible para funcionar. Y el cuidado del bienestar emocional, que la Declaración menciona junto con la salud corporal y mental, se refleja de manera modesta pero real en la reducción de la incertidumbre: saber a tiempo y con claridad qué puede ocurrir en la madrugada alivia la angustia de perder una cosecha.

---

### English

**How does the implemented AI agent/chatbot align with and support the Transhuman Person Declaration of Universidad de Cundinamarca regarding ethical AI integration, human augmentation, and educational transformation?**

*Ethical AI integration.* The Declaration begins with the idea that a person organizes and directs their own life and is free, autonomous, and responsible, able to set their own rules through dialogue. In SIPRAC, this became a concrete design rule: the assistant never decides or acts in place of the farmer. It explains, guides, and presents estimates together with their confidence level, but the decision to protect a crop stays with the person who knows the farm. The assistant does not trigger mechanisms such as anti-frost irrigation, nor does it claim to have done so, and the per-farm permission levels (view only, receive alerts, or control) distribute responsibility according to each person's role. Autonomy and responsibility therefore travel together, as the Declaration proposes. The same ethical stance appears in the machine learning pipeline: outliers are labeled but never deleted, because an extreme gust or downpour may be a real and relevant event, and the hourly forecast is an explainable statistical estimate that exposes its confidence instead of presenting itself as certainty.

The Declaration also asks that we go beyond ourselves, take the position of others, of living beings, and of nature, and choose actions that cause no harm to society, coexistence, or the environment. SIPRAC exists to protect the crops of farming families in the Ubaté province from frost and heavy rain, which means caring for a community's livelihood and the plant life it depends on. Its data model also records the water consumption of each protection activation so that the use of this resource can be evaluated. The assistant applies the same "do no harm" principle: it never fabricates alerts that could lead someone to waste resources or to feel falsely safe, it never reveals data from farms the user cannot access, and it openly acknowledges when a question is outside its scope.

*Human augmentation.* The Declaration states that a person improves themselves, not necessarily through technology and science. SIPRAC embraces that humility. Sensors and models extend the farmer's ability to observe conditions that are invisible or arrive in the middle of the night, but they do not replace local knowledge or an agronomist's judgment, and the assistant says so explicitly. Augmentation here means giving a person more and clearer information, never taking away their agency. Because the assistant answers in the user's own language and only from verified readings and its knowledge base, it also augments understanding without pretending to know what it does not.

*Educational transformation.* The Declaration speaks of learning from one's own life and from the good practices of others, and of improving and reinventing oneself every day. The chatbot works as a learning companion rather than a simple alert channel: it explains frost, relative humidity, or accumulated rainfall in clear language, so the farmer learns why a reading is worrying, not just that it is. Its answers are restricted to a structured knowledge base and real data, which makes the learning trustworthy, and its support for several languages widens access to that learning. Finally, the respect for dignity, diversity, and non-discrimination stressed in the institutional text is reflected in treating every user equally and in not needing sensitive personal information to function, while the Declaration's concern for emotional well-being is reflected, modestly but genuinely, in reducing uncertainty: knowing early and clearly what may happen before dawn eases the anxiety of losing a harvest.

---

## 11. Autores

- **Johan Steven Briceño Layton**
- **Jhon Sebastian Murcia Romero**

**Programa:** Ingeniería de Sistemas y Computación — Universidad de Cundinamarca
**Curso:** CADI Introducción a Machine Learning

Proyecto construido sobre el framework [Laravel](https://laravel.com), de código abierto bajo licencia [MIT](https://opensource.org/licenses/MIT).
