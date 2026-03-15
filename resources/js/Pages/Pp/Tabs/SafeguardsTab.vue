<template>
    <Card title="Safeguards (Wayleaves, Surveys & Compensation)">
        <template #actions>
            <Button variant="primary" size="sm" @click="openModal()">+ New Record</Button>
        </template>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Scope</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">WL Recv</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">WL Clrd</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">WL Pend</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Srv Recv</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Srv Clrd</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Srv Pend</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">PAPs</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Comp (ZMW)</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in safeguards.data" :key="s.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                        <td class="py-2 px-3 font-mono text-xs text-gray-500">{{ s.record_code }}</td>
                        <td class="py-2 px-3 font-medium text-gray-900 dark:text-white">{{ s.scope }}</td>
                        <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-200 hidden md:table-cell">{{ s.wayleave_received ?? '—' }}</td>
                        <td class="text-right py-2 px-3 text-green-600 dark:text-green-400 font-semibold">{{ s.wayleave_cleared ?? '—' }}</td>
                        <td class="text-right py-2 px-3 text-amber-600 dark:text-amber-400 font-semibold hidden lg:table-cell">{{ s.wayleave_pending ?? '—' }}</td>
                        <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-200 hidden md:table-cell">{{ s.survey_received ?? '—' }}</td>
                        <td class="text-right py-2 px-3 text-green-600 dark:text-green-400 font-semibold">{{ s.survey_cleared ?? '—' }}</td>
                        <td class="text-right py-2 px-3 text-amber-600 dark:text-amber-400 font-semibold hidden lg:table-cell">{{ s.survey_pending ?? '—' }}</td>
                        <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-200 hidden sm:table-cell">{{ s.paps ?? '—' }}</td>
                        <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-200 font-semibold hidden sm:table-cell">{{ formatNum(s.comp_paid_zmw) }}</td>
                        <td class="text-center py-2 px-3">
                            <button @click="editEntry(s)" class="text-zesco-600 hover:text-zesco-800 text-xs mr-2">Edit</button>
                            <button @click="deleteEntry(s.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!safeguards.data?.length">
                        <td colspan="11" class="text-center py-8 text-gray-400 text-sm">No safeguard records yet.</td>
                    </tr>
                    <!-- Summary row -->
                    <tr v-if="safeguards.data?.length" class="border-t-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30 font-semibold">
                        <td colspan="2" class="py-2 px-3 text-gray-700 dark:text-gray-200">Totals</td>
                        <td class="text-right py-2 px-3">{{ sum('wayleave_received') }}</td>
                        <td class="text-right py-2 px-3 text-green-600">{{ sum('wayleave_cleared') }}</td>
                        <td class="text-right py-2 px-3 text-amber-600">{{ sum('wayleave_pending') }}</td>
                        <td class="text-right py-2 px-3">{{ sum('survey_received') }}</td>
                        <td class="text-right py-2 px-3 text-green-600">{{ sum('survey_cleared') }}</td>
                        <td class="text-right py-2 px-3 text-amber-600">{{ sum('survey_pending') }}</td>
                        <td class="text-right py-2 px-3">{{ sum('paps') }}</td>
                        <td class="text-right py-2 px-3">{{ formatNum(sumNum('comp_paid_zmw')) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="safeguards.links?.length > 3" class="flex items-center justify-center gap-1 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <template v-for="link in safeguards.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded text-xs" :class="link.active ? 'bg-zesco-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'" v-html="link.label" />
                <span v-else class="px-3 py-1 text-xs text-gray-400" v-html="link.label" />
            </template>
        </div>
    </Card>

    <Modal :show="showModal" :title="editingId ? 'Edit Safeguard Record' : 'New Safeguard Record'" max-width="2xl" @close="closeModal">
        <form @submit.prevent="submitEntry" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.record_code" label="Record Code" placeholder="e.g. SG-PORT-Q4-2025" required :error="form.errors.record_code" />
                <Input v-model="form.scope" label="Scope" placeholder="e.g. Portfolio (Q4 2025)" required :error="form.errors.scope" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Select v-model="form.pp_project_id" :options="projectOptions" label="Project (optional)" :error="form.errors.pp_project_id" />
                <Input v-model="form.report_period" label="Report Period" placeholder="e.g. Q4 2025" :error="form.errors.report_period" />
            </div>
            <p class="text-xs font-semibold text-gray-500 uppercase pt-2">Wayleaves</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <Input v-model="form.wayleave_received" type="number" min="0" label="Received" :error="form.errors.wayleave_received" />
                <Input v-model="form.wayleave_cleared" type="number" min="0" label="Cleared" :error="form.errors.wayleave_cleared" />
                <Input v-model="form.wayleave_pending" type="number" min="0" label="Pending" :error="form.errors.wayleave_pending" />
            </div>
            <p class="text-xs font-semibold text-gray-500 uppercase pt-2">Surveys</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <Input v-model="form.survey_received" type="number" min="0" label="Received" :error="form.errors.survey_received" />
                <Input v-model="form.survey_cleared" type="number" min="0" label="Cleared" :error="form.errors.survey_cleared" />
                <Input v-model="form.survey_pending" type="number" min="0" label="Pending" :error="form.errors.survey_pending" />
            </div>
            <p class="text-xs font-semibold text-gray-500 uppercase pt-2">Compensation</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.paps" type="number" min="0" label="PAPs (Project Affected Persons)" :error="form.errors.paps" />
                <Input v-model="form.comp_paid_zmw" type="number" step="0.01" min="0" label="Compensation Paid (ZMW)" :error="form.errors.comp_paid_zmw" />
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
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    safeguards: { type: Object, default: () => ({ data: [], links: [] }) },
    ppProjects: { type: Array, default: () => [] },
});

const showModal = ref(false);
const editingId = ref(null);

const projectOptions = computed(() => [
    { value: '', label: '— Portfolio Level —' },
    ...props.ppProjects.map(p => ({ value: p.id, label: `${p.project_code} — ${p.project_name}` })),
]);

const form = useForm({
    record_code: '', scope: '', pp_project_id: '', report_period: '',
    wayleave_received: null, wayleave_cleared: null, wayleave_pending: null,
    survey_received: null, survey_cleared: null, survey_pending: null,
    paps: null, comp_paid_zmw: null, notes: '',
});

function sum(field) {
    return props.safeguards.data?.reduce((s, r) => s + (Number(r[field]) || 0), 0) || 0;
}
function sumNum(field) {
    return props.safeguards.data?.reduce((s, r) => s + (Number(r[field]) || 0), 0) || 0;
}
function formatNum(val) {
    if (!val && val !== 0) return '—';
    return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/pp/safeguards/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/pp/safeguards', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(s) {
    editingId.value = s.id;
    Object.keys(form.data()).forEach(k => { if (k in s) form[k] = s[k] ?? (typeof form[k] === 'number' ? null : ''); });
    form.pp_project_id = s.pp_project_id || '';
    showModal.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
}

function deleteEntry(id) {
    if (confirm('Delete this safeguard record?')) {
        router.delete(`/pp/safeguards/${id}`, { preserveScroll: true });
    }
}
</script>
