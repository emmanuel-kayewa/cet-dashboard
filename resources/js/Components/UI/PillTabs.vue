<template>
    <div>
        <nav class="flex space-x-1 overflow-x-auto scrollbar-hide" :aria-label="ariaLabel">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                @click="$emit('update:modelValue', tab.value)"
                :class="[
                    modelValue === tab.value
                        ? 'bg-gray-200 text-gray-800 dark:bg-white/10 dark:text-white'
                        : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white',
                    'rounded-md px-3 py-2 text-sm font-medium transition-colors whitespace-nowrap flex-shrink-0'
                ]"
                :aria-current="modelValue === tab.value ? 'page' : undefined"
            >
                {{ tab.label }}
            </button>
        </nav>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: String, required: true },
    tabs: {
        type: Array,
        required: true,
        // Each tab: { value: string, label: string }
    },
    ariaLabel: { type: String, default: 'Tabs' },
});

defineEmits(['update:modelValue']);
</script>
