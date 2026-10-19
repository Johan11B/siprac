{{--
    SIPRAC Chatbot Widget — Reusable partial
    =========================================
    Parameters (all optional with sensible defaults):
      $endpoint    (string)  URL to POST messages to
      $mode        (string)  'private' | 'public'
      $greeting    (string)  Welcome message shown on open
      $suggestions (array)   Quick-reply suggestions.
                             Keys = label, values = question text.
                             A plain indexed array is also accepted.
--}}
@php
    $chatEndpoint    = $endpoint    ?? '';
    $chatMode        = $mode        ?? 'private';
    $chatGreeting    = $greeting    ?? 'Hola, soy el asistente de SIPRAC. ¿En qué puedo ayudarte?';
    $chatSuggestions = $suggestions ?? [];
    $chatLoginUrl    = route('login');
    $chatAvatarUrl   = asset('asset/images/icono.webp');
@endphp

{{-- Chatbot standalone CSS --}}
<link rel="stylesheet" href="{{ asset('asset/css/chatbot.css') }}">

{{-- marked + DOMPurify for safe Markdown rendering --}}
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js" defer></script>

<section
    class="siprac-chatbot"
    aria-label="Asistente SIPRAC"
    data-endpoint="{{ $chatEndpoint }}"
    data-mode="{{ $chatMode }}"
    data-login-url="{{ $chatLoginUrl }}"
>
    {{-- Hidden avatar reference used by chatbot.js --}}
    <div class="siprac-chatbot__avatar" style="display:none;" aria-hidden="true">
        <img src="{{ $chatAvatarUrl }}" alt="SIPRAC" width="28" height="28">
    </div>

    {{-- ---- Toggle button ---- --}}
    <button
        type="button"
        class="siprac-chatbot__toggle"
        id="chatbotToggle"
        aria-expanded="false"
        aria-controls="chatbotPanel"
        title="Abrir asistente SIPRAC"
    >
        {{-- Stars / sparkles SVG (inline — no Bootstrap Icons dependency) --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M7.657 6.247c.11-.33.576-.33.686 0l.645 1.937a2.89 2.89 0 0 0 1.829 1.828l1.936.645c.33.11.33.576 0 .686l-1.937.645a2.89 2.89 0 0 0-1.828 1.829l-.645 1.936a.361.361 0 0 1-.686 0l-.645-1.937a2.89 2.89 0 0 0-1.828-1.828l-1.937-.645a.361.361 0 0 1 0-.686l1.937-.645a2.89 2.89 0 0 0 1.828-1.828zM3.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387A1.73 1.73 0 0 0 4.593 5.69l-.387 1.162a.217.217 0 0 1-.412 0L3.407 5.69A1.73 1.73 0 0 0 2.31 4.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.73 1.73 0 0 0 3.407 2.31zM10.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732L9.1 2.137a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"/>
        </svg>
        <span class="siprac-chatbot__toggle-label">Asistente IA</span>
    </button>

    {{-- ---- Panel ---- --}}
    <div class="siprac-chatbot__panel" id="chatbotPanel" hidden>

        {{-- Header --}}
        <header class="siprac-chatbot__header">
            <div>
                <span class="siprac-chatbot__eyebrow">SIPRAC</span>
                <h2>Asistente agroclimático</h2>
                <p>Pregúntame sobre el proyecto, clima y cultivos.</p>
            </div>
            <button
                type="button"
                class="siprac-chatbot__close"
                id="chatbotClose"
                aria-label="Cerrar asistente"
            >
                {{-- X icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                </svg>
            </button>
        </header>

        {{-- Messages --}}
        <div class="siprac-chatbot__messages" id="chatbotMessages" aria-live="polite" aria-atomic="false">
            {{-- Greeting message --}}
            <div class="siprac-chatbot__msg-row siprac-chatbot__msg-row--assistant">
                <div class="siprac-chatbot__avatar">
                    <img src="{{ $chatAvatarUrl }}" alt="SIPRAC" width="28" height="28">
                </div>
                <div class="siprac-chatbot__msg-content">
                    <div class="siprac-chatbot__message siprac-chatbot__message--assistant">
                        {{ $chatGreeting }}
                    </div>
                    <span class="siprac-chatbot__time" aria-hidden="true"></span>
                </div>
            </div>
        </div>

        {{-- Suggestion pills --}}
        @if(!empty($chatSuggestions))
        <div class="siprac-chatbot__suggestions" aria-label="Preguntas sugeridas">
            @foreach($chatSuggestions as $label => $question)
                @php
                    // Support plain indexed arrays (value = question, label = value)
                    $btnLabel    = is_string($label) ? $label : $question;
                    $btnQuestion = $question;
                @endphp
                <button type="button" data-chatbot-suggestion="{{ $btnQuestion }}">
                    {{ $btnLabel }}
                </button>
            @endforeach
        </div>
        @endif

        {{-- Input form --}}
        <form class="siprac-chatbot__form" id="chatbotForm" novalidate>
            <label class="visually-hidden" for="chatbotInput">Escribe tu pregunta</label>
            <input
                id="chatbotInput"
                name="message"
                type="text"
                maxlength="1000"
                autocomplete="off"
                placeholder="Escribe una pregunta…"
                required
            >
            <button type="submit" aria-label="Enviar pregunta" title="Enviar pregunta">
                {{-- Arrow-up icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5"/>
                </svg>
            </button>
        </form>

        <p class="siprac-chatbot__disclaimer">
            La información es orientativa. Verifica las alertas y lecturas de tu finca.
        </p>
    </div>{{-- /.siprac-chatbot__panel --}}
</section>

{{-- Chatbot standalone JS (reads data-* from the .siprac-chatbot container) --}}
<script src="{{ asset('asset/js/chatbot.js') }}" defer></script>
