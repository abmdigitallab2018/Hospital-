<script setup>
import { computed } from 'vue';

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    },
    perPage: {
        type: [Number, String],
        default: 10
    }
});

const emit = defineEmits(['page', 'update:perPage']);

const total = computed(() => props.pagination?.total || 0);
const from = computed(() => props.pagination?.from || 0);
const to = computed(() => props.pagination?.to || 0);
const currentPage = computed(() => props.pagination?.current_page || 1);
const lastPage = computed(() => props.pagination?.last_page || 1);

const links = computed(() => {
    return props.pagination?.links || [];
});

const onPageClick = (link) => {
    if (!link.url || link.active) return;
    try {
        const urlObj = new URL(link.url, window.location.origin);
        const page = urlObj.searchParams.get('page');
        if (page) {
            emit('page', Number(page));
        }
    } catch {
        // Fallback if URL parsing fails
    }
};
</script>

<template>
    <div class="px-5 py-4 bg-slate-50/75 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Records Counter & Per Page -->
        <div class="flex items-center justify-between sm:justify-start gap-4 text-xs text-slate-500">
            <span>
                Showing
                <span class="font-semibold text-slate-700">{{ from }}</span>
                to
                <span class="font-semibold text-slate-700">{{ to }}</span>
                of
                <span class="font-semibold text-slate-700">{{ total }}</span>
                records
            </span>

            <div class="flex items-center gap-1.5 pl-2 border-l border-slate-200">
                <label for="per-page-select" class="text-xs text-slate-400 hidden sm:inline">Per page:</label>
                <select
                    id="per-page-select"
                    :value="perPage"
                    @change="emit('update:perPage', Number($event.target.value))"
                    class="text-xs rounded-lg border-slate-300 py-1 pl-2 pr-6 bg-white text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-2xs"
                >
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Page Numbers Navigation -->
        <div v-if="lastPage > 1" class="flex items-center justify-center gap-1">
            <template v-for="(link, index) in links" :key="index">
                <button
                    v-if="link.label.includes('Previous')"
                    type="button"
                    :disabled="!link.url"
                    @click="onPageClick(link)"
                    :class="[
                        'px-2.5 py-1 text-xs font-medium rounded-lg transition',
                        link.url
                            ? 'text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
                            : 'text-slate-300 cursor-not-allowed'
                    ]"
                >
                    &lsaquo; Prev
                </button>

                <button
                    v-else-if="link.label.includes('Next')"
                    type="button"
                    :disabled="!link.url"
                    @click="onPageClick(link)"
                    :class="[
                        'px-2.5 py-1 text-xs font-medium rounded-lg transition',
                        link.url
                            ? 'text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
                            : 'text-slate-300 cursor-not-allowed'
                    ]"
                >
                    Next &rsaquo;
                </button>

                <button
                    v-else
                    type="button"
                    :disabled="!link.url || link.active"
                    @click="onPageClick(link)"
                    :class="[
                        'min-w-[32px] h-8 text-xs font-medium rounded-lg transition flex items-center justify-center',
                        link.active
                            ? 'bg-indigo-600 text-white font-bold shadow-xs shadow-indigo-500/20'
                            : (link.url ? 'text-slate-600 hover:bg-slate-200/70' : 'text-slate-300 cursor-not-allowed')
                    ]"
                    v-html="link.label"
                ></button>
            </template>
        </div>
    </div>
</template>
