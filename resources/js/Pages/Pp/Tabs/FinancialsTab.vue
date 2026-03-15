<template>
    <Card title="Financials">
        <template #actions>
            <div class="flex flex-wrap items-center gap-2">
                <Select
                    v-model="currencyFilter"
                    :options="[
                        { value: 'USD', label: 'USD' },
                        { value: 'ZMW', label: 'ZMW' }
                    ]"
                    placeholder="All Currencies"
                    size="md"
                    class="w-28 sm:w-32"
                />
                <Button variant="primary" size="md" @click="openModal()">+ New Record</Button>
            </div>
        </template>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Project</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">As-Of Date</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Committed</th>
                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Paid-to-Date</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Currency</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Notes</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="f in financials.data" :key="f.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                        <td class="py-2 px-3 font-mono text-xs text-gray-500">{{ f.finance_code }}</td>
                        <td class="py-2 px-3 text-gray-700 dark:text-gray-200">{{ f.project ? `${f.project.project_code}` : '—' }}</td>
                        <td class="py-2 px-3 text-gray-500 hidden sm:table-cell">{{ f.as_of_date }}</td>
                        <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-200 font-semibold">{{ formatNum(f.committed_amount) }}</td>
                        <td class="text-right py-2 px-3 text-green-600 dark:text-green-400 font-semibold hidden md:table-cell">{{ formatNum(f.paid_to_date) }}</td>
                        <td class="text-center py-2 px-3 hidden lg:table-cell"><span class="px-1.5 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ f.currency }}</span></td>
                        <td class="py-2 px-3 text-gray-500 max-w-xs truncate hidden lg:table-cell">{{ f.notes || '—' }}</td>
                        <td class="text-center py-2 px-3">
                            <button @click="editEntry(f)" class="text-zesco-600 hover:text-zesco-800 text-xs mr-2">Edit</button>
                            <button @click="deleteEntry(f.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!financials.data?.length">
                        <td colspan="8" class="text-center py-8 text-gray-400 text-sm">No financial records yet.</td>
                    </tr>
                    <!-- Summary row -->
                    <tr v-if="financials.data?.length" class="border-t-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30 font-semibold">
                        <td colspan="3" class="py-2 px-3 text-gray-700 dark:text-gray-200">Totals (this page)</td>
                        <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-200">{{ formatNum(totalCommitted) }}</td>
                        <td class="text-right py-2 px-3 text-green-600 dark:text-green-400">{{ formatNum(totalPaid) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="financials.links?.length > 3" class="flex items-center justify-center gap-1 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <template v-for="link in financials.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded text-xs" :class="link.active ? 'bg-zesco-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'" v-html="link.label" />
                <span v-else class="px-3 py-1 text-xs text-gray-400" v-html="link.label" />
            </template>
        </div>
    </Card>

    <Modal :show="showModal" :title="editingId ? 'Edit Financial Record' : 'New Financial Record'" max-width="lg" @close="closeModal">
        <form @submit.prevent="submitEntry" class="space-y-4">
            <Input v-model="form.finance_code" label="Finance Code" placeholder="e.g. FIN-GEN-001-USD" required :error="form.errors.finance_code" />
            <Select v-model="form.pp_project_id" :options="projectOptions" label="Project (optional)" placeholder="Portfolio-level if blank" :error="form.errors.pp_project_id" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.as_of_date" type="date" label="As-Of Date" required :error="form.errors.as_of_date" />
                <Select v-model="form.currency" :options="[{ value: 'USD', label: 'USD' }, { value: 'ZMW', label: 'ZMW' }]" label="Currency" required :error="form.errors.currency" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Input v-model="form.committed_amount" type="number" step="0.01" min="0" label="Committed Amount" :error="form.errors.committed_amount" />
                <Input v-model="form.paid_to_date" type="number" step="0.01" min="0" label="Paid-to-Date" :error="form.errors.paid_to_date" />
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
import { ref, computed, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    financials: { type: Object, default: () => ({ data: [], links: [] }) },
    ppProjects: { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
});

const currencyFilter = ref(props.filters?.currency || '');

watch(currencyFilter, () => {
    applyFilters();
});

const showModal = ref(false);
const editingId = ref(null);

const projectOptions = computed(() => [
    { value: '', label: '— Portfolio Level —' },
    ...props.ppProjects.map(p => ({ value: p.id, label: `${p.project_code} — ${p.project_name}` })),
]);

const totalCommitted = computed(() => props.financials.data?.reduce((s, f) => s + Number(f.committed_amount || 0), 0) || 0);
const totalPaid = computed(() => props.financials.data?.reduce((s, f) => s + Number(f.paid_to_date || 0), 0) || 0);

const form = useForm({
    finance_code: '', pp_project_id: '', as_of_date: new Date().toISOString().slice(0, 10),
    committed_amount: null, paid_to_date: null, currency: 'USD', notes: '',
});

function applyFilters() {
    const params = {};
    if (currencyFilter.value) params.currency = currencyFilter.value;
    router.get('/pp/financials', params, { preserveState: true, preserveScroll: true });
}

function formatNum(val) {
    if (!val && val !== 0) return '—';
    return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/pp/financials/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/pp/financials', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(f) {
    editingId.value = f.id;
    form.finance_code = f.finance_code;
    form.pp_project_id = f.pp_project_id || '';
    form.as_of_date = f.as_of_date || '';
    form.committed_amount = f.committed_amount;
    form.paid_to_date = f.paid_to_date;
    form.currency = f.currency;
    form.notes = f.notes || '';
    showModal.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.currency = 'USD';
    form.as_of_date = new Date().toISOString().slice(0, 10);
}

function deleteEntry(id) {
    if (confirm('Delete this financial record?')) {
        router.delete(`/pp/financials/${id}`, { preserveScroll: true });
    }
}
</script>
