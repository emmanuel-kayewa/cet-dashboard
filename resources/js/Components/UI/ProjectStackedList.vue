<template>
    <div>
        <ul v-if="projects.length > 0" role="list" class="divide-y divide-gray-100 dark:divide-white/5">
            <li v-for="p in projects" :key="p.id" class="relative flex items-center space-x-4 py-4">
                <div class="min-w-0 flex-auto">
                    <div class="flex items-center gap-x-3">
                        <div v-if="showRag && p.rag" class="flex-none rounded-full p-1" :class="ragPillClass(p.rag)">
                            <div class="size-2 rounded-full bg-current" />
                        </div>

                        <h2 class="min-w-0 text-sm/6 font-semibold text-gray-900 dark:text-white">
                            <button type="button" class="flex gap-x-2 text-left" @click="$emit('select', p.id)">
                                <span class="truncate">{{ p.name || '—' }}</span>
                                <span class="absolute inset-0" />
                            </button>
                        </h2>
                    </div>

                    <div v-if="showKeyIssue && p.key_issue" class="mt-3 flex items-center gap-x-2.5 text-xs/5 text-gray-500 dark:text-gray-400">
                        <p class="truncate">{{ p.key_issue }}</p>
                        <svg viewBox="0 0 2 2" class="size-0.5 flex-none fill-gray-500 dark:fill-gray-300" aria-hidden="true">
                            <circle cx="1" cy="1" r="1" />
                        </svg>
                        <p v-if="showRag && p.rag" class="whitespace-nowrap">{{ p.rag }}</p>
                    </div>

                    <div v-if="showProgress && isFiniteNumber(p.progress_pct)" class="mt-2">
                        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ Number(p.progress_pct).toFixed(0) }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                            <div class="h-full bg-zesco-600 rounded-full" :style="{ width: clampPct(p.progress_pct) + '%' }" />
                        </div>
                    </div>
                </div>

                <svg class="size-5 flex-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </li>
        </ul>

        <div v-else class="text-sm text-gray-400 text-center py-8">
            <p class="text-base mb-1">{{ emptyTitle }}</p>
            <p class="text-sm">{{ emptyBody }}</p>
        </div>
    </div>
</template>

<script setup>

defineEmits(['select']);

const props = defineProps({
    projects: { type: Array, default: () => [] },
    emptyTitle: { type: String, default: 'No projects found' },
    emptyBody: { type: String, default: '' },
    showRag: { type: Boolean, default: true },
    showKeyIssue: { type: Boolean, default: true },
    showProgress: { type: Boolean, default: false },
});

function isFiniteNumber(val) {
    const n = Number(val);
    return Number.isFinite(n);
}

function clampPct(val) {
    const n = Number(val);
    if (!Number.isFinite(n)) return 0;
    return Math.max(0, Math.min(100, n));
}

function ragPillClass(rag) {
    const r = (rag || '').toLowerCase();
    if (r === 'green') return 'bg-green-100 text-green-500 dark:bg-green-400/20 dark:text-green-400';
    if (r === 'amber') return 'bg-amber-100 text-amber-500 dark:bg-amber-400/20 dark:text-amber-400';
    if (r === 'red') return 'bg-rose-100 text-rose-500 dark:bg-rose-400/20 dark:text-rose-400';
    return 'bg-gray-100/10 text-gray-500 dark:bg-white/10';
}
</script>
