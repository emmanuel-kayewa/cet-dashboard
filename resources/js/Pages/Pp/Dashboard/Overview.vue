<template>
    <AppLayout
        :directorates="directorates"
        :aiScope="{ type: 'pp_portfolio', directorate_id: directorate.id }"
    >
        <template #title>PP Portfolio Overview</template>

        <Breadcrumb :items="[
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'PP Portfolio', current: true }
        ]" />

        <!-- Directorate Header -->
        <div class="mb-6 p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                         :style="{ backgroundColor: directorate.color }">
                        P
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">Planning &amp; Projects Portfolio</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">As at {{ ppData.kpis.as_of || '—' }} &middot; Click any chart to drill down</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-center sm:ml-auto flex-shrink-0">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ ppData.kpis.totalProjects }}</p>
                        <p class="text-xs text-gray-500">Projects</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ fmtM(ppData.kpis.totalCommitted) }}</p>
                        <p class="text-xs text-gray-500">Committed</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" :class="ppData.kpis.spendPct >= 50 ? 'text-green-600' : 'text-amber-600'">
                            {{ ppData.kpis.spendPct }}%
                        </p>
                        <p class="text-xs text-gray-500">Spend Rate</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ ppData.kpis.avgProgress }}%</p>
                        <p class="text-xs text-gray-500">Avg Progress</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="flex items-center gap-3 mb-6 no-print">
            <Link href="/pp/dashboard/explore" class="btn-primary text-sm px-4 py-2">
                Explore All Projects
            </Link>
            <Link href="/pp/dashboard/grid-studies" class="btn-secondary text-sm px-4 py-2">
                Grid Impact Studies
            </Link>
            <Link href="/pp/projects" class="btn-secondary text-sm px-4 py-2">
                Manage Data
            </Link>
        </div>

        <!-- ── Headline KPI Strip ── -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Total Projects</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ ppData.kpis.totalProjects }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Committed (USD)</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">${{ fmtM(ppData.kpis.totalCommitted) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Paid-to-Date</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">${{ fmtM(ppData.kpis.totalPaid) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Spend %</p>
                <p class="text-lg font-bold" :class="ppData.kpis.spendPct >= 50 ? 'text-green-600' : 'text-amber-600'">{{ ppData.kpis.spendPct }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">MW Commissioned</p>
                <p class="text-lg font-bold text-green-600">{{ ppData.kpis.genCommissioned }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Avg Progress</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ ppData.kpis.avgProgress }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Wayleave Closure</p>
                <p class="text-lg font-bold" :class="ppData.kpis.wlClosurePct >= 75 ? 'text-green-600' : 'text-amber-600'">{{ ppData.kpis.wlClosurePct }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Survey Closure</p>
                <p class="text-lg font-bold" :class="ppData.kpis.svClosurePct >= 75 ? 'text-green-600' : 'text-amber-600'">{{ ppData.kpis.svClosurePct }}%</p>
            </div>
        </div>

        <!-- ── Sector Quick-Access Cards ── -->
        <div class="mb-2">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Sectors at a Glance</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Click a card to explore that sector</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <Link v-for="card in ppData.sectorCards" :key="card.sector"
                  :href="`/pp/dashboard/explore?sector=${encodeURIComponent(card.sector)}`"
                  class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 hover:shadow-lg hover:border-zesco-200 dark:hover:border-zesco-600 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: card.color }"></div>
                        <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-zesco-600 dark:group-hover:text-zesco-400 transition-colors">{{ card.sector }}</h4>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-zesco-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <p class="text-xs text-gray-400">Projects</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ card.projectCount }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Investment</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">${{ fmtM(card.totalCost) }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <span v-if="card.totalMw" class="text-xs text-gray-500">{{ card.totalMw }} MW</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="flex items-center gap-0.5 text-xs">
                            <span class="w-2 h-2 rounded-full" :class="`bg-${getRagColor('Green')}-500`"></span>{{ card.ragCounts.Green || 0 }}
                        </span>
                        <span class="flex items-center gap-0.5 text-xs">
                            <span class="w-2 h-2 rounded-full" :class="`bg-${getRagColor('Amber')}-500`"></span>{{ card.ragCounts.Amber || 0 }}
                        </span>
                        <span class="flex items-center gap-0.5 text-xs">
                            <span class="w-2 h-2 rounded-full" :class="`bg-${getRagColor('Red')}-500`"></span>{{ card.ragCounts.Red || 0 }}
                        </span>
                    </div>
                </div>
                <!-- Progress bar -->
                <div class="mt-3">
                    <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                        <span>Avg Progress</span>
                        <span>{{ card.avgProgress }}%</span>
                    </div>
                    <div class="h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                             :style="{ width: card.avgProgress + '%', backgroundColor: card.color }"></div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- ── Grid Impact Studies Summary Card ── -->
        <div v-if="ppData.gridStudiesSummary && ppData.gridStudiesSummary.totalStudies > 0" class="mb-8">
            <div class="mb-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Grid Impact Studies</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">IPP grid connection study tracker overview</p>
            </div>
            <Link href="/pp/dashboard/grid-studies"
                  class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 hover:shadow-lg hover:border-purple-200 dark:hover:border-purple-600 transition-all duration-200 cursor-pointer group">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                    <!-- KPI Strip -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 flex-1">
                        <div>
                            <p class="text-xs text-gray-400">Total Studies</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ ppData.gridStudiesSummary.totalStudies }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Reports</p>
                            <p class="text-2xl font-bold text-blue-600">{{ ppData.gridStudiesSummary.totalReports }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Inception</p>
                            <p class="text-2xl font-bold text-purple-600">{{ ppData.gridStudiesSummary.totalInception }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Approved</p>
                            <p class="text-2xl font-bold text-green-600">{{ ppData.gridStudiesSummary.approvedCount }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Pipeline MW</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ ppData.gridStudiesSummary.totalCapacityMw }}</p>
                        </div>
                    </div>
                    <!-- Mini funnel visualization -->
                    <div class="flex items-end gap-1 justify-center lg:justify-end flex-shrink-0">
                        <div v-for="stage in ppData.gridStudiesSummary.reportFunnel" :key="stage.stage"
                             class="flex flex-col items-center">
                            <div class="w-8 rounded-t transition-all"
                                 :style="{ height: Math.max(8, (stage.count / Math.max(...ppData.gridStudiesSummary.reportFunnel.map(s => s.count), 1)) * 48) + 'px' }"
                                 :class="stage.stage === 'Approved' ? 'bg-green-500' : stage.stage === 'Under Review' ? 'bg-amber-500' : 'bg-blue-400'">
                            </div>
                            <p class="text-[8px] text-gray-400 mt-0.5 leading-tight text-center w-8">{{ stage.count }}</p>
                        </div>
                    </div>
                    <!-- Arrow icon -->
                    <div class="hidden lg:flex items-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </div>
            </Link>
        </div>

        <!-- ── Clickable Breakdown Charts ── -->
        <div class="mb-2">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Portfolio Breakdown</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Click any chart slice or bar to drill down</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Projects by Sector (3D Pie) -->
            <Card title="Projects by Sector">
                <Pie3DChart
                    :data="ppData.sectorBreakdown"
                    height="320px"
                    @chart-click="(p) => navigateExplore('sector', p.name)"
                />
            </Card>

            <!-- Projects by Project Type -->
            <Card title="Projects by Project Type">
                <Pie3DChart
                    :data="ppData.subSectorBreakdown"
                    height="320px"
                    :showLegend="false"
                    @chart-click="(p) => navigateExplore('sub_sector', p.name)"
                />
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Investment by Sector (Bar) -->
            <Card title="Committed Investment by Sector (USD)">
                <BarChart
                    :data="investmentBarData"
                    xField="label"
                    :multiSeries="investmentSeries"
                    height="320px"
                    @chart-click="(p) => navigateExplore('sector', p.name)"
                />
            </Card>

            <!-- Projects by RAG -->
            <Card title="Projects by RAG Status">
                <Pie3DChart
                    :data="ppData.ragBreakdown"
                    height="320px"
                    @chart-click="(p) => navigateExplore('rag_status', p.name)"
                />
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Projects by Status -->
            <Card title="Projects by Status">
                <BarChart
                    :data="statusBarData"
                    xField="label"
                    yField="value"
                    seriesName="Projects"
                    :colors="STATUS"
                    height="300px"
                    @chart-click="(p) => navigateExplore('status', extractLabel(p))"
                />
            </Card>

            <!-- Projects by Programme -->
            <Card title="Projects by Programme">
                <BarChart
                    :data="programmeBarData"
                    xField="label"
                    yField="value"
                    seriesName="Projects"
                    :colors="CATEGORICAL.slice(1)"
                    height="300px"
                    horizontal
                    @chart-click="(p) => navigateExplore('programme', extractLabel(p))"
                />
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Projects by Province (with map toggle) -->
            <Card title="Projects by Province">
                <ZambiaMapChart
                    :data="provinceMapData"
                    level="province"
                    metricLabel="Projects"
                    :showMetricToggle="true"
                    height="300px"
                    @region-click="(name) => navigateExplore('province', name)"
                >
                    <BarChart
                        :data="provinceBarData"
                        xField="label"
                        yField="value"
                        seriesName="Projects"
                        :colors="CATEGORICAL"
                        height="300px"
                        horizontal
                        @chart-click="(p) => navigateExplore('province', extractLabel(p))"
                    />
                </ZambiaMapChart>
            </Card>
        </div>

        <!-- ── Recent Issues ── -->
        <div v-if="ppData.recentIssues.length > 0" class="mb-8">
            <div class="mb-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Attention Required</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Projects with Red RAG or key issues</p>
            </div>
            <Card title="Critical Projects" noPadding>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <Link v-for="issue in ppData.recentIssues" :key="issue.id"
                          :href="`/pp/dashboard/projects/${issue.id}`"
                          class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer">
                        <span class="inline-block w-3 h-3 rounded-full flex-shrink-0" :class="`bg-${getRagColor(issue.rag)}-500`"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ issue.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ issue.sector }} &middot; {{ issue.key_issue || 'No details' }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ issue.progress ?? 0 }}%</p>
                            <p class="text-xs text-gray-400">progress</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </Link>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import { CATEGORICAL, STATUS, INVESTMENT, RAG } from '@/Composables/useChartPalette';
import { useBadges } from '@/Composables/useBadges';

const { getRagColor } = useBadges();

const Pie3DChart = defineAsyncComponent(() => import('@/Components/Charts/Pie3DChart.vue'));
const ZambiaMapChart = defineAsyncComponent(() => import('@/Components/Charts/ZambiaMapChart.vue'));

const props = defineProps({
    ppData: { type: Object, required: true },
    directorate: { type: Object, required: true },
    directorates: { type: Array, default: () => [] },
});

// ── Chart data transforms ──

const investmentBarData = computed(() => {
    return (props.ppData.sectorInvestment || []).map(item => ({
        label: item.sector,
        committed: item.committed || 0,
        paid: item.paid || 0,
    }));
});

const investmentSeries = [
    { name: 'Committed (USD)', field: 'committed', color: INVESTMENT.committed },
    { name: 'Paid (USD)', field: 'paid', color: INVESTMENT.paid },
];

const statusBarData = computed(() => {
    return (props.ppData.statusBreakdown || []).map(item => ({
        label: item.name,
        value: item.value,
    }));
});

const provinceBarData = computed(() => {
    return (props.ppData.provinceBreakdown || []).filter(i => i.name !== 'Unknown').map(item => ({
        label: item.name,
        value: item.value,
    }));
});

const provinceMapData = computed(() => {
    return (props.ppData.provinceBreakdown || []).filter(i => i.name !== 'Unknown').map(item => ({
        name: item.name,
        value: item.value,
        investment: item.totalCost || 0,
    }));
});

const programmeBarData = computed(() => {
    return (props.ppData.programmeBreakdown || []).filter(i => i.name !== 'Unknown').map(item => ({
        label: item.name,
        value: item.value,
    }));
});

// ── Navigation ──

function navigateExplore(dimension, value) {
    if (!value || value === 'Unknown') return;
    router.get('/pp/dashboard/explore', { [dimension]: value });
}

function extractLabel(params) {
    // ECharts bar click: params.name is the category label
    return params.name || params.data?.label || '';
}

// ── Helpers ──

function fmtM(val) {
    const n = Number(val) || 0;
    if (n >= 1e9) return (n / 1e9).toFixed(2) + 'B';
    if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K';
    return n.toLocaleString();
}
</script>
