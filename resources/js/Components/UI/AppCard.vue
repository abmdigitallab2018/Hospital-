<script setup>
defineProps({
    title: {
        type: String,
        default: ''
    },
    subtitle: {
        type: String,
        default: ''
    },
    padding: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden transition-all duration-150">
        <!-- Card Header -->
        <div
            v-if="title || $slots.header || $slots.actions"
            class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
        >
            <div>
                <slot name="header">
                    <h3 v-if="title" class="text-base font-semibold text-slate-900 tracking-tight">
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-xs text-slate-500 mt-0.5">
                        {{ subtitle }}
                    </p>
                </slot>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>

        <!-- Card Body -->
        <div :class="padding ? 'p-5 sm:p-6' : ''">
            <slot />
        </div>

        <!-- Card Footer -->
        <div
            v-if="$slots.footer"
            class="px-5 py-3.5 bg-slate-50/75 border-t border-slate-100 flex items-center justify-between"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
