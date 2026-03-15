<template>
    <Card title="Grid Impact Studies">
        <template #actions>
            <Button variant="primary" size="sm" @click="openModal()">+ New Study</Button>
        </template>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Type</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Project</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Technology</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Developer</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">MW</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Point of Connection</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Progress</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Pipeline</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in gridImpactStudies.data" :key="s.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                        <td class="py-2 px-3 font-mono text-xs text-gray-500">{{ s.study_code }}</td>
                        <td class="py-2 px-3 text-gray-900 dark:text-white font-medium max-w-[200px] truncate" :title="s.name">{{ s.name }}</td>
                        <td class="text-center py-2 px-3 hidden sm:table-cell">
                            <Badge variant="filled" :color="s.study_type === 'report' ? 'blue' : 'purple'" :label="s.study_type" />
                        </td>
                        <td class="py-2 px-3 text-gray-500 text-xs hidden md:table-cell">{{ s.project?.project_code || '—' }}</td>
                        <td class="py-2 px-3 text-gray-500 text-xs hidden md:table-cell">{{ s.technology || '—' }}</td>
                        <td class="py-2 px-3 text-gray-500 text-xs hidden lg:table-cell max-w-[140px] truncate" :title="s.developer">{{ s.developer || '—' }}</td>
                        <td class="py-2 px-3 text-right text-gray-900 dark:text-white">{{ s.capacity_mw ?? '—' }}</td>
                        <td class="py-2 px-3 text-gray-500 text-xs hidden lg:table-cell max-w-[160px] truncate" :title="s.point_of_connection">{{ s.point_of_connection || '—' }}</td>
                        <td class="text-center py-2 px-3 text-gray-900 dark:text-white">{{ s.progress_pct ?? 0 }}%</td>
                        <td class="text-center py-2 px-3 hidden sm:table-cell">
                            <div class="flex items-center gap-0.5 justify-center">
                                <span v-for="(stage, idx) in stageList" :key="idx"
                                      class="w-2.5 h-2.5 rounded-full"
                                      :class="s[stage.field] ? stage.activeColor : 'bg-gray-200 dark:bg-gray-600'"
                                      :title="stage.label">
                                </span>
                            </div>
                        </td>
                        <td class="text-center py-2 px-3">
                            <button @click="editEntry(s)" class="text-zesco-600 hover:text-zesco-800 text-xs mr-2">Edit</button>
                            <button @click="deleteEntry(s.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!gridImpactStudies.data?.length">
                        <td colspan="11" class="text-center py-8 text-gray-400 text-sm">No grid impact studies yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="gridImpactStudies.links?.length > 3" class="flex items-center justify-center gap-1 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <template v-for="link in gridImpactStudies.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded text-xs" :class="link.active ? 'bg-zesco-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'" v-html="link.label" />
                <span v-else class="px-3 py-1 text-xs text-gray-400" v-html="link.label" />
            </template>
        </div>
    </Card>

    <Modal :show="showModal" :title="editingId ? 'Edit Grid Impact Study' : 'New Grid Impact Study'" max-width="2xl" @close="closeModal">
        <form @submit.prevent="submitEntry" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.study_code" label="Study Code" placeholder="e.g. GIS-RPT-029" required :error="form.errors.study_code" />
                <Select v-model="form.study_type" :options="typeOptions" label="Study Type" required :error="form.errors.study_type" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.name" label="Name" placeholder="e.g. Goldenray Energy" required :error="form.errors.name" />
                <Select v-model="form.pp_project_id" :options="projectOptions" label="Linked Project (optional)" :error="form.errors.pp_project_id" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <Input v-model="form.capacity_mw" type="number" step="0.001" min="0" label="Capacity (MW)" :error="form.errors.capacity_mw" />
                <Input v-model="form.developer" label="Developer" :error="form.errors.developer" />
                <Input v-model="form.technology" label="Technology" placeholder="e.g. Solar PV" :error="form.errors.technology" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.project_area" label="Project Area" :error="form.errors.project_area" />
                <Input v-model="form.point_of_connection" label="Point of Connection" placeholder="e.g. Muzuma 330kV" :error="form.errors.point_of_connection" />
            </div>
            <div>
                <Input v-model="form.progress_pct" type="number" step="0.1" min="0" max="100" label="Progress (%)" :error="form.errors.progress_pct" />
            </div>

            <!-- Stage Pipeline Toggles -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pipeline Stages</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <label v-for="stage in stageList" :key="stage.field"
                           class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer transition-colors"
                           :class="form[stage.field] ? 'border-green-400 bg-green-50 dark:bg-green-900/20 dark:border-green-600' : 'border-gray-200 dark:border-gray-600'">
                        <input type="checkbox" v-model="form[stage.field]"
                               :true-value="true" :false-value="false"
                               class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ stage.label }}</span>
                    </label>
                </div>
            </div>

            <div class="w-full">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                <textarea v-model="form.notes" rows="2" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Optional notes..."></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <Button type="submit" variant="primary" size="md" :disabled="form.processing" class="flex-1">
                    {{ form.processing ? 'Saving...' : (editingId ? 'Update' : 'Create') }}
                </Button>
                <Button type="button" variant="secondary" size="md" @click="closeModal" class="flex-1">Cancel</Button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    gridImpactStudies: { type: Object, default: () => ({ data: [], links: [] }) },
    ppProjects:        { type: Array, default: () => [] },
});

const stageList = [
    { field: 'stage_received',       label: 'Received',       activeColor: 'bg-blue-500' },
    { field: 'stage_not_started',    label: 'Not Started',    activeColor: 'bg-gray-500' },
    { field: 'stage_under_review',   label: 'Under Review',   activeColor: 'bg-amber-500' },
    { field: 'stage_pending_client', label: 'Pending Client', activeColor: 'bg-orange-500' },
    { field: 'stage_revisions',      label: 'Revisions',      activeColor: 'bg-red-400' },
    { field: 'stage_approved',       label: 'Approved',       activeColor: 'bg-green-500' },
];

const showModal = ref(false);
const editingId = ref(null);

const projectOptions = computed(() => [
    { value: '', label: '— No linked project —' },
    ...props.ppProjects.map(p => ({ value: p.id, label: `${p.project_code} — ${p.project_name}` })),
]);

const typeOptions = [
    { value: 'report', label: 'Report' },
    { value: 'inception', label: 'Inception' },
];

const form = useForm({
    study_code: '',
    study_type: 'report',
    pp_project_id: '',
    name: '',
    capacity_mw: null,
    developer: '',
    technology: '',
    project_area: '',
    point_of_connection: '',
    progress_pct: 0,
    stage_received: false,
    stage_not_started: false,
    stage_under_review: false,
    stage_pending_client: false,
    stage_revisions: false,
    stage_approved: false,
    notes: '',
});

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/pp/grid-impact-studies/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/pp/grid-impact-studies', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(s) {
    editingId.value = s.id;
    Object.keys(form.data()).forEach(k => {
        if (k in s) form[k] = s[k] ?? '';
    });
    form.pp_project_id = s.pp_project_id || '';
    // Ensure booleans are actual booleans
    stageList.forEach(({ field }) => {
        form[field] = !!s[field];
    });
    showModal.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.study_type = 'report';
    form.progress_pct = 0;
    stageList.forEach(({ field }) => {
        form[field] = false;
    });
}

function deleteEntry(id) {
    if (confirm('Delete this grid impact study?')) {
        router.delete(`/pp/grid-impact-studies/${id}`, { preserveScroll: true });
    }
}
</script>
