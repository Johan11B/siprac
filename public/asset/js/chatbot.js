/**
 * SIPRAC Chatbot Widget — Standalone JavaScript
 * Reads configuration from the .siprac-chatbot container's data attributes.
 * Requires: marked (CDN) and DOMPurify (CDN) for Markdown rendering.
 * Falls back to textContent if libraries are unavailable.
 */
(() => {
    'use strict';

    const container = document.querySelector('.siprac-chatbot');
    if (!container) return;

    const endpoint  = container.dataset.endpoint;
    const mode      = container.dataset.mode || 'private';
    const loginUrl  = container.dataset.loginUrl || '/login';

    const toggle   = document.getElementById('chatbotToggle');
    const close    = document.getElementById('chatbotClose');
    const panel    = document.getElementById('chatbotPanel');
    const form     = document.getElementById('chatbotForm');
    const input    = document.getElementById('chatbotInput');
    const messages = document.getElementById('chatbotMessages');

    const csrfMeta  = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.content : '';

    const avatarSrc = container.querySelector('.siprac-chatbot__avatar img');
    const avatarUrl = avatarSrc ? avatarSrc.src : '';

    // ---- Markdown helpers ----
    function canRenderMarkdown() {
        return typeof marked !== 'undefined' && typeof DOMPurify !== 'undefined';
    }

    function renderMarkdown(text) {
        if (!canRenderMarkdown()) return null;

        try {
            const raw = marked.parse(text, { breaks: true, gfm: true });
            const clean = DOMPurify.sanitize(raw, {
                ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'ul', 'ol', 'li', 'code', 'pre', 'a', 'blockquote', 'h3', 'h4'],
                ALLOWED_ATTR: ['href', 'target', 'rel'],
            });
            return clean;
        } catch (e) {
            return null;
        }
    }

    // ---- Time formatting ----
    function formatTime() {
        return new Intl.DateTimeFormat('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date());
    }

    // ---- Panel open/close ----
    function setOpen(isOpen) {
        panel.hidden = !isOpen;
        toggle.setAttribute('aria-expanded', String(isOpen));
        if (isOpen) {
            input.focus();
        }
    }

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    close.addEventListener('click', () => setOpen(false));

    // Close with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) {
            setOpen(false);
        }
    });

    // ---- Set initial timestamp on greeting message ----
    const greetingTime = container.querySelector('.siprac-chatbot__time');
    if (greetingTime) greetingTime.textContent = formatTime();

    // ---- Create message ----
    function addMessage(text, type, opts = {}) {
        const row = document.createElement('div');
        row.className = `siprac-chatbot__msg-row siprac-chatbot__msg-row--${type}`;

        // Avatar (only for assistant)
        if (type === 'assistant' && avatarUrl) {
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'siprac-chatbot__avatar';
            const img = document.createElement('img');
            img.src = avatarUrl;
            img.alt = 'SIPRAC';
            img.width = 28;
            img.height = 28;
            avatarDiv.appendChild(img);
            row.appendChild(avatarDiv);
        }

        const content = document.createElement('div');
        content.className = 'siprac-chatbot__msg-content';

        const bubble = document.createElement('div');
        bubble.className = `siprac-chatbot__message siprac-chatbot__message--${type}`;

        if (opts.isError) {
            bubble.classList.add('siprac-chatbot__message--error');
        }

        if (type === 'assistant' && !opts.isError) {
            const html = renderMarkdown(text);
            if (html) {
                bubble.innerHTML = html;
                // Add target="_blank" to all rendered links
                bubble.querySelectorAll('a').forEach(a => {
                    a.setAttribute('target', '_blank');
                    a.setAttribute('rel', 'noopener noreferrer');
                });
            } else {
                bubble.textContent = text;
            }
        } else if (opts.isError && opts.html) {
            bubble.innerHTML = opts.html;
        } else {
            bubble.textContent = text;
        }

        content.appendChild(bubble);

        // Copy button (assistant messages only, not errors)
        if (type === 'assistant' && !opts.isError && !opts.isTyping) {
            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'siprac-chatbot__copy';
            copyBtn.setAttribute('aria-label', 'Copiar respuesta');
            copyBtn.title = 'Copiar';
            // Clipboard SVG
            copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/></svg>';
            copyBtn.addEventListener('click', () => {
                const plainText = bubble.textContent || bubble.innerText;
                navigator.clipboard.writeText(plainText).then(() => {
                    copyBtn.innerHTML = '✓';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/></svg>';
                    }, 1500);
                });
            });
            content.appendChild(copyBtn);
        }

        // Timestamp
        if (!opts.isTyping) {
            const time = document.createElement('span');
            time.className = 'siprac-chatbot__time';
            time.setAttribute('aria-hidden', 'true');
            time.textContent = formatTime();
            content.appendChild(time);
        }

        row.appendChild(content);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
        return row;
    }

    // ---- Typing indicator ----
    function addTypingIndicator() {
        const row = document.createElement('div');
        row.className = 'siprac-chatbot__msg-row siprac-chatbot__msg-row--assistant';
        row.id = 'chatbotTyping';

        if (avatarUrl) {
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'siprac-chatbot__avatar';
            const img = document.createElement('img');
            img.src = avatarUrl;
            img.alt = 'SIPRAC';
            img.width = 28;
            img.height = 28;
            avatarDiv.appendChild(img);
            row.appendChild(avatarDiv);
        }

        const content = document.createElement('div');
        content.className = 'siprac-chatbot__msg-content';

        const typing = document.createElement('div');
        typing.className = 'siprac-chatbot__typing';
        typing.setAttribute('aria-label', 'Escribiendo respuesta');
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('span');
            dot.className = 'siprac-chatbot__typing-dot';
            typing.appendChild(dot);
        }

        content.appendChild(typing);
        row.appendChild(content);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
        return row;
    }

    function removeTypingIndicator() {
        const el = document.getElementById('chatbotTyping');
        if (el) el.remove();
    }

    // ---- Suggestion buttons ----
    document.querySelectorAll('[data-chatbot-suggestion]').forEach((button) => {
        button.addEventListener('click', () => {
            input.value = button.dataset.chatbotSuggestion;
            form.requestSubmit();
        });
    });

    // ---- Form submission ----
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        addMessage(message, 'user');
        input.value = '';
        input.disabled = true;
        const typing = addTypingIndicator();

        // Dispatch custom event so idle-logout knows chatbot is active
        document.dispatchEvent(new CustomEvent('siprac:chatbot-activity'));

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message }),
            });

            removeTypingIndicator();

            if (response.status === 401 || response.status === 419) {
                const errorHtml = 'Tu sesión expiró. <a href="' + loginUrl + '">Inicia sesión de nuevo.</a>';
                addMessage('', 'assistant', { isError: true, html: errorHtml });
                return;
            }

            if (response.status === 429) {
                addMessage('Has enviado demasiados mensajes. Espera un momento antes de intentar de nuevo.', 'assistant', { isError: true });
                return;
            }

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || 'No se pudo procesar la pregunta.');
            }

            const data = await response.json();
            addMessage(data.reply, 'assistant');
        } catch (error) {
            removeTypingIndicator();
            addMessage(error.message || 'No hay conexión con el asistente. Inténtalo de nuevo.', 'assistant', { isError: true });
        } finally {
            input.disabled = false;
            input.focus();
        }
    });
})();
