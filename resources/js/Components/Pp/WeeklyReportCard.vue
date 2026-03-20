<template>
    <Link :href="href" class="group block">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-lg transition-all duration-200 overflow-hidden">
            <div class="p-5">
                <!-- Header row: Week badge + date -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-bold text-lg group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition-colors">
                            {{ report.week_number }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">
                                Week {{ report.week_number }}, {{ report.year }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ formatDate(report.report_date) }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions (PP route only) -->
                    <div v-if="showActions" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity" @click.prevent.stop>
                        <button
                            @click.prevent="$emit('edit', report)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors"
                            title="Edit report"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </button>
                        <button
                            @click.prevent="$emit('delete', report.id)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 dark:hover:text-red-400 transition-colors"
                            title="Delete report"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Stats grid -->
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg px-3 py-2.5 text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ report.total_projects ?? 0 }}</p>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-semibold tracking-wide">Projects</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg px-3 py-2.5 text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatMw(report.total_mw) }}</p>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-semibold tracking-wide">Total MW</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg px-3 py-2.5 text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ report.sections_count ?? 0 }}</p>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-semibold tracking-wide">Sections</p>
                    </div>
                </div>

                <!-- Footer: Author + arrow -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-[10px] font-semibold text-gray-700 dark:text-gray-200">
                            {{ report.author?.name?.charAt(0) || '?' }}
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ report.author?.name || 'Unknown' }}</span>
                    </div>
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400 flex items-center gap-1 group-hover:gap-2 group-hover:text-gray-900 dark:group-hover:text-white transition-all">
                        View Report
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    report: { type: Object, required: true },
    href: { type: String, required: true },
    showActions: { type: Boolean, default: false },
});

defineEmits(['edit', 'delete']);

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatMw(val) {
    if (!val) return '0';
    return Number(val).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
}
</script>
