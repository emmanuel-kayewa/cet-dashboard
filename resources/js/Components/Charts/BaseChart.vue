<template>
    <div ref="chartEl" :style="{ width: '100%', height: height }" class="echarts-container"></div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import * as echarts from 'echarts/core';
import { LineChart, BarChart, PieChart, GaugeChart, HeatmapChart, ScatterChart } from 'echarts/charts';
import {
    TitleComponent, TooltipComponent, LegendComponent, GridComponent,
    DataZoomComponent, ToolboxComponent, VisualMapComponent
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import { useDarkMode } from '@/Composables/useDarkMode';

echarts.use([
    LineChart, BarChart, PieChart, GaugeChart, HeatmapChart, ScatterChart,
    TitleComponent, TooltipComponent, LegendComponent, GridComponent,
    DataZoomComponent, ToolboxComponent, VisualMapComponent,
    CanvasRenderer,
]);

const props = defineProps({
    option: { type: Object, required: true },
    height: { type: String, default: '320px' },
    theme: { type: String, default: null },
    autoResize: { type: Boolean, default: true },
});

const emit = defineEmits(['chart-ready']);
const chartEl = ref(null);
let chart = null;
let resizeObserver = null;
const { isDark } = useDarkMode();

/** Build the dark mode overlay for chart options */
function getDarkOverrides() {
    if (!isDark.value) return {};
    return {
        tooltip: {
            backgroundColor: 'rgba(30, 41, 59, 0.95)',
            borderColor: '#475569',
            textStyle: { color: '#e2e8f0', fontSize: 13 },
        },
    };
}

function initChart() {
    if (!chartEl.value) return;
    if (chart) chart.dispose();
    chart = echarts.init(chartEl.value, isDark.value ? 'dark' : props.theme);
    chart.setOption({ ...props.option, ...getDarkOverrides() });
    emit('chart-ready', chart);
}

onMounted(() => {
    nextTick(() => {
        initChart();

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
                ...newOption,
                ...getDarkOverrides(),
                animation: true,
                animationDurationUpdate: 750,
                animationEasingUpdate: 'cubicInOut',
            }, { notMerge: false, lazyUpdate: false });
        }
    },
    { deep: true }
);

// React to dark mode toggle via the reactive composable ref
watch(isDark, () => {
    initChart();
});

onUnmounted(() => {
    resizeObserver?.disconnect();
    chart?.dispose();
});

defineExpose({
    getChart: () => chart,
    resize: () => chart?.resize(),
});
</script>
