<template>
    <div class="relative inline-block text-left">
        <button
            :id="buttonId"
            :data-dropdown-toggle="dropdownId"
            :class="buttonClass"
            type="button"
        >
            <slot name="trigger">
                {{ label }}
                <svg class="w-2.5 h-2.5 ml-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                </svg>
            </slot>
        </button>
        
        <!-- Dropdown menu -->
        <div 
            :id="dropdownId" 
            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700"
        >
            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" :aria-labelledby="buttonId">
                <li v-for="(item, index) in items" :key="index">
                    <a
                        v-if="!item.divider"
                        href="#"
                        @click.prevent="handleItemClick(item)"
                        :class="[
                            'block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white',
                            item.danger ? 'text-red-600 hover:text-red-700 dark:text-red-500' : ''
                        ]"
                    >
                        {{ item.label }}
                    </a>
                    <div v-else class="my-1 h-px bg-gray-100 dark:bg-gray-600"></div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    label: {
        type: String,
        default: 'Dropdown'
    },
    items: {
        type: Array,
        required: true,
        default: () => []
    },
    buttonClass: {
        type: String,
        default: 'text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800'
    },
    placement: {
        type: String,
        default: 'bottom' // bottom, top, left, right
    }
});

const emit = defineEmits(['item-click']);

const buttonId = ref(`dropdown-button-${Math.random().toString(36).substr(2, 9)}`);
const dropdownId = ref(`dropdown-${Math.random().toString(36).substr(2, 9)}`);

const handleItemClick = (item) => {
    if (item.action) {
        item.action();
    }
    emit('item-click', item);
};

onMounted(() => {
    // Flowbite will automatically initialize the dropdown
    if (window.Flowbite) {
        window.Flowbite.initFlowbite();
    }
});
</script>
