<template>
    <AppLayout :directorates="directorates">
        <template #title>Incident Management</template>

        <Breadcrumb :items="[
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'Incident Entry', current: true }
        ]" />

        <Card title="Incident Log">
            <template #actions>
                <Button variant="primary" size="sm" @click="openModal()">+ Report Incident</Button>
            </template>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Incident</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Dir.</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Severity</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="inc in incidents.data" :key="inc.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="py-2 px-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ inc.title }}</p>
                                <p class="text-xs text-gray-400 line-clamp-1">{{ inc.affected_area }}</p>
                            </td>
                            <td class="py-2 px-3 text-gray-500 text-xs">{{ inc.directorate?.code }}</td>
                            <td class="text-center py-2 px-3">
                                <Badge variant="dot" :color="getIncidentSeverityColor(inc.severity)" :label="inc.severity" />
                            </td>
                            <td class="py-2 px-3 text-gray-500 text-xs">{{ formatLabel(inc.type) }}</td>
                            <td class="py-2 px-3">
                                <Badge variant="dot" :color="getIncidentStatusColor(inc.status)" :label="inc.status" />
                            </td>
                            <td class="py-2 px-3 text-gray-500 text-xs">{{ inc.occurred_at ? new Date(inc.occurred_at).toLocaleDateString() : '—' }}</td>
                            <td class="text-center py-2 px-3">
                                <button @click="editEntry(inc)" class="text-zesco-600 hover:text-zesco-800 text-xs mr-2">Edit</button>
                                <button @click="deleteEntry(inc.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!incidents.data?.length">
                            <td colspan="7" class="text-center py-8 text-gray-400 text-sm">No incidents recorded yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="incidents.links?.length > 3" class="flex justify-center gap-1 mt-4 px-3 pb-2">
                <Link
                    v-for="link in incidents.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    class="px-3 py-1 text-xs rounded border transition"
                    :class="link.active ? 'bg-zesco-500 text-white border-zesco-500' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                    v-html="link.label"
                    preserve-scroll
                />
            </div>
        </Card>

        <!-- Form Modal -->
        <Modal :show="showModal" :title="editingId ? 'Edit Incident' : 'Report Incident'" max-width="xl" @close="closeModal">
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

                <Input
                    v-model="form.title"
                    type="text"
                    label="Incident Title"
                    placeholder="e.g., Power outage at Kariba substation"
                    size="md"
                    required
                    :error="form.errors.title"
                />

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea v-model="form.description" rows="3" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="Describe what happened..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3 items-start">
                    <Select
                        v-model="form.type"
                        :options="types.map(t => ({ value: t, label: formatLabel(t) }))"
                        label="Type"
                        placeholder="Select type"
                        size="md"
                        required
                    />
                    <Select
                        v-model="form.severity"
                        :options="severities.map(s => ({ value: s, label: s.charAt(0).toUpperCase() + s.slice(1) }))"
                        label="Severity"
                        size="md"
                        required
                    />
                </div>

                <Select
                    v-model="form.status"
                    :options="[
                        { value: 'reported', label: 'Reported' },
                        { value: 'investigating', label: 'Investigating' },
                        { value: 'mitigating', label: 'Mitigating' },
                        { value: 'resolved', label: 'Resolved' },
                        { value: 'closed', label: 'Closed' },
                    ]"
                    label="Status"
                    size="md"
                    required
                />

                <div class="grid grid-cols-2 gap-3 items-start">
                    <Input v-model="form.occurred_at" type="datetime-local" label="Occurred At" size="md" />
                    <Input v-model="form.resolved_at" type="datetime-local" label="Resolved At" size="md" />
                </div>

                <Input v-model="form.affected_area" type="text" label="Affected Area" placeholder="e.g., Lusaka Province" size="md" />
                <Input v-model="form.affected_customers" type="number" min="0" label="Affected Customers" placeholder="Number of customers affected" size="md" />

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Root Cause</label>
                    <textarea v-model="form.root_cause" rows="2" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="Root cause analysis..."></textarea>
                </div>

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Resolution</label>
                    <textarea v-model="form.resolution" rows="2" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="How was it resolved..."></textarea>
                </div>

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Lessons Learned</label>
                    <textarea v-model="form.lessons_learned" rows="2" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="Key takeaways..."></textarea>
                </div>

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="Additional notes..."></textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <Button type="submit" variant="primary" size="md" :disabled="form.processing" class="flex-1">
                        {{ form.processing ? 'Saving...' : (editingId ? 'Update Incident' : 'Report Incident') }}
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
import Badge from '@/Components/UI/Badge.vue';
import { useBadges } from '@/Composables/useBadges';

const { getIncidentSeverityColor, getIncidentStatusColor } = useBadges();

const props = defineProps({
    incidents: { type: Object, default: () => ({ data: [], links: [] }) },
    directorates: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    severities: { type: Array, default: () => [] },
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    directorate_id: '',
    title: '',
    description: '',
    type: '',
    severity: 'medium',
    status: 'reported',
    root_cause: '',
    resolution: '',
    lessons_learned: '',
    affected_area: '',
    affected_customers: null,
    occurred_at: '',
    resolved_at: '',
    notes: '',
});

function formatLabel(val) {
    if (!val) return '';
    return val.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/data-entry/incidents/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/data-entry/incidents', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(inc) {
    editingId.value = inc.id;
    Object.keys(form.data()).forEach(key => {
        if (inc[key] !== undefined) form[key] = inc[key] ?? '';
    });
    if (inc.occurred_at) form.occurred_at = inc.occurred_at.slice(0, 16);
    if (inc.resolved_at) form.resolved_at = inc.resolved_at.slice(0, 16);
    showModal.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.severity = 'medium';
    form.status = 'reported';
}

function deleteEntry(id) {
    if (confirm('Delete this incident?')) {
        router.delete(`/data-entry/incidents/${id}`, { preserveScroll: true });
    }
}
</script>
