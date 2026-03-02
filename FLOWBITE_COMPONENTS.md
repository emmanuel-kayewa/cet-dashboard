# Flowbite UI Components Documentation

This project now uses Flowbite components for a modern, consistent design system. Below is the documentation for all available UI components.

## 🎨 Components Overview

All components are located in `resources/js/Components/UI/`:
- **Button.vue** - Flexible button component with multiple variants and styles
- **Input.vue** - Form input component with validation and help text
- **Select.vue** - Dropdown select component for forms
- **Dropdown.vue** - Action menu dropdown component
- **DateRangePicker.vue** - Date range picker with Flowbite datepicker integration

## 📦 Installation

Flowbite is loaded via CDN in `resources/views/app.blade.php`:

```html
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/datepicker.min.js"></script>
```

## 🔘 Button Component

### Props
- `variant`: `'primary' | 'secondary' | 'success' | 'danger' | 'warning' | 'info' | 'dark' | 'light'` (default: `'primary'`)
- `outline`: `boolean` - Outline style (default: `false`)
- `size`: `'xs' | 'sm' | 'md' | 'lg' | 'xl'` (default: `'md'`)
- `pill`: `boolean` - Rounded pill style (default: `false`)
- `disabled`: `boolean` (default: `false`)
- `type`: `'button' | 'submit' | 'reset'` (default: `'button'`)

### Events
- `@click` - Click event handler

### Examples

```vue
<!-- Basic buttons -->
<Button variant="primary">Primary</Button>
<Button variant="success">Success</Button>
<Button variant="danger">Danger</Button>

<!-- Outline buttons -->
<Button variant="primary" outline>Primary Outline</Button>
<Button variant="danger" outline>Danger Outline</Button>

<!-- Sizes -->
<Button size="xs">Extra Small</Button>
<Button size="sm">Small</Button>
<Button size="lg">Large</Button>

<!-- Pill style -->
<Button variant="success" pill>Pill Button</Button>

<!-- Disabled -->
<Button disabled>Disabled Button</Button>

<!-- With click handler -->
<Button @click="handleSubmit">Submit</Button>
```

## 📝 Input Component

### Props
- `modelValue`: `string | number` - v-model binding
- `type`: `string` - HTML input type (default: `'text'`)
- `label`: `string` - Input label
- `placeholder`: `string` - Placeholder text
- `required`: `boolean` (default: `false`)
- `disabled`: `boolean` (default: `false`)
- `readonly`: `boolean` (default: `false`)
- `error`: `string` - Error message to display
- `helpText`: `string` - Help text to display
- `size`: `'sm' | 'md' | 'lg'` (default: `'md'`)
- `icon`: `string` - Icon type (currently supports: `'search'`, `'email'`, `'calendar'`)
- `min`, `max`, `step`: For number inputs

### Events
- `@update:modelValue` - v-model update
- `@blur` - Blur event
- `@focus` - Focus event

### Examples

```vue
<!-- Basic input -->
<Input
  v-model="form.email"
  label="Email Address"
  type="email"
  placeholder="name@example.com"
/>

<!-- With error -->
<Input
  v-model="form.password"
  label="Password"
  type="password"
  :error="form.errors.password"
/>

<!-- With help text -->
<Input
  v-model="form.username"
  label="Username"
  help-text="Must be unique and contain only letters"
/>

<!-- Number input -->
<Input
  v-model="form.quantity"
  type="number"
  label="Quantity"
  :min="0"
  :max="100"
  :step="1"
/>

<!-- Small size -->
<Input
  v-model="form.search"
  size="sm"
  placeholder="Search..."
/>
```

## 📋 Select Component

### Props
- `modelValue`: `string | number | boolean` - v-model binding
- `options`: `Array` - Array of options (strings or objects)
- `label`: `string` - Select label
- `placeholder`: `string` - Placeholder option text
- `required`: `boolean` (default: `false`)
- `disabled`: `boolean` (default: `false`)
- `error`: `string` - Error message
- `helpText`: `string` - Help text
- `size`: `'sm' | 'md' | 'lg'` (default: `'md'`)
- `optionValue`: `string` - Key for option value when using objects (default: `'value'`)
- `optionLabel`: `string` - Key for option label when using objects (default: `'label'`)

### Events
- `@update:modelValue` - v-model update
- `@blur` - Blur event
- `@focus` - Focus event

### Examples

```vue
<!-- Simple array of strings -->
<Select
  v-model="selectedCountry"
  :options="['USA', 'UK', 'Canada']"
  label="Country"
  placeholder="Select a country"
/>

<!-- Array of objects -->
<Select
  v-model="form.directorate_id"
  :options="directorates"
  option-value="id"
  option-label="name"
  label="Directorate"
  placeholder="Select directorate"
  required
  :error="form.errors.directorate_id"
/>

<!-- With custom keys -->
<Select
  v-model="form.user_id"
  :options="users"
  option-value="id"
  option-label="full_name"
  label="User"
/>
```

## 🎯 Dropdown Component (Action Menu)

### Props
- `label`: `string` - Button label (default: `'Dropdown'`)
- `items`: `Array` - Array of menu items
- `buttonClass`: `string` - Custom button classes
- `placement`: `string` - Dropdown placement (default: `'bottom'`)

### Item Structure
```typescript
{
  label: string;        // Display text
  action?: Function;    // Click handler
  danger?: boolean;     // Red danger styling
  divider?: boolean;    // Show as divider instead
}
```

### Events
- `@item-click` - Emitted when an item is clicked

### Examples

```vue
<!-- Basic dropdown -->
<Dropdown
  label="Actions"
  :items="[
    { label: 'Edit', action: () => edit() },
    { label: 'Duplicate', action: () => duplicate() },
    { divider: true },
    { label: 'Delete', danger: true, action: () => remove() }
  ]"
/>

<!-- With custom button styling -->
<Dropdown
  label="Options"
  :items="menuItems"
  button-class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5"
  @item-click="handleItemClick"
/>

<!-- Custom trigger slot -->
<Dropdown :items="menuItems">
  <template #trigger>
    <Button variant="primary">
      Custom Trigger
      <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </Button>
  </template>
</Dropdown>
```

## 📅 DateRangePicker Component

### Props
- `from`: `string` - Start date (format: YYYY-MM-DD)
- `to`: `string` - End date (format: YYYY-MM-DD)

### Events
- `@update:from` - Update start date
- `@update:to` - Update end date
- `@apply` - Apply button clicked
- `@clear` - Clear button clicked

### Examples

```vue
<!-- Basic usage -->
<DateRangePicker
  v-model:from="filters.startDate"
  v-model:to="filters.endDate"
  @apply="applyFilters"
  @clear="clearFilters"
/>

<!-- In script setup -->
<script setup>
import { ref } from 'vue';
import DateRangePicker from '@/Components/UI/DateRangePicker.vue';

const filters = ref({
  startDate: '',
  endDate: ''
});

function applyFilters() {
  console.log('Apply filters:', filters.value);
  // Perform filtering logic
}

function clearFilters() {
  filters.value.startDate = '';
  filters.value.endDate = '';
}
</script>
```

## 🎨 CSS Utility Classes

The project also includes Flowbite-styled utility classes in `resources/css/app.css`:

### Button Classes
- `.btn-primary` - Primary blue button
- `.btn-secondary` - Secondary gray button  
- `.btn-danger` - Red danger button

### Form Classes
- `.input-field` - Styled input/select/textarea field
- `.label` - Form label styling

### Examples
```html
<!-- Using utility classes directly -->
<button class="btn-primary">Click Me</button>
<input type="text" class="input-field" />
<label class="label">Field Label</label>
```

## 📖 Component Showcase

To see all components in action with live examples, create a route to the ComponentShowcase page:

```php
// routes/web.php
Route::get('/components', function () {
    return Inertia::render('ComponentShowcase');
})->name('components.showcase');
```

Then visit `/components` to see the interactive showcase.

## 🔄 Migration Guide

### Old Style → New Components

**Buttons:**
```vue
<!-- Old -->
<button class="btn-primary text-sm">Submit</button>

<!-- New -->
<Button variant="primary" size="sm">Submit</Button>
```

**Inputs:**
```vue
<!-- Old -->
<label class="label">Email</label>
<input v-model="form.email" type="email" class="input-field" />

<!-- New -->
<Input v-model="form.email" type="email" label="Email" />
```

**Selects:**
```vue
<!-- Old -->
<label class="label">Country</label>
<select v-model="form.country" class="input-field">
  <option value="">Select country</option>
  <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
</select>

<!-- New -->
<Select
  v-model="form.country"
  :options="countries"
  label="Country"
  placeholder="Select country"
/>
```

## 🎯 Best Practices

1. **Use components for new features** - All new forms and UI elements should use the component versions
2. **Consistent sizing** - Use `size="sm"` for compact forms, `size="md"` (default) for standard forms
3. **Error handling** - Always pass error messages to the `error` prop for form validation
4. **Accessibility** - Components include proper ARIA attributes and labels
5. **Dark mode** - All components support dark mode automatically

## 📚 Resources

- [Flowbite Documentation](https://flowbite.com/docs/getting-started/introduction/)
- [Flowbite Components](https://flowbite.com/docs/components/buttons/)
- [Flowbite Datepicker](https://flowbite.com/docs/plugins/datepicker/)

## 🤝 Contributing

When adding new UI components:
1. Follow the Flowbite design system
2. Include proper TypeScript-style prop definitions
3. Support dark mode
4. Add error and validation states
5. Document the component in this README
