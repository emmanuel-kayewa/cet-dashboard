<template>
    <BaseChart :option="chartOption" :height="height" />
</template>

<script setup>
import { computed } from 'vue';
import BaseChart from './BaseChart.vue';

const props = defineProps({
    data: { type: Array, default: () => [] }, // [[x, y, value]]
    xLabels: { type: Array, default: () => [] },
    yLabels: { type: Array, default: () => [] },
    height: { type: String, default: '400px' },
    title: { type: String, default: '' },
});

const chartOption = computed(() => ({
    title: props.title ? {
        text: props.title,
        textStyle: { color: '#334155', fontSize: 14, fontWeight: 600 },
    } : undefined,
    tooltip: {
        position: 'top',
        backgroundColor: 'rgba(255,255,255,0.95)',
        borderColor: '#e2e8f0',
        textStyle: { color: '#334155' },
    },
    grid: { left: '12%', right: '4%', bottom: '15%', top: '8%' },
    xAxis: {
        type: 'category',
        data: props.xLabels,
        splitArea: { show: true },
        axisLabel: { color: '#64748b', fontSize: 10, rotate: 30 },
    },
    yAxis: {
        type: 'category',
        data: props.yLabels,
        splitArea: { show: true },
        axisLabel: { color: '#64748b', fontSize: 10 },
    },
    visualMap: {
        min: 0,
        max: 100,
        calculable: true,
        orient: 'horizontal',
        left: 'center',
        bottom: '0%',
        inRange: {
            color: ['#f0f9ff', '#bae6fd', '#38bdf8', '#0284c7', '#0c4a6e'],
        },
        textStyle: { color: '#64748b' },
    },
    series: [{
        type: 'heatmap',
        data: props.data,
        label: { show: true, fontSize: 10, color: '#334155' },
        emphasis: {
            itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0, 0, 0, 0.2)' },
        },
    }],
}));
</script>
