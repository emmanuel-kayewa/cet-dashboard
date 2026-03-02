<template>
    <BaseChart :option="chartOption" :height="height" />
</template>

<script setup>
import { computed } from 'vue';
import BaseChart from './BaseChart.vue';

const props = defineProps({
    data: { type: Array, default: () => [] }, // [{name, value}]
    colors: { type: Array, default: () => ['#1e40af', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe'] },
    height: { type: String, default: '320px' },
    showLabel: { type: Boolean, default: true },
    roseType: { type: [String, Boolean], default: false }, // 'radius', 'area'
});

const chartOption = computed(() => ({
    tooltip: {
        trigger: 'item',
        backgroundColor: 'rgba(255,255,255,0.95)',
        borderColor: '#e2e8f0',
        textStyle: { color: '#334155', fontSize: 13 },
        formatter: '{b}: {c} ({d}%)',
    },
    legend: {
        orient: 'vertical',
        right: '5%',
        top: 'center',
        textStyle: { color: '#64748b', fontSize: 12 },
    },
    series: [{
        type: 'pie',
        radius: props.roseType ? ['20%', '65%'] : ['45%', '70%'],
        center: ['40%', '50%'],
        roseType: props.roseType || undefined,
        avoidLabelOverlap: true,
        itemStyle: {
            borderRadius: 6,
            borderColor: '#fff',
            borderWidth: 2,
        },
        label: props.showLabel ? {
            color: '#64748b',
            fontSize: 12,
        } : { show: false },
        emphasis: {
            label: { show: true, fontSize: 14, fontWeight: 'bold' },
            itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0, 0, 0, 0.15)' },
        },
        data: props.data.map((d, i) => ({
            ...d,
            itemStyle: { color: d.color || props.colors[i % props.colors.length] },
        })),
        animationType: 'scale',
        animationDuration: 800,
    }],
}));
</script>
