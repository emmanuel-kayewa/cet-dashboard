<template>
    <AppLayout>
        <template #title>PP Directorate — Portfolio Management</template>

        <Breadcrumb :items="breadcrumbItems" />

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :href="tab.href"
                    :class="[
                        'whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium transition',
                        currentTab === tab.key
                            ? 'border-zesco-600 text-zesco-600 dark:text-zesco-400 dark:border-zesco-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                    ]"
                >
                    {{ tab.label }}
                </Link>
            </nav>
        </div>

        <!-- Tab Content -->
        <ProjectsTab       v-if="currentTab === 'projects'"          :projects="projects"          :filters="filters" />
        <MilestonesTab     v-else-if="currentTab === 'milestones'"   :milestones="milestones"      :ppProjects="ppProjects" />
        <FinancialsTab     v-else-if="currentTab === 'financials'"   :financials="financials"      :ppProjects="ppProjects" :filters="filters" />
        <RisksTab          v-else-if="currentTab === 'risks'"        :risks="risks"                :ppProjects="ppProjects" />
        <SafeguardsTab     v-else-if="currentTab === 'safeguards'"   :safeguards="safeguards"      :ppProjects="ppProjects" />
        <ProgrammeOutputsTab v-else-if="currentTab === 'programme-outputs'" :programmeOutputs="programmeOutputs" />
        <GridImpactStudiesTab v-else-if="currentTab === 'grid-impact-studies'" :gridImpactStudies="gridImpactStudies" :ppProjects="ppProjects" />

        <!-- Default / fallback -->
        <div v-else class="text-center py-12 text-gray-400 text-sm">
            Select a tab above to manage PP data.
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import ProjectsTab from '@/Pages/Pp/Tabs/ProjectsTab.vue';
import MilestonesTab from '@/Pages/Pp/Tabs/MilestonesTab.vue';
import FinancialsTab from '@/Pages/Pp/Tabs/FinancialsTab.vue';
import RisksTab from '@/Pages/Pp/Tabs/RisksTab.vue';
import SafeguardsTab from '@/Pages/Pp/Tabs/SafeguardsTab.vue';
import ProgrammeOutputsTab from '@/Pages/Pp/Tabs/ProgrammeOutputsTab.vue';
import GridImpactStudiesTab from '@/Pages/Pp/Tabs/GridImpactStudiesTab.vue';

const props = defineProps({
    activeTab:        { type: String, default: 'projects' },
    // Projects tab
    projects:         { type: Object, default: () => ({ data: [], links: [] }) },
    filters:          { type: Object, default: () => ({}) },
    // Milestones tab
    milestones:       { type: Object, default: () => ({ data: [], links: [] }) },
    ppProjects:       { type: Array, default: () => [] },
    // Financials tab
    financials:       { type: Object, default: () => ({ data: [], links: [] }) },
    // Risks tab
    risks:            { type: Object, default: () => ({ data: [], links: [] }) },
    // Safeguards tab
    safeguards:       { type: Object, default: () => ({ data: [], links: [] }) },
    // Programme Outputs tab
    programmeOutputs: { type: Object, default: () => ({ data: [], links: [] }) },
    // Grid Impact Studies tab
    gridImpactStudies: { type: Object, default: () => ({ data: [], links: [] }) },
});

const currentTab = computed(() => props.activeTab);

const tabs = [
    { key: 'projects',          label: 'Projects',          href: '/pp/projects' },
    { key: 'milestones',        label: 'Milestones',        href: '/pp/milestones' },
    { key: 'financials',        label: 'Financials',        href: '/pp/financials' },
    { key: 'risks',             label: 'Risks',             href: '/pp/risks' },
    { key: 'safeguards',        label: 'Safeguards',        href: '/pp/safeguards' },
    { key: 'programme-outputs', label: 'Programme Outputs', href: '/pp/programme-outputs' },
    { key: 'grid-impact-studies', label: 'Grid Studies', href: '/pp/grid-impact-studies' },
];

const breadcrumbItems = computed(() => [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'PP Directorate', href: '/dashboard/directorate/pp' },
    { label: currentTab.value.replace('-', ' '), current: true }
]);
</script>
