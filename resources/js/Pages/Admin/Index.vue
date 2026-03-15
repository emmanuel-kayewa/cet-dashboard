<template>
    <AppLayout :directorates="directorates">
        <template #title>Administration</template>

        <Breadcrumb :items="[
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'Admin', current: true }
        ]" />

        <!-- Simulation Control -->
        <Card title="Simulation Engine" class="mb-6">
            <div class="flex flex-wrap items-center gap-6 p-2">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Simulation Active</label>
                    <button @click="toggleSimulation" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                            :class="simulationActive ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                              :class="simulationActive ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                    <span class="text-xs" :class="simulationActive ? 'text-green-600' : 'text-gray-400'">
                        {{ simulationActive ? 'Running' : 'Stopped' }}
                    </span>
                </div>
                <button @click="runSimulationCycle" :disabled="runningCycle" class="btn-primary text-xs">
                    {{ runningCycle ? 'Running...' : 'Run Cycle Now' }}
                </button>
                <div class="text-xs text-gray-400">
                    Data Source: <span class="font-medium text-gray-600 dark:text-gray-300">{{ currentDataSource }}</span>
                </div>
            </div>
        </Card>

        <!-- Settings -->
        <Card title="Dashboard Settings" class="mb-6">
            <form @submit.prevent="saveSettings" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-2">
                <div v-for="setting in settings" :key="setting.key">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ setting.label || setting.key }}</label>
                    <input v-if="setting.type === 'number'" v-model="settingsForm[setting.key]" type="number" class="input-field w-full text-sm" />
                    <input v-else-if="setting.type === 'boolean'" v-model="settingsForm[setting.key]" type="checkbox" class="rounded border-gray-300 text-zesco-600 focus:ring-zesco-500" />
                    <input v-else v-model="settingsForm[setting.key]" type="text" class="input-field w-full text-sm" />
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <button type="submit" class="btn-primary text-sm">Save Settings</button>
                </div>
            </form>
        </Card>

        <!-- User Management -->
        <Card title="User Management" class="mb-6">
            <template #actions>
                <button @click="showAddUser = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-black dark:bg-white text-white dark:text-black text-xs font-medium rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add User
                </button>
            </template>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Email</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Role</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Directorate</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">WhatsApp</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">WA Phone</th>
                            <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Last Login</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="py-2 px-3 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="py-2 px-3 text-gray-500 hidden sm:table-cell">{{ user.email }}</td>
                            <td class="py-2 px-3">
                                <select v-model="user.role_id" @change="updateUserRole(user)" class="text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-gray-500 focus:border-gray-500">
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                            </td>
                            <td class="py-2 px-3">
                                <select v-model="user.directorate_id" @change="updateUserDirectorate(user)" class="text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-gray-500 focus:border-gray-500">
                                    <option :value="null">None</option>
                                    <option v-for="d in directorates" :key="d.id" :value="d.id">{{ d.code }}</option>
                                </select>
                            </td>
                            <td class="py-2 px-3">
                                <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    <input v-model="user.whatsapp_opt_in" @change="updateUserWhatsApp(user)" type="checkbox" class="rounded border-gray-300 text-zesco-600 focus:ring-zesco-500" />
                                    <span>Opt-in</span>
                                </label>
                            </td>
                            <td class="py-2 px-3 hidden md:table-cell">
                                <input
                                    v-model="user.whatsapp_phone"
                                    @blur="updateUserWhatsApp(user)"
                                    type="text"
                                    placeholder="+260XXXXXXXXX"
                                    class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-gray-500 focus:border-gray-500"
                                />
                            </td>
                            <td class="py-2 px-3 text-gray-400 text-xs hidden md:table-cell">{{ user.last_login_at || 'Never' }}</td>
                            <td class="text-center py-2 px-3">
                                <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-800 text-xs">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Add User Modal -->
        <div v-if="showAddUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showAddUser = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New User</h3>
                    <button @click="showAddUser = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submitAddUser">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input v-model="newUserForm.name" type="text" required placeholder="John Doe" class="w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white outline-none" />
                            <p v-if="newUserForm.errors.name" class="text-xs text-red-500 mt-1">{{ newUserForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                            <input v-model="newUserForm.email" type="email" required placeholder="user@zesco.co.zm" class="w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white outline-none" />
                            <p v-if="newUserForm.errors.email" class="text-xs text-red-500 mt-1">{{ newUserForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <select v-model="newUserForm.role_id" required class="w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white outline-none">
                                <option value="" disabled>Select a role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name || role.name }}</option>
                            </select>
                            <p v-if="newUserForm.errors.role_id" class="text-xs text-red-500 mt-1">{{ newUserForm.errors.role_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Directorate <span class="text-gray-400 font-normal">(optional)</span></label>
                            <select v-model="newUserForm.directorate_id" class="w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white outline-none">
                                <option :value="null">None</option>
                                <option v-for="d in directorates" :key="d.id" :value="d.id">{{ d.code }} — {{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <input v-model="newUserForm.whatsapp_opt_in" type="checkbox" class="rounded border-gray-300 text-zesco-600 focus:ring-zesco-500" />
                                <span>WhatsApp Opt-in</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">WhatsApp Phone <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input v-model="newUserForm.whatsapp_phone" type="text" placeholder="+260XXXXXXXXX" class="w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white outline-none" />
                            <p v-if="newUserForm.errors.whatsapp_phone" class="text-xs text-red-500 mt-1">{{ newUserForm.errors.whatsapp_phone }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showAddUser = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="newUserForm.processing" class="px-4 py-2 text-sm font-medium bg-black dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 disabled:opacity-50 transition-colors">
                            {{ newUserForm.processing ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Directorate Management -->
        <Card title="Directorates" class="mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 p-2">
                <div v-for="d in allDirectorates" :key="d.id" class="p-3 border border-gray-100 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: d.color }"></div>
                        <span class="font-medium text-sm text-gray-900 dark:text-white">{{ d.code }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded-full" :class="d.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                            {{ d.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ d.name }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ d.head_name || 'No head assigned' }}</p>
                </div>
            </div>
        </Card>

        <!-- Audit Logs Link -->
        <Card title="Audit Trail">
            <div class="p-2 flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">View all system activity and data change logs.</p>
                <Link href="/admin/audit-logs" class="btn-primary text-xs">View Audit Logs</Link>
            </div>
        </Card>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';

const props = defineProps({
    settings: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    directorates: { type: Array, default: () => [] },
    allDirectorates: { type: Array, default: () => [] },
    simulationActive: { type: Boolean, default: false },
    currentDataSource: { type: String, default: 'simulation' },
});

const runningCycle = ref(false);
const showAddUser = ref(false);

const newUserForm = useForm({
    name: '',
    email: '',
    role_id: '',
    directorate_id: null,
    whatsapp_opt_in: false,
    whatsapp_phone: '',
});

function submitAddUser() {
    newUserForm.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => {
            showAddUser.value = false;
            newUserForm.reset();
        },
    });
}

const settingsForm = reactive(
    Object.fromEntries(props.settings.map(s => [s.key, s.value]))
);

function toggleSimulation() {
    router.post('/admin/simulation/toggle', {}, { preserveScroll: true });
}

function runSimulationCycle() {
    runningCycle.value = true;
    router.post('/admin/simulation/run', {}, {
        preserveScroll: true,
        onFinish: () => { runningCycle.value = false; },
    });
}

function saveSettings() {
    router.post('/admin/settings', settingsForm, { preserveScroll: true });
}

function updateUserRole(user) {
    router.put(`/admin/users/${user.id}`, { 
        role_id: user.role_id, 
        directorate_id: user.directorate_id,
        is_active: user.is_active ?? true,
        whatsapp_opt_in: user.whatsapp_opt_in ?? false,
        whatsapp_phone: user.whatsapp_phone ?? null,
    }, { preserveScroll: true });
}

function updateUserDirectorate(user) {
    router.put(`/admin/users/${user.id}`, { 
        role_id: user.role_id,
        directorate_id: user.directorate_id,
        is_active: user.is_active ?? true,
        whatsapp_opt_in: user.whatsapp_opt_in ?? false,
        whatsapp_phone: user.whatsapp_phone ?? null,
    }, { preserveScroll: true });
}

function updateUserWhatsApp(user) {
    router.put(`/admin/users/${user.id}`, {
        role_id: user.role_id,
        directorate_id: user.directorate_id,
        is_active: user.is_active ?? true,
        whatsapp_opt_in: user.whatsapp_opt_in ?? false,
        whatsapp_phone: user.whatsapp_phone ?? null,
    }, { preserveScroll: true });
}

function deleteUser(id) {
    if (confirm('Remove this user?')) {
        router.delete(`/admin/users/${id}`, { preserveScroll: true });
    }
}
</script>
