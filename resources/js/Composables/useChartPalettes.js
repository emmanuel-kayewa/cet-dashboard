import { computed, ref, watch } from 'vue';
import { CATEGORICAL, INVESTMENT, SEQUENTIAL } from '@/Composables/useChartPalette';

const STORAGE_KEY = 'chart_palette';

function clamp01(n) {
    return Math.max(0, Math.min(1, n));
}

function hexToRgb(hex) {
    const cleaned = String(hex || '').trim().replace('#', '');
    const full = cleaned.length === 3
        ? cleaned.split('').map((c) => c + c).join('')
        : cleaned;

    if (!/^[0-9a-fA-F]{6}$/.test(full)) return { r: 0, g: 0, b: 0 };

    const n = parseInt(full, 16);
    return {
        r: (n >> 16) & 255,
        g: (n >> 8) & 255,
        b: n & 255,
    };
}

function rgbToHex({ r, g, b }) {
    const to2 = (v) => Math.round(Math.max(0, Math.min(255, v))).toString(16).padStart(2, '0');
    return `#${to2(r)}${to2(g)}${to2(b)}`;
}

/**
 * Mix two hex colors.
 * `t=0` => a, `t=1` => b
 */
function mixHex(a, b, t) {
    const ta = clamp01(t);
    const A = hexToRgb(a);
    const B = hexToRgb(b);
    return rgbToHex({
        r: A.r + (B.r - A.r) * ta,
        g: A.g + (B.g - A.g) * ta,
        b: A.b + (B.b - A.b) * ta,
    });
}

/**
 * Generate `n` shades of a base color.
 * We generate a balanced range from slightly darker → much lighter.
 */
function monochromeCategorical(baseHex, n = 10) {
    const stops = [
        -0.25, -0.15, -0.05,
        0.08, 0.18, 0.30,
        0.42, 0.54, 0.66, 0.76,
    ];

    const tVals = Array.from({ length: n }, (_, i) => stops[i] ?? (i / Math.max(n - 1, 1)) * 0.7);

    return tVals.map((t) => {
        if (t < 0) return mixHex(baseHex, '#000000', Math.abs(t));
        return mixHex(baseHex, '#ffffff', t);
    });
}

function monochromeSequential(baseHex, n = 5) {
    const tVals = [0.82, 0.62, 0.42, 0.22, -0.18];
    const vals = Array.from({ length: n }, (_, i) => tVals[i] ?? (i / Math.max(n - 1, 1)));

    return vals.map((t) => {
        if (t < 0) return mixHex(baseHex, '#000000', Math.abs(t));
        return mixHex(baseHex, '#ffffff', t);
    });
}

function monochromeTwoTone(baseHex) {
    return [
        mixHex(baseHex, '#000000', 0.12),
        mixHex(baseHex, '#ffffff', 0.35),
    ];
}

// Exported so PalettePicker can iterate palettes and render swatch previews.
export const PALETTES = {
    current: {
        label: 'Current (multi-color)',
        categorical: CATEGORICAL,
        sequential: SEQUENTIAL,
        twoTone: [INVESTMENT.committed, INVESTMENT.paid],
    },

    blue: {
        label: 'Blue (single-hue)',
        base: CATEGORICAL[0],
    },
    teal: {
        label: 'Teal (single-hue)',
        base: CATEGORICAL[1],
    },
    green: {
        label: 'Green (single-hue)',
        base: CATEGORICAL[2],
    },
    amber: {
        label: 'Amber (single-hue)',
        base: CATEGORICAL[3],
    },
    purple: {
        label: 'Purple (single-hue)',
        base: CATEGORICAL[5],
    },

    gray: {
        label: 'Gray (single-hue)',
        // Tailwind-ish slate base that remains readable in light/dark.
        base: '#64748b',
    },
};

function buildDerivedPalette(baseHex) {
    return {
        categorical: monochromeCategorical(baseHex, 10),
        sequential: monochromeSequential(baseHex, 5),
        twoTone: monochromeTwoTone(baseHex),
    };
}

// Singleton reactive state (shared across all imports)
const paletteKey = ref('current');

// Initialize from server-side user preference first, then fall back to localStorage
try {
    const serverPref =
        typeof window !== 'undefined'
            ? window.__inertia_initial_page?.props?.auth?.user?.preferences?.chart_palette
            : null;
    const stored = typeof window !== 'undefined' ? window.localStorage?.getItem(STORAGE_KEY) : null;
    const initial = serverPref || stored;
    if (initial && initial in PALETTES) paletteKey.value = initial;
} catch {
    // ignore storage access failures
}

watch(paletteKey, (val) => {
    // Dual-write: localStorage for instant reload + server for cross-device persistence
    try {
        window.localStorage?.setItem(STORAGE_KEY, val);
    } catch {
        // ignore
    }
    // Persist to server (fire-and-forget, plain fetch to avoid Inertia page visit)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch('/user/preferences', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        },
        body: JSON.stringify({ key: 'chart_palette', value: val }),
    }).catch(() => {});
    // Helps ECharts/Highcharts recompute layout when palette changes.
    window.dispatchEvent(new Event('resize'));
});

const paletteOptions = Object.entries(PALETTES).map(([value, p]) => ({
    value,
    label: p.label,
}));

const activePalette = computed(() => {
    const p = PALETTES[paletteKey.value] || PALETTES.current;
    if (p.categorical && p.sequential && p.twoTone) return p;
    const derived = buildDerivedPalette(p.base);
    return { ...p, ...derived };
});

const categorical = computed(() => activePalette.value.categorical);
const sequential = computed(() => activePalette.value.sequential);
const twoTone = computed(() => activePalette.value.twoTone);

function setPalette(key) {
    if (key in PALETTES) paletteKey.value = key;
}

function pickCategorical(n) {
    const list = categorical.value || [];
    if (!n) return [];
    if (!list.length) return Array.from({ length: n }, () => '#64748b');
    return Array.from({ length: n }, (_, i) => list[i % list.length]);
}

/**
 * Return the first `count` categorical colours for a given palette key.
 * Used by PalettePicker to render swatch previews without activating the palette.
 */
function previewColors(key, count = 6) {
    const p = PALETTES[key];
    if (!p) return [];
    const cats = p.categorical || buildDerivedPalette(p.base).categorical;
    return cats.slice(0, count);
}

// TODO: Future — expand CSS custom properties for full UI theming
// (badges, buttons, active tabs, sidebar highlights).

/**
 * Inject CSS custom properties derived from the active palette's primary colour
 * so UI elements (hover borders, focus rings) can react to palette changes.
 */
function applyAccentProperties() {
    if (typeof document === 'undefined') return;
    const p = PALETTES[paletteKey.value] || PALETTES.current;
    // Use the palette's base colour, or the first categorical colour for multi-colour palettes
    const base = p.base || (p.categorical?.[0]) || CATEGORICAL[0];
    const style = document.documentElement.style;
    style.setProperty('--palette-accent',       base);
    style.setProperty('--palette-accent-light',  mixHex(base, '#ffffff', 0.65));
    style.setProperty('--palette-accent-lighter', mixHex(base, '#ffffff', 0.82));
    style.setProperty('--palette-accent-dark',   mixHex(base, '#000000', 0.25));
}

// Apply on init + whenever palette changes
applyAccentProperties();
watch(paletteKey, applyAccentProperties);

export function useChartPalettes() {
    return {
        paletteKey,
        paletteOptions,
        setPalette,
        categorical,
        sequential,
        twoTone,
        pickCategorical,
        previewColors,
    };
}
