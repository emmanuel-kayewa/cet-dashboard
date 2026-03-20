<template>
    <div class="relative">
        <!-- Map / Bar toggle -->
        <div v-if="showToggle" class="absolute top-0 right-0 z-10 flex items-center gap-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-0.5">
            <button @click="viewMode = 'chart'" :class="toggleBtnClass('chart')" title="Bar chart view">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </button>
            <button @click="viewMode = 'map'" :class="toggleBtnClass('map')" title="Map view">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
            </button>
        </div>

        <!-- Metric toggle (only visible in map mode when both metrics exist) -->
        <div v-if="viewMode === 'map' && showMetricToggle" class="flex items-center gap-2 mb-3 mt-1">
            <span class="text-xs text-gray-500 dark:text-gray-400">View:</span>
            <button v-for="m in metrics" :key="m.key"
                    @click="activeMetric = m.key"
                    :class="[
                        'text-xs px-3 py-1 rounded-full transition-colors',
                        activeMetric === m.key
                            ? 'bg-zesco-100 text-zesco-700 dark:bg-zesco-900/40 dark:text-zesco-400 font-medium'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                    ]">
                {{ m.label }}
            </button>
        </div>

        <!-- Slot for bar chart (shown when viewMode === 'chart') -->
        <div v-show="viewMode === 'chart'">
            <slot></slot>
        </div>

        <!-- Map (shown when viewMode === 'map') -->
        <div v-show="viewMode === 'map'">
            <BaseChart ref="mapChart" :option="mapOption" :height="height" @chart-click="handleMapClick" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import * as echarts from 'echarts/core';
import BaseChart from './BaseChart.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useChartPalettes } from '@/Composables/useChartPalettes';

const { isDark } = useDarkMode();

const props = defineProps({
    /** [{ name: 'Lusaka', value: 5, investment: 1200000 }, ...] */
    data: { type: Array, default: () => [] },
    /** 'province' or 'district' */
    level: { type: String, default: 'province' },
    /** Label for the primary metric (used in tooltip) */
    metricLabel: { type: String, default: 'Projects' },
    /** Whether to show the chart/map toggle */
    showToggle: { type: Boolean, default: true },
    /** Whether to show investment toggle */
    showMetricToggle: { type: Boolean, default: false },
    /** Investment data field name in data items */
    investmentField: { type: String, default: 'investment' },
    height: { type: String, default: '400px' },
    colors: { type: Array, default: () => [] },
});

const emit = defineEmits(['region-click']);

const viewMode = ref('map'); // Default to map view
const activeMetric = ref('count');
const geoLoaded = ref(false);
const mapChart = ref(null);

const { sequential } = useChartPalettes();

const effectiveRamp = computed(() => {
    const list = (props.colors && props.colors.length > 0)
        ? props.colors
        : (sequential.value || []);
    return list.length > 0 ? list : ['#e2e8f0', '#94a3b8', '#334155'];
});

const metrics = [
    { key: 'count', label: 'Project Count' },
    { key: 'investment', label: 'Investment (USD)' },
];

const mapName = computed(() => props.level === 'district' ? 'zambia-districts' : 'zambia-provinces');
const geoUrl = computed(() => props.level === 'district' ? '/geojson/zambia-districts.json' : '/geojson/zambia-provinces.json');

// Load GeoJSON on mount
async function loadGeo() {
    try {
        const resp = await fetch(geoUrl.value);
        const geoJson = await resp.json();
        echarts.registerMap(mapName.value, geoJson);
        geoLoaded.value = true;
    } catch (e) {
        console.error('Failed to load Zambia GeoJSON:', e);
    }
}

onMounted(loadGeo);

// Reload if level changes
watch(() => props.level, () => {
    geoLoaded.value = false;
    loadGeo();
});

const mapData = computed(() => {
    if (!props.data) return [];
    return props.data.map(item => ({
        name: item.name || item.label,
        value: activeMetric.value === 'investment'
            ? (item[props.investmentField] || item.investment || 0)
            : (item.value || 0),
    }));
});

const maxValue = computed(() => {
    const vals = mapData.value.map(d => d.value).filter(Boolean);
    return vals.length ? Math.max(...vals) : 1;
});

const mapOption = computed(() => {
    if (!geoLoaded.value) return {};

    return {
        tooltip: {
            trigger: 'item',
            formatter: (params) => {
                const val = params.value ?? 0;
                const label = activeMetric.value === 'investment' ? 'Investment (USD)' : props.metricLabel;
                const formatted = activeMetric.value === 'investment'
                    ? '$' + formatNumber(val)
                    : val.toLocaleString();
                return `<strong>${params.name}</strong><br/>${label}: ${formatted}`;
            },
        },
        visualMap: {
            min: 0,
            max: maxValue.value || 1,
            left: 'left',
            bottom: 10,
            text: ['High', 'Low'],
            textStyle: {
                color: isDark.value ? '#94a3b8' : '#64748b',
                fontSize: 11,
            },
            inRange: {
                color: effectiveRamp.value,
            },
            calculable: true,
            itemWidth: 12,
            itemHeight: 80,
        },
        series: [{
            type: 'map',
            map: mapName.value,
            roam: true,
            scaleLimit: { min: 0.8, max: 5 },
            emphasis: {
                label: { show: true, fontSize: 11, fontWeight: 600 },
                itemStyle: {
                    areaColor: isDark.value ? '#475569' : '#bfdbfe',
                    borderColor: isDark.value ? '#94a3b8' : '#3b82f6',
                    borderWidth: 1.5,
                },
            },
            select: {
                disabled: true,
            },
            label: {
                show: true,
                fontSize: 9,
                color: isDark.value ? '#cbd5e1' : '#475569',
                formatter: (params) => {
                    // Only show names for districts that actually have positive data values
                    return params.value > 0 ? params.name : '';
                },
            },
            itemStyle: {
                borderColor: isDark.value ? '#4b5563' : '#cbd5e1',
                borderWidth: 0.8,
                areaColor: isDark.value ? '#1f2937' : '#f1f5f9',
            },
            data: mapData.value,
        }],
    };
});

function handleMapClick(params) {
    if (params.name) {
        emit('region-click', params.name);
    }
}

function toggleBtnClass(mode) {
    return [
        'p-1.5 rounded transition-colors',
        viewMode.value === mode
            ? 'bg-zesco-100 text-zesco-700 dark:bg-zesco-900/40 dark:text-zesco-400'
            : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300',
    ];
}

function formatNumber(n) {
    if (n >= 1e9) return (n / 1e9).toFixed(2) + 'B';
    if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K';
    return n.toLocaleString();
}
</script>
