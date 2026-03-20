<template>
    <div :class="['card overflow-hidden', $attrs.class]">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <h3 v-if="title || $slots.title" class="text-sm font-semibold text-gray-900 dark:text-white min-w-0 truncate">
                <slot name="title">{{ title }}</slot>
            </h3>
            <div class="flex items-center gap-1 flex-shrink-0">
                <!-- Zoom Controls -->
                <div class="flex items-center gap-0.5 bg-gray-100 dark:bg-gray-700/50 rounded-lg p-0.5">
                    <button
                        @click="zoomOut"
                        :disabled="zoomLevel <= minZoom"
                        class="p-1 rounded text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                        title="Zoom out"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </button>
                    <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 w-8 text-center select-none">{{ Math.round(zoomLevel * 100) }}%</span>
                    <button
                        @click="zoomIn"
                        :disabled="zoomLevel >= maxZoom"
                        class="p-1 rounded text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                        title="Zoom in"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                    <button
                        v-if="zoomLevel !== 1"
                        @click="resetZoom"
                        class="p-1 rounded text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-600 transition-colors"
                        title="Reset zoom"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
                <!-- Expand Button -->
                <button
                    @click="expanded = true"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="Expand chart"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>
                <!-- Extra actions slot -->
                <slot name="actions" />
            </div>
        </div>
        <div :class="['card-body overflow-hidden transition-all duration-300', noPadding && '!p-0']">
            <div :style="{ height: baseHeight + 'px' }" class="overflow-hidden">
                <div :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left', width: `${100 / zoomLevel}%`, height: `${100 / zoomLevel}%` }" class="transition-transform duration-300">
                    <slot :zoom-level="zoomLevel" :zoomed-height="baseHeight + 'px'" />
                </div>
            </div>
        </div>

        <!-- Expanded Modal -->
        <ChartModal v-model="expanded" :title="title || 'Chart'">
            <slot name="expanded" :zoom-level="1" :zoomed-height="'500px'">
                <slot :zoom-level="1" :zoomed-height="'500px'" />
            </slot>
        </ChartModal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ChartModal from '@/Components/UI/ChartModal.vue';

const props = defineProps({
    title: { type: String, default: '' },
    noPadding: { type: Boolean, default: false },
    baseHeight: { type: Number, default: 320 },
    minZoom: { type: Number, default: 0.5 },
    maxZoom: { type: Number, default: 2.0 },
    zoomStep: { type: Number, default: 0.25 },
});

defineOptions({ inheritAttrs: false });

const zoomLevel = ref(1);
const expanded = ref(false);

const zoomedHeight = computed(() => {
    return Math.round(props.baseHeight * zoomLevel.value) + 'px';
});

function zoomIn() {
    zoomLevel.value = Math.min(props.maxZoom, +(zoomLevel.value + props.zoomStep).toFixed(2));
}

function zoomOut() {
    zoomLevel.value = Math.max(props.minZoom, +(zoomLevel.value - props.zoomStep).toFixed(2));
}

function resetZoom() {
    zoomLevel.value = 1;
}

defineExpose({ zoomLevel, zoomIn, zoomOut, resetZoom });
</script>
