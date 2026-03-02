<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            {{ label }}
            <span v-if="required" class="text-red-600">*</span>
        </label>
        <div class="relative">
            <div v-if="icon" class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path v-if="icon === 'search'" d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                    <path v-else-if="icon === 'email'" d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path v-else-if="icon === 'calendar'" d="M4 5h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V7a2 2 0 012-2zm0 5h12V7H4v3z"/>
                </svg>
            </div>
            <input
                :id="id"
                :type="type"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                @blur="$emit('blur', $event)"
                @focus="$emit('focus', $event)"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                :readonly="readonly"
                :min="min"
                :max="max"
                :step="step"
                :class="inputClasses"
            />
            <div v-if="helpText && !error" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ helpText }}
            </div>
            <div v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-500">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    type: {
        type: String,
        default: 'text'
    },
    label: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    readonly: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: ''
    },
    helpText: {
        type: String,
        default: ''
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    icon: {
        type: String,
        default: ''
    },
    id: {
        type: String,
        default: () => `input-${Math.random().toString(36).substr(2, 9)}`
    },
    min: {
        type: [String, Number],
        default: undefined
    },
    max: {
        type: [String, Number],
        default: undefined
    },
    step: {
        type: [String, Number],
        default: undefined
    }
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const inputClasses = computed(() => {
    const baseClasses = 'block w-full border rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500';
    
    // Size classes
    const sizeClasses = {
        sm: 'p-2 text-xs',
        md: 'p-2.5 text-sm',
        lg: 'p-4 text-base'
    };
    
    // Icon padding
    const iconPadding = props.icon ? 'pl-10' : '';
    
    // Error state
    const errorClasses = props.error 
        ? 'bg-red-50 border-red-500 text-red-900 placeholder-red-700 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500 dark:border-red-500'
        : 'bg-gray-50 border-gray-300 text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white';
    
    // Disabled state
    const disabledClasses = props.disabled ? 'opacity-50 cursor-not-allowed' : '';
    
    return [
        baseClasses,
        sizeClasses[props.size],
        iconPadding,
        errorClasses,
        disabledClasses
    ].join(' ');
});
</script>
