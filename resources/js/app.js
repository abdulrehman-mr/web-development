import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;

// Reveal-on-scroll for elements marked [data-reveal]; respects reduced motion.
document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('[data-reveal]');
    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -10% 0px' });
    items.forEach((el) => io.observe(el));
});

// Hero slideshow: crossfades slides, pauses on hover and for reduced motion.
Alpine.data('heroSlider', (count, seconds) => ({
    active: 0,
    timer: null,
    init() {
        this.play();
    },
    play() {
        this.pause();
        if (count < 2 || seconds <= 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        this.timer = setInterval(() => this.next(), seconds * 1000);
    },
    pause() {
        clearInterval(this.timer);
    },
    go(index) {
        this.active = (index + count) % count;
    },
    next() {
        this.go(this.active + 1);
    },
    prev() {
        this.go(this.active - 1);
    },
}));

// Searchable country picker used by booking and contact forms.
Alpine.data('countrySelect', (countries, popular, selected) => ({
    countries,
    popular,
    selected: selected || '',
    query: selected || '',
    open: false,
    active: 0,
    get results() {
        const q = this.query.trim().toLowerCase();
        if (q === '' || q === this.selected.toLowerCase()) {
            return [...this.popular, ...this.countries.filter((c) => !this.popular.includes(c))];
        }
        const starts = this.countries.filter((c) => c.toLowerCase().startsWith(q));
        const contains = this.countries.filter((c) => !c.toLowerCase().startsWith(q) && c.toLowerCase().includes(q));
        return [...starts, ...contains];
    },
    show() {
        this.open = true;
        this.active = Math.max(this.results.indexOf(this.selected), 0);
        this.$nextTick(() => this.scrollActive());
    },
    choose(country) {
        this.selected = country;
        this.query = country;
        this.open = false;
    },
    close() {
        this.open = false;
        this.query = this.selected;
    },
    move(step) {
        if (!this.open) return this.show();
        this.active = Math.min(Math.max(this.active + step, 0), this.results.length - 1);
        this.$nextTick(() => this.scrollActive());
    },
    scrollActive() {
        this.$refs.list?.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' });
    },
}));

// Booking configurator. Mirrors App\Support\BookingPricing; the server re-prices on submit.
Alpine.data('bookingConfigurator', (pricing, currency, initial) => ({
    ...pricing,
    currency,
    adults: initial.adults,
    children: initial.children,
    roomId: initial.roomId ?? pricing.rooms.find((r) => r.default)?.id ?? null,
    chosenExtras: initial.extras,
    chosenOptions: Object.fromEntries(pricing.groups.map((g) => [g.key, initial.options?.[g.key] ?? g.options[0].key])),
    get travellers() { return this.adults + this.children },
    adultPriceFor(travellers) {
        const tier = this.tiers.filter((t) => travellers >= t.min).pop();
        return tier ? tier.price : this.adultPrice;
    },
    get adultUnit() { return this.adultPriceFor(this.travellers) },
    get childUnit() { return this.childPrice ?? Math.round(this.adultUnit * this.childRate * 100) / 100 },
    get groupPrice() { return this.adultUnit < this.adultPrice },
    get nextTier() { return this.tiers.find((t) => t.min > this.travellers) ?? null },
    get room() { return this.rooms.find((r) => r.id === this.roomId) ?? null },
    get lines() {
        const lines = [{ label: `${this.adults} × adult${this.groupPrice ? ' (group price)' : ''}`, amount: this.adults * this.adultUnit }];
        if (this.children > 0) lines.push({ label: `${this.children} × child`, amount: this.children * this.childUnit });
        if (this.room && this.room.adjustment !== 0) {
            lines.push({ label: `${this.room.name} × ${this.adults} adult${this.adults > 1 ? 's' : ''}`, amount: this.adults * this.room.adjustment });
        }
        this.groups.forEach((g) => {
            const o = g.options.find((x) => x.key === this.chosenOptions[g.key]);
            if (!o || o.price === 0) return;
            const qty = o.per === 'person' ? this.travellers : (o.per === 'booking' ? 1 : this.adults);
            lines.push({ label: `${g.name}: ${o.label}${qty > 1 ? ` × ${qty}` : ''}`, amount: qty * o.price });
        });
        this.extras.filter((e) => this.chosenExtras.includes(e.key)).forEach((e) => {
            const qty = e.per === 'person' ? this.travellers : 1;
            lines.push({ label: e.name + (qty > 1 ? ` × ${qty}` : ''), amount: qty * e.price });
        });
        return lines;
    },
    get total() { return Math.max(this.lines.reduce((sum, l) => sum + l.amount, 0), 0) },
    step(field, delta, min) { this[field] = Math.min(40, Math.max(min, (Number(this[field]) || 0) + delta)) },
    money(base, signed = false) {
        const value = Math.round(Math.abs(base) * this.currency.rate).toLocaleString('en-US');
        const text = this.currency.after ? `${value} ${this.currency.symbol}` : this.currency.symbol + (/\p{L}$/u.test(this.currency.symbol) ? ' ' : '') + value;
        return (signed ? (base < 0 ? '− ' : '+ ') : (base < 0 ? '−' : '')) + text;
    },
    approx(base) {
        if (!this.currency.secondaryRate) return '';
        return 'about ' + this.currency.secondarySymbol + Math.round(base * this.currency.secondaryRate).toLocaleString('en-US');
    },
}));

Alpine.start();
