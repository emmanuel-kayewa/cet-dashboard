<template>
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
            <slot name="actions" />
        </div>
        <div :class="['card-body', noPadding && '!p-0']">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    noPadding: { type: Boolean, default: false },
});
</script>
