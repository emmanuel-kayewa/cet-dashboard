<template>
    <AppLayout :directorates="directorates">
        <template #title>Project Updates</template>

        <nav class="text-sm mb-6">
            <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <li><Link href="/dashboard" class="hover:text-zesco-600">Dashboard</Link></li>
                <li>/</li>
                <li class="font-medium text-gray-900 dark:text-white">Projects</li>
            </ol>
        </nav>

        <Card title="Projects">
            <template #actions>
                <Button variant="primary" size="sm" @click="openModal()">+ New Project</Button>
            </template>
            <div class="space-y-3">
                <div v-for="project in entries.data" :key="project.id"
                     class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ project.name }}</h4>
                        <span class="text-xs px-2 py-0.5 rounded-full"
                              :class="{
                                  'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': project.status === 'on_track' || project.status === 'completed',
                                  'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': project.status === 'at_risk',
                                  'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': project.status === 'delayed',
                                  'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': project.status === 'planning' || project.status === 'in_progress',
                              }">
                            {{ project.status?.replace('_', ' ') }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ project.directorate?.code }} &middot; {{ project.description }}</p>

                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>Progress</span>
                        <span class="font-medium">{{ project.completion_percentage }}%</span>
                    </div>
                    <div class="h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-zesco-600 rounded-full" :style="{ width: project.completion_percentage + '%' }"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 text-xs text-gray-400">
                            <span v-if="project.budget">Budget: {{ formatCurrency(project.budget) }}</span>
                            <span v-if="project.start_date">{{ project.start_date }} — {{ project.end_date }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="editEntry(project)" class="text-zesco-600 hover:text-zesco-800 text-xs">Edit</button>
                            <button @click="deleteEntry(project.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                        </div>
                    </div>
                </div>
                <p v-if="!entries.data?.length" class="text-center py-8 text-gray-400 text-sm">No projects yet.</p>
            </div>
        </Card>

        <!-- Form Modal -->
        <Modal :show="showModal" :title="editingId ? 'Edit Project' : 'New Project'" max-width="xl" @close="closeModal">
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
                    v-model="form.name"
                    type="text"
                    label="Project Name"
                    placeholder="e.g., Kafue Gorge Lower"
                    size="md"
                    required
                    :error="form.errors.name"
                />

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea v-model="form.description" rows="2" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="Brief description..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3 items-start">
                    <Select
                        v-model="form.status"
                        :options="[
                            { value: 'planning', label: 'Planning' },
                            { value: 'in_progress', label: 'In Progress' },
                            { value: 'on_track', label: 'On Track' },
                            { value: 'at_risk', label: 'At Risk' },
                            { value: 'delayed', label: 'Delayed' },
                            { value: 'completed', label: 'Completed' },
                        ]"
                        label="Status"
                        size="md"
                        required
                    />
                    <Input
                        v-model="form.completion_percentage"
                        type="number"
                        min="0"
                        max="100"
                        label="Completion %"
                        size="md"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-3 items-start">
                    <Input v-model="form.budget" type="number" step="0.01" label="Budget (ZMW)" size="md" />
                    <Input v-model="form.actual_cost" type="number" step="0.01" label="Spent (ZMW)" size="md" />
                </div>

                <div class="grid grid-cols-2 gap-3 items-start">
                    <Input v-model="form.start_date" type="date" label="Start Date" size="md" />
                    <Input v-model="form.end_date" type="date" label="End Date" size="md" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <Button type="submit" variant="primary" size="md" :disabled="form.processing" class="flex-1">
                        {{ form.processing ? 'Saving...' : (editingId ? 'Update Project' : 'Add Project') }}
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
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import { formatCurrency } from '@/Composables/useFormatters';

const props = defineProps({
    entries: { type: Object, default: () => ({ data: [], links: [] }) },
    directorates: { type: Array, default: () => [] },
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    directorate_id: '',
    name: '',
    description: '',
    status: 'planning',
    completion_percentage: 0,
    budget: '',
    actual_cost: '',
    start_date: '',
    end_date: '',
});

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/data-entry/projects/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/data-entry/projects', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(project) {
    editingId.value = project.id;
    Object.keys(form.data()).forEach(key => { if (project[key] !== undefined) form[key] = project[key]; });
    showModal.value = true;
}

function resetForm() { editingId.value = null; form.reset(); }
function deleteEntry(id) { if (confirm('Delete this project?')) router.delete(`/data-entry/projects/${id}`, { preserveScroll: true }); }
</script>
