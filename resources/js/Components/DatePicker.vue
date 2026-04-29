<script setup>
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { id } from 'date-fns/locale';
import { computed, ref } from 'vue';
import { CalendarDays } from 'lucide-vue-next';

const props = defineProps({
    modelValue: [String, Date],
    placeholder: { type: String, default: 'Pilih tanggal' },
    minDate: [String, Date],
    maxDate: [String, Date],
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    id: { type: String, default: '' },
    enableTimePicker: { type: Boolean, default: false },
    autoApply: { type: Boolean, default: true },
    textInput: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);

// Detect dark mode
const isDark = computed(() => {
    if (typeof window !== 'undefined') {
        return document.documentElement.classList.contains('dark');
    }
    return false;
});

// Internal date state
const internalDate = computed({
    get() {
        return props.modelValue || null;
    },
    set(val) {
        if (val instanceof Date) {
            // Format to YYYY-MM-DD string
            const year = val.getFullYear();
            const month = String(val.getMonth() + 1).padStart(2, '0');
            const day = String(val.getDate()).padStart(2, '0');
            emit('update:modelValue', `${year}-${month}-${day}`);
        } else if (typeof val === 'string') {
            emit('update:modelValue', val);
        } else {
            emit('update:modelValue', null);
        }
    }
});

const format = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <VueDatePicker
        v-model="internalDate"
        :locale="id"
        :format="format"
        :placeholder="placeholder"
        :min-date="minDate"
        :max-date="maxDate"
        :required="required"
        :disabled="disabled"
        :uid="id"
        :enable-time-picker="enableTimePicker"
        :auto-apply="autoApply"
        :text-input="textInput"
        :dark="isDark"
        :day-names="['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']"
        month-name-format="long"
        week-start="1"
    >
        <template #input-icon>
            <CalendarDays class="w-5 h-5 text-gray-400 ml-3" />
        </template>
    </VueDatePicker>
</template>

<style>
/* ============================
   Kebunku Date Picker Theme
   ============================ */

/* Light mode */
.dp__theme_light {
    --dp-background-color: #ffffff;
    --dp-text-color: #111827;
    --dp-hover-color: #f0fdf4;
    --dp-hover-text-color: #111827;
    --dp-hover-icon-color: #059669;
    --dp-primary-color: #10b981;
    --dp-primary-disabled-color: #6ee7b7;
    --dp-primary-text-color: #ffffff;
    --dp-secondary-color: #d1d5db;
    --dp-border-color: #e5e7eb;
    --dp-menu-border-color: #e5e7eb;
    --dp-input-border-color: #e5e7eb;
    --dp-input-background-color: #ffffff;
    --dp-disabled-color: #f3f4f6;
    --dp-scroll-bar-background: #f3f4f6;
    --dp-scroll-bar-color: #d1d5db;
    --dp-success-color: #10b981;
    --dp-success-color-disabled: #6ee7b7;
    --dp-icon-color: #6b7280;
    --dp-danger-color: #ef4444;
    --dp-marker-color: #10b981;
    --dp-tooltip-color: #374151;
    --dp-highlight-color: rgba(16, 185, 129, 0.1);
    --dp-input-padding: 10px 12px 10px 40px;
}

/* Dark mode */
.dp__theme_dark {
    --dp-background-color: #1a241f;
    --dp-text-color: #f3f4f6;
    --dp-hover-color: rgba(16, 185, 129, 0.15);
    --dp-hover-text-color: #ffffff;
    --dp-hover-icon-color: #34d399;
    --dp-primary-color: #10b981;
    --dp-primary-disabled-color: #065f46;
    --dp-primary-text-color: #ffffff;
    --dp-secondary-color: #4b5563;
    --dp-border-color: rgba(255, 255, 255, 0.1);
    --dp-menu-border-color: rgba(255, 255, 255, 0.1);
    --dp-input-border-color: rgba(255, 255, 255, 0.1);
    --dp-input-background-color: #1a241f;
    --dp-disabled-color: #131b17;
    --dp-scroll-bar-background: #131b17;
    --dp-scroll-bar-color: #374151;
    --dp-success-color: #10b981;
    --dp-success-color-disabled: #065f46;
    --dp-icon-color: #9ca3af;
    --dp-danger-color: #ef4444;
    --dp-marker-color: #10b981;
    --dp-tooltip-color: #e5e7eb;
    --dp-highlight-color: rgba(16, 185, 129, 0.2);
    --dp-input-padding: 10px 12px 10px 40px;
}

/* Shared styling */
.dp__input {
    border-radius: 0.75rem !important;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.dp__input:hover {
    border-color: #10b981;
}

.dp__input:focus,
.dp__input_focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
}

.dp__menu {
    border-radius: 1rem !important;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}

.dp__cell_inner {
    border-radius: 0.5rem;
    font-weight: 500;
}

.dp__today {
    border-color: #10b981 !important;
}

.dp__action_button {
    border-radius: 0.5rem !important;
    font-weight: 600;
}

.dp__arrow_top,
.dp__arrow_bottom {
    display: none;
}

.dp__calendar_header_item {
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.05em;
}

.dp__month_year_select {
    font-weight: 700;
}

.dp__button {
    border-radius: 0.5rem;
}
</style>
