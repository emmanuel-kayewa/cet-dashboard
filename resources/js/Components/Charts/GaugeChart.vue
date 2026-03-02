<template>
    <BaseChart :option="chartOption" :height="height" />
</template>

<script setup>
import { computed } from 'vue';
import BaseChart from './BaseChart.vue';

const props = defineProps({
    value: { type: Number, default: 0 },
    min: { type: Number, default: 0 },
    max: { type: Number, default: 100 },
    title: { type: String, default: '' },
    unit: { type: String, default: '%' },
    height: { type: String, default: '240px' },
    thresholds: { type: Array, default: () => [[0.6, '#dc2626'], [0.8, '#f59e0b'], [1, '#22c55e']] },
});

const chartOption = computed(() => ({
    series: [{
        type: 'gauge',
        startAngle: 210,
        endAngle: -30,
        min: props.min,
        max: props.max,
        pointer: {
            length: '60%',
            width: 4,
            itemStyle: { color: '#334155' },
        },
        progress: {
            show: true,
            width: 14,
            roundCap: true,
            itemStyle: { color: getColor(props.value) },
        },
        axisLine: {
            lineStyle: {
                width: 14,
                color: props.thresholds,
            },
            roundCap: true,
        },
        axisTick: { show: false },
        splitLine: { show: false },
        axisLabel: { show: false },
        detail: {
            valueAnimation: true,
            formatter: `{value}${props.unit}`,
            fontSize: 20,
            fontWeight: 600,
            color: '#334155',
            offsetCenter: [0, '70%'],
        },
        title: {
            show: !!props.title,
            offsetCenter: [0, '90%'],
            fontSize: 12,
            color: '#94a3b8',
        },
        data: [{ value: props.value, name: props.title }],
        animationDuration: 1000,
    }],
}));

function getColor(value) {
    const ratio = (value - props.min) / (props.max - props.min);
    for (const [threshold, color] of props.thresholds) {
        if (ratio <= threshold) return color;
    }
    return '#22c55e';
}
</script>
