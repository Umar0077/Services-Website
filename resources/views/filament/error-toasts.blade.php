{{--
    Admin error alerts. Every failed Livewire request (server error, expired session, permission, rate limit,
    maintenance, lost connection) shows a toast instead of a raw error page or browser dialog.

    Toasts are drawn entirely in the browser: Filament's own notifications need a server round-trip, which
    cannot work when the connection is down or the session has expired.
--}}
<style>
    #hvac-toasts { position: fixed; top: 16px; right: 16px; z-index: 2147483000; display: flex; flex-direction: column; gap: 10px; width: min(380px, calc(100vw - 32px)); pointer-events: none; }
    .hvac-toast { pointer-events: auto; display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; border-radius: 12px; background: #fff; color: #111827; border: 1px solid #e5e7eb; border-left-width: 4px; box-shadow: 0 18px 40px -16px rgba(14, 41, 56, .35); font: 14px/1.45 Inter, ui-sans-serif, system-ui, sans-serif; opacity: 0; transform: translateY(-6px); transition: opacity .2s ease, transform .2s ease; }
    .hvac-toast.is-in { opacity: 1; transform: none; }
    .hvac-toast--danger { border-left-color: #d92d20; }
    .hvac-toast--warning { border-left-color: #f79009; }
    .hvac-toast--success { border-left-color: #12b76a; }
    .hvac-toast strong { display: block; font-weight: 600; margin-bottom: 2px; }
    .hvac-toast p { margin: 0; color: #4b5563; }
    .hvac-toast button { all: unset; cursor: pointer; }
    .hvac-toast .hvac-toast__action { display: inline-block; margin-top: 8px; padding: 6px 12px; border-radius: 8px; background: #0e2938; color: #fff; font-weight: 600; font-size: 13px; }
    .hvac-toast .hvac-toast__close { margin-left: auto; padding: 0 4px; color: #6b7280; font-size: 18px; line-height: 1; }
    .dark .hvac-toast { background: #18212f; color: #f3f4f6; border-color: rgba(255,255,255,.1); }
    .dark .hvac-toast p { color: #c7cdd6; }
    @media (prefers-reduced-motion: reduce) { .hvac-toast { transition: none; } }
</style>
<div id="hvac-toasts" role="status" aria-live="polite"></div>
<script>
    (() => {
        const debug = @json((bool) config('app.debug'));
        const box = document.getElementById('hvac-toasts');

        const toast = (title, body, status = 'danger', { persistent = false, reload = false } = {}) => {
            const el = document.createElement('div');
            el.className = `hvac-toast hvac-toast--${status}`;
            el.setAttribute('role', status === 'danger' ? 'alert' : 'status');
            const text = document.createElement('div');
            const t = document.createElement('strong'); t.textContent = title;
            const p = document.createElement('p'); p.textContent = body || '';
            text.append(t, p);
            if (reload) {
                const a = document.createElement('button');
                a.type = 'button'; a.className = 'hvac-toast__action'; a.textContent = 'Refresh page';
                a.addEventListener('click', () => window.location.reload());
                text.append(a);
            }
            const close = document.createElement('button');
            close.type = 'button'; close.className = 'hvac-toast__close'; close.setAttribute('aria-label', 'Dismiss'); close.textContent = '×';
            const remove = () => { el.classList.remove('is-in'); setTimeout(() => el.remove(), 200); };
            close.addEventListener('click', remove);
            el.append(text, close);
            box.append(el);
            requestAnimationFrame(() => el.classList.add('is-in'));
            setTimeout(() => el.classList.add('is-in'), 50); // in case animation frames are paused
            if (!persistent) setTimeout(remove, 8000);
            while (box.children.length > 4) box.firstElementChild.remove();
        };
        window.hvacToast = toast;

        const messages = {
            403: ['Not allowed', 'Your account does not have permission to do that.'],
            404: ['Not found', 'This item no longer exists. It may have been deleted by someone else.'],
            413: ['File too large', 'The upload is larger than the server allows. Try a smaller file.'],
            419: ['Session expired', 'Your session timed out. Refresh the page to continue — unsaved changes may need to be entered again.'],
            429: ['Too many requests', 'Please wait a moment, then try again.'],
            503: ['Site in maintenance', 'The site is being updated. Please try again in a minute.'],
        };

        let last = 0;
        const throttle = () => { if (Date.now() - last < 800) return true; last = Date.now(); return false; };

        const register = () => {
            if (!window.Livewire?.interceptRequest) return;

            window.Livewire.interceptRequest(({ onError, onFailure }) => {
                onError(({ response, body, preventDefault }) => {
                    if (response.aborted) return;
                    preventDefault();
                    if (throttle()) return;
                    const status = response.status;
                    const [title, text] = messages[status]
                        ?? ['Something went wrong', 'The server could not complete that request, so your last change may not have been saved. Please try again. If it keeps happening, contact your site administrator.'];
                    toast(title, text, status === 429 ? 'warning' : 'danger', { persistent: status === 419 || status >= 500, reload: status === 419 });
                    if (debug && status >= 500) console.error('[HVAC admin] Server error ' + status, body);
                });

                onFailure(() => {
                    if (throttle()) return;
                    toast('Connection problem', navigator.onLine === false
                        ? 'You are offline. Changes cannot be saved until your connection is back.'
                        : 'Could not reach the server. Check your internet connection and try again.', 'danger');
                });
            });
        };

        window.Livewire ? register() : document.addEventListener('livewire:init', register);

        window.addEventListener('offline', () => toast('You are offline', 'Changes cannot be saved until your connection is back.', 'warning', { persistent: true }));
        window.addEventListener('online', () => toast('Back online', 'Your connection is restored.', 'success'));
    })();
</script>
