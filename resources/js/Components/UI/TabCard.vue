<template>
    <div :class="['card overflow-hidden', $attrs.class]">
        <div class="card-header">
            <div class="flex items-center justify-between gap-2">
                <h3 v-if="title || $slots.title" class="text-sm font-semibold text-gray-900 dark:text-white min-w-0 truncate">
                    <slot name="title">{{ title }}</slot>
                </h3>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700/50 rounded-lg p-1">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                'px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200',
                                activeTab === tab.key
                                    ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                            :title="tab.label"
                        >
                            <span v-if="tab.icon" class="mr-1.5">{{ tab.icon }}</span>
                            <span>{{ tab.label }}</span>
                        </button>
                    </div>
                    <slot name="actions" />
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="relative">
                <template v-for="tab in tabs" :key="tab.key">
                    <div
                        v-show="activeTab === tab.key"
                        :class="[
                            'transition-opacity duration-200',
                            activeTab === tab.key ? 'opacity-100' : 'opacity-0'
                        ]"
                    >
                        <slot :name="`tab-${tab.key}`" />
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    tabs: { type: Array, required: true }, // Array of { key, label, icon? }
    defaultTab: { type: String, default: '' },
    modelValue: { type: String, default: '' }, // v-model support
});

const emit = defineEmits(['tab-change', 'update:modelValue']);

// Disable automatic attribute inheritance since we're manually applying classes
defineOptions({
    inheritAttrs: false
});

// Initialize with modelValue, defaultTab, or first tab
const activeTab = ref(props.modelValue || props.defaultTab || props.tabs[0]?.key);

// Watch for external v-model changes
watch(() => props.modelValue, (newVal) => {
    if (newVal && newVal !== activeTab.value) {
        activeTab.value = newVal;
    }
});

// Emit when tab changes
watch(activeTab, (newTab) => {
    emit('tab-change', newTab);
    emit('update:modelValue', newTab);
});
</script>
