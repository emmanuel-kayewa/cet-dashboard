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

const PALETTES = {
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

// Initialize once from localStorage
try {
    const stored = typeof window !== 'undefined' ? window.localStorage?.getItem(STORAGE_KEY) : null;
    if (stored && stored in PALETTES) paletteKey.value = stored;
} catch {
    // ignore storage access failures
}

watch(paletteKey, (val) => {
    try {
        window.localStorage?.setItem(STORAGE_KEY, val);
    } catch {
        // ignore
    }
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

export function useChartPalettes() {
    return {
        paletteKey,
        paletteOptions,
        setPalette,
        categorical,
        sequential,
        twoTone,
        pickCategorical,
    };
}
