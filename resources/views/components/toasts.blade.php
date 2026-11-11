{{--
    Website toast alerts. Server messages arrive via session('toast') = ['type' => success|error|warning|info, 'title', 'message'].
    JavaScript can also call window.siteToast(type, title, message).
    Rendered as a polite live region so screen readers announce them.
--}}
@php
    $initial = [];
    if ($flash = session('toast')) {
        $initial[] = $flash;
    } elseif ($errors->any() && ! session('toast_suppress_errors')) {
        $initial[] = ['type' => 'error', 'title' => 'Please check the form', 'message' => $errors->count() > 1 ? "{$errors->count()} fields need your attention." : $errors->first()];
    }
@endphp
<div x-data="{
        items: [],
        add(t) {
            const id = Date.now() + Math.random();
            this.items.push({ id, type: t.type || 'info', title: t.title || '', message: t.message || '' });
            setTimeout(() => this.remove(id), t.type === 'error' ? 9000 : 6000);
        },
        remove(id) { this.items = this.items.filter(i => i.id !== id) },
        init() {
            @js($initial).forEach(t => this.add(t));
            window.siteToast = (type, title, message) => this.add({ type, title, message });
            window.addEventListener('offline', () => this.add({ type: 'warning', title: 'You are offline', message: 'Check your connection before sending a request.' }));
        },
     }"
     class="pointer-events-none fixed inset-x-4 top-[calc(var(--header-h)+12px)] z-[70] flex flex-col items-center gap-3 sm:inset-x-auto sm:right-5 sm:items-end"
     role="status" aria-live="polite">
    <template x-for="item in items" :key="item.id">
        <div x-transition:enter="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="pointer-events-auto flex w-full max-w-[380px] items-start gap-3 rounded-xl border bg-white p-4 shadow-[var(--shadow-lift)]"
             :class="{
                'border-[#FECDCA]': item.type === 'error',
                'border-[#FEDF89]': item.type === 'warning',
                'border-mint-200': item.type === 'success' || item.type === 'info',
             }">
            <span class="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-full"
                  :class="{
                    'bg-[#FEF3F2] text-[#B42318]': item.type === 'error',
                    'bg-[#FFFAEB] text-[#B54708]': item.type === 'warning',
                    'bg-mint text-teal-700': item.type === 'success' || item.type === 'info',
                  }">
                <svg x-show="item.type === 'success'" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                <svg x-show="item.type !== 'success'" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-[0.9375rem] font-semibold text-ink" x-text="item.title"></p>
                <p class="mt-0.5 text-sm leading-relaxed text-body" x-show="item.message" x-text="item.message"></p>
            </div>
            <button type="button" class="-m-1 inline-flex size-8 shrink-0 items-center justify-center rounded-lg text-muted hover:bg-page hover:text-ink" @click="remove(item.id)">
                <span class="sr-only">Dismiss</span>
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </template>
</div>
