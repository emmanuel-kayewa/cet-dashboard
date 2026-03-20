<template>
    <div ref="chartEl" :style="{ width: '100%', height: computedHeight }" class="echarts-container overflow-hidden"></div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import * as echarts from 'echarts/core';
import { LineChart, BarChart, PieChart, GaugeChart, HeatmapChart, ScatterChart, MapChart } from 'echarts/charts';
import {
    TitleComponent, TooltipComponent, LegendComponent, GridComponent,
    DataZoomComponent, ToolboxComponent, VisualMapComponent, GeoComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import { useDarkMode } from '@/Composables/useDarkMode';

echarts.use([
    LineChart, BarChart, PieChart, GaugeChart, HeatmapChart, ScatterChart, MapChart,
    TitleComponent, TooltipComponent, LegendComponent, GridComponent,
    DataZoomComponent, ToolboxComponent, VisualMapComponent, GeoComponent,
    CanvasRenderer,
]);

const props = defineProps({
    option: { type: Object, required: true },
    height: { type: String, default: '320px' },
    theme: { type: String, default: null },
    autoResize: { type: Boolean, default: true },
    responsive: { type: Boolean, default: true },
});

const emit = defineEmits(['chart-ready', 'chart-click']);
const chartEl = ref(null);
const windowWidth = ref(window.innerWidth);
let chart = null;
let resizeObserver = null;
const { isDark } = useDarkMode();

/** Calculate responsive height based on viewport width */
const computedHeight = computed(() => {
    if (!props.responsive) return props.height;
    
    // Parse the height prop to get base height value
    const baseHeight = parseInt(props.height) || 320;
    
    // On mobile (< 640px), reduce height to fit screen better
    if (windowWidth.value < 640) {
        return Math.min(baseHeight, 280) + 'px';
    }
    // On tablet (640-1024px), use moderate height
    if (windowWidth.value < 1024) {
        return Math.min(baseHeight, 300) + 'px';
    }
    // On desktop, use full height
    return props.height;
});

/**
 * Build theme overlays for chart options.
 * We always force a transparent canvas background so charts blend with their parent cards.
 */
function getThemeOverrides() {
    const base = {
        backgroundColor: 'transparent',
    };

    if (!isDark.value) return base;

    return {
        ...base,
        tooltip: {
            backgroundColor: 'rgba(30, 41, 59, 0.95)',
            borderColor: '#475569',
            textStyle: { color: '#e2e8f0', fontSize: 13 },
        },
    };
}

function buildOption(option) {
    // Force our overlays to win over any per-chart options/theme defaults.
    return { ...option, ...getThemeOverrides() };
}

function initChart() {
    if (!chartEl.value) return;
    if (chart) chart.dispose();
    chart = echarts.init(chartEl.value, isDark.value ? 'dark' : props.theme);
    chart.setOption(buildOption(props.option), { notMerge: true, lazyUpdate: false });
    chart.on('click', (params) => emit('chart-click', params));
    emit('chart-ready', chart);
}

function handleWindowResize() {
    windowWidth.value = window.innerWidth;
}

onMounted(() => {
    nextTick(() => {
        initChart();

        // Listen to window resize for responsive height
        window.addEventListener('resize', handleWindowResize);

        if (props.autoResize && chartEl.value) {
            resizeObserver = new ResizeObserver(() => {
                chart?.resize();
            });
            resizeObserver.observe(chartEl.value);
        }
    });
});

watch(
    () => props.option,
    (newOption) => {
        if (chart) {
            chart.setOption({
                ...buildOption(newOption),
                animation: true,
                animationDurationUpdate: 750,
                animationEasingUpdate: 'cubicInOut',
            }, { notMerge: false, lazyUpdate: false });
        }
    },
    { deep: true }
);

// React to dark mode toggle via the reactive composable ref
watch(isDark, async () => {
    // Ensure DOM/theme class updates settle before re-init.
    await nextTick();
    initChart();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleWindowResize);
    resizeObserver?.disconnect();
    chart?.dispose();
});

defineExpose({
    getChart: () => chart,
    resize: () => chart?.resize(),
});
</script>
