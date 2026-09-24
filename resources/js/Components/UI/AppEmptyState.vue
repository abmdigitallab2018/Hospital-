<script setup>
defineProps({
    title: {
        type: String,
        default: 'No records found'
    },
    description: {
        type: String,
        default: 'There are currently no items to display.'
    },
    icon: {
        type: String,
        default: 'folder'
    }
});
</script>

<template>
    <div class="text-center py-12 px-4 max-w-sm mx-auto">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3.5 shadow-2xs">
            <svg v-if="icon === 'search'" class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <svg v-else-if="icon === 'inbox'" class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
        </div>

        <h4 class="text-sm font-bold text-slate-900 tracking-tight">
            {{ title }}
        </h4>

        <p class="mt-1 text-xs text-slate-500 leading-relaxed">
            {{ description }}
        </p>

        <div v-if="$slots.actions" class="mt-5 flex justify-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
