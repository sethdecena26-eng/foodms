import './bootstrap';

// ── Alpine.js ────────────────────────────────────────────────────────────────
import Alpine from 'alpinejs';
import focus   from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

Alpine.plugin(focus);
Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();

// ── Flowbite ─────────────────────────────────────────────────────────────────
import 'flowbite';

// ── Global helpers ────────────────────────────────────────────────────────────

/**
 * Currency formatter for the PH Peso.
 * Usage: formatCurrency(1234.5) → "₱1,234.50"
 */
window.formatCurrency = (value) =>
    '₱' + parseFloat(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

/**
 * Flash a temporary toast notification.
 * Usage: window.toast('Order placed!', 'success')
 */
window.toast = (message, type = 'success') => {
    const colors = {
        success: 'bg-emerald-600',
        error:   'bg-red-500',
        warning: 'bg-amber-500',
        info:    'bg-blue-500',
    };

    const el = document.createElement('div');
    el.className = `fixed bottom-4 right-4 z-[9999] px-4 py-3 rounded-xl text-white text-sm
                    font-semibold shadow-xl flex items-center gap-2 animate-fade-up
                    ${colors[type] || colors.info}`;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
};

// ── Livewire global event hooks ───────────────────────────────────────────────
document.addEventListener('livewire:initialized', () => {

    // Listen for toast events dispatched from Livewire components
    // Usage in component: $this->dispatch('toast', message: 'Done!', type: 'success')
    Livewire.on('toast', ({ message, type }) => {
        window.toast(message, type ?? 'success');
    });
});