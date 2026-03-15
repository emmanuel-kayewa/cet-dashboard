<template>
    <div class=" relative" ref="target">
        <!-- LABEL -->
        <label v-if="label" :for="id" class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-gray-300">
            {{ label }}
            <span v-if="required" class="text-red-600">*</span>
        </label>

        <!-- INPUT BOX (Trigger) -->
        <button
            type="button"
            :id="id"
            @click="toggle"
            :disabled="disabled"
            :class="selectClasses"
            aria-haspopup="listbox"
            :aria-expanded="isOpen"
        >
            <span 
                :class="displayLabel === placeholder ? 'text-zinc-400 dark:text-gray-500' : 'text-zinc-700 dark:text-gray-200'"
                class="block truncate"
            >
                {{ displayLabel }}
            </span>

            <!-- Chevron Down Icon -->
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg 
                    class="h-4 w-4 text-zinc-400 transition-transform duration-200" 
                    :class="{ 'rotate-180': isOpen }"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </button>

        <!-- DROPDOWN -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div 
                v-show="isOpen"
                class="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 border border-zinc-200 dark:border-gray-700 rounded-lg shadow-lg py-1 max-h-60 overflow-y-auto focus:outline-none"
                role="listbox"
            >
                <!-- Placeholder/Clear Option -->
                <div 
                    v-if="placeholder"
                    @click="choose('')"
                    class="relative flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-gray-700 text-sm italic text-zinc-400 dark:text-gray-500"
                >
                    <span class="w-4 h-4"></span>
                    <span>{{ placeholder }}</span>
                </div>

                <!-- Options List -->
                <div 
                    v-for="option in options" 
                    :key="getOptionValue(option)"
                    @click="choose(getOptionValue(option))"
                    class="relative flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-gray-700 text-sm"
                    :class="[
                        getOptionValue(option) == modelValue ? 'bg-zinc-50 dark:bg-gray-700' : '',
                        option.disabled ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                >
                    <!-- Checkmark -->
                    <span class="flex items-center justify-center w-4 h-4">
                        <svg 
                            v-if="getOptionValue(option) == modelValue"
                            class="w-4 h-4 text-zinc-600 dark:text-zinc-400" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>

                    <span 
                        class="block truncate"
                        :class="getOptionValue(option) == modelValue ? 'font-medium text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-gray-300'"
                    >
                        {{ getOptionLabel(option) }}
                    </span>
                </div>
            </div>
        </transition>

        <!-- MESSAGES -->
        <div v-if="helpText && !error" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ helpText }}
        </div>
        <div v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-500">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { onClickOutside } from '@vueuse/core';

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
        default: 'Select...'
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

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const isOpen = ref(false);
const target = ref(null);

onClickOutside(target, () => {
    isOpen.value = false;
});

const toggle = (event) => {
    if (props.disabled) return;
    event.stopPropagation();
    isOpen.value = !isOpen.value;
    if (isOpen.value) emit('focus');
    else emit('blur');
};

const choose = (value) => {
    emit('update:modelValue', value);
    isOpen.value = false;
    emit('blur');
};

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

const displayLabel = computed(() => {
    const selectedOption = props.options.find(opt => getOptionValue(opt) == props.modelValue);
    return selectedOption ? getOptionLabel(selectedOption) : props.placeholder;
});

const selectClasses = computed(() => {
    const baseClasses = 'relative w-full text-left appearance-none ps-3 pe-10 block rounded-lg shadow-sm border transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900';
    
    // Size classes
    const sizeClasses = {
        sm: 'h-8 py-1 text-xs',
        md: 'h-10 py-2 text-sm',
        lg: 'h-12 py-3 text-base'
    };
    
    // Focus Ring and Border (Black in light, White in dark)
    const focusClasses = 'focus:ring-zinc-900 focus:border-zinc-900 dark:focus:ring-white dark:focus:border-white';
    
    // State-based classes (error, disabled, or normal)
    let stateClasses = '';
    if (props.error) {
        stateClasses = 'bg-red-50 border-red-500 text-red-900 cursor-pointer dark:bg-gray-800 dark:text-red-400 dark:border-red-500';
    } else if (props.disabled) {
        stateClasses = 'bg-gray-50 border-zinc-200 text-zinc-700 opacity-50 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400';
    } else {
        stateClasses = 'bg-white border-zinc-200 border-b-zinc-300/80 text-zinc-700 cursor-pointer dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200';
    }
    
    return [
        baseClasses,
        sizeClasses[props.size],
        focusClasses,
        stateClasses
    ].join(' ');
});
</script>
