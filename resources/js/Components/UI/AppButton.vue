<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'success', 'danger', 'warning', 'ghost', 'gradient'].includes(v)
    },
    size: {
        type: String,
        default: 'md',
        validator: (s) => ['xs', 'sm', 'md', 'lg'].includes(s)
    },
    type: {
        type: String,
        default: 'button'
    },
    href: {
        type: String,
        default: null
    },
    as: {
        type: String,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const isExternal = computed(() => {
    return props.href && (props.href.startsWith('http://') || props.href.startsWith('https://') || props.href.startsWith('mailto:') || props.href.startsWith('tel:'));
});

const componentTag = computed(() => {
    if (props.as) return props.as;
    if (props.href) {
        return isExternal.value ? 'a' : Link;
    }
    return 'button';
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 shadow-sm focus:ring-slate-300';
        case 'success':
            return 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-500/20 border border-transparent focus:ring-emerald-500';
        case 'danger':
            return 'bg-red-600 text-white hover:bg-red-700 shadow-sm shadow-red-500/20 border border-transparent focus:ring-red-500';
        case 'warning':
            return 'bg-amber-600 text-white hover:bg-amber-700 shadow-sm shadow-amber-500/20 border border-transparent focus:ring-amber-500';
        case 'ghost':
            return 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 border border-transparent focus:ring-slate-200';
        case 'gradient':
            return 'bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white shadow-sm shadow-indigo-500/25 border border-transparent focus:ring-indigo-500';
        case 'primary':
        default:
            return 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-500/20 border border-transparent focus:ring-indigo-500';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs':
            return 'px-2.5 py-1 text-xs rounded-lg gap-1.5';
        case 'sm':
            return 'px-3 py-1.5 text-xs font-medium rounded-xl gap-1.5';
        case 'lg':
            return 'px-5 py-2.5 text-base font-semibold rounded-xl gap-2.5';
        case 'md':
        default:
            return 'px-4 py-2 text-sm font-medium rounded-xl gap-2';
    }
});
</script>

<template>
    <component
        :is="componentTag"
        :href="href"
        :type="componentTag === 'button' ? type : undefined"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center font-sans tracking-tight transition-all duration-150 ease-in-out select-none outline-none focus:ring-2 focus:ring-offset-2',
            variantClasses,
            sizeClasses,
            (disabled || loading) ? 'opacity-60 cursor-not-allowed pointer-events-none' : 'active:scale-[0.98]'
        ]"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-0.5 mr-2 h-4 w-4 text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <slot />
    </component>
</template>
