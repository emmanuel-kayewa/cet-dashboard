<template>
    <AppLayout :directorates="directorates">
        <template #title>Risk Register</template>

        <nav class="text-sm mb-6">
            <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <li><Link href="/dashboard" class="hover:text-zesco-600">Dashboard</Link></li>
                <li>/</li>
                <li class="font-medium text-gray-900 dark:text-white">Risk Entry</li>
            </ol>
        </nav>

        <Card title="Risk Register">
            <template #actions>
                <Button variant="primary" size="sm" @click="openModal()">+ New Risk</Button>
            </template>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Risk</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Dir.</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Score</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Level</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="risk in entries.data" :key="risk.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="py-2 px-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ risk.title }}</p>
                                <p class="text-xs text-gray-400 line-clamp-1">{{ risk.description }}</p>
                            </td>
                            <td class="py-2 px-3 text-gray-500 text-xs">{{ risk.directorate?.code }}</td>
                            <td class="text-center py-2 px-3 font-bold">{{ risk.impact * risk.likelihood }}</td>
                            <td class="text-center py-2 px-3">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      :class="{
                                          'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': risk.risk_level === 'critical',
                                          'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': risk.risk_level === 'high',
                                          'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': risk.risk_level === 'medium',
                                          'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': risk.risk_level === 'low',
                                      }">
                                    {{ risk.risk_level }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-gray-500 capitalize text-xs">{{ risk.category }}</td>
                            <td class="py-2 px-3 text-gray-500 capitalize text-xs">{{ risk.status }}</td>
                            <td class="text-center py-2 px-3">
                                <button @click="editEntry(risk)" class="text-zesco-600 hover:text-zesco-800 text-xs mr-2">Edit</button>
                                <button @click="deleteEntry(risk.id)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!entries.data?.length">
                            <td colspan="7" class="text-center py-8 text-gray-400 text-sm">No risks registered yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Form Modal -->
        <Modal :show="showModal" :title="editingId ? 'Edit Risk' : 'Register New Risk'" max-width="xl" @close="closeModal">
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
                    label="Risk Title"
                    placeholder="e.g., Load shedding escalation"
                    size="md"
                    required
                    :error="form.errors.title"
                />

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description <span class="text-red-600">*</span></label>
                    <textarea v-model="form.description" rows="3" required class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="Describe the risk, trigger and potential impact..."></textarea>
                </div>

                <Select
                    v-model="form.category"
                    :options="[
                        { value: 'operational', label: 'Operational' },
                        { value: 'financial', label: 'Financial' },
                        { value: 'strategic', label: 'Strategic' },
                        { value: 'compliance', label: 'Compliance' },
                        { value: 'environmental', label: 'Environmental' },
                        { value: 'safety', label: 'Safety' },
                    ]"
                    label="Category"
                    placeholder="Select category"
                    size="md"
                    required
                />

                <div class="grid grid-cols-2 gap-3 items-start">
                    <Input v-model="form.impact" type="number" min="1" max="5" label="Impact (1-5)" size="md" required />
                    <Input v-model="form.likelihood" type="number" min="1" max="5" label="Likelihood (1-5)" size="md" required />
                </div>

                <!-- Risk Score Display -->
                <div class="p-3 rounded-lg text-center" :class="riskScoreClass">
                    <p class="text-xs font-medium mb-1">Risk Score</p>
                    <p class="text-2xl font-bold">{{ riskScore }}</p>
                    <p class="text-xs mt-1">{{ riskLevel }}</p>
                </div>

                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mitigation Plan</label>
                    <textarea v-model="form.mitigation" rows="2" class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500" placeholder="Planned response actions..."></textarea>
                </div>

                <Select
                    v-model="form.status"
                    :options="[
                        { value: 'identified', label: 'Identified' },
                        { value: 'assessing', label: 'Assessing' },
                        { value: 'mitigating', label: 'Mitigating' },
                        { value: 'mitigated', label: 'Mitigated' },
                        { value: 'accepted', label: 'Accepted' },
                        { value: 'closed', label: 'Closed' },
                    ]"
                    label="Status"
                    size="md"
                    required
                />

                <div class="flex items-center gap-3 pt-2">
                    <Button type="submit" variant="primary" size="md" :disabled="form.processing" class="flex-1">
                        {{ form.processing ? 'Saving...' : (editingId ? 'Update Risk' : 'Register Risk') }}
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
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    entries: { type: Object, default: () => ({ data: [], links: [] }) },
    directorates: { type: Array, default: () => [] },
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    directorate_id: '',
    title: '',
    description: '',
    category: '',
    impact: 3,
    likelihood: 3,
    mitigation: '',
    status: 'identified',
});

const riskScore = computed(() => (form.impact || 0) * (form.likelihood || 0));
const riskLevel = computed(() => {
    const s = riskScore.value;
    if (s >= 20) return 'Critical';
    if (s >= 12) return 'High';
    if (s >= 6) return 'Medium';
    return 'Low';
});
const riskScoreClass = computed(() => {
    const s = riskScore.value;
    if (s >= 20) return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
    if (s >= 12) return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
    if (s >= 6) return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
});

function openModal() { resetForm(); showModal.value = true; }
function closeModal() { showModal.value = false; resetForm(); }

function submitEntry() {
    if (editingId.value) {
        form.put(`/data-entry/risks/${editingId.value}`, { preserveScroll: true, onSuccess: () => closeModal() });
    } else {
        form.post('/data-entry/risks', { preserveScroll: true, onSuccess: () => closeModal() });
    }
}

function editEntry(risk) {
    editingId.value = risk.id;
    Object.keys(form.data()).forEach(key => { if (risk[key] !== undefined) form[key] = risk[key]; });
    showModal.value = true;
}

function resetForm() { editingId.value = null; form.reset(); form.impact = 3; form.likelihood = 3; form.status = 'identified'; }
function deleteEntry(id) { if (confirm('Delete this risk?')) router.delete(`/data-entry/risks/${id}`, { preserveScroll: true }); }
</script>
