<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    label: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'text'
    },
    placeholder: {
        type: String,
        default: ''
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
        default: () => `input-${Math.random().toString(36).substring(2, 9)}`
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

const updateValue = (e) => {
    emit('update:modelValue', e.target.value);
};
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
            <div v-if="$slots.prefix" class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <slot name="prefix" />
            </div>

            <input
                :id="id"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                @input="updateValue"
                :class="[
                    'block w-full text-sm rounded-xl transition duration-150 ease-in-out bg-white text-slate-900 placeholder:text-slate-400',
                    $slots.prefix ? 'pl-10' : 'pl-3.5',
                    $slots.suffix ? 'pr-10' : 'pr-3.5',
                    'py-2.5',
                    error
                        ? 'border-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-500/20'
                        : 'border-slate-300 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20',
                    disabled ? 'bg-slate-50 text-slate-500 cursor-not-allowed border-slate-200' : ''
                ]"
            />

            <div v-if="$slots.suffix" class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                <slot name="suffix" />
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
