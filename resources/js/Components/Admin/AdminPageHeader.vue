<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <!-- Breadcrumbs if provided -->
            <nav v-if="breadcrumbs.length > 0" class="flex items-center gap-1.5 text-xs text-slate-400 mb-1.5 font-medium">
                <template v-for="(crumb, idx) in breadcrumbs" :key="idx">
                    <span v-if="idx > 0" class="text-slate-300">/</span>
                    <a
                        v-if="crumb.href"
                        :href="crumb.href"
                        class="hover:text-slate-700 transition"
                    >
                        {{ crumb.label }}
                    </a>
                    <span v-else class="text-slate-600 font-semibold">
                        {{ crumb.label }}
                    </span>
                </template>
            </nav>

            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                {{ title }}
            </h1>
            <p v-if="description" class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed max-w-2xl">
                {{ description }}
            </p>
        </div>

        <div v-if="$slots.actions" class="flex items-center gap-2.5 self-start sm:self-auto flex-wrap">
            <slot name="actions" />
        </div>
    </div>
</template>
