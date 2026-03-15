<template>
    <AppLayout :directorates="directorates">
        <template #title>KPI Data Entry</template>

        <Breadcrumb :items="[
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'KPI Entry', current: true }
        ]" />

        <Card title="Recent Entries">
            <template #actions>
                <Button variant="primary" size="sm" @click="openModal()">+ New Entry</Button>
            </template>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">KPI</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Directorate</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Value</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Period Type</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Source</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="entry in entries.data" :key="entry.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="py-2 px-3 font-medium text-gray-900 dark:text-white">{{ entry.kpi?.name }}</td>
                            <td class="py-2 px-3 text-gray-500">{{ entry.directorate?.code }}</td>
                            <td class="text-right py-2 px-3 font-medium">{{ entry.value }}</td>
                            <td class="py-2 px-3 text-gray-500 capitalize">{{ entry.period_type }}</td>
                            <td class="py-2 px-3 text-gray-500">{{ entry.period_date }}</td>
                            <td class="py-2 px-3">
                                <span class="text-xs px-2 py-0.5 rounded-full"
                                      :class="entry.source === 'manual' ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'">
                                    {{ entry.source }}
                                </span>
                            </td>
                            <td class="text-center py-2 px-3">
                                <template v-if="entry.source === 'manual'">
                                    <button @click="editEntry(entry)" class="text-blue-600 hover:text-blue-800 dark:text-blue-500 dark:hover:text-blue-400 text-xs mr-2 font-medium">Edit</button>
                                    <button @click="deleteEntry(entry.id)" class="text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400 text-xs font-medium">Delete</button>
                                </template>
                                <span v-else class="text-xs text-gray-400">—</span>
                            </td>
                        </tr>
                        <tr v-if="!entries.data?.length">
                            <td colspan="7" class="text-center py-8 text-gray-400 text-sm">No entries yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="entries.links?.length > 3" class="flex items-center justify-center gap-1 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <template v-for="link in entries.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded text-xs" :class="link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'" v-html="link.label" />
                    <span v-else class="px-3 py-1 text-xs text-gray-400" v-html="link.label" />
                </template>
            </div>
        </Card>

        <!-- Form Modal -->
        <Modal :show="showModal" :title="editingId ? 'Edit KPI Entry' : 'New KPI Entry'" max-width="lg" @close="closeModal">
            <form @submit.prevent="submitEntry" class="space-y-4">
                <Select
                    v-model="form.directorate_id"
                    :options="directorates"
                    option-value="id"
                    option-label="code"
                    label="Directorate"
                    placeholder="Select directorate"
                    size="md"
                    required
                    :error="form.errors.directorate_id"
                />

                <Select
                    v-model="form.kpi_id"
                    :options="filteredKpis"
                    option-value="id"
                    option-label="name"
                    label="KPI"
                    placeholder="Select KPI"
                    size="md"
                    required
                    :error="form.errors.kpi_id"
                />

                <Input
                    v-model="form.value"
                    type="number"
                    step="0.01"
                    label="Value"
                    size="md"
                    required
                    :error="form.errors.value"
                />

                <Select
                    v-model="form.period_type"
                    :options="['daily', 'weekly', 'monthly', 'quarterly', 'yearly']"
                    label="Period Type"
                    size="md"
                    required
                    :error="form.errors.period_type"
                />

                <Input
                    v-model="form.period_date"
                    type="date"
                    label="Period Date"
                    size="md"
                    required
                    :error="form.errors.period_date"
                />

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Optional notes..."
                    ></textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <Button type="submit" variant="primary" size="md" :disabled="form.processing" class="flex-1">
                        {{ form.processing ? 'Saving...' : (editingId ? 'Update Entry' : 'Submit Entry') }}
                    </Button>
                    <Button type="button" variant="secondary" size="md" @click="closeModal" class="flex-1">
                        Cancel
                    </Button>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    entries: { type: Object, default: () => ({ data: [], links: [] }) },
    kpis: { type: Array, default: () => [] },
    directorates: { type: Array, default: () => [] },
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    kpi_id: '',
    directorate_id: '',
    value: '',
    period_date: new Date().toISOString().slice(0, 10),
    period_type: 'monthly',
    notes: '',
});

const filteredKpis = computed(() => {
    if (!form.directorate_id) return props.kpis;
    return props.kpis.filter(k => !k.directorate_id || k.directorate_id == form.directorate_id);
});

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/data-entry/kpi-entries/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/data-entry/kpi-entries', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(entry) {
    editingId.value = entry.id;
    form.kpi_id = entry.kpi_id;
    form.directorate_id = entry.directorate_id;
    form.value = entry.value;
    form.period_type = entry.period_type;
    form.period_date = entry.period_date;
    form.notes = entry.notes || '';
    showModal.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.period_date = new Date().toISOString().slice(0, 10);
    form.period_type = 'monthly';
}

function deleteEntry(id) {
    if (confirm('Are you sure you want to delete this entry?')) {
        router.delete(`/data-entry/kpi-entries/${id}`, { preserveScroll: true });
    }
}
</script>
