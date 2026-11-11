const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Jakarta',
});

const dateTimeFormatter = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'Asia/Jakarta',
});

function toDate(value) {
    if (!value) return null;
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? null : date;
}

export function formatDate(value, fallback = '-') {
    const date = toDate(value);
    return date ? dateFormatter.format(date) : fallback;
}

export function formatDateTime(value, fallback = '-') {
    const date = toDate(value);
    return date ? dateTimeFormatter.format(date) : fallback;
}

export function readingTime(content = '') {
    const words = String(content || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean).length;
    return Math.max(1, Math.round(words / 200));
}

export function categoryMeta(categories = [], value = '') {
    return (
        categories.find((category) => category.value === value) ?? {
            value,
            label: value || 'Umum',
            badgeClass: 'bg-slate-200 text-slate-800',
        }
    );
}

export function initials(name = '') {
    const parts = String(name).trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
}

export function numberFormat(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}
