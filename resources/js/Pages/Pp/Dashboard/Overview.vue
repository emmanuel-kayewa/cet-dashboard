<template>
    <AppLayout
        :directorates="directorates"
        :aiScope="{ type: 'pp_portfolio', directorate_id: directorate.id }"
    >
        <template #title>PP Portfolio Overview</template>

        <!-- Header only shown when NOT in directorate sidebar mode -->
        <template v-if="!directorateStore.activeDirectorate">
            <Breadcrumb :items="[
                { label: 'Dashboard', href: '/dashboard' },
                { label: 'PP Portfolio', current: true }
            ]" />

            <PageHeader :metrics="headerMetrics">
                <template #left>
                    <div class="flex items-center gap-4 min-w-0">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                            :style="{ backgroundColor: directorate.color }"
                        >
                            P
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">Planning &amp; Projects Portfolio</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">As at {{ ppData.kpis.as_of || '—' }} &middot; Click a sector to drill down</p>
                        </div>
                    </div>
                </template>
            </PageHeader>

            <!-- Quick Links -->
            <div class="flex items-center gap-3 mb-6 no-print overflow-x-auto flex-nowrap whitespace-nowrap pr-2 pb-1">
                <Link href="/pp/dashboard/explore" class="btn-primary text-sm px-4 py-2 flex-shrink-0 whitespace-nowrap">
                    Portfolio Breakdown
                </Link>
                <Link href="/pp/dashboard/explore?rag_status=Red" class="btn-secondary text-sm px-4 py-2 flex-shrink-0 whitespace-nowrap">
                    Critical Projects
                </Link>
                <Link href="/pp/projects" class="btn-secondary text-sm px-4 py-2 flex-shrink-0 whitespace-nowrap">
                    Manage Data
                </Link>
            </div>
        </template>

        <!-- ── Headline KPI Strip ── -->
        <!-- <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Total Projects</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ ppData.kpis.totalProjects }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Committed (USD)</p>
                <p class="text-lg font-bold" :style="{ color: INVESTMENT.committed }">${{ fmtM(ppData.kpis.totalCommitted) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Paid-to-Date</p>
                <p class="text-lg font-bold" :style="{ color: INVESTMENT.paid }">${{ fmtM(ppData.kpis.totalPaid) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Spend %</p>
                <p class="text-lg font-bold" :style="{ color: (ppData.kpis.spendPct ?? 0) >= 50 ? RAG.green : RAG.amber }">{{ ppData.kpis.spendPct }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">MW Commissioned</p>
                <p class="text-lg font-bold" :style="{ color: RAG.green }">{{ ppData.kpis.genCommissioned }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Avg Progress</p>
                <p class="text-lg font-bold" :style="{ color: (ppData.kpis.avgProgress ?? 0) >= 50 ? RAG.green : RAG.amber }">{{ ppData.kpis.avgProgress }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Wayleave Closure</p>
                <p class="text-lg font-bold" :style="{ color: (ppData.kpis.wlClosurePct ?? 0) >= 75 ? RAG.green : RAG.amber }">{{ ppData.kpis.wlClosurePct }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Survey Closure</p>
                <p class="text-lg font-bold" :style="{ color: (ppData.kpis.svClosurePct ?? 0) >= 75 ? RAG.green : RAG.amber }">{{ ppData.kpis.svClosurePct }}%</p>
            </div>
        </div> -->

        <!-- ── Sector Quick-Access Cards ── -->
        <div class="mb-2">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Sectors at a Glance</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Click a card to explore that sector</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-5 mb-8">
            <Link v-for="card in ppData.sectorCards" :key="card.sector"
                  :href="`/pp/dashboard/explore?sector=${encodeURIComponent(card.sector)}`"
                  class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-7 hover:shadow-lg hover:border-zesco-200 dark:hover:border-zesco-600 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: card.color }"></div>
                        <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-zesco-600 dark:group-hover:text-zesco-400 transition-colors truncate">{{ card.sector }}</h4>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-zesco-500 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div>
                        <p class="text-xs text-gray-400">Projects</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ card.projectCount }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Investment</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ fmtM(card.totalCost) }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
                    <span>Paid: ${{ fmtM(card.totalPaid) }}</span>
                    <span>Spend: {{ card.spendPct ?? 0 }}%</span>
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
                    <div class="h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                             :style="{ width: card.avgProgress + '%', backgroundColor: card.color }"></div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- ── Grid Impact Studies Summary Card ── -->
        <!--
        <div v-if="ppData.gridStudiesSummary && ppData.gridStudiesSummary.totalStudies > 0" class="mb-8">
            <div class="mb-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Grid Impact Studies</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">IPP grid connection study tracker overview</p>
            </div>
            <Link href="/pp/dashboard/grid-studies"
                  class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 hover:shadow-lg hover:border-purple-200 dark:hover:border-purple-600 transition-all duration-200 cursor-pointer group">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 flex-1">
                        <div>
                            <p class="text-xs text-gray-400">Total Studies</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ ppData.gridStudiesSummary.totalStudies }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Reports</p>
                            <p class="text-2xl font-bold" :style="{ color: CATEGORICAL[0] }">{{ ppData.gridStudiesSummary.totalReports }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Inception</p>
                            <p class="text-2xl font-bold" :style="{ color: CATEGORICAL[5] }">{{ ppData.gridStudiesSummary.totalInception }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Approved</p>
                            <p class="text-2xl font-bold" :style="{ color: RAG.green }">{{ ppData.gridStudiesSummary.approvedCount }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Pipeline MW</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ ppData.gridStudiesSummary.totalCapacityMw }}</p>
                        </div>
                    </div>
                    <div class="flex items-end gap-1 justify-center lg:justify-end flex-shrink-0">
                        <div v-for="stage in ppData.gridStudiesSummary.reportFunnel" :key="stage.stage"
                             class="flex flex-col items-center">
                            <div class="w-8 rounded-t transition-all"
                                 :style="{ height: Math.max(8, (stage.count / Math.max(...ppData.gridStudiesSummary.reportFunnel.map(s => s.count), 1)) * 48) + 'px', backgroundColor: stage.stage === 'Approved' ? RAG.green : stage.stage === 'Under Review' ? RAG.amber : CATEGORICAL[0] }">
                            </div>
                            <p class="text-[8px] text-gray-400 mt-0.5 leading-tight text-center w-8">{{ stage.count }}</p>
                        </div>
                    </div>
                    <div class="hidden lg:flex items-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </div>
            </Link>
        </div>
        -->
    </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import { INVESTMENT, RAG } from '@/Composables/useChartPalette';
import { useBadges } from '@/Composables/useBadges';
import { useDirectorateStore } from '@/stores/useDirectorateStore';

const { getRagColor } = useBadges();
const directorateStore = useDirectorateStore();

const props = defineProps({
    ppData: { type: Object, required: true },
    directorate: { type: Object, required: true },
    directorates: { type: Array, default: () => [] },
});

const headerMetrics = computed(() => {
    const spendColor = (props.ppData.kpis?.spendPct ?? 0) >= 50 ? RAG.green : RAG.amber;
    const progressColor = (props.ppData.kpis?.avgProgress ?? 0) >= 50 ? RAG.green : RAG.amber;
    return [
        { label: 'Projects', value: props.ppData.kpis?.totalProjects ?? 0 },
        { label: 'Committed', value: `$${fmtM(props.ppData.kpis?.totalCommitted)}`, color: INVESTMENT.committed },
        { label: 'Spend Rate', value: `${props.ppData.kpis?.spendPct ?? 0}%`, color: spendColor },
        { label: 'Avg Progress', value: `${props.ppData.kpis?.avgProgress ?? 0}%`, color: progressColor },
    ];
});

// Provide summary data to directorate sidebar
onMounted(() => {
    // Auto-enter directorate mode if not already active (e.g. direct URL navigation)
    if (!directorateStore.activeDirectorate) {
        directorateStore.enterDirectorate(props.directorate);
    }
    directorateStore.updateSummary({
        totalProjects: props.ppData.kpis?.totalProjects ?? 0,
        totalCommitted: props.ppData.kpis?.totalCommitted ?? 0,
        spendPct: props.ppData.kpis?.spendPct ?? 0,
    });
});

// ── Helpers ──

function fmtM(val) {
    const n = Number(val) || 0;
    if (n >= 1e9) return (n / 1e9).toFixed(2) + 'B';
    if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K';
    return n.toLocaleString();
}
</script>
