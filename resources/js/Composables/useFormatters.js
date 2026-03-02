/**
 * Number formatting utilities for ZESCO Dashboard.
 */

export function formatCurrency(value, currency = 'ZMW') {
    if (value === null || value === undefined) return '—';
    const absValue = Math.abs(value);

    let formatted;
    if (absValue >= 1_000_000_000) {
        formatted = (value / 1_000_000_000).toFixed(2) + 'B';
    } else if (absValue >= 1_000_000) {
        formatted = (value / 1_000_000).toFixed(2) + 'M';
    } else if (absValue >= 1_000) {
        formatted = (value / 1_000).toFixed(1) + 'K';
    } else {
        formatted = value.toFixed(2);
    }

    return `${currency} ${formatted}`;
}

export function formatNumber(value, decimals = 0) {
    if (value === null || value === undefined) return '—';
    return new Intl.NumberFormat('en-ZM', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value);
}

export function formatPercentage(value, decimals = 1) {
    if (value === null || value === undefined) return '—';
    return `${Number(value).toFixed(decimals)}%`;
}

export function formatChange(value) {
    if (value === null || value === undefined) return { text: '—', class: 'text-gray-400' };

    const prefix = value > 0 ? '+' : '';
    const text = `${prefix}${Number(value).toFixed(1)}%`;
    const colorClass = value > 0
        ? 'text-green-600 dark:text-green-400'
        : value < 0
            ? 'text-red-600 dark:text-red-400'
            : 'text-gray-500';

    return { text, class: colorClass };
}

export function getStatusColor(status) {
    return {
        healthy: 'text-green-600 bg-green-50 dark:text-green-400 dark:bg-green-900/20',
        warning: 'text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-900/20',
        critical: 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-900/20',
    }[status] || 'text-gray-600 bg-gray-50';
}

export function getRiskColor(level) {
    return {
        low: '#22c55e',
        medium: '#eab308',
        high: '#f97316',
        critical: '#dc2626',
    }[level] || '#94a3b8';
}
