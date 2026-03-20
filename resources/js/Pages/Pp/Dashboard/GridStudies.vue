<template>
    <AppLayout
        :directorates="directorates"
        :aiScope="{ type: 'pp_grid_studies', directorate_id: directorate.id, filters: gridData.appliedFilters }"
    >
        <template #title>Grid Impact Studies</template>

        <!-- Header only shown when NOT in directorate sidebar mode -->
        <template v-if="!directorateStore.activeDirectorate">
            <Breadcrumb :items="[
                { label: 'Dashboard', href: '/dashboard' },
                { label: 'PP Portfolio', href: '/pp/dashboard' },
                { label: 'Grid Impact Studies', current: true }
            ]" />

            <PageHeader :metrics="headerMetrics">
                <template #left>
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0" :style="{ backgroundColor: CATEGORICAL[5] }">
                            GIS
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">Grid Impact Studies Tracker</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">IPP grid connection studies &middot; Click charts to filter</p>
                        </div>
                    </div>
                </template>
            </PageHeader>
        </template>

        <!-- Quick Links -->
        <div class="flex items-center gap-3 mb-6 no-print">
            <Link href="/pp/dashboard" class="btn-secondary text-sm px-4 py-2">
                &larr; Back to Overview
            </Link>
            <Link href="/pp/dashboard/explore?sector=IPP" class="btn-secondary text-sm px-4 py-2">
                Explore IPP Projects
            </Link>
        </div>

        <!-- ── KPI Strip ── -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Total Studies</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ gridData.kpis.totalStudies }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Approved</p>
                <p class="text-lg font-bold text-green-600">{{ gridData.kpis.approvedCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Under Review</p>
                <p class="text-lg font-bold text-amber-600">{{ gridData.kpis.underReview }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Pending Client</p>
                <p class="text-lg font-bold text-orange-600">{{ gridData.kpis.pendingClient }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Total Capacity</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ gridData.kpis.totalCapacityMw }} MW</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight mb-1">Avg Progress</p>
                <p class="text-lg font-bold" :class="gridData.kpis.avgProgress >= 50 ? 'text-green-600' : 'text-amber-600'">{{ gridData.kpis.avgProgress }}%</p>
            </div>
        </div>

        <!-- Active Filter Chips -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Filters:</span>

            <template v-if="hasFilters">
                <span v-for="(val, key) in gridData.appliedFilters" :key="key"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                    <span class="text-[10px] uppercase text-purple-500">{{ filterLabels[key] || key }}:</span>
                    {{ val }}
                    <button @click="removeFilter(key)" class="ml-0.5 hover:text-red-600 transition-colors" title="Remove filter">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </span>
                <button @click="clearAllFilters" class="text-xs text-red-500 hover:text-red-700 font-medium ml-2">
                    Clear All
                </button>
            </template>

            <!-- Add filter -->
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
                    :options="addFilterValueOptions"
                    placeholder="Select value…"
                    size="sm"
                    class="w-48"
                />
            </div>
        </div>

        <!-- ── Stage Funnel ── -->
        <div class="mb-2">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Study Pipeline</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">5-stage workflow funnel — click a stage to filter</p>
        </div>
        <div class="mb-8">
            <Card title="Stage Funnel" noPadding>
                <div class="p-4">
                    <div class="overflow-x-auto -mx-4 px-4">
                        <div class="min-w-max">
                            <div class="flex items-end gap-2 justify-center">
                                <div v-for="(stage, i) in gridData.stageFunnel" :key="stage.stage"
                                     class="flex flex-col items-center cursor-pointer group"
                                     @click="addFilter('stage', stageKeys[i])">
                                    <!-- Bar -->
                                    <div class="relative mb-2">
                                        <div class="w-16 sm:w-20 rounded-t-lg transition-all duration-300 group-hover:opacity-80"
                                             :style="{ height: funnelHeight(stage.count) + 'px', backgroundColor: stage.color, minHeight: '24px' }">
                                            <span class="absolute inset-0 flex items-center justify-center text-white font-bold text-sm">
                                                {{ stage.count }}
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Label -->
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 text-center leading-tight w-16 sm:w-20 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                                        {{ stage.stage }}
                                    </p>
                                    <!-- Arrow connector -->
                                    <div v-if="i < gridData.stageFunnel.length - 1"
                                         class="hidden sm:block absolute" style="right: -8px; top: 50%;">
                                    </div>
                                </div>
                            </div>
                            <!-- Flow arrows between stages -->
                            <div class="flex items-center justify-center gap-0 mt-2">
                                <template v-for="(stage, i) in gridData.stageFunnel" :key="'arrow-' + i">
                                    <div class="w-16 sm:w-20 h-1 rounded" :style="{ backgroundColor: stage.color }"></div>
                                    <svg v-if="i < gridData.stageFunnel.length - 1" class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <!-- ── Drillable Charts ── -->
        <div class="mb-2">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Breakdown Charts</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Click any chart segment to add a filter</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Technology Pie -->
            <Card title="Studies by Technology">
                <Pie3DChart
                    :data="(gridData.techPie || []).map(d => ({ name: d.name, value: d.value }))"
                    height="300px"
                    @chart-click="(p) => addFilter('technology', p.name)"
                />
            </Card>

            <!-- Study Type Breakdown -->
            <Card title="Report vs Inception">
                <Pie3DChart
                    :data="(gridData.typeBreakdown || []).map(d => ({ name: d.name, value: d.value }))"
                    height="300px"
                    @chart-click="(p) => addFilter('study_type', p.name.toLowerCase())"
                />
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Project Area Breakdown -->
            <Card title="Studies by Project Area">
                <BarChart
                    :data="areaBarData"
                    xField="label"
                    yField="value"
                    seriesName="Studies"
                    height="300px"
                    horizontal
                    @chart-click="(p) => addFilter('project_area', p.name || extractBarLabel(p))"
                />
            </Card>

            <!-- Progress Histogram -->
            <Card title="Progress Distribution">
                <BarChart
                    :data="progressBarData"
                    xField="label"
                    yField="value"
                    seriesName="Studies"
                    :colors="pickCategorical(progressBarData.length)"
                    height="300px"
                />
            </Card>
        </div>

        <!-- ── Study Table ── -->
        <div class="mb-6">
            <div class="mb-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">All Studies</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ gridData.totalCount }} studies — click a row to view linked project
                </p>
            </div>

            <!-- Table controls -->
            <div class="flex items-center gap-3 mb-3 no-print">
                <Input
                    v-model="tableSearch"
                    type="text"
                    placeholder="Search studies…"
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
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Type</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Technology</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Developer</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">MW</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Area</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Progress</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Stage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="s in paginatedStudies" :key="s.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                                :class="s.project_id ? 'cursor-pointer' : ''"
                                @click="s.project_id && goToProject(s)">
                                <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ s.study_code }}</td>
                                <td class="px-4 py-2 text-gray-900 dark:text-white font-medium max-w-xs truncate">{{ s.name }}</td>
                                <td class="px-4 py-2 text-center hidden sm:table-cell">
                                    <Badge variant="filled" :color="s.study_type === 'report' ? 'blue' : 'purple'" :label="s.study_type" />
                                </td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300 text-xs hidden md:table-cell">{{ s.technology || '—' }}</td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300 text-xs hidden lg:table-cell max-w-[160px] truncate">{{ s.developer || '—' }}</td>
                                <td class="px-4 py-2 text-right text-gray-900 dark:text-white">{{ s.capacity_mw?.toLocaleString() || '—' }}</td>
                                <td class="px-4 py-2 text-center text-xs text-gray-500 hidden md:table-cell">{{ s.project_area || '—' }}</td>
                                <td class="px-4 py-2 text-center text-gray-900 dark:text-white">{{ s.progress_pct ?? 0 }}%</td>
                                <td class="px-4 py-2 text-center">
                                    <!-- Stage pipeline visual -->
                                    <div class="flex items-center gap-0.5 justify-center">
                                        <span v-for="(active, idx) in stageDotsForStudy(s)" :key="idx"
                                              class="w-2.5 h-2.5 rounded-full transition-colors"
                                              :class="active ? stageColorClass(idx) : 'bg-gray-200 dark:bg-gray-600'"
                                              :title="stageLabels[idx]">
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ s.current_stage || '—' }}</p>
                                </td>
                            </tr>
                            <tr v-if="filteredStudies.length === 0">
                                <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                    <p class="text-lg mb-1">No studies match these filters</p>
                                    <p class="text-sm">Try removing some filters or <button @click="clearAllFilters" class="text-purple-600 hover:underline">clear all</button></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500">
                        Showing {{ (currentPage - 1) * pageSize + 1 }}–{{ Math.min(currentPage * pageSize, filteredStudies.length) }} of {{ filteredStudies.length }}
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
import { ref, computed, watch, defineAsyncComponent, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Select from '@/Components/UI/Select.vue';
import Input from '@/Components/UI/Input.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import { CATEGORICAL, RAG } from '@/Composables/useChartPalette';
import { useChartPalettes } from '@/Composables/useChartPalettes';
import { useDirectorateStore } from '@/stores/useDirectorateStore';

const Pie3DChart = defineAsyncComponent(() => import('@/Components/Charts/Pie3DChart.vue'));

const { pickCategorical } = useChartPalettes();
const directorateStore = useDirectorateStore();

const props = defineProps({
    gridData: { type: Object, required: true },
    directorate: { type: Object, required: true },
    directorates: { type: Array, default: () => [] },
});

// Auto-enter directorate mode if not already active (e.g. direct URL navigation)
onMounted(() => {
    if (!directorateStore.activeDirectorate) {
        directorateStore.enterDirectorate(props.directorate);
    }
});

const headerMetrics = computed(() => {
    const avgColor = (props.gridData.kpis?.avgProgress ?? 0) >= 50 ? RAG.green : RAG.amber;
    return [
        { label: 'Studies', value: props.gridData.kpis?.totalStudies ?? 0 },
        { label: 'Approved', value: props.gridData.kpis?.approvedCount ?? 0, color: RAG.green },
        { label: 'Total MW', value: props.gridData.kpis?.totalCapacityMw ?? 0, color: CATEGORICAL[0] },
        { label: 'Avg Progress', value: `${props.gridData.kpis?.avgProgress ?? 0}%`, color: avgColor },
    ];
});

// ── Constants ──

const stageKeys = ['received', 'not_started', 'under_review', 'pending_client', 'revisions', 'approved'];
const stageLabels = ['Received', 'Not Started', 'Under Review', 'Pending Client', 'Revisions', 'Approved'];
const filterLabels = {
    study_type: 'Type',
    technology: 'Technology',
    project_area: 'Area',
    stage: 'Stage',
};

// ── Filter management ──

const hasFilters = computed(() => Object.keys(props.gridData.appliedFilters || {}).length > 0);

const availableDimensionsOptions = computed(() => {
    const applied = props.gridData.appliedFilters || {};
    return Object.entries(filterLabels)
        .filter(([key]) => !applied[key])
        .map(([value, label]) => ({ value, label }));
});

const addFilterDim = ref('');
const addFilterVal = ref('');

const addFilterValueOptions = computed(() => {
    if (!addFilterDim.value) return [];
    return (props.gridData.filterOptions?.[addFilterDim.value] || []).map(opt => ({
        value: opt,
        label: typeof opt === 'string' ? (opt.charAt(0).toUpperCase() + opt.slice(1)) : opt,
    }));
});

watch(addFilterDim, () => { addFilterVal.value = ''; });

watch(addFilterVal, (newVal) => {
    if (newVal) {
        addFilter(addFilterDim.value, newVal);
        addFilterDim.value = '';
        addFilterVal.value = '';
    }
});

function addFilter(key, value) {
    if (!key || !value || value === 'Unknown') return;
    const current = { ...(props.gridData.appliedFilters || {}) };
    current[key] = value;
    router.get('/pp/dashboard/grid-studies', current);
}

function removeFilter(key) {
    const current = { ...(props.gridData.appliedFilters || {}) };
    delete current[key];
    if (Object.keys(current).length === 0) {
        router.get('/pp/dashboard/grid-studies');
    } else {
        router.get('/pp/dashboard/grid-studies', current);
    }
}

function clearAllFilters() {
    router.get('/pp/dashboard/grid-studies');
}

// ── Chart data transforms ──

const areaBarData = computed(() => {
    return (props.gridData.areaBreakdown || []).map(item => ({
        label: item.name,
        value: item.value,
    }));
});

const progressBarData = computed(() => {
    return (props.gridData.progressHistogram || []).map(item => ({
        label: item.name,
        value: item.value,
    }));
});

// ── Stage helpers ──

function stageDotsForStudy(s) {
    return [
        s.stage_received,
        s.stage_not_started,
        s.stage_under_review,
        s.stage_pending_client,
        s.stage_revisions,
        s.stage_approved,
    ];
}

function stageColorClass(idx) {
    const colors = ['bg-blue-500', 'bg-gray-400', 'bg-amber-500', 'bg-orange-500', 'bg-red-400', 'bg-green-500'];
    return colors[idx] || 'bg-gray-400';
}

function funnelHeight(count) {
    const max = Math.max(...(props.gridData.stageFunnel || []).map(s => s.count), 1);
    return Math.max(24, (count / max) * 160);
}

// ── Table management ──

const tableSearch = ref('');
const sortField = ref('capacity_mw');
const sortOptions = [
    { value: 'capacity_mw', label: 'Sort: MW (high→low)' },
    { value: 'name', label: 'Sort: Name' },
    { value: 'progress_pct', label: 'Sort: Progress' },
    { value: 'study_type', label: 'Sort: Type' },
];
const currentPage = ref(1);
const pageSize = 20;

const filteredStudies = computed(() => {
    let list = [...(props.gridData.studies || [])];

    if (tableSearch.value) {
        const q = tableSearch.value.toLowerCase();
        list = list.filter(s =>
            (s.name || '').toLowerCase().includes(q) ||
            (s.study_code || '').toLowerCase().includes(q) ||
            (s.developer || '').toLowerCase().includes(q) ||
            (s.technology || '').toLowerCase().includes(q) ||
            (s.project_area || '').toLowerCase().includes(q)
        );
    }

    list.sort((a, b) => {
        if (sortField.value === 'name') return (a.name || '').localeCompare(b.name || '');
        if (sortField.value === 'progress_pct') return (b.progress_pct || 0) - (a.progress_pct || 0);
        if (sortField.value === 'study_type') return (a.study_type || '').localeCompare(b.study_type || '');
        return (b.capacity_mw || 0) - (a.capacity_mw || 0);
    });

    return list;
});

const totalPages = computed(() => Math.ceil(filteredStudies.value.length / pageSize));

const paginatedStudies = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredStudies.value.slice(start, start + pageSize);
});

watch(tableSearch, () => { currentPage.value = 1; });
watch(sortField, () => { currentPage.value = 1; });

function goToProject(study) {
    if (!study.project_id) return;
    router.get(`/pp/dashboard/projects/${study.project_id}`);
}

function extractBarLabel(params) {
    return params.name || params.data?.label || '';
}
</script>
