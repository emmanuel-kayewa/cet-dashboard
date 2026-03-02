<template>
    <AppLayout :directorates="directorates">
        <template #title>{{ directorate.name }}</template>

        <!-- Breadcrumb -->
        <nav class="text-sm mb-6 no-print">
            <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <li><Link href="/dashboard" class="hover:text-zesco-600">Dashboard</Link></li>
                <li>/</li>
                <li class="font-medium text-gray-900 dark:text-white">{{ directorate.code }}</li>
            </ol>
        </nav>

        <!-- Directorate Header -->
        <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-zesco-50 to-blue-50 dark:from-gray-800 dark:to-gray-800 border border-zesco-100 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                         :style="{ backgroundColor: directorate.color }">
                        {{ directorate.code?.charAt(0) }}
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ directorate.name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ directorate.code }} &middot; {{ directorate.head_name || 'Head not assigned' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-center sm:ml-auto flex-shrink-0">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ kpiSummary.total || 0 }}</p>
                        <p class="text-xs text-gray-500">KPIs</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" :class="kpiSummary.completion_percentage >= 75 ? 'text-green-600' : 'text-amber-600'">
                            {{ kpiSummary.completion_percentage || 0 }}%
                        </p>
                        <p class="text-xs text-gray-500">Completion</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" :class="kpiSummary.high_risk > 2 ? 'text-red-600' : 'text-gray-900 dark:text-white'">
                            {{ kpiSummary.high_risk || 0 }}
                        </p>
                        <p class="text-xs text-gray-500">High Risks</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-end gap-4 mb-6 no-print">
            <DateRangePicker
                v-model:from="filters.from"
                v-model:to="filters.to"
                @apply="applyFilters"
                @clear="clearFilters"
            />
            <Select
                v-model="selectedKpiCategory"
                :options="[{ value: '', label: 'All Categories' }, ...kpiCategories.map(c => ({ value: c, label: c }))]"
                size="md"
                class="w-full sm:!w-48 sm:flex-none"
            />

            <div class="flex items-center gap-2 ml-auto">
                <a :href="`/export/directorate/${directorate.slug}/pdf`" class="btn-secondary text-sm">PDF</a>
                <a :href="`/export/directorate/${directorate.slug}/excel`" class="btn-secondary text-sm">Excel</a>
            </div>
        </div>

        <!-- KPI Cards Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <KpiCard
                v-for="kpi in topKpis"
                :key="kpi.id"
                :title="kpi.name"
                :formattedValue="kpi.formatted_value"
                :change="kpi.change"
                :status="kpi.status"
            />
        </div>

        <!-- KPI Trend & Financial Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-start">
            <Card title="KPI Trend">
                <div class="mb-3">
                    <Select
                        v-model="trendKpiId"
                        :options="kpis"
                        option-value="id"
                        option-label="name"
                        size="md"
                        @update:modelValue="fetchTrend"
                    />
                </div>
                <LineChart
                    :data="trendData"
                    xField="date"
                    yField="value"
                    seriesName="Actual"
                    :forecast="trendForecast"
                    height="280px"
                />
            </Card>

            <Card title="Financial Overview">
                <template #actions>
                    <div v-if="financials.length > perPage" class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ finPage + 1 }}/{{ Math.ceil(financials.length / perPage) }}</span>
                        <button @click="finPage = Math.max(0, finPage - 1)" :disabled="finPage === 0" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button @click="finPage = Math.min(Math.ceil(financials.length / perPage) - 1, finPage + 1)" :disabled="finPage >= Math.ceil(financials.length / perPage) - 1" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </template>
                <div v-if="financials.length > 0">
                    <div class="space-y-3">
                        <div v-for="fin in pagedFinancials" :key="fin.id" class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ fin.category }}</span>
                                <span class="text-xs text-gray-500">{{ fin.period }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <p class="text-gray-400">Budget</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(fin.budget_amount) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400">Actual</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(fin.actual_amount) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400">Variance</p>
                                    <p class="font-semibold" :class="fin.variance >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ fin.variance >= 0 ? '+' : '' }}{{ fin.variance?.toFixed(1) }}%
                                    </p>
                                </div>
                            </div>
                            <!-- Budget utilization bar -->
                            <div class="mt-2 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300"
                                     :class="fin.utilization > 100 ? 'bg-red-500' : fin.utilization > 85 ? 'bg-amber-500' : 'bg-green-500'"
                                     :style="{ width: Math.min(fin.utilization || 0, 100) + '%' }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 text-center py-8">No financial data available.</div>
            </Card>
        </div>

        <!-- Projects & Risks -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-start">
            <Card title="Projects">
                <template #actions>
                    <div v-if="projects.length > perPage" class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ projPage + 1 }}/{{ Math.ceil(projects.length / perPage) }}</span>
                        <button @click="projPage = Math.max(0, projPage - 1)" :disabled="projPage === 0" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button @click="projPage = Math.min(Math.ceil(projects.length / perPage) - 1, projPage + 1)" :disabled="projPage >= Math.ceil(projects.length / perPage) - 1" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </template>
                <div class="space-y-3">
                    <div v-for="project in pagedProjects" :key="project.id" class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ project.name }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  :class="{
                                      'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': project.status === 'on_track',
                                      'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': project.status === 'at_risk',
                                      'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': project.status === 'delayed',
                                      'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': project.status === 'completed',
                                  }">
                                {{ project.status?.replace('_', ' ') }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span>{{ project.completion_percentage }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                <div class="h-full bg-zesco-600 rounded-full" :style="{ width: project.completion_percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    <p v-if="projects.length === 0" class="text-sm text-gray-400 text-center py-4">No projects tracked.</p>
                </div>
            </Card>

            <Card title="Risk Register">
                <template #actions>
                    <div v-if="risks.length > perPage" class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ riskPage + 1 }}/{{ Math.ceil(risks.length / perPage) }}</span>
                        <button @click="riskPage = Math.max(0, riskPage - 1)" :disabled="riskPage === 0" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button @click="riskPage = Math.min(Math.ceil(risks.length / perPage) - 1, riskPage + 1)" :disabled="riskPage >= Math.ceil(risks.length / perPage) - 1" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </template>
                <div class="space-y-3">
                    <div v-for="risk in pagedRisks" :key="risk.id" class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ risk.title }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                  :class="{
                                      'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': risk.risk_level === 'critical',
                                      'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': risk.risk_level === 'high',
                                      'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': risk.risk_level === 'medium',
                                      'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': risk.risk_level === 'low',
                                  }">
                                {{ risk.risk_level }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ risk.description }}</p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                            <span>Impact: {{ risk.impact }}/5</span>
                            <span>Likelihood: {{ risk.likelihood }}/5</span>
                            <span :class="risk.status === 'mitigated' ? 'text-green-600' : ''">{{ risk.status }}</span>
                        </div>
                    </div>
                    <p v-if="risks.length === 0" class="text-sm text-gray-400 text-center py-4">No risks registered.</p>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import KpiCard from '@/Components/Dashboard/KpiCard.vue';
import Card from '@/Components/UI/Card.vue';
import Select from '@/Components/UI/Select.vue';
import DateRangePicker from '@/Components/UI/DateRangePicker.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import { formatCurrency } from '@/Composables/useFormatters';

const props = defineProps({
    directorate: { type: Object, required: true },
    directorates: { type: Array, default: () => [] },
    kpis: { type: Array, default: () => [] },
    kpiSummary: { type: Object, default: () => ({}) },
    financials: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    risks: { type: Array, default: () => [] },
    trend: { type: Object, default: () => ({ data: [], forecast: [] }) },
    filters: { type: Object, default: () => ({ from: '', to: '' }) },
});

const filters = ref({ ...props.filters });
const selectedKpiCategory = ref('');
const trendKpiId = ref(props.kpis[0]?.id || null);
const trendData = ref(props.trend.data || []);
const trendForecast = ref(props.trend.forecast || []);

// Pagination for list cards
const perPage = 3;
const finPage = ref(0);
const projPage = ref(0);
const riskPage = ref(0);

const pagedFinancials = computed(() => props.financials.slice(finPage.value * perPage, (finPage.value + 1) * perPage));
const pagedProjects = computed(() => props.projects.slice(projPage.value * perPage, (projPage.value + 1) * perPage));
const pagedRisks = computed(() => props.risks.slice(riskPage.value * perPage, (riskPage.value + 1) * perPage));

const kpiCategories = computed(() => {
    const cats = new Set(props.kpis.map(k => k.category).filter(Boolean));
    return Array.from(cats);
});

const topKpis = computed(() => {
    let list = props.kpis;
    if (selectedKpiCategory.value) {
        list = list.filter(k => k.category === selectedKpiCategory.value);
    }
    return list.slice(0, 8);
});

async function fetchTrend() {
    if (!trendKpiId.value) return;
    try {
        const params = new URLSearchParams({
            kpi_id: trendKpiId.value,
            directorate_id: props.directorate.id,
            ...Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v)),
        });

        const response = await fetch(`/api/kpi-trend?${params.toString()}`);
        const json = await response.json();
        trendData.value = (json.trend || []).map(p => ({
            date: p.label ?? p.period,
            value: p.value,
        }));
        trendForecast.value = (json.forecast || []).map(p => ({
            date: p.label ?? p.period,
            value: p.value,
        }));
    } catch (e) {
        console.error('Failed to fetch trend data:', e);
    }
}

function applyFilters() {
    router.get(`/dashboard/directorate/${props.directorate.slug}`, {
        from: filters.value.from || undefined,
        to: filters.value.to || undefined,
    }, { preserveState: true });
}

function clearFilters() {
    filters.value = { from: '', to: '' };
    router.get(`/dashboard/directorate/${props.directorate.slug}`);
}
</script>
