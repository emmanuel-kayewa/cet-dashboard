<template>
    <AppLayout :directorates="directorates">
        <template #title>Directorate Comparison</template>

        <nav class="text-sm mb-6 no-print">
            <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <li><Link href="/dashboard" class="hover:text-zesco-600">Dashboard</Link></li>
                <li>/</li>
                <li class="font-medium text-gray-900 dark:text-white">Comparison</li>
            </ol>
        </nav>

        <!-- Metric Selector -->
        <div class="flex flex-wrap items-end gap-4 mb-6 no-print">
            <Select
                v-model="selectedMetric"
                :options="[
                    { value: 'completion', label: 'Completion %' },
                    { value: 'revenue', label: 'Revenue' },
                    { value: 'budget_utilization', label: 'Budget Utilization' },
                    { value: 'risk_score', label: 'Risk Score' },
                    { value: 'project_count', label: 'Project Count' },
                ]"
                size="md"
                class="w-56"
            />
        </div>

        <!-- Bar Chart Comparison -->
        <Card title="Cross-Directorate Comparison" class="mb-6">
            <BarChart
                :data="comparisonData"
                :xField="'code'"
                :yField="selectedMetric"
                :seriesName="metricLabel"
                :horizontal="true"
                height="400px"
            />
        </Card>

        <!-- Comparison Table -->
        <Card title="Detailed Comparison">
            <div class="overflow-x-auto -mx-6">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Directorate</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Compl.</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Revenue</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Budget</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Projects</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Risks</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in comparison" :key="d.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20 transition">
                            <td class="py-3 px-4">
                                <Link :href="`/dashboard/directorate/${d.slug}`" class="font-medium text-zesco-700 dark:text-zesco-400 hover:underline truncate block max-w-[200px]" :title="`${d.code} — ${d.name}`">
                                    {{ d.code }} — {{ d.name }}
                                </Link>
                            </td>
                            <td class="text-right py-3 px-4 font-medium" :class="d.completion >= 75 ? 'text-green-600' : d.completion >= 50 ? 'text-amber-600' : 'text-red-600'">
                                {{ d.completion }}%
                            </td>
                            <td class="text-right py-3 px-4 hidden sm:table-cell">{{ formatCurrency(d.revenue) }}</td>
                            <td class="text-right py-3 px-4 hidden md:table-cell">
                                <span :class="d.budget_utilization > 100 ? 'text-red-600' : ''">{{ d.budget_utilization }}%</span>
                            </td>
                            <td class="text-right py-3 px-4 hidden lg:table-cell">{{ d.project_count }}</td>
                            <td class="text-right py-3 px-4">
                                <span :class="d.high_risk_count > 2 ? 'text-red-600 font-semibold' : ''">
                                    {{ d.risk_count }} ({{ d.high_risk_count }} high)
                                </span>
                            </td>
                            <td class="text-center py-3 px-4">
                                <span class="inline-block w-2.5 h-2.5 rounded-full"
                                      :class="{
                                          'bg-green-500': d.status === 'healthy',
                                          'bg-amber-500': d.status === 'warning',
                                          'bg-red-500': d.status === 'critical',
                                      }"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Select from '@/Components/UI/Select.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import { formatCurrency } from '@/Composables/useFormatters';

const props = defineProps({
    comparison: { type: Array, default: () => [] },
    directorates: { type: Array, default: () => [] },
});

const selectedMetric = ref('completion');

const metricLabel = computed(() => {
    const labels = {
        completion: 'Completion %',
        revenue: 'Revenue (ZMW)',
        budget_utilization: 'Budget Utilization %',
        risk_score: 'Risk Score',
        project_count: 'Projects',
    };
    return labels[selectedMetric.value] || selectedMetric.value;
});

const comparisonData = computed(() => {
    return props.comparison.map(d => ({
        code: d.code,
        [selectedMetric.value]: d[selectedMetric.value] ?? d.completion,
    }));
});
</script>
