<template>
    <BaseChart :option="chartOption" :height="height" />
</template>

<script setup>
import { computed } from 'vue';
import BaseChart from './BaseChart.vue';
import { useChartPalettes } from '@/Composables/useChartPalettes';

const props = defineProps({
    data: { type: Array, default: () => [] },
    xField: { type: String, default: 'label' },
    yField: { type: String, default: 'value' },
    seriesName: { type: String, default: 'Value' },
    color: { type: String, default: '' },
    height: { type: String, default: '320px' },
    smooth: { type: Boolean, default: true },
    showArea: { type: Boolean, default: true },
    forecast: { type: Array, default: () => [] },
});

const { categorical } = useChartPalettes();

const effectiveColor = computed(() => {
    return props.color || categorical.value?.[0] || '#64748b';
});

const chartOption = computed(() => {
    const labels = [...props.data.map(d => d[props.xField]), ...props.forecast.map(d => d[props.xField])];
    const values = props.data.map(d => d[props.yField]);
    const forecastValues = [...new Array(props.data.length).fill(null), ...props.forecast.map(d => d[props.yField])];

    const series = [
        {
            name: props.seriesName,
            type: 'line',
            data: values,
            smooth: props.smooth,
            symbol: 'circle',
            symbolSize: 6,
            lineStyle: { width: 2.5, color: effectiveColor.value },
            itemStyle: { color: effectiveColor.value },
            areaStyle: props.showArea ? {
                color: {
                    type: 'linear',
                    x: 0, y: 0, x2: 0, y2: 1,
                    colorStops: [
                        { offset: 0, color: effectiveColor.value + '30' },
                        { offset: 1, color: effectiveColor.value + '05' },
                    ],
                },
            } : undefined,
            animationDuration: 800,
            animationDurationUpdate: 750,
            animationEasingUpdate: 'cubicInOut',
        },
    ];

    if (props.forecast.length > 0) {
        series.push({
            name: 'Forecast',
            type: 'line',
            data: forecastValues,
            smooth: props.smooth,
            symbol: 'diamond',
            symbolSize: 6,
            lineStyle: { width: 2, type: 'dashed', color: '#94a3b8' },
            itemStyle: { color: '#94a3b8' },
        });
    }

    return {
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(255,255,255,0.95)',
            borderColor: '#e2e8f0',
            textStyle: { color: '#334155', fontSize: 13 },
        },
        grid: { left: '3%', right: '4%', bottom: '8%', top: '8%', containLabel: true },
        xAxis: {
            type: 'category',
            data: labels,
            axisLine: { lineStyle: { color: '#cbd5e1' } },
            axisLabel: { color: '#64748b', fontSize: 11 },
        },
        yAxis: {
            type: 'value',
            axisLine: { show: false },
            splitLine: { lineStyle: { color: '#f1f5f9', type: 'dashed' } },
            axisLabel: { color: '#64748b', fontSize: 11 },
        },
        legend: props.forecast.length > 0 ? {
            data: [props.seriesName, 'Forecast'],
            textStyle: { color: '#64748b' },
        } : undefined,
        series,
    };
});
</script>
