<template>
    <div class="min-h-screen flex">
        <!-- Mobile Overlay -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            @click="mobileOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 no-print',
                // Mobile: slide in/out
                mobileOpen ? 'translate-x-0 w-64' : '-translate-x-full w-64',
                // Desktop: always visible, toggle width
                'lg:translate-x-0',
                sidebarOpen ? 'lg:w-64' : 'lg:w-20'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 flex items-center justify-center flex-shrink-0">
                        <!-- Light mode: black logo, Dark mode: white logo -->
                        <img src="/images/zesco_black_logo.svg" alt="ZESCO" class="w-20 h-20 object-contain dark:hidden" />
                        <img src="/images/zesco_white_logo.svg" alt="ZESCO" class="w-20 h-20 object-contain hidden dark:block" />
                    </div>
                    <transition name="fade">
                        <div v-if="sidebarOpen" class="overflow-hidden">
                            <h1 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">ZESCO</h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Executive Dashboard</p>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <SidebarLink
                    href="/dashboard"
                    icon="dashboard"
                    :label="sidebarOpen ? 'Overview' : ''"
                    :active="$page.url === '/dashboard'"
                />
                <SidebarLink
                    href="/dashboard/comparison"
                    icon="chart"
                    :label="sidebarOpen ? 'Comparison' : ''"
                    :active="$page.url.includes('/comparison')"
                />
                <SidebarLink
                    href="/dashboard/wayleaves"
                    icon="map"
                    :label="sidebarOpen ? 'Wayleaves & Surveys' : ''"
                    :active="$page.url.includes('/wayleaves')"
                />
                <SidebarLink
                    href="/ai"
                    icon="ai"
                    :label="sidebarOpen ? 'AI Insights' : ''"
                    :active="$page.url.startsWith('/ai')"
                />

                <div v-if="sidebarOpen" class="pt-4 pb-2 px-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Directorates</p>
                </div>

                <SidebarLink
                    v-for="d in sortedDirectorates"
                    :key="d.slug"
                    :href="`/dashboard/directorate/${d.slug}`"
                    icon="building"
                    :label="sidebarOpen ? d.name : ''"
                    :title="d.name"
                    :active="$page.url.includes(d.slug)"
                    :color="d.color"
                />

                <template v-if="auth?.can_input_data">
                    <div v-if="sidebarOpen" class="pt-4 pb-2 px-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Data Entry</p>
                    </div>
                    <SidebarLink href="/data-entry/kpi-entries" icon="chart" :label="sidebarOpen ? 'KPI Entry' : ''" :active="$page.url.includes('/kpi-entries')" />
                    <SidebarLink href="/data-entry/financial-entries" icon="money" :label="sidebarOpen ? 'Financial' : ''" :active="$page.url.includes('/financial-entries')" />
                    <SidebarLink href="/data-entry/projects" icon="folder" :label="sidebarOpen ? 'Projects' : ''" :active="$page.url.includes('/projects')" />
                    <SidebarLink href="/data-entry/risks" icon="shield" :label="sidebarOpen ? 'Risks' : ''" :active="$page.url.includes('/risks')" />
                    <SidebarLink href="/data-entry/incidents" icon="alert" :label="sidebarOpen ? 'Incidents' : ''" :active="$page.url.includes('/incidents')" />
                </template>

                <template v-if="auth?.is_admin">
                    <div v-if="sidebarOpen" class="pt-4 pb-2 px-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Administration</p>
                    </div>
                    <SidebarLink href="/admin" icon="settings" :label="sidebarOpen ? 'Settings' : ''" :active="$page.url === '/admin'" />
                    <SidebarLink href="/admin/kpi-import" icon="upload" :label="sidebarOpen ? 'KPI Import' : ''" :active="$page.url.includes('/kpi-import')" />
                    <SidebarLink href="/admin/audit-logs" icon="log" :label="sidebarOpen ? 'Audit Logs' : ''" :active="$page.url.includes('/audit-logs')" />
                </template>
            </nav>

            <!-- Collapse Toggle (desktop only) -->
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="hidden lg:flex items-center justify-center h-12 border-t border-gray-200 dark:border-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
            >
                <svg :class="['w-5 h-5 transition-transform', !sidebarOpen && 'rotate-180']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>

            <!-- Close button (mobile only) -->
            <button
                @click="mobileOpen = false"
                class="lg:hidden flex items-center justify-center h-12 border-t border-gray-200 dark:border-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </aside>

        <!-- Main Content -->
        <div :class="['flex-1 flex flex-col transition-all duration-300', 'lg:ml-0', sidebarOpen ? 'lg:ml-64' : 'lg:ml-20']">
            <!-- Top Header -->
            <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 sm:px-6 no-print">
                <div class="flex items-center gap-3 min-w-0 flex-1 mr-4">
                    <!-- Hamburger (mobile only) -->
                    <button @click="mobileOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">
                        <slot name="title">Dashboard</slot>
                    </h2>
                </div>

                <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                    <!-- Data Source Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                          :class="app?.simulation_enabled ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="app?.simulation_enabled ? 'bg-amber-500' : 'bg-green-500'"></span>
                        {{ app?.data_source === 'simulation' ? 'Simulation' : app?.data_source === 'manual' ? 'Manual' : 'Live' }}
                    </span>

                    <!-- Dark Mode Toggle -->
                    <button @click="toggleDark" class="p-2 rounded-lg text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Toggle dark mode">
                        <svg v-if="isDark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button>

                    <!-- User Menu -->
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ auth?.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth?.role_display }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-zesco-100 dark:bg-zesco-900/50 flex items-center justify-center">
                            <span class="text-sm font-semibold text-zesco-600 dark:text-zesco-400">
                                {{ auth?.name?.charAt(0) }}
                            </span>
                        </div>
                        <Link href="/logout" method="post" as="button" class="p-2 rounded-lg text-gray-400 hover:text-red-600 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="mx-6 mt-4">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
                    {{ $page.props.flash.success }}
                </div>
            </div>
            <div v-if="$page.props.flash?.error" class="mx-6 mt-4">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg text-sm">
                    {{ $page.props.flash.error }}
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import SidebarLink from './SidebarLink.vue';

// No need for directorates prop - we get it from global Inertia share
const page = usePage();
const auth = computed(() => page.props.auth?.user);
const app = computed(() => page.props.app);
const directorates = computed(() => page.props.directorates || []);

// MD first, then alphabetical by name
const sortedDirectorates = computed(() => {
    const list = [...directorates.value];
    list.sort((a, b) => {
        if (a.code === 'MD') return -1;
        if (b.code === 'MD') return 1;
        return a.name.localeCompare(b.name);
    });
    return list;
});

const sidebarOpen = ref(true);
const mobileOpen = ref(false);
const { isDark, toggle: toggleDark } = useDarkMode();
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
