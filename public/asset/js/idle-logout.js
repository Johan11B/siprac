/**
 * SIPRAC — Idle Logout
 * Automatically logs the user out after SESSION_LIFETIME minutes of inactivity.
 * Shows a Bootstrap modal warning 1 minute before expiry.
 *
 * Usage in Blade:
 *   <script src="{{ asset('asset/js/idle-logout.js') }}" data-minutes="{{ config('session.lifetime') }}" defer></script>
 *
 * Design decision: we do NOT use beforeunload/sendBeacon to logout on tab close,
 * because those events also fire on page reload/navigation and are unreliable.
 * Instead, SESSION_EXPIRE_ON_CLOSE=true (server-side cookie config) handles that.
 */
(() => {
    'use strict';

    const script = document.currentScript || document.querySelector('script[data-minutes]');
    if (!script) return;

    const totalMinutes = parseInt(script.dataset.minutes, 10) || 30;
    const totalMs      = totalMinutes * 60 * 1000;
    const warningMs    = 60 * 1000; // Show warning 1 minute before
    const keepAliveUrl = script.dataset.keepalive || '/session/keep-alive';

    let timeoutId   = null;
    let warningId   = null;
    let modalEl     = null;
    let modalInstance = null;

    // ---- Create the Bootstrap modal (injected once) ----
    function ensureModal() {
        if (modalEl) return;

        const div = document.createElement('div');
        div.innerHTML = `
            <div class="modal fade" id="idleLogoutModal" tabindex="-1" aria-labelledby="idleLogoutLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                        <div class="modal-header" style="background: linear-gradient(135deg, #115e59, #0f766e); color: #fff; border: none; padding: 1rem 1.25rem;">
                            <h5 class="modal-title" id="idleLogoutLabel" style="font-size: 0.95rem; font-weight: 700;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: -3px; margin-right: 6px;">
                                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
                                </svg>
                                Sesión por expirar
                            </h5>
                        </div>
                        <div class="modal-body text-center" style="padding: 1.5rem;">
                            <p style="font-size: 0.88rem; color: #334155; margin-bottom: 0.5rem;">Tu sesión está por expirar por inactividad.</p>
                            <p style="font-size: 0.78rem; color: #64748b;">¿Deseas seguir conectado?</p>
                        </div>
                        <div class="modal-footer justify-content-center" style="border: none; padding: 0 1.25rem 1.25rem;">
                            <button type="button" class="btn" id="idleKeepAliveBtn" style="background: #0f766e; color: #fff; border-radius: 10px; padding: 0.5rem 1.5rem; font-weight: 600; font-size: 0.85rem;">
                                Seguir conectado
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

        modalEl = div.firstElementChild;
        document.body.appendChild(modalEl);

        // Need Bootstrap's Modal class
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            modalInstance = new bootstrap.Modal(modalEl);
        }

        document.getElementById('idleKeepAliveBtn').addEventListener('click', () => {
            keepAlive();
        });
    }

    // ---- Keep alive ----
    function keepAlive() {
        if (modalInstance) modalInstance.hide();

        fetch(keepAliveUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).catch(() => {}); // silent

        resetTimers();
    }

    // ---- Logout ----
    function doLogout() {
        if (modalInstance) modalInstance.hide();
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            logoutForm.submit();
        } else {
            window.location.href = '/login';
        }
    }

    // ---- Timer management ----
    function resetTimers() {
        clearTimeout(warningId);
        clearTimeout(timeoutId);

        // Show warning 1 minute before expiry
        warningId = setTimeout(() => {
            ensureModal();
            if (modalInstance) modalInstance.show();

            // Auto-logout after the remaining 1 minute
            timeoutId = setTimeout(doLogout, warningMs);
        }, totalMs - warningMs);
    }

    // ---- Activity listeners ----
    let throttled = false;

    function onActivity() {
        // Don't reset if modal is already showing
        if (modalEl && modalEl.classList.contains('show')) return;

        if (throttled) return;
        throttled = true;
        setTimeout(() => { throttled = false; }, 1000); // throttle to 1s

        resetTimers();
    }

    ['click', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, onActivity, { passive: true });
    });

    // mousemove with heavier throttle (2s)
    let mouseMoveThrottled = false;
    document.addEventListener('mousemove', () => {
        if (modalEl && modalEl.classList.contains('show')) return;
        if (mouseMoveThrottled) return;
        mouseMoveThrottled = true;
        setTimeout(() => { mouseMoveThrottled = false; }, 2000);
        resetTimers();
    }, { passive: true });

    // Also reset on chatbot activity
    document.addEventListener('siprac:chatbot-activity', onActivity);

    // ---- Start ----
    resetTimers();
})();
