<template>
    <AppLayout>
        <template #title>New Weekly Report</template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6">
                <!-- Header -->
                <div class="flex items-center gap-4">
                    <Link href="/pp/weekly-reports" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">New Weekly Brief Report</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Created by: <span class="font-medium text-gray-700 dark:text-gray-300">{{ auth?.name }}</span></p>
                    </div>
                </div>

                <form @submit.prevent="submitReport" class="space-y-6">
                    <!-- Report Meta -->
                    <Card title="Report Details">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <Input v-model="form.week_number" type="number" label="Week Number" :min="1" :max="53" required :error="form.errors.week_number" />
                            <Input v-model="form.year" type="number" label="Year" :min="2020" :max="2099" required :error="form.errors.year" />
                            <Input v-model="form.report_date" type="date" label="Report Date" required :error="form.errors.report_date" />
                        </div>
                    </Card>

                    <!-- Sections -->
                    <div v-for="(section, si) in form.sections" :key="si" class="space-y-0">
                        <Card>
                            <template #title>
                                <div class="flex items-center gap-3 w-full min-w-0">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-zesco-100 dark:bg-zesco-900/30 text-zesco-700 dark:text-zesco-400 text-sm font-bold flex-shrink-0">
                                        {{ si + 1 }}
                                    </span>
                                    <Input
                                        v-model="section.title"
                                        size="sm"
                                        :placeholder="`Section ${si + 1} Title`"
                                        class="flex-1 min-w-0"
                                    />
                                </div>
                            </template>
                            <template #actions>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="triggerUpload(si)" :disabled="uploading" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 border border-emerald-300 dark:border-emerald-700 transition" title="Upload CSV or Excel">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        {{ uploading ? 'Parsing...' : 'Upload' }}
                                    </button>
                                    <input
                                        :ref="el => fileInputRefs[si] = el"
                                        type="file"
                                        accept=".csv,.xlsx,.xls"
                                        class="hidden"
                                        @change="handleFileUpload($event, si)"
                                    />
                                    <Badge :label="sectionTypeLabel(section.section_type)" variant="filled-dot" color="blue" />
                                </div>
                            </template>

                            <!-- Project Entries (sections 1-3) -->
                            <template v-if="section.section_type !== 'net_metering'">
                                <p v-if="uploadError && si === form.sections.indexOf(section)" class="text-red-500 text-xs mb-2">{{ uploadError }}</p>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase w-8">#</th>
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Name of Project</th>
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Location</th>
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Developer</th>
                                                <th class="text-right py-2 px-2 text-xs font-semibold text-gray-500 uppercase" v-if="section.section_type !== 'transmission_projects'">Size (MW)</th>
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Project Type</th>
                                                <th v-if="section.section_type !== 'completed_solar'" class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Est. Completion</th>
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Notes</th>
                                                <th class="w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(entry, ei) in section.project_entries" :key="ei" class="border-b border-gray-100 dark:border-gray-700/50">
                                                <td class="py-1.5 px-2 text-gray-400 text-xs">{{ ei + 1 }}</td>
                                                <td class="py-1.5 px-2">
                                                    <Input v-model="entry.name" size="sm" placeholder="Project name" class="min-w-[180px]" :error="form.errors[`sections.${si}.project_entries.${ei}.name`]" />
                                                </td>
                                                <td class="py-1.5 px-2">
                                                    <Input v-model="entry.location" size="sm" placeholder="Location" class="min-w-[120px]" />
                                                </td>
                                                <td class="py-1.5 px-2">
                                                    <Input v-model="entry.developer" size="sm" placeholder="Developer" class="min-w-[120px]" />
                                                </td>
                                                <td class="py-1.5 px-2" v-if="section.section_type !== 'transmission_projects'">
                                                    <Input v-model="entry.size_mw" type="number" size="sm" placeholder="0.000" class="min-w-[80px]" />
                                                </td>
                                                <td class="py-1.5 px-2">
                                                    <Select v-model="entry.project_type" :options="projectTypeOptions" placeholder="Select type" size="sm" class="min-w-[140px]" />
                                                </td>
                                                <td v-if="section.section_type !== 'completed_solar'" class="py-1.5 px-2">
                                                    <Input v-model="entry.est_completion" size="sm" placeholder="e.g. July 2026" class="min-w-[140px]" />
                                                </td>
                                                <td class="py-1.5 px-2">
                                                    <Input v-model="entry.notes" size="sm" placeholder="Notes" class="min-w-[120px]" />
                                                </td>
                                                <td class="py-1.5 px-2">
                                                    <button type="button" @click="removeProjectEntry(si, ei)" class="p-1 text-red-400 hover:text-red-600 transition" title="Remove row">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr v-if="section.section_type !== 'transmission_projects'" class="border-t-2 border-gray-300 dark:border-gray-600">
                                                <td :colspan="section.section_type === 'completed_solar' ? 4 : 4" class="py-2 px-2 text-sm font-bold text-gray-900 dark:text-white text-right">
                                                    TOTAL PROJECTS: {{ section.project_entries.length }}
                                                </td>
                                                <td class="py-2 px-2 text-sm font-bold text-gray-900 dark:text-white text-right">
                                                    {{ totalMw(section.project_entries) }}
                                                </td>
                                                <td :colspan="section.section_type === 'completed_solar' ? 3 : 4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <button type="button" @click="addProjectEntry(si)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-zesco-600 dark:text-zesco-400 hover:bg-zesco-50 dark:hover:bg-zesco-900/20 border border-dashed border-zesco-300 dark:border-zesco-700 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Add Project
                                    </button>
                                </div>
                            </template>

                            <!-- Net Metering Entries (section 4) -->
                            <template v-else>
                                <p v-if="uploadError && section.section_type === 'net_metering'" class="text-red-500 text-xs mb-2">{{ uploadError }}</p>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase w-8">#</th>
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Key Initiative</th>
                                                <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                                <th class="w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(entry, ei) in section.net_metering_entries" :key="ei" class="border-b border-gray-100 dark:border-gray-700/50">
                                                <td class="py-1.5 px-2 text-gray-400 text-xs">{{ ei + 1 }}</td>
                                                <td class="py-1.5 px-2">
                                                    <Input v-model="entry.key_initiative" size="sm" placeholder="Key Initiative" class="min-w-[240px]" :error="form.errors[`sections.${si}.net_metering_entries.${ei}.key_initiative`]" />
                                                </td>
                                                <td class="py-1.5 px-2">
                                                    <Input v-model="entry.status_value" size="sm" placeholder="Status value" class="min-w-[180px]" :error="form.errors[`sections.${si}.net_metering_entries.${ei}.status_value`]" />
                                                </td>
                                                <td class="py-1.5 px-2">
                                                    <button type="button" @click="removeNetMeteringEntry(si, ei)" class="p-1 text-red-400 hover:text-red-600 transition" title="Remove row">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <button type="button" @click="addNetMeteringEntry(si)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-zesco-600 dark:text-zesco-400 hover:bg-zesco-50 dark:hover:bg-zesco-900/20 border border-dashed border-zesco-300 dark:border-zesco-700 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Add Entry
                                    </button>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <!-- Notes / Comments -->
                    <Card title="Notes & Comments">
                        <div>
                            <textarea
                                v-model="form.notes"
                                rows="4"
                                class="block w-full text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-[var(--palette-accent)] focus:border-[var(--palette-accent)] dark:bg-gray-700 dark:border-gray-600 dark:text-white p-3"
                                placeholder="Additional notes, phased completion dates, or any other relevant information..."
                            ></textarea>
                            <p v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</p>
                        </div>
                    </Card>

                    <!-- Submit -->
                    <div class="flex items-center gap-3">
                        <Button type="submit" variant="primary" size="md" :disabled="form.processing" class="min-w-[160px]">
                            {{ form.processing ? 'Saving...' : 'Create Report' }}
                        </Button>
                        <Link href="/pp/weekly-reports" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Cancel
                        </Link>
                    </div>
                </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Select from '@/Components/UI/Select.vue';
import { useReportUpload } from '@/Composables/useReportUpload';

const props = defineProps({
    defaults: { type: Object, default: () => ({}) },
});

const page = usePage();
const auth = computed(() => page.props.auth?.user);

// ── File upload ───────────────────────────────────────────
const { uploadError, uploading, parseProjectFile, parseNetMeteringFile } = useReportUpload();
const fileInputRefs = ref([]);

function triggerUpload(sectionIndex) {
    fileInputRefs.value[sectionIndex]?.click();
}

async function handleFileUpload(event, sectionIndex) {
    const file = event.target.files?.[0];
    if (!file) return;
    const section = form.sections[sectionIndex];
    if (section.section_type === 'net_metering') {
        const entries = await parseNetMeteringFile(file);
        if (entries) section.net_metering_entries = entries;
    } else {
        const entries = await parseProjectFile(file);
        if (entries) section.project_entries = entries;
    }
    // Reset the input so the same file can be re-uploaded
    event.target.value = '';
}

const projectTypeOptions = [
    { value: 'Grid-scale Solar Plant', label: 'Grid-scale Solar Plant' },
    { value: 'Rooftop Solar', label: 'Rooftop Solar' },
    { value: 'Solar Rooftop and Carport', label: 'Solar Rooftop and Carport' },
    { value: 'Off Grid', label: 'Off Grid' },
    { value: 'Thermal', label: 'Thermal' },
    { value: 'Transmission Line', label: 'Transmission Line' },
    { value: 'Substation', label: 'Substation' },
];

const defaultSections = [
    {
        title: 'SOLAR PROJECTS COMPLETED',
        section_type: 'completed_solar',
        sort_order: 0,
        project_entries: [emptyProjectEntry(0)],
        net_metering_entries: [],
    },
    {
        title: 'PROJECTS UNDER CONSTRUCTION',
        section_type: 'construction_projects',
        sort_order: 1,
        project_entries: [emptyProjectEntry(0)],
        net_metering_entries: [],
    },
    {
        title: 'TRANSMISSION PROJECTS UNDER CONSTRUCTION',
        section_type: 'transmission_projects',
        sort_order: 2,
        project_entries: [emptyProjectEntry(0)],
        net_metering_entries: [],
    },
    {
        title: 'NET METERING',
        section_type: 'net_metering',
        sort_order: 3,
        project_entries: [],
        net_metering_entries: [emptyNetMeteringEntry(0)],
    },
];

const form = useForm({
    week_number: props.defaults.week_number || 1,
    year: props.defaults.year || new Date().getFullYear(),
    report_date: props.defaults.report_date || new Date().toISOString().split('T')[0],
    notes: '',
    sections: JSON.parse(JSON.stringify(defaultSections)),
});

function emptyProjectEntry(sortOrder) {
    return {
        name: '', location: '', developer: '', size_mw: null,
        project_type: '', est_completion: '', notes: '', sort_order: sortOrder,
    };
}

function emptyNetMeteringEntry(sortOrder) {
    return { key_initiative: '', status_value: '', sort_order: sortOrder };
}

function addProjectEntry(sectionIndex) {
    const entries = form.sections[sectionIndex].project_entries;
    entries.push(emptyProjectEntry(entries.length));
}

function removeProjectEntry(sectionIndex, entryIndex) {
    const entries = form.sections[sectionIndex].project_entries;
    if (entries.length > 1) {
        entries.splice(entryIndex, 1);
        entries.forEach((e, i) => e.sort_order = i);
    }
}

function addNetMeteringEntry(sectionIndex) {
    const entries = form.sections[sectionIndex].net_metering_entries;
    entries.push(emptyNetMeteringEntry(entries.length));
}

function removeNetMeteringEntry(sectionIndex, entryIndex) {
    const entries = form.sections[sectionIndex].net_metering_entries;
    if (entries.length > 1) {
        entries.splice(entryIndex, 1);
        entries.forEach((e, i) => e.sort_order = i);
    }
}

function totalMw(entries) {
    const total = entries.reduce((sum, e) => sum + (Number(e.size_mw) || 0), 0);
    return total.toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 3 });
}

function sectionTypeLabel(type) {
    const labels = {
        completed_solar: 'Completed Solar',
        construction_projects: 'Under Construction',
        transmission_projects: 'Transmission',
        net_metering: 'Net Metering',
    };
    return labels[type] || type;
}

function submitReport() {
    // Filter out empty entries before submitting to avoid silent validation failures
    form.transform((data) => ({
        ...data,
        sections: data.sections.map(section => ({
            ...section,
            project_entries: section.project_entries.filter(e => e.name?.trim()),
            net_metering_entries: section.net_metering_entries.filter(e => e.key_initiative?.trim()),
        })),
    })).post('/pp/weekly-reports', {
        preserveScroll: true,
    });
}
</script>
