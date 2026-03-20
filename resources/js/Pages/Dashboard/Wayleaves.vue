<template>
    <AppLayout :directorates="directorates">
        <template #title>Wayleaves &amp; Surveys</template>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Wayleave Received</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">87</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Wayleave Cleared</p>
                <p class="text-2xl font-bold text-green-600">46</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Survey Received</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">33</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Survey Cleared</p>
                <p class="text-2xl font-bold text-green-600">24</p>
            </div>
        </div>

        <!-- Wayleave Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card title="Wayleave Aspects — Received vs Cleared vs Pending">
                <BarChart
                    :data="wayleaveData"
                    xField="label"
                    :multiSeries="wayleaveMultiSeries"
                    height="360px"
                />
            </Card>

            <Card title="Wayleave Status Distribution (Total)">
                <PieChart
                    :data="wayleavePieData"
                    height="360px"
                />
            </Card>
        </div>

        <!-- Wayleave Horizontal Bar — Pending Focus -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card title="Wayleave — Pending by Aspect">
                <BarChart
                    :data="wayleavePendingData"
                    xField="label"
                    yField="value"
                    seriesName="Pending"
                    height="320px"
                    horizontal
                />
            </Card>

            <Card title="Wayleave Clearance Rate by Aspect">
                <BaseChart :option="wayleaveClearanceGaugeOption" height="320px" />
            </Card>
        </div>

        <!-- Survey Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card title="Survey Aspects — Received vs Cleared vs Pending">
                <BarChart
                    :data="surveyData"
                    xField="label"
                    :multiSeries="surveyMultiSeries"
                    height="360px"
                />
            </Card>

            <Card title="Survey Status Distribution (Total)">
                <PieChart
                    :data="surveyPieData"
                    height="360px"
                />
            </Card>
        </div>

        <!-- Survey Horizontal Bar — Pending Focus -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card title="Survey — Pending by Aspect">
                <BarChart
                    :data="surveyPendingData"
                    xField="label"
                    yField="value"
                    seriesName="Pending"
                    height="320px"
                    horizontal
                />
            </Card>

            <Card title="Survey Clearance Rate by Aspect">
                <BaseChart :option="surveyClearanceGaugeOption" height="320px" />
            </Card>
        </div>

        <!-- Combined Data Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card title="Wayleave Data Table" noPadding>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">S/N</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aspect</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Received</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cleared</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pending</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(row, i) in wayleaveRawData" :key="i" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ i + 1 }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ row.aspect }}</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ row.received }}</td>
                                <td class="px-4 py-3 text-right text-green-600 dark:text-green-400 font-semibold">{{ row.cleared }}</td>
                                <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400 font-semibold">{{ row.pending }}</td>
                            </tr>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 font-bold">
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">TOTAL</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">87</td>
                                <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">46</td>
                                <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">41</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <Card title="Survey Data Table" noPadding>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">S/N</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aspect</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Received</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cleared</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pending</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(row, i) in surveyRawData" :key="i" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ i + 1 }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ row.aspect }}</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ row.received }}</td>
                                <td class="px-4 py-3 text-right text-green-600 dark:text-green-400 font-semibold">{{ row.cleared }}</td>
                                <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400 font-semibold">{{ row.pending }}</td>
                            </tr>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 font-bold">
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">TOTAL</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">33</td>
                                <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">24</td>
                                <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">9</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';
import BaseChart from '@/Components/Charts/BaseChart.vue';

defineProps({
    directorates: { type: Array, default: () => [] },
});

// ── Raw Wayleave Data ──────────────────────────────────────
const wayleaveRawData = [
    { aspect: 'Line route planning and wayleave acquisition', received: 16, cleared: 8, pending: 8 },
    { aspect: 'Encroachments on distribution and medium voltage lines', received: 12, cleared: 4, pending: 8 },
    { aspect: 'Wayleave clearance for development of fuel service stations', received: 13, cleared: 6, pending: 7 },
    { aspect: 'Wayleave inspections', received: 14, cleared: 8, pending: 6 },
    { aspect: 'Re-routing', received: 30, cleared: 18, pending: 12 },
    { aspect: 'Land acquisition', received: 2, cleared: 2, pending: 0 },
];

// ── Raw Survey Data ────────────────────────────────────────
const surveyRawData = [
    { aspect: 'Cadastral survey works', received: 18, cleared: 15, pending: 3 },
    { aspect: 'Engineering survey works', received: 3, cleared: 1, pending: 2 },
    { aspect: 'Transmission Detailed Survey Works', received: 6, cleared: 3, pending: 3 },
    { aspect: 'Distribution Detailed Survey Works', received: 6, cleared: 5, pending: 1 },
];

// ── Wayleave Chart Data ────────────────────────────────────
const wayleaveData = computed(() =>
    wayleaveRawData.map(d => ({
        label: d.aspect.length > 20 ? d.aspect.substring(0, 18) + '…' : d.aspect,
        received: d.received,
        cleared: d.cleared,
        pending: d.pending,
    }))
);

const wayleaveMultiSeries = [
    { name: 'Received', field: 'received' },
    { name: 'Cleared', field: 'cleared' },
    { name: 'Pending', field: 'pending' },
];

const wayleavePieData = computed(() => [
    { name: 'Cleared', value: 46 },
    { name: 'Pending', value: 41 },
]);

const wayleavePendingData = computed(() =>
    wayleaveRawData
        .filter(d => d.pending > 0)
        .map(d => ({
            label: d.aspect.length > 30 ? d.aspect.substring(0, 28) + '…' : d.aspect,
            value: d.pending,
        }))
        .sort((a, b) => b.value - a.value)
);

// Multi-gauge showing clearance rate per wayleave aspect
const wayleaveClearanceGaugeOption = computed(() => ({
    tooltip: {
        trigger: 'item',
        backgroundColor: 'rgba(255,255,255,0.95)',
        borderColor: '#e2e8f0',
        textStyle: { color: '#334155', fontSize: 13 },
        formatter: (params) => `${params.name}: ${params.value}%`,
    },
    series: [
        {
            type: 'gauge',
            startAngle: 200,
            endAngle: -20,
            center: ['50%', '60%'],
            radius: '85%',
            min: 0,
            max: 100,
            pointer: { show: false },
            progress: {
                show: true,
                overlap: false,
                roundCap: true,
                clip: false,
                itemStyle: { borderWidth: 1, borderColor: '#fff' },
            },
            axisLine: { lineStyle: { width: 30, color: [[1, '#f1f5f9']] } },
            splitLine: { show: false },
            axisTick: { show: false },
            axisLabel: { show: false },
            title: { fontSize: 11, color: '#64748b', offsetCenter: ['0%', '30%'] },
            detail: {
                width: 40,
                height: 14,
                fontSize: 14,
                color: 'inherit',
                formatter: '{value}%',
                offsetCenter: ['0%', '0%'],
            },
            data: [
                {
                    value: Math.round((46 / 87) * 100),
                    name: 'Overall Clearance',
                    title: { offsetCenter: ['0%', '25%'] },
                    detail: { offsetCenter: ['0%', '5%'] },
                    itemStyle: { color: '#22c55e' },
                },
            ],
        },
    ],
}));

// ── Survey Chart Data ──────────────────────────────────────
const surveyData = computed(() =>
    surveyRawData.map(d => ({
        label: d.aspect.length > 20 ? d.aspect.substring(0, 18) + '…' : d.aspect,
        received: d.received,
        cleared: d.cleared,
        pending: d.pending,
    }))
);

const surveyMultiSeries = [
    { name: 'Received', field: 'received' },
    { name: 'Cleared', field: 'cleared' },
    { name: 'Pending', field: 'pending' },
];

const surveyPieData = computed(() => [
    { name: 'Cleared', value: 24 },
    { name: 'Pending', value: 9 },
]);

const surveyPendingData = computed(() =>
    surveyRawData
        .filter(d => d.pending > 0)
        .map(d => ({
            label: d.aspect.length > 30 ? d.aspect.substring(0, 28) + '…' : d.aspect,
            value: d.pending,
        }))
        .sort((a, b) => b.value - a.value)
);

// Multi-gauge showing clearance rate per survey aspect
const surveyClearanceGaugeOption = computed(() => ({
    tooltip: {
        trigger: 'item',
        backgroundColor: 'rgba(255,255,255,0.95)',
        borderColor: '#e2e8f0',
        textStyle: { color: '#334155', fontSize: 13 },
        formatter: (params) => `${params.name}: ${params.value}%`,
    },
    series: [
        {
            type: 'gauge',
            startAngle: 200,
            endAngle: -20,
            center: ['50%', '60%'],
            radius: '85%',
            min: 0,
            max: 100,
            pointer: { show: false },
            progress: {
                show: true,
                overlap: false,
                roundCap: true,
                clip: false,
                itemStyle: { borderWidth: 1, borderColor: '#fff' },
            },
            axisLine: { lineStyle: { width: 30, color: [[1, '#f1f5f9']] } },
            splitLine: { show: false },
            axisTick: { show: false },
            axisLabel: { show: false },
            title: { fontSize: 11, color: '#64748b', offsetCenter: ['0%', '30%'] },
            detail: {
                width: 40,
                height: 14,
                fontSize: 14,
                color: 'inherit',
                formatter: '{value}%',
                offsetCenter: ['0%', '0%'],
            },
            data: [
                {
                    value: Math.round((24 / 33) * 100),
                    name: 'Overall Clearance',
                    title: { offsetCenter: ['0%', '25%'] },
                    detail: { offsetCenter: ['0%', '5%'] },
                    itemStyle: { color: '#22c55e' },
                },
            ],
        },
    ],
}));
</script>
