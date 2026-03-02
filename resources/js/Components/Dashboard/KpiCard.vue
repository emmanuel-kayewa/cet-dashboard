<template>
    <div class="kpi-card">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ title }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formattedValue }}</p>
            </div>
            <div :class="['px-2 py-1 rounded-md text-xs font-semibold', statusClass]">
                {{ status }}
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <svg v-if="change > 0" class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="change < 0" class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span :class="['text-sm font-medium', changeClass]">
                    {{ change !== null ? `${change > 0 ? '+' : ''}${change.toFixed(1)}%` : '—' }}
                </span>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500">
                Target: {{ targetFormatted }}
            </p>
        </div>

        <!-- Mini sparkline placeholder -->
        <div v-if="showSparkline" class="mt-3 h-10 bg-gray-50 dark:bg-gray-700/30 rounded-md flex items-end gap-0.5 px-1 pb-1 overflow-hidden">
            <div
                v-for="(bar, i) in sparkline"
                :key="i"
                class="flex-1 rounded-sm transition-all duration-300"
                :style="{ height: bar + '%', backgroundColor: color || '#3b82f6' }"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { getStatusColor } from '@/Composables/useFormatters';

const props = defineProps({
    title: { type: String, required: true },
    value: { type: [Number, String], default: 0 },
    formattedValue: { type: String, default: '0' },
    target: { type: Number, default: null },
    targetFormatted: { type: String, default: '—' },
    change: { type: Number, default: null },
    status: { type: String, default: 'healthy' },
    unit: { type: String, default: 'number' },
    color: { type: String, default: null },
    showSparkline: { type: Boolean, default: false },
    sparkline: { type: Array, default: () => [40, 55, 70, 45, 80, 65, 75, 90, 60, 85] },
});

const statusClass = computed(() => getStatusColor(props.status));
const changeClass = computed(() => {
    if (props.change > 0) return 'text-green-600 dark:text-green-400';
    if (props.change < 0) return 'text-red-600 dark:text-red-400';
    return 'text-gray-500';
});
</script>
