<template>
    <div class="min-h-screen flex overflow-x-hidden bg-gray-100 dark:bg-gray-900">
        <!-- Mobile Overlay -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            @click="mobileOpen = false"
        ></div>

        <!-- Notifications Overlay -->
        <div
            v-if="notificationsOpen"
            class="fixed inset-0 bg-black/30 z-40"
            @click="notificationsOpen = false"
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
            <!-- ═══ Directorate-Focused Sidebar ═══ -->
            <transition name="sidebar-swap" mode="out-in">
                <DirectorateSidebar
                    v-if="directorateStore.activeDirectorate"
                    key="directorate"
                    :directorate="directorateStore.activeDirectorate"
                    :expanded="sidebarOpen"
                    :summary="directorateStore.activeDirectorate?.summary || null"
                    :kpiCategories="directorateStore.activeDirectorate?.kpiCategories || []"
                    :showFilters="directorateStore.activeDirectorate?.code !== 'PP'"
                    @filter-change="handleDirectorateFilterChange"
                />

                <!-- ═══ Main Navigation Sidebar ═══ -->
                <div v-else key="main" class="flex flex-col h-full">
                    <!-- Logo -->
                    <div class="flex items-center h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 flex items-center justify-center flex-shrink-0">
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
                            href="/ai"
                            icon="ai"
                            :label="sidebarOpen ? 'AI Insights' : ''"
                            :active="$page.url.startsWith('/ai')"
                        />
                        <SidebarLink
                            href="/weekly-reports"
                            icon="document"
                            :label="sidebarOpen ? 'Weekly Report' : ''"
                            :active="$page.url.startsWith('/weekly-reports')"
                        />

                        <div v-if="sidebarOpen" class="pt-4 pb-2 px-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Directorates</p>
                        </div>

                        <template v-for="d in sortedDirectorates" :key="d.slug">
                            <!-- PP Directorate: click enters directorate sidebar -->
                            <button
                                v-if="d.code === 'PP'"
                                @click="enterDirectorateView(d, '/pp/dashboard')"
                                :class="['sidebar-link w-full', ($page.url.includes('/pp') || $page.url.includes(d.slug)) && 'sidebar-link-active']"
                                :title="d.name"
                            >
                                <span :class="[
                                    ($page.url.includes('/pp') || $page.url.includes(d.slug)) ? 'border-gray-900 text-gray-900 dark:border-white dark:text-white' : 'border-gray-200 text-gray-400 dark:border-gray-700 dark:text-gray-500',
                                    'flex size-6 shrink-0 items-center justify-center rounded-lg border bg-white dark:bg-gray-800 text-[8px] font-bold transition-colors duration-150'
                                ]">
                                    {{ d.code?.substring(0, 3) }}
                                </span>
                                <span v-if="sidebarOpen" class="truncate">{{ d.name }}</span>
                            </button>
                            <!-- All other directorates: click enters directorate sidebar -->
                            <button
                                v-else
                                @click="enterDirectorateView(d, `/dashboard/directorate/${d.slug}`)"
                                :class="['sidebar-link w-full', $page.url.includes(d.slug) && 'sidebar-link-active']"
                                :title="d.name"
                            >
                                <span :class="[
                                    $page.url.includes(d.slug) ? 'border-gray-900 text-gray-900 dark:border-white dark:text-white' : 'border-gray-200 text-gray-400 dark:border-gray-700 dark:text-gray-500',
                                    'flex size-6 shrink-0 items-center justify-center rounded-lg border bg-white dark:bg-gray-800 text-[8px] font-bold transition-colors duration-150'
                                ]">
                                    {{ d.code === 'CSE' ? 'CS' : d.code?.substring(0, 3) }}
                                </span>
                                <span v-if="sidebarOpen" class="truncate">{{ d.name }}</span>
                            </button>
                        </template>

                        <template v-if="auth?.is_admin">
                            <div v-if="sidebarOpen" class="pt-4 pb-2 px-3">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Administration</p>
                            </div>
                            <SidebarLink href="/admin" icon="settings" :label="sidebarOpen ? 'Settings' : ''" :active="$page.url === '/admin'" />
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
                </div>
            </transition>

            <!-- Collapse Toggle (desktop, when in directorate mode) -->
            <button
                v-if="directorateStore.activeDirectorate"
                @click="sidebarOpen = !sidebarOpen"
                class="hidden lg:flex items-center justify-center h-12 border-t border-gray-200 dark:border-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition flex-shrink-0"
            >
                <svg :class="['w-5 h-5 transition-transform', !sidebarOpen && 'rotate-180']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>

            <!-- Close button (mobile, when in directorate mode) -->
            <button
                v-if="directorateStore.activeDirectorate"
                @click="mobileOpen = false"
                class="lg:hidden flex items-center justify-center h-12 border-t border-gray-200 dark:border-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition flex-shrink-0"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </aside>

        <!-- Main Content -->
        <div :class="[
            'flex-1 flex flex-col transition-all duration-300 overflow-x-hidden max-w-full',
            'lg:ml-0',
            sidebarOpen ? 'lg:ml-64' : 'lg:ml-20',
            // When AI drawer is open, reserve space so the drawer doesn't cover content.
            aiOpen ? 'pr-80 sm:pr-96' : '',
        ]">
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
                    <!-- <Badge 
                        variant="filled-dot" 
                        :color="getDataSourceColor(app?.data_source)" 
                        :label="app?.data_source === 'simulation' ? 'Simulation' : app?.data_source === 'manual' ? 'Manual' : 'Live'" 
                    /> -->

                    <!-- Chart Palette Picker -->
                    <PalettePicker />

                    <!-- Dark Mode Toggle -->
                    <button @click="toggleDark" class="p-2 rounded-lg text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Toggle dark mode">
                        <svg v-if="isDark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button>

                    <!-- Page AI (scoped) -->
                    <button
                        v-if="aiScope"
                        type="button"
                        @click="toggleAi"
                        class="p-2 rounded-lg text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        title="Ask AI about this view"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13.5l.375 1.313a2.25 2.25 0 001.545 1.545L21.25 16l-1.33.442a2.25 2.25 0 00-1.545 1.545L18 19.5l-.375-1.313a2.25 2.25 0 00-1.545-1.545L14.75 16l1.33-.442a2.25 2.25 0 001.545-1.545L18 13.5z" />
                        </svg>
                    </button>

                    <!-- Notifications -->
                    <button
                        type="button"
                        @click="toggleNotifications"
                        class="relative p-2 rounded-lg text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        title="Notifications"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9" />
                        </svg>

                        <span
                            v-if="unreadCount > 0"
                            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] leading-[18px] text-center font-semibold"
                        >
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
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

            <!-- Notifications Drawer (right side) -->
            <aside
                :class="[
                    'fixed inset-y-0 right-0 z-50 w-80 sm:w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 transform transition-transform duration-300 no-print',
                    notificationsOpen ? 'translate-x-0' : 'translate-x-full',
                ]"
            >
                <div class="h-16 px-4 sm:px-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">Notifications</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Unread: {{ unreadCount }}</p>
                    </div>
                    <button
                        type="button"
                        @click="notificationsOpen = false"
                        class="p-2 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        title="Close"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="h-[calc(100vh-4rem)] overflow-y-auto p-3 sm:p-4">
                    <div v-if="notificationsLoading" class="text-sm text-gray-500 dark:text-gray-400 px-2 py-4">
                        Loading notifications...
                    </div>

                    <div v-else-if="notifications.length === 0" class="text-sm text-gray-500 dark:text-gray-400 px-2 py-4">
                        No unread notifications.
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="alert in notifications"
                            :key="alert.id"
                            class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/30"
                        >
                            <div
                                class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                                :class="{
                                    'bg-red-500': alert.severity === 'critical',
                                    'bg-amber-500': alert.severity === 'warning',
                                    'bg-blue-500': alert.severity === 'info',
                                }"
                            ></div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ alert.title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 break-words">{{ alert.message }}</p>
                                <p v-if="alert.created_at" class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                    {{ formatAlertTime(alert.created_at) }}
                                </p>

                                <div class="mt-2 flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="markAlertRead(alert.id)"
                                        class="text-xs font-medium text-zesco-700 hover:text-zesco-800 dark:text-zesco-400 dark:hover:text-zesco-300"
                                    >
                                        Mark as read
                                    </button>
                                    <button
                                        type="button"
                                        @click="dismissAlert(alert.id)"
                                        class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                        Dismiss
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <AiDrawer
                :open="aiOpen"
                :scope="aiScope"
                @close="aiOpen = false"
            />

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
            <main class="flex-1 p-6 overflow-x-hidden max-w-full">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import SidebarLink from './SidebarLink.vue';
import SidebarGroup from './SidebarGroup.vue';
import DirectorateSidebar from './DirectorateSidebar.vue';
import Badge from '@/Components/UI/Badge.vue';
import PalettePicker from '@/Components/UI/PalettePicker.vue';
import { useBadges } from '@/Composables/useBadges';
import AiDrawer from '@/Components/AI/AiDrawer.vue';
import { useDirectorateStore } from '@/stores/useDirectorateStore';

defineProps({
    // Kept for backwards compatibility: many pages pass this, but AppLayout uses global shared props.
    directorates: { type: Array, default: undefined },
    // Optional scope object for page-scoped AI (e.g., PP portfolio, PP explorer, PP project).
    aiScope: { type: Object, default: null },
});

const { getDataSourceColor } = useBadges();
const directorateStore = useDirectorateStore();

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

// ── Directorate sidebar ──────────────────────────────────
function enterDirectorateView(directorate, href) {
    directorateStore.enterDirectorate(directorate);
    router.visit(href);
}

function handleDirectorateFilterChange(filters) {
    const d = directorateStore.activeDirectorate;
    if (!d) return;
    if (d.code === 'PP') return;
    router.get(`/dashboard/directorate/${d.slug}`, {
        from: filters.from || undefined,
        to: filters.to || undefined,
    }, { preserveState: true });
}

// Auto-detect directorate context from URL on page navigation
watch(() => page.url, (url) => {
    // Auto-exit when navigating away from directorate pages
    if (!url.includes('/dashboard/directorate/') && !url.includes('/pp')) {
        if (directorateStore.activeDirectorate) {
            directorateStore.exitDirectorate();
        }
    }
    // Auto-enter: if on a directorate URL but store is empty, find the matching directorate
    // (The page's own onMounted will also handle this, but this catches edge cases)
    if (!directorateStore.activeDirectorate) {
        if (url.includes('/pp')) {
            const ppDir = directorates.value.find(d => d.code === 'PP');
            if (ppDir) directorateStore.enterDirectorate(ppDir);
        } else if (url.includes('/dashboard/directorate/')) {
            const slug = url.split('/dashboard/directorate/')[1]?.split('?')[0];
            const matchDir = directorates.value.find(d => d.slug === slug);
            if (matchDir) directorateStore.enterDirectorate(matchDir);
        }
    }
}, { immediate: true });

// ── Page-scoped AI drawer ───────────────────────────────
const aiOpen = ref(false);

function toggleAi() {
    aiOpen.value = !aiOpen.value;
    if (aiOpen.value) {
        notificationsOpen.value = false;
        // Give the page breathing room when AI is open.
        sidebarOpen.value = false;
        mobileOpen.value = false;
    }

    // Charts (ECharts, etc.) often need a resize event when layout width changes.
    window.dispatchEvent(new Event('resize'));
}

// ── Notifications (alerts) ───────────────────────────────
const notificationsOpen = ref(false);
const notificationsLoading = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function fetchUnreadAlerts() {
    notificationsLoading.value = true;
    try {
        const resp = await fetch('/api/alerts/unread?limit=20', {
            headers: {
                'Accept': 'application/json',
            },
        });
        const data = await resp.json();
        notifications.value = data.alerts || [];
        unreadCount.value = data.unread_count ?? (notifications.value?.length || 0);
    } catch {
        // Notifications are optional; fail silently
        notifications.value = [];
        unreadCount.value = 0;
    } finally {
        notificationsLoading.value = false;
    }
}

function toggleNotifications() {
    notificationsOpen.value = !notificationsOpen.value;
    if (notificationsOpen.value) {
        aiOpen.value = false;
        fetchUnreadAlerts();
    }
}

async function markAlertRead(alertId) {
    try {
        await fetch(`/api/alerts/${alertId}/read`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({}),
        });
    } finally {
        await fetchUnreadAlerts();
    }
}

async function dismissAlert(alertId) {
    try {
        await fetch(`/api/alerts/${alertId}/dismiss`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({}),
        });
    } finally {
        await fetchUnreadAlerts();
    }
}

function formatAlertTime(value) {
    // value is typically an ISO string from Laravel
    try {
        const dt = new Date(value);
        return isNaN(dt.getTime()) ? '' : dt.toLocaleString();
    } catch {
        return '';
    }
}

onMounted(() => {
    // Preload count so the bell can show it without opening
    fetchUnreadAlerts();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Sidebar swap transition */
.sidebar-swap-enter-active {
    transition: opacity 0.25s ease-out, transform 0.25s ease-out;
}
.sidebar-swap-leave-active {
    transition: opacity 0.15s ease-in, transform 0.15s ease-in;
}
.sidebar-swap-enter-from {
    opacity: 0;
    transform: translateX(-8px);
}
.sidebar-swap-leave-to {
    opacity: 0;
    transform: translateX(8px);
}
</style>
