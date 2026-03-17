<template>
    <div
        v-bind="rootAttrs"
        :class="[
            'mb-6 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700',
            paddingClass,
            attrsClass,
        ]"
    >
        <div :class="['flex flex-col gap-4', stackAtClass, alignAtClass]">
            <div :class="['min-w-0', leftClass]">
                <slot name="left">
                    <h2 v-if="title" class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ title }}</h2>
                    <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ subtitle }}
                    </p>
                </slot>
            </div>

            <div v-if="hasRight" :class="['w-full', rightWrapperClass]">
                <slot name="metrics">
                    <div :class="metricsContainerClass">
                        <div v-for="(m, idx) in metrics" :key="idx">
                            <p
                                class="text-2xl font-bold"
                                :class="m.color ? '' : 'text-gray-900 dark:text-white'"
                                :style="m.color ? { color: m.color } : undefined"
                            >
                                {{ m.value }}
                            </p>
                            <p class="text-xs text-gray-500">{{ m.label }}</p>
                        </div>
                    </div>
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, useAttrs, useSlots } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },

    metrics: {
        type: Array,
        default: () => [],
    },

    /**
     * Extra Tailwind classes for the metrics container.
     */
    metricsClass: { type: String, default: '' },

    /**
    * When to switch from stacked layout to side-by-side.
    * Default is "sm" to match existing page headers.
     */
    stackAt: { type: String, default: 'sm' },

    /**
     * Alignment on the breakpoint: "center" or "start".
     */
    alignAt: { type: String, default: 'center' },

    /**
     * Header padding size: "sm" or "md".
     */
    padding: { type: String, default: 'md' },

    leftClass: { type: String, default: '' },
    rightClass: { type: String, default: '' },
});

const attrs = useAttrs();
const slots = useSlots();

const attrsClass = computed(() => attrs.class);

const rootAttrs = computed(() => {
    // Avoid duplicating class bindings in v-bind
    const { class: _class, ...rest } = attrs;
    return rest;
});

const hasRight = computed(() => props.metrics.length > 0 || !!slots.metrics);

const paddingClass = computed(() => {
    return props.padding === 'sm' ? 'p-4' : 'p-4';
});

const stackAtClass = computed(() => {
    const bp = props.stackAt || 'md';
    return `${bp}:flex-row`;
});

const alignAtClass = computed(() => {
    const bp = props.stackAt || 'md';
    const align = props.alignAt === 'start' ? 'items-start' : 'items-center';
    return `${bp}:${align}`;
});

const rightWrapperClass = computed(() => {
    const bp = props.stackAt || 'md';
    // On small screens we want metrics to take full width and wrap. On larger screens, keep it compact.
    return [`${bp}:w-auto`, `${bp}:ml-auto`, `${bp}:flex-shrink-0`, props.rightClass].filter(Boolean).join(' ');
});

const metricsContainerClass = computed(() => {
    const bp = props.stackAt || 'sm';
    // Mobile: grid so KPIs don’t spill and remain readable.
    // >= breakpoint: match the original header layout (row of KPIs aligned right).
    return [
        'grid grid-cols-2 gap-4 text-center justify-items-center',
        `${bp}:flex`,
        `${bp}:flex-wrap`,
        `${bp}:items-center`,
        `${bp}:gap-6`,
        `${bp}:justify-end`,
        props.metricsClass,
    ].filter(Boolean).join(' ');
});
</script>
