<template>
    <AppLayout
        :directorates="directorates"
        :aiScope="{ type: 'pp_explorer', directorate_id: directorate.id, filters: explorerData.appliedFilters }"
    >
        <template #title>PP Explorer</template>

        <Breadcrumb :items="breadcrumbItems" />

        <!-- Page Header -->
        <div class="mb-6 p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        <template v-if="hasFilters">
                            {{ filterSummaryTitle }}
                        </template>
                        <template v-else>
                            All Projects
                        </template>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ explorerData.totalCount }} project{{ explorerData.totalCount !== 1 ? 's' : '' }} matching
                        <template v-if="hasFilters">current filters</template>
                        <template v-else>all criteria</template>
                    </p>
                </div>
                <div class="flex items-center gap-6 text-center flex-shrink-0">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ explorerData.kpis.totalProjects }}</p>
                        <p class="text-xs text-gray-500">Projects</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-600">${{ fmtM(explorerData.kpis.totalCommitted) }}</p>
                        <p class="text-xs text-gray-500">Committed</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" :class="explorerData.kpis.spendPct >= 50 ? 'text-green-600' : 'text-amber-600'">
                            {{ explorerData.kpis.spendPct }}%
                        </p>
                        <p class="text-xs text-gray-500">Spend</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">{{ explorerData.kpis.avgProgress }}%</p>
                        <p class="text-xs text-gray-500">Avg Progress</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Filter Chips -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Filters:</span>

            <template v-if="hasFilters">
                <span v-for="(val, dim) in explorerData.appliedFilters" :key="dim"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-zesco-100 text-zesco-700 dark:bg-zesco-900/30 dark:text-zesco-400">
                    <span class="text-[10px] uppercase text-zesco-500 dark:text-zesco-500">{{ dimensionLabels[dim] || dim }}:</span>
                    {{ val }}
                    <button @click="removeFilter(dim)" class="ml-0.5 hover:text-red-600 transition-colors" title="Remove filter">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </span>
                <button @click="clearAllFilters" class="text-xs text-red-500 hover:text-red-700 font-medium ml-2">
                    Clear All
                </button>
            </template>
            <span v-else class="text-xs text-gray-400 italic">No filters applied — showing all projects</span>

            <!-- Add filter dropdowns for un-applied dimensions -->
            <div class="ml-auto flex items-center gap-2 no-print">
                <Select
                    v-model="addFilterDim"
                    :options="availableDimensionsOptions"
                    placeholder="+ Add Filter"
                    size="sm"
                    class="w-40"
                />
                <Select
                    v-if="addFilterDim"
                    v-model="addFilterVal"
                    :options="addFilterOptions"
                    placeholder="Select value…"
                    size="sm"
                    class="w-48"
                />
            </div>
        </div>

        <!-- ── Adaptive Breakdown Charts ── -->
        <template v-if="Object.keys(explorerData.breakdowns).length > 0">
            <div class="mb-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Drill Down Further</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Click any chart element to add a filter</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <template v-for="(bd, dim) in explorerData.breakdowns" :key="dim">
                    <Card :title="`Projects by ${bd.label}`">
                        <!-- Use 3D pie for sector/RAG -->
                        <Pie3DChart
                            v-if="dim === 'sector' || dim === 'rag_status'"
                            :data="bd.data"
                            height="300px"
                            @chart-click="(p) => addDimensionFilter(dim, p.name)"
                        />

                        <!-- Province / District: map ↔ bar toggle -->
                        <ZambiaMapChart
                            v-else-if="dim === 'province' || dim === 'district'"
                            :data="bd.data.map(d => ({ name: d.name, value: d.value, investment: d.totalCost || 0 }))"
                            :level="dim"
                            :metricLabel="bd.label"
                            :showToggle="true"
                            :showMetricToggle="true"
                            height="300px"
                            @region-click="(name) => addDimensionFilter(dim, name)"
                        >
                            <BarChart
                                :data="bd.data.map(d => ({ label: d.name, value: d.value }))"
                                xField="label"
                                yField="value"
                                :seriesName="bd.label"
                                :colors="bd.data.map(d => d.color)"
                                height="300px"
                                :horizontal="bd.data.length > 5"
                                @chart-click="(p) => addDimensionFilter(dim, p.name || extractBarLabel(p))"
                            />
                        </ZambiaMapChart>

                        <!-- Other dimensions: plain bar -->
                        <BarChart
                            v-else
                            :data="bd.data.map(d => ({ label: d.name, value: d.value }))"
                            xField="label"
                            yField="value"
                            :seriesName="bd.label"
                            :colors="bd.data.map(d => d.color)"
                            height="300px"
                            :horizontal="bd.data.length > 5"
                            @chart-click="(p) => addDimensionFilter(dim, p.name || extractBarLabel(p))"
                        />
                    </Card>
                </template>
            </div>
        </template>

        <!-- ── Investment Chart (always shown if sectors vary) ── -->
        <div v-if="explorerData.sectorInvestment.length > 1" class="mb-8">
            <Card title="Investment by Sector — Committed vs Paid (USD)">
                <BarChart
                    :data="investmentBarData"
                    xField="label"
                    :multiSeries="investmentSeries"
                    height="320px"
                    @chart-click="(p) => addDimensionFilter('sector', p.name)"
                />
            </Card>
        </div>

        <!-- ── Grid Impact Studies Banner (shows when IPP sector is filtered) ── -->
        <div v-if="isIppContext" class="mb-8">
            <Link href="/pp/dashboard/grid-studies"
                  class="block bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-xl border border-purple-200 dark:border-purple-700/50 p-5 hover:shadow-lg transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                            Grid Impact Studies Tracker
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            View the dedicated 5-stage pipeline tracker for IPP grid connection studies
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-500 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </Link>
        </div>

        <!-- ── Risk Summary (if available) ── -->
        <div v-if="explorerData.risksByCategory.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <Card title="Risks by Category">
                <Pie3DChart
                    :data="riskCategoryData"
                    height="280px"
                />
            </Card>
            <Card title="Risks by Level">
                <BarChart
                    :data="riskLevelData"
                    xField="label"
                    yField="value"
                    seriesName="Risks"
                    :colors="[RAG.green, RAG.amber, RAG.red]"
                    height="280px"
                />
            </Card>
        </div>

        <!-- ── Project Table ── -->
        <div class="mb-6">
            <div class="mb-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Projects</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ explorerData.projects.length }} projects — click a row for full details
                </p>
            </div>

            <!-- Table search / sort controls -->
            <div class="flex items-center gap-3 mb-3 no-print">
                <Input
                    v-model="tableSearch"
                    type="text"
                    placeholder="Search projects…"
                    icon="search"
                    size="sm"
                    class="flex-1 max-w-md"
                />
                <Select
                    v-model="sortField"
                    :options="sortOptions"
                    size="sm"
                    class="w-48"
                />
            </div>

            <Card noPadding>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Project</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Sector</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Province</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">MW</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Cost (USD)</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Progress</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">RAG</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="p in paginatedProjects" :key="p.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-colors"
                                @click="goToProject(p.id)">
                                <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ p.code }}</td>
                                <td class="px-4 py-2 text-gray-900 dark:text-white font-medium max-w-xs truncate">{{ p.name }}</td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300 text-xs hidden sm:table-cell">{{ p.sector }}</td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300 text-xs hidden md:table-cell">{{ p.province || '—' }}</td>
                                <td class="px-4 py-2 text-center">
                                    <Badge variant="dot" :color="getProjectStatusColor(p.status)" :label="p.status" />
                                </td>
                                <td class="px-4 py-2 text-right text-gray-900 dark:text-white hidden lg:table-cell">{{ p.capacity_mw?.toLocaleString() || '—' }}</td>
                                <td class="px-4 py-2 text-right text-gray-900 dark:text-white hidden md:table-cell">{{ p.cost_usd ? '$' + fmtM(p.cost_usd) : '—' }}</td>
                                <td class="px-4 py-2 text-center text-gray-900 dark:text-white hidden sm:table-cell">{{ p.progress_pct ?? 0 }}%</td>
                                <td class="px-4 py-2 text-center">
                                    <Badge variant="dot" :color="getRagColor(p.rag)" :label="p.rag" />
                                </td>
                            </tr>
                            <tr v-if="filteredProjects.length === 0">
                                <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                    <p class="text-lg mb-1">No projects match these filters</p>
                                    <p class="text-sm">Try removing some filters or <button @click="clearAllFilters" class="text-zesco-600 hover:underline">clear all</button></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500">
                        Showing {{ (currentPage - 1) * pageSize + 1 }}–{{ Math.min(currentPage * pageSize, filteredProjects.length) }} of {{ filteredProjects.length }}
                    </p>
                    <div class="flex items-center gap-1">
                        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                                class="p-1.5 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <span class="text-xs text-gray-500 px-2">{{ currentPage }} / {{ totalPages }}</span>
                        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage >= totalPages"
                                class="p-1.5 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, defineAsyncComponent } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Select from '@/Components/UI/Select.vue';
import Input from '@/Components/UI/Input.vue';
import Badge from '@/Components/UI/Badge.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import { INVESTMENT, RISK_CATEGORIES, RAG } from '@/Composables/useChartPalette';
import { useBadges } from '@/Composables/useBadges';

const { getProjectStatusColor, getRagColor } = useBadges();

const Pie3DChart = defineAsyncComponent(() => import('@/Components/Charts/Pie3DChart.vue'));
const ZambiaMapChart = defineAsyncComponent(() => import('@/Components/Charts/ZambiaMapChart.vue'));

const props = defineProps({
    explorerData: { type: Object, required: true },
    directorate: { type: Object, required: true },
    directorates: { type: Array, default: () => [] },
    dimensionLabels: { type: Object, default: () => ({}) },
});

// ── Breadcrumb items ──

const breadcrumbItems = computed(() => {
    const items = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'PP Portfolio', href: '/pp/dashboard' }
    ];
    
    // Add filter items dynamically
    for (const [dim, val] of Object.entries(props.explorerData.appliedFilters || {})) {
        items.push({ label: val, href: breadcrumbUrl(dim) });
    }
    
    return items;
});

// ── Filter management ──

const hasFilters = computed(() => Object.keys(props.explorerData.appliedFilters || {}).length > 0);

const isIppContext = computed(() => {
    const applied = props.explorerData.appliedFilters || {};
    return applied.sector === 'IPP';
});

const filterSummaryTitle = computed(() => {
    const parts = Object.entries(props.explorerData.appliedFilters || {}).map(([dim, val]) => val);
    return parts.join(' › ');
});

const availableDimensions = computed(() => {
    const applied = props.explorerData.appliedFilters || {};
    const result = {};
    for (const [dim, label] of Object.entries(props.dimensionLabels)) {
        if (!applied[dim]) {
            // Only show if there are options with data
            const opts = props.explorerData.filterOptions?.[dim] || [];
            if (opts.length > 0) {
                result[dim] = label;
            }
        }
    }
    return result;
});

const availableDimensionsOptions = computed(() => {
    return Object.entries(availableDimensions.value).map(([value, label]) => ({
        value,
        label
    }));
});

const addFilterDim = ref('');
const addFilterVal = ref('');

const addFilterOptions = computed(() => {
    if (!addFilterDim.value) return [];
    return (props.explorerData.filterOptions?.[addFilterDim.value] || []).map(opt => ({
        value: opt,
        label: opt
    }));
});

watch(addFilterDim, () => { addFilterVal.value = ''; });

// Watch for value change since our custom component emits update:modelValue
watch(addFilterVal, (newVal) => {
    if (newVal) {
        applyAddedFilter();
    }
});

function applyAddedFilter() {
    if (!addFilterDim.value || !addFilterVal.value) return;
    addDimensionFilter(addFilterDim.value, addFilterVal.value);
    addFilterDim.value = '';
    addFilterVal.value = '';
}

function addDimensionFilter(dim, value) {
    if (!value || value === 'Unknown') return;
    const current = { ...(props.explorerData.appliedFilters || {}) };
    current[dim] = value;
    router.get('/pp/dashboard/explore', current);
}

function removeFilter(dim) {
    const current = { ...(props.explorerData.appliedFilters || {}) };
    delete current[dim];
    if (Object.keys(current).length === 0) {
        router.get('/pp/dashboard/explore');
    } else {
        router.get('/pp/dashboard/explore', current);
    }
}

function clearAllFilters() {
    router.get('/pp/dashboard/explore');
}

function breadcrumbUrl(upToDim) {
    const filters = {};
    for (const [dim, val] of Object.entries(props.explorerData.appliedFilters || {})) {
        filters[dim] = val;
        if (dim === upToDim) break;
    }
    return '/pp/dashboard/explore?' + new URLSearchParams(filters).toString();
}

// ── Chart data transforms ──

const investmentBarData = computed(() => {
    return (props.explorerData.sectorInvestment || []).map(item => ({
        label: item.sector,
        committed: item.committed || 0,
        paid: item.paid || 0,
    }));
});

const investmentSeries = [
    { name: 'Committed (USD)', field: 'committed', color: INVESTMENT.committed },
    { name: 'Paid (USD)', field: 'paid', color: INVESTMENT.paid },
];

const riskCategoryData = computed(() => {
    return (props.explorerData.risksByCategory || []).map((item, i) => ({
        name: item.category,
        value: item.count,
        color: RISK_CATEGORIES[i % RISK_CATEGORIES.length],
    }));
});

const riskLevelData = computed(() => {
    const colorMap = { Green: RAG.green, Amber: RAG.amber, Red: RAG.red };
    return (props.explorerData.risksByLevel || []).map(item => ({
        label: item.level,
        value: item.count,
        color: colorMap[item.level] || RAG.grey,
    }));
});

// ── Table management ──

const tableSearch = ref('');
const sortField = ref('cost_usd');
const sortOptions = [
    { value: 'cost_usd', label: 'Sort: Cost (high→low)' },
    { value: 'name', label: 'Sort: Name' },
    { value: 'progress_pct', label: 'Sort: Progress' },
    { value: 'capacity_mw', label: 'Sort: MW' },
];
const currentPage = ref(1);
const pageSize = 15;

const filteredProjects = computed(() => {
    let list = [...(props.explorerData.projects || [])];

    // Search
    if (tableSearch.value) {
        const q = tableSearch.value.toLowerCase();
        list = list.filter(p =>
            (p.name || '').toLowerCase().includes(q) ||
            (p.code || '').toLowerCase().includes(q) ||
            (p.sector || '').toLowerCase().includes(q) ||
            (p.province || '').toLowerCase().includes(q) ||
            (p.district || '').toLowerCase().includes(q)
        );
    }

    // Sort
    list.sort((a, b) => {
        if (sortField.value === 'name') return (a.name || '').localeCompare(b.name || '');
        if (sortField.value === 'progress_pct') return (b.progress_pct || 0) - (a.progress_pct || 0);
        if (sortField.value === 'capacity_mw') return (b.capacity_mw || 0) - (a.capacity_mw || 0);
        return (b.cost_usd || 0) - (a.cost_usd || 0);
    });

    return list;
});

const totalPages = computed(() => Math.ceil(filteredProjects.value.length / pageSize));

const paginatedProjects = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredProjects.value.slice(start, start + pageSize);
});

// Reset page on search change
watch(tableSearch, () => { currentPage.value = 1; });
watch(sortField, () => { currentPage.value = 1; });

function goToProject(id) {
    const filters = { ...(props.explorerData.appliedFilters || {}) };
    router.get(`/pp/dashboard/projects/${id}`, filters);
}

function extractBarLabel(params) {
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
