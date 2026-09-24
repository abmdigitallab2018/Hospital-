<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [Number, String],
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    href: {
        type: String,
        default: null
    },
    hrefText: {
        type: String,
        default: 'View all'
    },
    variant: {
        type: String,
        default: 'indigo',
        validator: (v) => ['indigo', 'emerald', 'amber', 'purple', 'rose'].includes(v)
    }
});

const iconVariantClasses = computed(() => {
    switch (props.variant) {
        case 'emerald':
            return 'bg-emerald-50 text-emerald-600 border border-emerald-100';
        case 'amber':
            return 'bg-amber-50 text-amber-600 border border-amber-100';
        case 'purple':
            return 'bg-purple-50 text-purple-600 border border-purple-100';
        case 'rose':
            return 'bg-rose-50 text-rose-600 border border-rose-100';
        case 'indigo':
        default:
            return 'bg-indigo-50 text-indigo-600 border border-indigo-100';
    }
});

const linkClasses = computed(() => {
    switch (props.variant) {
        case 'emerald':
            return 'text-emerald-600 hover:text-emerald-800';
        case 'amber':
            return 'text-amber-600 hover:text-amber-800';
        case 'purple':
            return 'text-purple-600 hover:text-purple-800';
        case 'rose':
            return 'text-rose-600 hover:text-rose-800';
        case 'indigo':
        default:
            return 'text-indigo-600 hover:text-indigo-800';
    }
});
</script>

<template>
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-sm transition-all duration-150 flex flex-col justify-between">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                    {{ title }}
                </p>
                <p class="text-3xl font-extrabold text-slate-900 tracking-tight mt-2 font-mono">
                    {{ value }}
                </p>
                <p v-if="description" class="text-xs text-slate-500 mt-1">
                    {{ description }}
                </p>
            </div>

            <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-2xs', iconVariantClasses]">
                <slot name="icon" />
            </div>
        </div>

        <div v-if="href" class="mt-4 pt-3 border-t border-slate-100">
            <Link
                :href="href"
                :class="['text-xs font-semibold inline-flex items-center gap-1 transition-colors', linkClasses]"
            >
                <span>{{ hrefText }}</span>
                <span>&rarr;</span>
            </Link>
        </div>
    </div>
</template>
