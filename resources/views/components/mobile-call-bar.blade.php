{{-- Phones only: persistent call button. The body has matching bottom padding so it never covers content. --}}
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-white/10 bg-teal-900/97 px-4 pt-3 pb-[max(12px,env(safe-area-inset-bottom))] backdrop-blur md:hidden">
    <div class="flex items-center gap-3">
        <div class="min-w-0 flex-1 leading-tight">
            <p class="truncate text-sm font-semibold text-white">Heating or cooling problem?</p>
            <p class="truncate text-[0.8125rem] text-on-dark-muted">Call to get connected</p>
        </div>
        <x-phone class="btn btn-primary min-h-12 shrink-0 px-5" />
    </div>
</div>
