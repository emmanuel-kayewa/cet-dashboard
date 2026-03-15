<template>
    <AppLayout :directorates="directorates">
        <template #title>Wayleave Figures (PP)</template>

        <Breadcrumb :items="[
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'Wayleave Entry', current: true }
        ]" />

        <Card title="Entries">
            <template #actions>
                <Button variant="primary" size="sm" @click="openModal()">+ New Entry</Button>
            </template>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Aspect</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Received</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Cleared</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Pending</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Report Date</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="entry in entries.data" :key="entry.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="py-2 px-3 text-gray-500 capitalize">{{ entry.category }}</td>
                            <td class="py-2 px-3 font-medium text-gray-900 dark:text-white">{{ entry.aspect }}</td>
                            <td class="text-right py-2 px-3 text-gray-700 dark:text-gray-200">{{ entry.received }}</td>
                            <td class="text-right py-2 px-3 text-green-600 dark:text-green-400 font-semibold">{{ entry.cleared }}</td>
                            <td class="text-right py-2 px-3 text-amber-600 dark:text-amber-400 font-semibold">{{ pending(entry) }}</td>
                            <td class="py-2 px-3 text-gray-500">{{ entry.report_date }}</td>
                            <td class="text-center py-2 px-3">
                                <button @click="editEntry(entry)" class="text-zesco-600 hover:text-zesco-800 text-xs mr-2">Edit</button>
                                <button @click="deleteEntry(entry.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!entries.data?.length">
                            <td colspan="7" class="text-center py-8 text-gray-400 text-sm">No entries yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="entries.links?.length > 3" class="flex items-center justify-center gap-1 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <template v-for="link in entries.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded text-xs" :class="link.active ? 'bg-zesco-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'" v-html="link.label" />
                    <span v-else class="px-3 py-1 text-xs text-gray-400" v-html="link.label" />
                </template>
            </div>
        </Card>

        <Modal :show="showModal" :title="editingId ? 'Edit Entry' : 'New Entry'" max-width="lg" @close="closeModal">
            <form @submit.prevent="submitEntry" class="space-y-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Directorate: <span class="font-medium text-gray-700 dark:text-gray-200">{{ pp_directorate?.code }} — {{ pp_directorate?.name }}</span>
                </div>

                <Select
                    v-model="form.category"
                    :options="[
                        { value: 'wayleave', label: 'Wayleave' },
                        { value: 'survey', label: 'Survey' },
                    ]"
                    label="Category"
                    placeholder="Select category"
                    size="md"
                    required
                    :error="form.errors.category"
                />

                <Input
                    v-model="form.aspect"
                    type="text"
                    label="Aspect"
                    placeholder="e.g., Wayleave inspections"
                    size="md"
                    required
                    :error="form.errors.aspect"
                />

                <div class="grid grid-cols-2 gap-3">
                    <Input
                        v-model="form.received"
                        type="number"
                        min="0"
                        step="1"
                        label="Received"
                        size="md"
                        required
                        :error="form.errors.received"
                    />
                    <Input
                        v-model="form.cleared"
                        type="number"
                        min="0"
                        step="1"
                        label="Cleared"
                        size="md"
                        required
                        :error="form.errors.cleared"
                    />
                </div>

                <Input
                    v-model="form.report_date"
                    type="date"
                    label="Report Date"
                    size="md"
                    required
                    :error="form.errors.report_date"
                />

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
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
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

defineProps({
    entries: { type: Object, default: () => ({ data: [], links: [] }) },
    directorates: { type: Array, default: () => [] },
    pp_directorate: { type: Object, default: () => ({}) },
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    category: 'wayleave',
    aspect: '',
    received: 0,
    cleared: 0,
    report_date: new Date().toISOString().slice(0, 10),
    notes: '',
});

function pending(entry) {
    const received = Number(entry.received || 0);
    const cleared = Number(entry.cleared || 0);
    return Math.max(0, received - cleared);
}

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/data-entry/wayleave-entries/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/data-entry/wayleave-entries', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(entry) {
    editingId.value = entry.id;
    form.category = entry.category;
    form.aspect = entry.aspect;
    form.received = entry.received;
    form.cleared = entry.cleared;
    form.report_date = entry.report_date;
    form.notes = entry.notes || '';
    showModal.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.category = 'wayleave';
    form.received = 0;
    form.cleared = 0;
    form.report_date = new Date().toISOString().slice(0, 10);
}

function deleteEntry(id) {
    if (confirm('Delete this entry?')) {
        router.delete(`/data-entry/wayleave-entries/${id}`, { preserveScroll: true });
    }
}
</script>
