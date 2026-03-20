<template>
    <AppLayout :directorates="directorates">
        <template #title>Executive Overview</template>

        <!-- Filters Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
            <div class="flex items-center gap-4">
                <DateRangePicker
                    v-model:from="filters.from"
                    v-model:to="filters.to"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />
                <div v-if="isLive" class="flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400" title="Receiving live data updates">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="hidden sm:inline">Live</span>
                    <span v-if="lastUpdated" class="hidden md:inline text-gray-400">&middot; {{ lastUpdated }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="/export/executive/pdf" class="btn-secondary text-xs" title="Download PDF">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    PDF
                </a>
                <a href="/export/executive/excel" class="btn-secondary text-xs" title="Download Excel">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Excel
                </a>
                <button @click="printView" class="btn-secondary text-xs">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Print
                </button>
            </div>
        </div>

        <!-- AI Summary Banner -->
        <div v-if="textSummary" class="mb-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-zesco-100 dark:bg-zesco-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-zesco-600 dark:text-zesco-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-zesco-700 dark:text-zesco-400 mb-1">Executive Insight</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ textSummary }}</p>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <KpiCard
                title="Total Revenue"
                :formattedValue="formatCurrency(summary.total_revenue)"
                :change="5.2"
                status="healthy"
                :showSparkline="true"
            />
            <KpiCard
                title="Average Completion"
                :formattedValue="summary.avg_completion + '%'"
                :change="-1.3"
                status="warning"
                :showSparkline="true"
            />
            <KpiCard
                title="Risk Exposure"
                :formattedValue="summary.high_risks + ' High'"
                :change="null"
                :status="summary.high_risks > 5 ? 'critical' : 'healthy'"
            />
            <KpiCard
                title="Operational Uptime"
                :formattedValue="(summary.avg_uptime || 0).toFixed(2) + '%'"
                :change="0.3"
                status="healthy"
                :showSparkline="true"
            />
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <ChartCard title="Revenue by Directorate" :baseHeight="320">
                <template #default="{ zoomedHeight }">
                    <BarChart
                        :data="directorateRevenue"
                        xField="code"
                        yField="revenue"
                        seriesName="Revenue (ZMW)"
                        :height="zoomedHeight"
                    />
                </template>
            </ChartCard>

            <ChartCard title="Budget Utilization" :baseHeight="320">
                <template #default="{ zoomedHeight }">
                    <PieChart
                        :data="budgetPieData"
                        :height="zoomedHeight"
                    />
                </template>
            </ChartCard>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <ChartCard title="Uptime" :baseHeight="200">
                <template #default="{ zoomedHeight }">
                    <GaugeChart
                        :value="summary.avg_uptime || 0"
                        :min="90"
                        :max="100"
                        title="Avg Uptime"
                        unit="%"
                        :height="zoomedHeight"
                    />
                </template>
            </ChartCard>

            <ChartCard title="Risk Distribution" class="md:col-span-2" :baseHeight="200">
                <template #default="{ zoomedHeight }">
                    <HeatmapChart
                        :data="riskHeatmapData"
                        :xLabels="directorateLabels"
                        :yLabels="['Low', 'Medium', 'High', 'Critical']"
                        :height="zoomedHeight"
                    />
                </template>
            </ChartCard>
        </div>

        <!-- Directorates Grid -->
        <Card title="Directorate Performance" class="mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-2">
                <Link
                    v-for="d in summary.directorates"
                    :key="d.id"
                    :href="`/dashboard/directorate/${d.slug}`"
                    class="block p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-executive-md hover:border-zesco-200 dark:hover:border-zesco-700 transition-all duration-200"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: d.color }"></div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ d.code }}</h4>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 truncate" :title="d.name">{{ d.name }}</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <p class="text-gray-400">Completion</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ d.completion_percentage }}%</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Risks</p>
                            <p class="font-semibold" :class="d.high_risk_count > 2 ? 'text-red-600' : 'text-gray-900 dark:text-white'">
                                {{ d.risk_count }} ({{ d.high_risk_count }} high)
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400">Employees</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ d.employees?.toLocaleString() || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Budget Util.</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ d.budget_utilization }}%</p>
                        </div>
                    </div>
                </Link>
            </div>
        </Card>

    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import KpiCard from '@/Components/Dashboard/KpiCard.vue';
import Card from '@/Components/UI/Card.vue';
import ChartCard from '@/Components/UI/ChartCard.vue';
import DateRangePicker from '@/Components/UI/DateRangePicker.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';
import GaugeChart from '@/Components/Charts/GaugeChart.vue';
import HeatmapChart from '@/Components/Charts/HeatmapChart.vue';
import { formatCurrency } from '@/Composables/useFormatters';
import { useEcho } from '@/Composables/useEcho';

const props = defineProps({
    summary: { type: Object, default: () => ({}) },
    directorates: { type: Array, default: () => [] },
    textSummary: { type: String, default: '' },
    filters: { type: Object, default: () => ({ from: '', to: '' }) },
});

const filters = ref({ ...props.filters });

const directorateRevenue = computed(() => {
    return (props.summary.directorates || []).map(d => ({
        code: d.code,
        revenue: d.revenue,
    }));
});

const budgetPieData = computed(() => {
    return (props.summary.directorates || []).slice(0, 6).map(d => ({
        name: d.code,
        value: d.budget,
    }));
});

const directorateLabels = computed(() => (props.summary.directorates || []).map(d => d.code));

const riskHeatmapData = computed(() => {
    const data = [];
    (props.summary.directorates || []).forEach((d, x) => {
        // Distribute risks across levels
        data.push([x, 0, Math.max(0, d.risk_count - d.high_risk_count)]); // Low
        data.push([x, 1, Math.floor(d.risk_count * 0.3)]); // Medium
        data.push([x, 2, Math.floor(d.high_risk_count * 0.6)]); // High
        data.push([x, 3, Math.ceil(d.high_risk_count * 0.4)]); // Critical
    });
    return data;
});

function applyFilters() {
    router.get('/dashboard', {
        from: filters.value.from || undefined,
        to: filters.value.to || undefined,
    }, { preserveState: true });
}

function clearFilters() {
    filters.value = { from: '', to: '' };
    router.get('/dashboard');
}

function printView() {
    window.print();
}

// ── WebSocket: Listen for real-time simulation updates ────
const { connect, listenForUpdates, disconnect } = useEcho();
const isLive = ref(false);
const lastUpdated = ref(null);

onMounted(async () => {
    try {
        await connect();
        isLive.value = true;
        listenForUpdates(() => {
            // Reload all dashboard data when simulation pushes new data
            router.reload({
                only: ['summary', 'textSummary'],
                preserveScroll: true,
                onSuccess: () => {
                    lastUpdated.value = new Date().toLocaleTimeString();
                },
            });
        });
    } catch {
        // WebSocket is optional — dashboard works fine without it
        isLive.value = false;
    }
});

onUnmounted(() => {
    disconnect();
});
</script>
