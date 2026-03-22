<template>
    <Card title="Milestones">
        <template #actions>
            <div class="flex items-center gap-2">
                <Button variant="secondary" size="sm" @click="showImport = true">
                    <svg class="w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </Button>
                <Button variant="primary" size="sm" @click="openModal()">+ New Milestone</Button>
            </div>
        </template>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Project</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Category</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Milestone</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Baseline</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Actual Date</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Forecast</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Notes</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="m in milestones.data" :key="m.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                        <td class="py-2 px-3 font-mono text-xs text-gray-500">{{ m.milestone_code }}</td>
                        <td class="py-2 px-3 text-gray-700 dark:text-gray-200 hidden sm:table-cell">{{ m.project?.project_code }} — {{ m.project?.project_name }}</td>
                        <td class="py-2 px-3 text-gray-500 hidden md:table-cell">{{ m.category || '—' }}</td>
                        <td class="py-2 px-3 font-medium text-gray-900 dark:text-white">{{ m.milestone }}</td>
                        <td class="py-2 px-3 text-gray-500 hidden lg:table-cell">{{ m.baseline_date || '—' }}</td>
                        <td class="py-2 px-3 text-gray-500 hidden md:table-cell">{{ m.actual_date || '—' }}</td>
                        <td class="py-2 px-3 text-gray-500 hidden lg:table-cell">{{ m.forecast_date || '—' }}</td>
                        <td class="text-center py-2 px-3">
                            <Badge variant="dot" :color="getMilestoneStatusColor(m.status)" :label="m.status" />
                        </td>
                        <td class="py-2 px-3 text-gray-500 max-w-xs truncate hidden lg:table-cell">{{ m.notes || '—' }}</td>
                        <td class="text-center py-2 px-3">
                            <button @click="editEntry(m)" class="text-zesco-600 hover:text-zesco-800 text-xs mr-2">Edit</button>
                            <button @click="deleteEntry(m.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!milestones.data?.length">
                        <td colspan="10" class="text-center py-8 text-gray-400 text-sm">No milestones yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="milestones.links?.length > 3" class="flex items-center justify-center gap-1 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <template v-for="link in milestones.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded text-xs" :class="link.active ? 'bg-zesco-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'" v-html="link.label" />
                <span v-else class="px-3 py-1 text-xs text-gray-400" v-html="link.label" />
            </template>
        </div>
    </Card>

    <Modal :show="showModal" :title="editingId ? 'Edit Milestone' : 'New Milestone'" max-width="2xl" @close="closeModal">
        <form @submit.prevent="submitEntry" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.milestone_code" label="Milestone Code" placeholder="e.g. MS-GEN-001" required :error="form.errors.milestone_code" />
                <Select v-model="form.pp_project_id" :options="projectOptions" label="Project" required :error="form.errors.pp_project_id" />
            </div>
            <Input v-model="form.milestone" label="Milestone Description" placeholder="Describe the milestone" required :error="form.errors.milestone" />
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <Select v-model="form.category" :options="categoryOpts" label="Category" :error="form.errors.category" />
                <Select v-model="form.status" :options="statusOpts" label="Status" required :error="form.errors.status" />
                <Input v-model="form.owner" label="Owner" :error="form.errors.owner" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <Input v-model="form.baseline_date" type="date" label="Baseline Date" :error="form.errors.baseline_date" />
                <Input v-model="form.actual_date" type="date" label="Actual Date" :error="form.errors.actual_date" />
                <Input v-model="form.forecast_date" type="date" label="Forecast Date" :error="form.errors.forecast_date" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.weight_pct" type="number" step="0.01" min="0" max="100" label="Weight (%)" :error="form.errors.weight_pct" />
                <Input v-model="form.delay_days" type="number" step="1" label="Delay (days)" :error="form.errors.delay_days" />
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

    <!-- Import Modal -->
    <PpImportModal :show="showImport" entity="milestones" @close="showImport = false" @imported="() => { showImport = false; router.reload(); }" />
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import Badge from '@/Components/UI/Badge.vue';
import PpImportModal from '@/Components/PpImportModal.vue';
import { useBadges } from '@/Composables/useBadges';

const { getMilestoneStatusColor } = useBadges();

const props = defineProps({
    milestones: { type: Object, default: () => ({ data: [], links: [] }) },
    ppProjects: { type: Array, default: () => [] },
});

const showModal = ref(false);
const showImport = ref(false);
const editingId = ref(null);

const projectOptions = computed(() => props.ppProjects.map(p => ({ value: p.id, label: `${p.project_code} — ${p.project_name}` })));
const statusOpts = [
    { value: 'Completed', label: 'Completed' },
    { value: 'In Progress', label: 'In Progress' },
    { value: 'Pending', label: 'Pending' },
    { value: 'Overdue', label: 'Overdue' },
    { value: 'At Risk', label: 'At Risk' },
    { value: 'Not Started', label: 'Not Started' },
];
const categoryOpts = [
    { value: 'Contract', label: 'Contract' },
    { value: 'Construction', label: 'Construction' },
    { value: 'Procurement', label: 'Procurement' },
    { value: 'Engineering', label: 'Engineering' },
    { value: 'Commissioning', label: 'Commissioning' },
];

const form = useForm({
    milestone_code: '', pp_project_id: '', milestone: '', actual_date: '', status: 'Pending', notes: '',
    category: '', baseline_date: '', forecast_date: '', weight_pct: null, delay_days: null, owner: '',
});

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/pp/milestones/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/pp/milestones', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(m) {
    editingId.value = m.id;
    Object.keys(form.data()).forEach(k => {
        if (k in m) form[k] = m[k] ?? (typeof form[k] === 'number' ? null : '');
    });
    showModal.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.status = 'Pending';
}

function deleteEntry(id) {
    if (confirm('Delete this milestone?')) {
        router.delete(`/pp/milestones/${id}`, { preserveScroll: true });
    }
}
</script>
