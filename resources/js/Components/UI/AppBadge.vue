<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'indigo',
        validator: (v) => ['indigo', 'emerald', 'success', 'amber', 'warning', 'rose', 'danger', 'slate', 'muted', 'violet'].includes(v)
    },
    size: {
        type: String,
        default: 'sm',
        validator: (s) => ['sm', 'md'].includes(s)
    },
    dot: {
        type: Boolean,
        default: false
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'emerald':
        case 'success':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200/70';
        case 'amber':
        case 'warning':
            return 'bg-amber-50 text-amber-700 border-amber-200/70';
        case 'rose':
        case 'danger':
            return 'bg-rose-50 text-rose-700 border-rose-200/70';
        case 'slate':
        case 'muted':
            return 'bg-slate-100 text-slate-700 border-slate-200';
        case 'violet':
            return 'bg-violet-50 text-violet-700 border-violet-200/70';
        case 'indigo':
        default:
            return 'bg-indigo-50 text-indigo-700 border-indigo-200/70';
    }
});

const dotClasses = computed(() => {
    switch (props.variant) {
        case 'emerald':
        case 'success':
            return 'bg-emerald-500';
        case 'amber':
        case 'warning':
            return 'bg-amber-500';
        case 'rose':
        case 'danger':
            return 'bg-rose-500';
        case 'slate':
        case 'muted':
            return 'bg-slate-400';
        case 'violet':
            return 'bg-violet-500';
        case 'indigo':
        default:
            return 'bg-indigo-500';
    }
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center font-medium rounded-full border shadow-2xs select-none',
            variantClasses,
            size === 'sm' ? 'px-2.5 py-0.5 text-[11px] gap-1.5' : 'px-3 py-1 text-xs gap-2'
        ]"
    >
        <span v-if="dot" :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', dotClasses]"></span>
        <slot />
    </span>
</template>
