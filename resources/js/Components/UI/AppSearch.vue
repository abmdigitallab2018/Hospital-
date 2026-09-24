<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Search...'
    },
    debounce: {
        type: Number,
        default: 300
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'clear']);

const localValue = ref(props.modelValue);
let timeout = null;

watch(() => props.modelValue, (newVal) => {
    localValue.value = newVal;
});

const onInput = (e) => {
    const val = e.target.value;
    localValue.value = val;
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        emit('update:modelValue', val);
    }, props.debounce);
};

const clearSearch = () => {
    localValue.value = '';
    clearTimeout(timeout);
    emit('update:modelValue', '');
    emit('clear');
};
</script>

<template>
    <div class="relative w-full max-w-sm">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg v-if="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <svg v-else class="animate-spin w-4 h-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        </div>

        <input
            type="text"
            :value="localValue"
            @input="onInput"
            :placeholder="placeholder"
            class="block w-full text-xs sm:text-sm pl-10 pr-9 py-2 rounded-xl bg-white border border-slate-300 placeholder:text-slate-400 text-slate-900 transition hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-xs outline-none"
        />

        <button
            v-if="localValue"
            type="button"
            @click="clearSearch"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
            aria-label="Clear search"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>
