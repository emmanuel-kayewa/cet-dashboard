<template>
    <div :class="['card overflow-hidden', $attrs.class]">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <h3 v-if="title || $slots.title" :class="['text-sm font-semibold text-gray-900 dark:text-white min-w-0', $slots.title ? 'flex-1' : 'truncate']">
                <slot name="title">{{ title }}</slot>
            </h3>
            <div class="flex items-center gap-1 flex-shrink-0">
                <slot name="actions" />
            </div>
        </div>
        <div :class="['card-body', noPadding && '!p-0']">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, default: '' },
    noPadding: { type: Boolean, default: false },
});

// Disable automatic attribute inheritance since we're manually applying classes
defineOptions({
    inheritAttrs: false
});
</script>
