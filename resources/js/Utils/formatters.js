/**
 * Format a date string to Indonesian locale.
 */
export function formatDate(date, options = {}) {
    if (!date) return '-';
    const defaults = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(date).toLocaleDateString('id-ID', { ...defaults, ...options });
}

/**
 * Format a short date (e.g., "28 Apr 2024").
 */
export function formatShortDate(date) {
    return formatDate(date, { month: 'short' });
}

/**
 * Get a relative time string (e.g., "3 hari lagi").
 */
export function daysFromNow(date) {
    if (!date) return '-';
    const diff = Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24));
    if (diff === 0) return 'Hari ini';
    if (diff === 1) return 'Besok';
    if (diff > 0) return `${diff} hari lagi`;
    return `${Math.abs(diff)} hari lalu`;
}
