<template>
    <AppLayout :directorates="[]">
        <template #title>Flowbite Components Showcase</template>

        <div class="space-y-8">
            <!-- Buttons Section -->
            <Card title="Buttons">
                <div class="space-y-6">
                    <!-- Filled Buttons -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Filled Buttons</h3>
                        <div class="flex flex-wrap gap-2">
                            <Button variant="primary">Primary</Button>
                            <Button variant="secondary">Secondary</Button>
                            <Button variant="success">Success</Button>
                            <Button variant="danger">Danger</Button>
                            <Button variant="warning">Warning</Button>
                            <Button variant="info">Info</Button>
                            <Button variant="dark">Dark</Button>
                            <Button variant="light">Light</Button>
                        </div>
                    </div>

                    <!-- Outline Buttons -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Outline Buttons</h3>
                        <div class="flex flex-wrap gap-2">
                            <Button variant="primary" outline>Primary</Button>
                            <Button variant="secondary" outline>Secondary</Button>
                            <Button variant="success" outline>Success</Button>
                            <Button variant="danger" outline>Danger</Button>
                            <Button variant="warning" outline>Warning</Button>
                            <Button variant="info" outline>Info</Button>
                            <Button variant="dark" outline>Dark</Button>
                        </div>
                    </div>

                    <!-- Button Sizes -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Button Sizes</h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <Button variant="primary" size="xs">Extra Small</Button>
                            <Button variant="primary" size="sm">Small</Button>
                            <Button variant="primary" size="md">Medium</Button>
                            <Button variant="primary" size="lg">Large</Button>
                            <Button variant="primary" size="xl">Extra Large</Button>
                        </div>
                    </div>

                    <!-- Pill Buttons -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Pill Buttons</h3>
                        <div class="flex flex-wrap gap-2">
                            <Button variant="primary" pill>Primary Pill</Button>
                            <Button variant="success" pill outline>Success Outline Pill</Button>
                            <Button variant="danger" pill>Danger Pill</Button>
                        </div>
                    </div>

                    <!-- Disabled Buttons -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Disabled Buttons</h3>
                        <div class="flex flex-wrap gap-2">
                            <Button variant="primary" disabled>Disabled</Button>
                            <Button variant="success" outline disabled>Disabled Outline</Button>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Inputs Section -->
            <Card title="Input Fields">
                <div class="space-y-6">
                    <!-- Basic Input -->
                    <div class="max-w-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Basic Input</h3>
                        <Input
                            v-model="sampleInputs.basic"
                            label="Email Address"
                            type="email"
                            placeholder="name@example.com"
                        />
                    </div>

                    <!-- Input with Error -->
                    <div class="max-w-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Input with Error</h3>
                        <Input
                            v-model="sampleInputs.error"
                            label="Password"
                            type="password"
                            placeholder="Enter password"
                            error="Password must be at least 8 characters"
                        />
                    </div>

                    <!-- Input with Help Text -->
                    <div class="max-w-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Input with Help Text</h3>
                        <Input
                            v-model="sampleInputs.help"
                            label="Username"
                            placeholder="Choose a username"
                            help-text="Must be unique and contain only letters and numbers"
                        />
                    </div>

                    <!-- Input Sizes -->
                    <div class="max-w-md space-y-3">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Input Sizes</h3>
                        <Input v-model="sampleInputs.small" size="sm" placeholder="Small input" />
                        <Input v-model="sampleInputs.medium" size="md" placeholder="Medium input (default)" />
                        <Input v-model="sampleInputs.large" size="lg" placeholder="Large input" />
                    </div>

                    <!-- Number Input -->
                    <div class="max-w-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Number Input</h3>
                        <Input
                            v-model="sampleInputs.number"
                            label="Quantity"
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                        />
                    </div>

                    <!-- Disabled and Readonly -->
                    <div class="max-w-md space-y-3">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Disabled & Readonly</h3>
                        <Input v-model="sampleInputs.disabled" label="Disabled Input" disabled />
                        <Input v-model="sampleInputs.readonly" label="Readonly Input" readonly />
                    </div>
                </div>
            </Card>

            <!-- Select / Dropdown (Form) Section -->
            <Card title="Select Dropdowns (Forms)">
                <div class="space-y-6">
                    <!-- Basic Select -->
                    <div class="max-w-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Basic Select</h3>
                        <Select
                            v-model="sampleSelects.country"
                            :options="countries"
                            label="Country"
                            placeholder="Select a country"
                        />
                    </div>

                    <!-- Select with Objects -->
                    <div class="max-w-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Select with Objects</h3>
                        <Select
                            v-model="sampleSelects.user"
                            :options="users"
                            option-value="id"
                            option-label="name"
                            label="User"
                            placeholder="Select a user"
                        />
                    </div>

                    <!-- Select with Error -->
                    <div class="max-w-md">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Select with Error</h3>
                        <Select
                            v-model="sampleSelects.role"
                            :options="roles"
                            label="Role"
                            placeholder="Select a role"
                            required
                            error="Please select a role"
                        />
                    </div>

                    <!-- Select Sizes -->
                    <div class="max-w-md space-y-3">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Select Sizes</h3>
                        <Select v-model="sampleSelects.small" :options="sizeOptions" size="sm" placeholder="Small select" />
                        <Select v-model="sampleSelects.medium" :options="sizeOptions" size="md" placeholder="Medium select" />
                        <Select v-model="sampleSelects.large" :options="sizeOptions" size="lg" placeholder="Large select" />
                    </div>
                </div>
            </Card>

            <!-- Dropdown Menu Section -->
            <Card title="Dropdown Menus (Actions)">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Action Dropdowns</h3>
                        <div class="flex flex-wrap gap-3">
                            <Dropdown label="Actions" :items="dropdownItems" @item-click="handleDropdownClick" />
                            
                            <Dropdown 
                                label="Options" 
                                :items="[
                                    { label: 'Edit', action: () => showAlert('Edit clicked') },
                                    { label: 'Duplicate', action: () => showAlert('Duplicate clicked') },
                                    { divider: true },
                                    { label: 'Delete', danger: true, action: () => showAlert('Delete clicked') }
                                ]"
                                button-class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                            />
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Date Range Picker Section -->
            <Card title="Date Range Picker">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Select Date Range</h3>
                        <DateRangePicker
                            v-model:from="dateRange.from"
                            v-model:to="dateRange.to"
                            @apply="handleDateApply"
                            @clear="handleDateClear"
                        />
                        <div v-if="dateRange.from || dateRange.to" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                <strong>Selected Range:</strong> 
                                {{ dateRange.from || 'Not set' }} to {{ dateRange.to || 'Not set' }}
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Usage Code Examples -->
            <Card title="Usage Examples">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Button Component</h3>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg text-xs overflow-x-auto"><code>&lt;Button variant="primary" size="md" @click="handleClick"&gt;
  Click Me
&lt;/Button&gt;

&lt;Button variant="success" outline pill&gt;
  Outline Pill Button
&lt;/Button&gt;</code></pre>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Input Component</h3>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg text-xs overflow-x-auto"><code>&lt;Input
  v-model="form.email"
  label="Email Address"
  type="email"
  placeholder="name@example.com"
  required
  :error="form.errors.email"
/&gt;</code></pre>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select Component</h3>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg text-xs overflow-x-auto"><code>&lt;Select
  v-model="form.directorate_id"
  :options="directorates"
  option-value="id"
  option-label="name"
  label="Directorate"
  placeholder="Select directorate"
  required
/&gt;</code></pre>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Dropdown Component</h3>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg text-xs overflow-x-auto"><code>&lt;Dropdown 
  label="Actions" 
  :items="[
    { label: 'Edit', action: () => edit() },
    { label: 'Delete', danger: true, action: () => remove() }
  ]"
  @item-click="handleClick"
/&gt;</code></pre>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">DateRangePicker Component</h3>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg text-xs overflow-x-auto"><code>&lt;DateRangePicker
  v-model:from="filters.startDate"
  v-model:to="filters.endDate"
  @apply="applyFilters"
  @clear="clearFilters"
/&gt;</code></pre>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Dropdown from '@/Components/UI/Dropdown.vue';
import DateRangePicker from '@/Components/UI/DateRangePicker.vue';

// Sample data for inputs
const sampleInputs = ref({
    basic: '',
    error: '',
    help: '',
    small: '',
    medium: '',
    large: '',
    number: 0,
    disabled: 'This is disabled',
    readonly: 'This is readonly'
});

// Sample data for selects
const sampleSelects = ref({
    country: '',
    user: '',
    role: '',
    small: '',
    medium: '',
    large: ''
});

const countries = ['United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France', 'Zambia'];

const users = [
    { id: 1, name: 'John Doe', email: 'john@example.com' },
    { id: 2, name: 'Jane Smith', email: 'jane@example.com' },
    { id: 3, name: 'Bob Johnson', email: 'bob@example.com' }
];

const roles = ['Admin', 'User', 'Manager', 'Viewer'];

const sizeOptions = ['Small', 'Medium', 'Large'];

// Dropdown items
const dropdownItems = [
    { label: 'Dashboard', action: () => showAlert('Dashboard clicked') },
    { label: 'Settings', action: () => showAlert('Settings clicked') },
    { label: 'Earnings', action: () => showAlert('Earnings clicked') },
    { divider: true },
    { label: 'Sign out', action: () => showAlert('Sign out clicked') }
];

// Date range
const dateRange = ref({
    from: '',
    to: ''
});

function handleDropdownClick(item) {
    console.log('Dropdown item clicked:', item);
}

function handleDateApply() {
    showAlert(`Date range applied: ${dateRange.value.from} to ${dateRange.value.to}`);
}

function handleDateClear() {
    showAlert('Date range cleared');
}

function showAlert(message) {
    alert(message);
}
</script>
