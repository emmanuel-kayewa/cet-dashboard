<template>
    <BaseChart :option="chartOption" :height="height" @chart-click="(p) => $emit('chart-click', p)" />
</template>

<script setup>
import { computed } from 'vue';
import BaseChart from './BaseChart.vue';
import { useChartPalettes } from '@/Composables/useChartPalettes';

defineEmits(['chart-click']);

const props = defineProps({
    data: { type: Array, default: () => [] },
    xField: { type: String, default: 'label' },
    yField: { type: String, default: 'value' },
    seriesName: { type: String, default: 'Value' },
    colors: { type: Array, default: () => [] },
    height: { type: String, default: '320px' },
    horizontal: { type: Boolean, default: false },
    stacked: { type: Boolean, default: false },
    multiSeries: { type: Array, default: () => [] }, // [{name, field, color}]
});

const { categorical } = useChartPalettes();

const effectiveColors = computed(() => {
    const list = (props.colors && props.colors.length > 0)
        ? props.colors
        : (categorical.value || []);

    return list.length > 0 ? list : ['#64748b'];
});

const chartOption = computed(() => {
    const labels = props.data.map(d => d[props.xField]);

    let series;
    if (props.multiSeries.length > 0) {
        series = props.multiSeries.map((s, i) => ({
            name: s.name,
            type: 'bar',
            data: props.data.map(d => d[s.field]),
            itemStyle: { color: s.color || effectiveColors.value[i % effectiveColors.value.length], borderRadius: [4, 4, 0, 0] },
            stack: props.stacked ? 'total' : undefined,
            barMaxWidth: 40,
            animationDuration: 600 + i * 200,
        }));
    } else {
        series = [{
            name: props.seriesName,
            type: 'bar',
            data: props.data.map((d, i) => ({
                value: d[props.yField],
                itemStyle: { color: effectiveColors.value[i % effectiveColors.value.length], borderRadius: props.horizontal ? [0, 4, 4, 0] : [4, 4, 0, 0] },
            })),
            barMaxWidth: 40,
            animationDuration: 800,
            animationDurationUpdate: 750,
            animationEasingUpdate: 'cubicInOut',
        }];
    }

    const xAxisConfig = {
        type: props.horizontal ? 'value' : 'category',
        data: props.horizontal ? undefined : labels,
        axisLine: { lineStyle: { color: '#cbd5e1' } },
        axisLabel: { color: '#64748b', fontSize: 11 },
        splitLine: props.horizontal ? { lineStyle: { color: '#f1f5f9', type: 'dashed' } } : undefined,
    };

    const yAxisConfig = {
        type: props.horizontal ? 'category' : 'value',
        data: props.horizontal ? labels : undefined,
        axisLine: { show: false },
        splitLine: !props.horizontal ? { lineStyle: { color: '#f1f5f9', type: 'dashed' } } : undefined,
        axisLabel: { color: '#64748b', fontSize: 11 },
    };

    return {
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            backgroundColor: 'rgba(255,255,255,0.95)',
            borderColor: '#e2e8f0',
            textStyle: { color: '#334155', fontSize: 13 },
        },
        grid: { left: '3%', right: '4%', bottom: '8%', top: '12%', containLabel: true },
        xAxis: xAxisConfig,
        yAxis: yAxisConfig,
        legend: props.multiSeries.length > 0 ? {
            data: props.multiSeries.map(s => s.name),
            textStyle: { color: '#64748b' },
        } : undefined,
        series,
    };
});
</script>
