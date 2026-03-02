<template>
    <AppLayout :directorates="directorates">
        <template #title>Audit Logs</template>

        <nav class="text-sm mb-6">
            <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <li><Link href="/dashboard" class="hover:text-zesco-600">Dashboard</Link></li>
                <li>/</li>
                <li><Link href="/admin" class="hover:text-zesco-600">Admin</Link></li>
                <li>/</li>
                <li class="font-medium text-gray-900 dark:text-white">Audit Logs</li>
            </ol>
        </nav>

        <!-- Filters -->
        <div class="flex flex-wrap items-end gap-3 mb-6 no-print">
            <Select
                v-model="filterAction"
                :options="[
                    { value: '', label: 'All Actions' },
                    { value: 'create', label: 'Create' },
                    { value: 'update', label: 'Update' },
                    { value: 'delete', label: 'Delete' },
                    { value: 'login', label: 'Login' },
                    { value: 'export', label: 'Export' },
                ]"
                size="md"
                class="w-44"
                @update:modelValue="applyFilter"
            />
            <Input
                v-model="filterSearch"
                placeholder="Search user or description..."
                size="md"
                class="w-64"
                @keyup.enter="applyFilter"
            />
            <Button variant="primary" size="md" @click="applyFilter">Filter</Button>
            <Button variant="secondary" size="md" @click="clearFilter">Clear</Button>
        </div>

        <Card title="Activity Log">
            <div class="overflow-x-auto -mx-6">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Timestamp</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Entity</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="py-2 px-3 text-gray-400 text-xs whitespace-nowrap">{{ log.created_at }}</td>
                            <td class="py-2 px-3 font-medium text-gray-900 dark:text-white text-xs whitespace-nowrap">{{ log.user?.name || 'System' }}</td>
                            <td class="py-2 px-3">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      :class="{
                                          'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': log.action === 'create',
                                          'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': log.action === 'update',
                                          'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': log.action === 'delete',
                                          'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': log.action === 'login',
                                          'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400': !['create','update','delete','login'].includes(log.action),
                                      }">
                                    {{ log.action }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-gray-500 text-xs hidden md:table-cell">{{ log.entity_type }}{{ log.entity_id ? ` #${log.entity_id}` : '' }}</td>
                            <td class="py-2 px-3 text-gray-600 dark:text-gray-400 text-xs max-w-[200px] truncate" :title="log.description">{{ log.description }}</td>
                            <td class="py-2 px-3 text-gray-400 text-xs hidden lg:table-cell">{{ log.ip_address }}</td>
                        </tr>
                        <tr v-if="!logs.data?.length">
                            <td colspan="6" class="text-center py-8 text-gray-400 text-sm">No audit logs found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links?.length > 3" class="flex items-center justify-center gap-1 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <template v-for="link in logs.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded text-xs" :class="link.active ? 'bg-zesco-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'" v-html="link.label" />
                    <span v-else class="px-3 py-1 text-xs text-gray-400" v-html="link.label" />
                </template>
            </div>
        </Card>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Select from '@/Components/UI/Select.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    logs: { type: Object, default: () => ({ data: [], links: [] }) },
    directorates: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filterAction = ref(props.filters?.action || '');
const filterSearch = ref(props.filters?.search || '');

function applyFilter() {
    router.get('/admin/audit-logs', {
        action: filterAction.value || undefined,
        search: filterSearch.value || undefined,
    }, { preserveState: true });
}

function clearFilter() {
    filterAction.value = '';
    filterSearch.value = '';
    router.get('/admin/audit-logs');
}
</script>
