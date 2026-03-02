<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            {{ label }}
            <span v-if="required" class="text-red-600">*</span>
        </label>
        <select
            :id="id"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"
            :required="required"
            :disabled="disabled"
            :class="selectClasses"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option 
                v-for="option in options" 
                :key="getOptionValue(option)"
                :value="getOptionValue(option)"
                :disabled="option.disabled"
            >
                {{ getOptionLabel(option) }}
            </option>
        </select>
        <div v-if="helpText && !error" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ helpText }}
        </div>
        <div v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-500">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        default: ''
    },
    options: {
        type: Array,
        required: true,
        default: () => []
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
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substr(2, 9)}`
    },
    optionValue: {
        type: String,
        default: 'value'
    },
    optionLabel: {
        type: String,
        default: 'label'
    }
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const getOptionValue = (option) => {
    if (typeof option === 'object' && option !== null) {
        return option[props.optionValue] ?? option.value ?? option.id;
    }
    return option;
};

const getOptionLabel = (option) => {
    if (typeof option === 'object' && option !== null) {
        return option[props.optionLabel] ?? option.label ?? option.name ?? option.value;
    }
    return option;
};

const selectClasses = computed(() => {
    const baseClasses = 'block w-full border rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500';
    
    // Size classes
    const sizeClasses = {
        sm: 'p-2 text-xs',
        md: 'p-2.5 text-sm',
        lg: 'p-4 text-base'
    };
    
    // Error state
    const errorClasses = props.error 
        ? 'bg-red-50 border-red-500 text-red-900 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:border-red-500'
        : 'bg-gray-50 border-gray-300 text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white';
    
    // Disabled state
    const disabledClasses = props.disabled ? 'opacity-50 cursor-not-allowed' : '';
    
    return [
        baseClasses,
        sizeClasses[props.size],
        errorClasses,
        disabledClasses
    ].join(' ');
});
</script>
