<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        default: ''
    },
    label: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    options: {
        type: Array,
        default: () => []
    },
    error: {
        type: String,
        default: ''
    },
    hint: {
        type: String,
        default: ''
    },
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substring(2, 9)}`
    },
    disabled: {
        type: Boolean,
        default: false
    },
    required: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

// Normalize options to [{ value, label }]
const normalizedOptions = computed(() => {
    return props.options.map(opt => {
        if (typeof opt === 'object' && opt !== null) {
            return {
                value: opt.value !== undefined ? opt.value : opt.id,
                label: opt.label !== undefined ? opt.label : (opt.name || opt.title || opt.value)
            };
        }
        return { value: opt, label: opt };
    });
});
</script>

<template>
    <div class="w-full">
        <div v-if="label" class="flex items-center justify-between mb-1.5">
            <label :for="id" class="block text-xs font-semibold text-slate-700">
                {{ label }}
                <span v-if="required" class="text-red-500 ml-0.5">*</span>
            </label>
            <span v-if="hint && !error" class="text-[11px] text-slate-400">{{ hint }}</span>
        </div>

        <div class="relative rounded-xl shadow-xs">
            <select
                :id="id"
                :value="modelValue"
                :disabled="disabled"
                :required="required"
                @change="emit('update:modelValue', $event.target.value)"
                :class="[
                    'block w-full text-sm rounded-xl transition duration-150 ease-in-out bg-white text-slate-900 py-2.5 pl-3.5 pr-10 appearance-none',
                    error
                        ? 'border-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-500/20'
                        : 'border-slate-300 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20',
                    disabled ? 'bg-slate-50 text-slate-500 cursor-not-allowed border-slate-200' : ''
                ]"
            >
                <option v-if="placeholder" value="">{{ placeholder }}</option>
                <option
                    v-for="opt in normalizedOptions"
                    :key="opt.value"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </option>
            </select>

            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <p v-if="error" class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
    </div>
</template>
