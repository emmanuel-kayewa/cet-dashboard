<template>
    <AppLayout :directorates="directorates">
        <template #title>Import KPIs</template>

        <Breadcrumb :items="[
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'Admin', href: '/admin' },
            { label: 'Import KPIs', current: true }
        ]" />

        <!-- Step 1: Upload -->
        <Card v-if="step === 'upload'" title="Upload KPI File">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Upload an Excel (.xlsx) or CSV file containing your KPI definitions. The system will attempt to auto-map columns.
                </p>

                <div class="flex items-center gap-3">
                    <a href="/admin/kpi-import/template" class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Template
                    </a>
                </div>

                <!-- Drop Zone -->
                <div @dragover.prevent="dragover = true" @dragleave="dragover = false" @drop.prevent="handleDrop"
                     :class="['border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
                        dragover ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400']"
                     @click="$refs.fileInput.click()">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Drop your file here, or <span class="text-blue-600 dark:text-blue-400">browse</span></p>
                    <p class="text-xs text-gray-400">Excel (.xlsx, .xls) or CSV — Max {{ Math.round(maxFileSize / 1024) }}MB</p>
                    <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" @change="handleFile" class="hidden" />
                </div>

                <div v-if="selectedFile" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span class="text-sm text-gray-700 dark:text-gray-300 flex-1">{{ selectedFile.name }}</span>
                    <button @click="selectedFile = null" class="text-xs text-gray-400 hover:text-red-500">Remove</button>
                </div>

                <div class="flex justify-end">
                    <Button @click="parseFile" :disabled="!selectedFile || parsing" :loading="parsing" variant="primary" size="sm">
                        {{ parsing ? 'Parsing...' : 'Parse & Preview' }}
                    </Button>
                </div>

                <div v-if="parseError" class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                    {{ parseError }}
                </div>
            </div>
        </Card>

        <!-- Step 2: Preview & Map -->
        <template v-if="step === 'preview'">
            <Card title="Column Mapping" class="mb-6">
                <div class="space-y-4">
                    <p class="text-sm text-gray-500">
                        {{ parsedData.total_rows }} rows detected. Review the auto-detected column mappings below.
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <div v-for="header in parsedData.headers" :key="header" class="text-sm">
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ header }}</label>
                            <select v-model="columnMapping[header]" class="input-field w-full text-xs">
                                <option value="">— Skip —</option>
                                <option v-for="field in parsedData.available_fields" :key="field" :value="field">{{ field }}</option>
                            </select>
                            <span v-if="parsedData.auto_mapping[header]" class="text-[10px] text-green-600">Auto-mapped</span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <Button @click="step = 'upload'" variant="secondary" size="sm">← Back</Button>
                        <div class="flex gap-2">
                            <Button @click="aiEnrich" :disabled="enriching || !previewKpis.length" :loading="enriching" variant="secondary" size="sm">
                                <svg :class="['w-4 h-4', enriching && 'animate-spin']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z" />
                                </svg>
                                {{ enriching ? 'AI Analyzing...' : 'AI Auto-Categorize' }}
                            </Button>
                            <Button @click="step = 'confirm'" variant="primary" size="sm">Review & Import →</Button>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Preview Table -->
            <Card title="Data Preview" :noPadding="true">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs min-w-[600px]">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-3 font-semibold text-gray-500 uppercase">#</th>
                                <th v-for="field in mappedFields" :key="field" class="text-left py-2 px-3 font-semibold text-gray-500 uppercase">{{ field }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, idx) in previewKpis.slice(0, 20)" :key="idx" class="border-b border-gray-100 dark:border-gray-700/50">
                                <td class="py-2 px-3 text-gray-400">{{ idx + 1 }}</td>
                                <td v-for="field in mappedFields" :key="field" class="py-2 px-3 text-gray-700 dark:text-gray-300">
                                    {{ row[field] ?? '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </template>

        <!-- Step 3: Confirm -->
        <template v-if="step === 'confirm'">
            <Card title="Confirm KPI Import" class="mb-6">
                <div class="space-y-4">
                    <p class="text-sm text-gray-500">Review the {{ previewKpis.length }} KPIs to import. You can edit values before confirming.</p>

                    <!-- Assign Directorates Globally -->
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Assign All to Directorates</label>
                            <select v-model="globalDirectorateIds" multiple class="input-field w-full text-sm" style="min-height: 80px;">
                                <option v-for="d in directorates" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <Button @click="applyGlobalDirectorates" variant="secondary" size="xs">Apply to All</Button>
                    </div>

                    <!-- Editable KPI Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs min-w-[900px]">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2 px-2 font-semibold text-gray-500 uppercase w-6">
                                        <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600">
                                    </th>
                                    <th class="text-left py-2 px-2 font-semibold text-gray-500 uppercase">Name</th>
                                    <th class="text-left py-2 px-2 font-semibold text-gray-500 uppercase">Category</th>
                                    <th class="text-left py-2 px-2 font-semibold text-gray-500 uppercase">Unit</th>
                                    <th class="text-left py-2 px-2 font-semibold text-gray-500 uppercase">Target</th>
                                    <th class="text-left py-2 px-2 font-semibold text-gray-500 uppercase">Direction</th>
                                    <th class="text-left py-2 px-2 font-semibold text-gray-500 uppercase">Deadline</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(kpi, idx) in importKpis" :key="idx"
                                    :class="['border-b border-gray-100 dark:border-gray-700/50', kpi.selected ? '' : 'opacity-40']">
                                    <td class="py-1.5 px-2">
                                        <input type="checkbox" v-model="kpi.selected" class="rounded border-gray-300 text-blue-600">
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input v-model="kpi.name" class="input-field w-full text-xs" />
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <select v-model="kpi.category" class="input-field w-full text-xs">
                                            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                                        </select>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <select v-model="kpi.unit" class="input-field w-full text-xs">
                                            <option value="number">Number</option>
                                            <option value="percentage">Percentage</option>
                                            <option value="currency">Currency</option>
                                            <option value="ratio">Ratio</option>
                                        </select>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input v-model="kpi.target_value" type="number" step="0.01" class="input-field w-full text-xs" />
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <select v-model="kpi.trend_direction" class="input-field w-full text-xs">
                                            <option value="up_is_good">Up is good</option>
                                            <option value="down_is_good">Down is good</option>
                                            <option value="neutral">Neutral</option>
                                        </select>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input v-model="kpi.target_deadline" type="date" class="input-field w-full text-xs" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center">
                        <Button @click="step = 'preview'" variant="secondary" size="sm">← Back</Button>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-gray-400">{{ selectedCount }} of {{ importKpis.length }} selected</span>
                            <Button @click="confirmImport" :disabled="importing || !selectedCount" :loading="importing" variant="primary" size="sm">
                                {{ importing ? 'Importing...' : `Import ${selectedCount} KPIs` }}
                            </Button>
                        </div>
                    </div>
                </div>
            </Card>
        </template>

        <!-- Step 4: Result -->
        <Card v-if="step === 'result'" title="Import Complete">
            <div class="text-center py-6 space-y-3">
                <svg class="w-12 h-12 mx-auto text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ importResult.message }}</p>
                <div class="flex justify-center gap-6 text-sm">
                    <div class="text-green-600">{{ importResult.imported }} imported</div>
                    <div v-if="importResult.skipped" class="text-amber-600">{{ importResult.skipped }} skipped</div>
                </div>
                <div v-if="importResult.errors?.length" class="text-left max-w-md mx-auto mt-4">
                    <p class="text-xs font-medium text-red-600 mb-1">Errors:</p>
                    <ul class="text-xs text-red-500 space-y-0.5">
                        <li v-for="e in importResult.errors" :key="e">{{ e }}</li>
                    </ul>
                </div>
                <div class="pt-4 flex justify-center gap-3">
                    <Link href="/admin" class="btn-secondary text-sm">Back to Admin</Link>
                    <Button @click="reset" variant="primary" size="sm">Import More</Button>
                </div>
            </div>
        </Card>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    directorates: { type: Array, default: () => [] },
    categories: { type: Object, default: () => ({}) },
    maxFileSize: { type: Number, default: 10240 },
});

const step = ref('upload');
const dragover = ref(false);
const selectedFile = ref(null);
const parsing = ref(false);
const parseError = ref('');
const enriching = ref(false);
const importing = ref(false);

// Parse results
const parsedData = ref({ headers: [], auto_mapping: {}, preview: [], total_rows: 0, available_fields: [] });
const columnMapping = ref({});
const previewKpis = ref([]);

// Confirm data
const importKpis = ref([]);
const globalDirectorateIds = ref([]);
const selectAll = ref(true);

// Result
const importResult = ref({});

const mappedFields = computed(() => {
    return Object.values(columnMapping.value).filter(Boolean);
});

const selectedCount = computed(() => {
    return importKpis.value.filter(k => k.selected).length;
});

function handleFile(event) {
    selectedFile.value = event.target.files[0] || null;
}

function handleDrop(event) {
    dragover.value = false;
    const file = event.dataTransfer.files[0];
    if (file) selectedFile.value = file;
}

async function parseFile() {
    if (!selectedFile.value) return;

    parsing.value = true;
    parseError.value = '';

    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const resp = await fetch('/admin/kpi-import/parse', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: formData,
        });

        const data = await resp.json();
        if (!resp.ok) {
            parseError.value = data.message || 'Failed to parse file.';
            return;
        }

        parsedData.value = data;
        columnMapping.value = { ...data.auto_mapping };

        // Build preview from mapped data
        rebuildPreview();
        step.value = 'preview';
    } catch (e) {
        parseError.value = 'An error occurred while parsing the file.';
    } finally {
        parsing.value = false;
    }
}

function rebuildPreview() {
    // Re-map the raw preview data using current column mapping
    const reverseMap = {};
    for (const [fileHeader, modelField] of Object.entries(columnMapping.value)) {
        if (modelField) reverseMap[modelField] = fileHeader;
    }

    previewKpis.value = parsedData.value.preview.map(row => {
        const mapped = {};
        for (const [modelField, fileHeader] of Object.entries(reverseMap)) {
            mapped[modelField] = row[fileHeader] ?? row[modelField] ?? null;
        }
        // Also try direct field access (if already mapped by backend)
        for (const field of parsedData.value.available_fields) {
            if (!mapped[field] && row[field] !== undefined) {
                mapped[field] = row[field];
            }
        }
        return mapped;
    }).filter(r => r.name);

    // Prepare import data
    importKpis.value = previewKpis.value.map(k => ({
        ...k,
        selected: true,
        category: k.category || 'operational',
        unit: k.unit || 'number',
        trend_direction: k.trend_direction || 'up_is_good',
        directorate_ids: [],
    }));
}

async function aiEnrich() {
    enriching.value = true;
    try {
        const kpisToEnrich = previewKpis.value.map(k => ({
            name: k.name || '',
            description: k.description || '',
            category: k.category || '',
            unit: k.unit || '',
        }));

        const resp = await fetch('/admin/kpi-import/ai-enrich', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ kpis: kpisToEnrich }),
        });

        const data = await resp.json();
        if (data.success && data.enriched_kpis) {
            // Merge AI enrichments into import data
            data.enriched_kpis.forEach((enriched, idx) => {
                if (importKpis.value[idx]) {
                    const kpi = importKpis.value[idx];
                    if (enriched.category) kpi.category = enriched.category;
                    if (enriched.unit) kpi.unit = enriched.unit;
                    if (enriched.trend_direction) kpi.trend_direction = enriched.trend_direction;
                    if (enriched.suggested_target && !kpi.target_value) kpi.target_value = enriched.suggested_target;
                    if (enriched.suggested_warning_threshold) kpi.warning_threshold = enriched.suggested_warning_threshold;
                    if (enriched.suggested_critical_threshold) kpi.critical_threshold = enriched.suggested_critical_threshold;
                    if (enriched.description && !kpi.description) kpi.description = enriched.description;
                    if (enriched.suggested_name) kpi.name = enriched.suggested_name;
                }
            });
        }
    } catch (e) {
        console.error('AI enrichment failed', e);
    } finally {
        enriching.value = false;
    }
}

function applyGlobalDirectorates() {
    importKpis.value.forEach(k => {
        k.directorate_ids = [...globalDirectorateIds.value];
    });
}

function toggleSelectAll() {
    importKpis.value.forEach(k => { k.selected = selectAll.value; });
}

async function confirmImport() {
    importing.value = true;
    try {
        const selectedKpis = importKpis.value
            .filter(k => k.selected)
            .map(k => ({
                name: k.name,
                code: k.code || null,
                description: k.description || null,
                category: k.category,
                unit: k.unit,
                target_value: k.target_value || null,
                warning_threshold: k.warning_threshold || null,
                critical_threshold: k.critical_threshold || null,
                trend_direction: k.trend_direction,
                target_deadline: k.target_deadline || null,
                directorate_ids: k.directorate_ids || [],
                current_value: k.current_value || null,
            }));

        const resp = await fetch('/admin/kpi-import/confirm', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ kpis: selectedKpis }),
        });

        const data = await resp.json();
        importResult.value = data;
        step.value = 'result';
    } catch (e) {
        console.error('Import failed', e);
    } finally {
        importing.value = false;
    }
}

function reset() {
    step.value = 'upload';
    selectedFile.value = null;
    parsedData.value = { headers: [], auto_mapping: {}, preview: [], total_rows: 0, available_fields: [] };
    columnMapping.value = {};
    previewKpis.value = [];
    importKpis.value = [];
    importResult.value = {};
}
</script>
