<template>
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
        <div :id="dateRangeId" date-rangepicker class="flex items-center gap-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                    </svg>
                </div>
                <input 
                    :id="startDateId"
                    name="start" 
                    type="text"
                    :value="from"
                    @input="handleFromChange"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Select start date"
                />
            </div>
            <span class="mx-1 text-gray-500 dark:text-gray-400">to</span>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                    </svg>
                </div>
                <input 
                    :id="endDateId"
                    name="end" 
                    type="text"
                    :value="to"
                    @input="handleToChange"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Select end date"
                />
            </div>
        </div>
        <div class="flex items-center gap-2">
            <Button type="button" variant="primary" size="sm" @click="handleApply">Apply</Button>
            <Button type="button" variant="secondary" size="sm" @click="handleClear">Clear</Button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    from: { type: String, default: '' },
    to: { type: String, default: '' },
});

const emit = defineEmits(['update:from', 'update:to', 'apply', 'clear']);

const dateRangeId = ref(`date-range-${Math.random().toString(36).substr(2, 9)}`);
const startDateId = ref(`start-date-${Math.random().toString(36).substr(2, 9)}`);
const endDateId = ref(`end-date-${Math.random().toString(36).substr(2, 9)}`);

let datepickerInstance = null;

const handleFromChange = (event) => {
    emit('update:from', event.target.value);
};

const handleToChange = (event) => {
    emit('update:to', event.target.value);
};

const handleApply = () => {
    emit('apply');
};

const handleClear = () => {
    emit('update:from', '');
    emit('update:to', '');
    emit('clear');
    
    // Clear the datepicker inputs
    const startInput = document.getElementById(startDateId.value);
    const endInput = document.getElementById(endDateId.value);
    if (startInput) startInput.value = '';
    if (endInput) endInput.value = '';
};

onMounted(() => {
    // Initialize Flowbite datepicker if available
    if (window.Datepicker) {
        try {
            const dateRangeElement = document.getElementById(dateRangeId.value);
            if (dateRangeElement) {
                // Initialize date pickers for the range
                const startInput = document.getElementById(startDateId.value);
                const endInput = document.getElementById(endDateId.value);
                
                if (startInput && window.Datepicker) {
                    new window.Datepicker(startInput, {
                        autohide: true,
                        format: 'yyyy-mm-dd'
                    });
                }
                
                if (endInput && window.Datepicker) {
                    new window.Datepicker(endInput, {
                        autohide: true,
                        format: 'yyyy-mm-dd'
                    });
                }
            }
        } catch (error) {
            console.warn('Flowbite Datepicker initialization failed:', error);
        }
    }
});

watch([() => props.from, () => props.to], ([newFrom, newTo]) => {
    // Update input values when props change
    const startInput = document.getElementById(startDateId.value);
    const endInput = document.getElementById(endDateId.value);
    if (startInput) startInput.value = newFrom || '';
    if (endInput) endInput.value = newTo || '';
});
</script>

