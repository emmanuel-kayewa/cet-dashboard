<template>
    <div class="min-h-screen flex bg-white dark:bg-gray-950 transition-colors duration-300">

        <!-- Left Side — Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 sm:px-12 lg:px-20 xl:px-28 py-12 relative">

            <!-- Dark Mode Toggle -->
            <button
                @click="toggleDark"
                class="absolute top-6 right-6 p-2 rounded-lg text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <!-- Sun icon (shown in dark mode) -->
                <svg v-if="isDark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <!-- Moon icon (shown in light mode) -->
                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            <div class="w-full max-w-sm mx-auto">
                <!-- Logo -->
                <div class="mb-10 flex justify-center lg:justify-center">
                    <img :src="isDark ? '/images/zesco_white_logo.svg' : '/images/zesco_black_logo.svg'" alt="ZESCO" class="h-16 w-auto" />
                </div>

                <!-- Heading -->
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Welcome back</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">Sign in to the Executive Dashboard</p>
                </div>

                <!-- Flash Messages -->
                <div v-if="flash?.error" class="mb-5 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/50 rounded-lg text-sm text-red-700 dark:text-red-400">
                    {{ flash.error }}
                </div>
                <div v-if="flash?.success" class="mb-5 p-3 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800/50 rounded-lg text-sm text-green-700 dark:text-green-400">
                    {{ flash.success }}
                </div>

                <!-- Azure AD Button -->
                <a
                    href="/auth/azure/redirect"
                    class="flex items-center justify-center gap-3 w-full py-3 px-4 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 text-white dark:text-black rounded-lg font-medium transition-colors duration-200 shadow-sm"
                >
                    <svg class="w-5 h-5" viewBox="0 0 21 21" fill="currentColor">
                        <rect x="1" y="1" width="9" height="9" />
                        <rect x="11" y="1" width="9" height="9" />
                        <rect x="1" y="11" width="9" height="9" />
                        <rect x="11" y="11" width="9" height="9" />
                    </svg>
                    Sign in with Microsoft
                </a>

                <!-- Divider -->
                <div class="relative my-7">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-3 text-xs text-gray-400 dark:text-gray-600 bg-white dark:bg-gray-950">or use magic link</span>
                    </div>
                </div>

                <!-- Magic Link Form -->
                <form @submit.prevent="sendMagicLink">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Work Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="you@zesco.co.zm"
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-600 focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white outline-none transition-colors"
                            :class="{ 'border-red-500 dark:border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ form.errors.email }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 px-4 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed text-white dark:text-black rounded-lg font-medium transition-colors duration-200"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            Sending...
                        </span>
                        <span v-else>Send Magic Link</span>
                    </button>
                </form>

                <!-- Magic Link Sent -->
                <div v-if="linkSent" class="mt-5 p-3 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800/50 rounded-lg text-sm text-green-700 dark:text-green-400 text-center">
                    Check your inbox! A sign-in link has been sent to your email.
                </div>

                <!-- Footer -->
                <p class="text-xs text-gray-400 dark:text-gray-600 mt-10 text-center">
                    &copy; {{ new Date().getFullYear() }} ZESCO Limited. Authorized access only.
                </p>
            </div>
        </div>

        <!-- Right Side — Dashboard Motif Panel -->
        <div class="hidden lg:flex lg:w-1/2 bg-gray-950 dark:bg-gray-900 relative overflow-hidden items-center justify-center">
            <!-- Abstract grid pattern background -->
            <div class="absolute inset-0 opacity-[0.04]">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Glowing orbs for depth -->
            <div class="absolute top-1/4 -left-20 w-80 h-80 bg-zesco-500/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-1/4 right-0 w-64 h-64 bg-blue-500/15 rounded-full blur-[100px]"></div>

            <!-- Dashboard motif content -->
            <div class="relative z-10 px-12 xl:px-20 max-w-lg">
                <!-- Mock dashboard cards -->
                <div class="space-y-4 mb-10">
                    <!-- Stats row -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-white/[0.06] backdrop-blur-sm border border-white/[0.08] rounded-xl p-4">
                            <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Revenue</div>
                            <div class="text-lg font-bold text-white">K2.4B</div>
                            <div class="flex items-center gap-1 mt-1">
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="text-[10px] text-emerald-400">+12%</span>
                            </div>
                        </div>
                        <div class="bg-white/[0.06] backdrop-blur-sm border border-white/[0.08] rounded-xl p-4">
                            <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Clients</div>
                            <div class="text-lg font-bold text-white">1.2M</div>
                            <div class="flex items-center gap-1 mt-1">
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="text-[10px] text-emerald-400">+5%</span>
                            </div>
                        </div>
                        <div class="bg-white/[0.06] backdrop-blur-sm border border-white/[0.08] rounded-xl p-4">
                            <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">KPIs Met</div>
                            <div class="text-lg font-bold text-white">87%</div>
                            <div class="flex items-center gap-1 mt-1">
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="text-[10px] text-emerald-400">+3%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chart mockup -->
                    <div class="bg-white/[0.06] backdrop-blur-sm border border-white/[0.08] rounded-xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-xs font-medium text-gray-300">Performance Trend</div>
                            <div class="text-[10px] text-gray-500">Last 12 months</div>
                        </div>
                        <div class="flex items-end gap-1.5 h-20">
                            <div v-for="(h, i) in chartBars" :key="i"
                                class="flex-1 rounded-t transition-all duration-500"
                                :style="{ height: h + '%' }"
                                :class="i === chartBars.length - 1 ? 'bg-zesco-500' : 'bg-white/10'"
                            ></div>
                        </div>
                    </div>

                    <!-- Bottom row -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/[0.06] backdrop-blur-sm border border-white/[0.08] rounded-xl p-4">
                            <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-2">Projects</div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full w-3/4 bg-zesco-500 rounded-full"></div>
                                </div>
                                <span class="text-[10px] text-gray-400">75%</span>
                            </div>
                        </div>
                        <div class="bg-white/[0.06] backdrop-blur-sm border border-white/[0.08] rounded-xl p-4">
                            <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-2">Risk Score</div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full w-1/4 bg-emerald-500 rounded-full"></div>
                                </div>
                                <span class="text-[10px] text-gray-400">Low</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tagline -->
                <div class="border-t border-white/[0.08] pt-6">
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Real-time insights for <span class="text-white font-medium">executive decision-making</span> across all ZESCO directorates.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const { props: pageProps } = usePage();
const flash = pageProps.flash || {};

const form = useForm({ email: '' });
const linkSent = ref(false);

// Dark mode
const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark')
        || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    applyTheme();
});

function toggleDark() {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    applyTheme();
}

function applyTheme() {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Chart bar heights for the dashboard motif
const chartBars = [35, 45, 30, 55, 65, 50, 70, 60, 75, 80, 68, 90];

function sendMagicLink() {
    form.post('/auth/magic-link', {
        preserveScroll: true,
        onSuccess: () => {
            linkSent.value = true;
        },
    });
}
</script>
