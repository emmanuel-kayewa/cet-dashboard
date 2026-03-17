<template>
    <AppLayout
        :directorates="directorates"
        :aiScope="{ type: 'pp_project', directorate_id: directorate.id, pp_project_id: projectData.project.id }"
    >
        <template #title>{{ projectData.project.name }}</template>

        <Breadcrumb :items="breadcrumbItems" />

        <PageHeader stackAt="lg" alignAt="start" class="p-5">
            <template #left>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span :class="ragDot(projectData.project.rag_status)" class="w-4 h-4 rounded-full flex-shrink-0"></span>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ projectData.project.name }}</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ projectData.project.code }}</span>
                        <span>{{ projectData.project.sector }}</span>
                        <span v-if="projectData.project.sub_sector">&middot; {{ projectData.project.sub_sector }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ projectData.project.status }}</span>
                    </div>
                    <p v-if="projectData.project.key_issue_summary" class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                        <strong>Key Issue:</strong> {{ projectData.project.key_issue_summary }}
                    </p>
                </div>
            </template>

            <template #metrics>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ projectData.project.progress_pct ?? 0 }}%</p>
                        <p class="text-xs text-gray-500">Progress</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" :style="{ color: INVESTMENT.committed }">${{ fmtM(projectData.project.cost_usd) }}</p>
                        <p class="text-xs text-gray-500">Cost (USD)</p>
                    </div>
                    <div v-if="projectData.project.capacity_mw">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ projectData.project.capacity_mw }} MW</p>
                        <p class="text-xs text-gray-500">Capacity</p>
                    </div>
                    <div>
                        <p
                            class="text-2xl font-bold"
                            :style="{ color: (projectData.summary.burnRate ?? 0) >= 50 ? RAG.green : RAG.amber }"
                        >
                            {{ projectData.summary.burnRate }}%
                        </p>
                        <p class="text-xs text-gray-500">Burn Rate</p>
                    </div>
                </div>
            </template>
        </PageHeader>

        <!-- Project Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left: Key Information -->
            <div class="lg:col-span-1">
                <Card title="Project Information" noPadding>
                    <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="field in infoFields" :key="field.label" class="px-4 py-2.5 flex items-center justify-between">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ field.label }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white text-right max-w-[60%] truncate">{{ field.value || '—' }}</dd>
                        </div>
                    </dl>
                </Card>
            </div>

            <!-- Right: Progress & Financial Summary -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Progress gauge + Milestone progress -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <Card title="Overall Progress">
                        <div class="flex items-center justify-center py-2">
                            <RadialProgress
                                :value="Number(projectData.project.progress_pct || 0)"
                                :max="100"
                                unit="%"
                                label="Progress"
                                sizeClass="w-full max-w-[180px]"
                            />
                        </div>
                    </Card>
                    <Card title="Milestone Completion">
                        <div class="flex items-center justify-center py-2">
                            <RadialProgress
                                :value="projectData.summary.milestoneProgress"
                                :max="100"
                                unit="%"
                                label="Milestones"
                                :sublabel="`${projectData.summary.completedMilestones} / ${projectData.summary.totalMilestones} completed`"
                                sizeClass="w-full max-w-[180px]"
                            />
                        </div>
                    </Card>
                </div>

                <!-- Financial summary bar -->
                <Card title="Financial Summary">
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-400">Committed (USD)</p>
                            <p class="text-xl font-bold" :style="{ color: INVESTMENT.committed }">${{ fmtM(projectData.summary.totalCommitted) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Paid-to-Date (USD)</p>
                            <p class="text-xl font-bold" :style="{ color: INVESTMENT.paid }">${{ fmtM(projectData.summary.totalPaid) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Burn Rate</p>
                            <p class="text-xl font-bold" :style="{ color: (projectData.summary.burnRate ?? 0) >= 50 ? RAG.green : RAG.amber }">
                                {{ projectData.summary.burnRate }}%
                            </p>
                        </div>
                    </div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                             :style="{ width: Math.min(projectData.summary.burnRate, 100) + '%', backgroundColor: (projectData.summary.burnRate ?? 0) > 80 ? RAG.green : (projectData.summary.burnRate ?? 0) > 50 ? INVESTMENT.committed : RAG.amber }"></div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- ── Tabbed Sections ── -->
        <div class="mb-6">
            <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4 no-print overflow-x-auto flex-nowrap whitespace-nowrap pr-2 pb-1">
                <button v-for="tab in tabs" :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px flex-shrink-0 whitespace-nowrap',
                            activeTab === tab.key
                                ? 'border-zesco-600 text-zesco-600 dark:text-zesco-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]">
                    {{ tab.label }}
                    <span v-if="tab.count !== undefined" class="ml-1.5 text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded-full">{{ tab.count }}</span>
                </button>
            </div>

            <!-- Milestones Tab -->
            <div v-if="activeTab === 'milestones'">
                <Card title="Milestones" noPadding>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Milestone</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="m in projectData.milestones" :key="m.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-2 text-gray-900 dark:text-white font-medium">{{ m.milestone }}</td>
                                    <td class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">{{ m.actual_date || '—' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span :class="milestoneStatusClass(m.status)" class="text-xs px-2 py-0.5 rounded-full">
                                            {{ m.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 max-w-sm truncate">{{ m.notes || '—' }}</td>
                                </tr>
                                <tr v-if="projectData.milestones.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">No milestones recorded</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>

            <!-- Financials Tab -->
            <div v-if="activeTab === 'financials'">
                <Card title="Financial Records" noPadding>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">As Of</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Committed</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Paid</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Currency</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="f in projectData.financials" :key="f.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-2 text-xs text-gray-500 font-mono">{{ f.code }}</td>
                                    <td class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">{{ f.as_of_date || '—' }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900 dark:text-white font-medium">{{ formatAmount(f.committed_amount, f.currency) }}</td>
                                    <td class="px-4 py-2 text-right text-green-600 font-medium">{{ formatAmount(f.paid_to_date, f.currency) }}</td>
                                    <td class="px-4 py-2 text-center text-xs text-gray-500">{{ f.currency }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 max-w-sm truncate">{{ f.notes || '—' }}</td>
                                </tr>
                                <tr v-if="projectData.financials.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No financial records</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>

            <!-- Risks Tab -->
            <div v-if="activeTab === 'risks'">
                <Card title="Risk Register" noPadding>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Risk</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">L</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">I</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">S</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Level</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Mitigation</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="r in projectData.risks" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-2 text-gray-900 dark:text-white font-medium max-w-xs">{{ r.description }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ r.category }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-center text-gray-900 dark:text-white">{{ r.likelihood }}</td>
                                    <td class="px-4 py-2 text-center text-gray-900 dark:text-white">{{ r.impact }}</td>
                                    <td class="px-4 py-2 text-center font-bold text-gray-900 dark:text-white">{{ r.severity }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span :class="ragDot(r.level)" class="inline-block w-3 h-3 rounded-full"></span>
                                    </td>
                                    <td class="px-4 py-2 text-center text-xs"
                                        :class="r.status === 'Open' ? 'text-red-600' : r.status === 'Mitigating' ? 'text-amber-600' : 'text-green-600'">
                                        {{ r.status }}
                                    </td>
                                    <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 max-w-sm">{{ r.mitigation || '—' }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">{{ r.owner || '—' }}</td>
                                </tr>
                                <tr v-if="projectData.risks.length === 0">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">No risks registered</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>

            <!-- Safeguards Tab -->
            <div v-if="activeTab === 'safeguards'">
                <Card title="Safeguards" noPadding>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Scope</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WL Recv</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WL Clear</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WL Pend</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sv Recv</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sv Clear</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sv Pend</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">PAPs</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Comp (ZMW)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="s in projectData.safeguards" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-2 text-gray-900 dark:text-white font-medium">{{ s.scope }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900 dark:text-white">{{ s.wayleave_received ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right text-green-600 font-medium">{{ s.wayleave_cleared ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right text-amber-600">{{ s.wayleave_pending ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900 dark:text-white">{{ s.survey_received ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right text-green-600 font-medium">{{ s.survey_cleared ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right text-amber-600">{{ s.survey_pending ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900 dark:text-white">{{ s.paps ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900 dark:text-white">K{{ fmtM(s.comp_paid_zmw) }}</td>
                                </tr>
                                <tr v-if="projectData.safeguards.length === 0">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">No safeguard records</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>

            <!-- Grid Impact Studies Tab -->
            <div v-if="activeTab === 'gridStudies'">
                <Card title="Grid Impact Studies" noPadding>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Study</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Technology</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">MW</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Progress</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pipeline Stage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="gs in projectData.gridStudies" :key="gs.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3">
                                        <p class="text-gray-900 dark:text-white font-medium">{{ gs.name }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ gs.study_code }}</p>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <Badge variant="filled" :color="gs.study_type === 'report' ? 'blue' : 'purple'" :label="gs.study_type" />
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300 text-xs hidden md:table-cell">{{ gs.technology || '—' }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900 dark:text-white font-medium">{{ gs.capacity_mw?.toLocaleString() || '—' }}</td>
                                    <td class="px-4 py-2 text-center text-gray-900 dark:text-white">{{ gs.progress_pct ?? 0 }}%</td>
                                    <td class="px-4 py-3">
                                        <!-- 6-stage visual pipeline -->
                                        <div class="flex items-center gap-0.5 justify-center">
                                            <div v-for="(stage, idx) in gridStageList" :key="idx"
                                                 class="flex flex-col items-center">
                                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[8px] font-bold border-2 transition-colors"
                                                     :class="gs[stage.field]
                                                         ? 'border-transparent text-white ' + stage.activeClass
                                                         : 'border-gray-300 dark:border-gray-600 text-gray-300 dark:text-gray-600 bg-transparent'"
                                                     :title="stage.label">
                                                    {{ gs[stage.field] ? '✓' : idx + 1 }}
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-0.5 text-center">{{ gs.current_stage || '—' }}</p>
                                    </td>
                                </tr>
                                <tr v-if="!projectData.gridStudies || projectData.gridStudies.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No grid impact studies</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Notes Section -->
        <div v-if="projectData.project.notes" class="mb-6">
            <Card title="Notes">
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ projectData.project.notes }}</p>
            </Card>
        </div>

        <!-- Back Button -->
        <div class="no-print">
            <Link :href="backUrl" class="inline-flex items-center gap-2 text-sm font-medium text-zesco-600 hover:text-zesco-700 dark:text-zesco-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Explorer
            </Link>
        </div>
    </AppLayout>
</template>
<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import RadialProgress from '@/Components/Charts/RadialProgress.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import { INVESTMENT, RAG } from '@/Composables/useChartPalette';

const props = defineProps({
    projectData: { type: Object, required: true },
    directorate: { type: Object, required: true },
    directorates: { type: Array, default: () => [] },
    backFilters: { type: Object, default: () => ({}) },
});

// ── Breadcrumb items ──

const breadcrumbItems = computed(() => [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'PP Portfolio', href: '/pp/dashboard' },
    { label: 'Explorer', href: backUrl.value },
    { label: props.projectData.project.name, current: true, truncate: true }
]);

// ── Tabs ──

const activeTab = ref('milestones');

const tabs = computed(() => {
    const t = [
        { key: 'milestones', label: 'Milestones', count: props.projectData.milestones.length },
        { key: 'financials', label: 'Financials', count: props.projectData.financials.length },
        { key: 'risks', label: 'Risks', count: props.projectData.risks.length },
        { key: 'safeguards', label: 'Safeguards', count: props.projectData.safeguards.length },
    ];
    if (props.projectData.gridStudies && props.projectData.gridStudies.length > 0) {
        t.push({ key: 'gridStudies', label: 'Grid Studies', count: props.projectData.gridStudies.length });
    }
    return t;
});

// ── Info fields ──

const gridStageList = [
    { field: 'stage_received', label: 'Received', activeClass: 'bg-blue-500' },
    { field: 'stage_not_started', label: 'Not Started', activeClass: 'bg-gray-500' },
    { field: 'stage_under_review', label: 'Under Review', activeClass: 'bg-amber-500' },
    { field: 'stage_pending_client', label: 'Pending Client', activeClass: 'bg-orange-500' },
    { field: 'stage_revisions', label: 'Revisions', activeClass: 'bg-red-400' },
    { field: 'stage_approved', label: 'Approved', activeClass: 'bg-green-500' },
];

const infoFields = computed(() => {
    const p = props.projectData.project;
    return [
        { label: 'Sector', value: p.sector },
        { label: 'Sub-Sector', value: p.sub_sector },
        { label: 'Programme', value: p.programme },
        { label: 'Province', value: p.province },
        { label: 'District', value: p.district },
        { label: 'Contractor', value: p.contractor },
        { label: 'Developer', value: p.developer },
        { label: 'Funder', value: p.funder },
        { label: 'Funding Type', value: p.funding_type },
        { label: 'Cost (ZMW)', value: p.cost_zmw ? `K${fmtM(p.cost_zmw)}` : null },
        { label: 'COD Planned', value: p.cod_planned },
        { label: 'Last Updated', value: p.last_update_date },
    ].filter(f => f.value);
});

// ── Back navigation ──

const backUrl = computed(() => {
    const params = new URLSearchParams(props.backFilters || {});
    const qs = params.toString();
    return '/pp/dashboard/explore' + (qs ? '?' + qs : '');
});

// ── Helpers ──

function fmtM(val) {
    const n = Number(val) || 0;
    if (n >= 1e9) return (n / 1e9).toFixed(2) + 'B';
    if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K';
    return n.toLocaleString();
}

function ragDot(rag) {
    const r = (rag || '').toLowerCase();
    if (r === 'green' || r === 'low') return 'bg-green-500';
    if (r === 'amber' || r === 'medium') return 'bg-amber-500';
    if (r === 'red' || r === 'high' || r === 'critical') return 'bg-red-500';
    return 'bg-gray-400';
}

function milestoneStatusClass(status) {
    if (status === 'Completed') return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
    if (status === 'In Progress') return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
    return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
}

function formatAmount(val, currency) {
    const n = Number(val) || 0;
    const prefix = currency === 'ZMW' ? 'K' : '$';
    return prefix + fmtM(n);
}
</script>
