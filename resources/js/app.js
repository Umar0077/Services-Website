import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

Alpine.plugin(focus);
Alpine.plugin(collapse);

// Header: shadow once the page scrolls.
Alpine.data('siteHeader', () => ({
    scrolled: false,
    mobileOpen: false,
    init() {
        const update = () => { this.scrolled = window.scrollY > 8; };
        update();
        window.addEventListener('scroll', update, { passive: true });
        this.$watch('mobileOpen', (open) => {
            document.documentElement.classList.toggle('overflow-hidden', open);
            // Return keyboard focus to the menu button when the drawer closes.
            if (!open) this.$nextTick(() => this.$refs.menuButton?.focus());
        });
    },
}));

// Desktop dropdown: opens on hover or click, closes on Escape / outside click, fully keyboard operable.
Alpine.data('dropdown', () => ({
    open: false,
    timer: null,
    show() { clearTimeout(this.timer); this.open = true; },
    hide(delay = 120) { clearTimeout(this.timer); this.timer = setTimeout(() => { this.open = false; }, delay); },
    toggle() { this.open = !this.open; },
}));

// Client-side filter for the state directory (the full list is server-rendered, so it works without JS).
Alpine.data('stateFilter', () => ({
    q: '',
    matches(name) {
        return this.q.trim() === '' || name.toLowerCase().includes(this.q.trim().toLowerCase());
    },
}));

Alpine.start();

// Click-to-call tracking for configured analytics / GTM (fires once per tap when dataLayer is active).
document.addEventListener('click', (event) => {
    const link = event.target.closest?.('a[data-track="call"]');
    if (!link || !Array.isArray(window.dataLayer)) return;
    window.dataLayer.push({
        event: 'click_to_call',
        phone_href: link.getAttribute('href') || '',
        cta_location: link.dataset.ctaLocation || 'page',
        page_path: window.location.pathname,
    });
});

// Subtle one-time reveal. Skipped entirely when the user prefers reduced motion.
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const reveals = document.querySelectorAll('.reveal');
if (reduceMotion || !('IntersectionObserver' in window)) {
    reveals.forEach((el) => el.classList.add('is-visible'));
} else {
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    reveals.forEach((el) => io.observe(el));
}
