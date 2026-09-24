<script setup>
import { computed } from 'vue';
import AppSearch from '../UI/AppSearch.vue';
import AppBadge from '../UI/AppBadge.vue';
import AppEmptyState from '../UI/AppEmptyState.vue';
import DataTablePagination from './DataTablePagination.vue';

const props = defineProps({
    data: {
        type: [Array, Object],
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    search: {
        type: String,
        default: ''
    },
    searchPlaceholder: {
        type: String,
        default: 'Search records...'
    },
    sortKey: {
        type: String,
        default: ''
    },
    sortDirection: {
        type: String,
        default: 'desc',
        validator: (d) => ['asc', 'desc'].includes(d)
    },
    perPage: {
        type: [Number, String],
        default: 10
    },
    loading: {
        type: Boolean,
        default: false
    },
    hideSearch: {
        type: Boolean,
        default: false
    },
    emptyTitle: {
        type: String,
        default: 'No records found'
    },
    emptyDescription: {
        type: String,
        default: 'There are no items to display matching your criteria.'
    }
});

const emit = defineEmits(['update:search', 'search', 'sort', 'page', 'update:perPage']);

// Determine if data is a Laravel paginator object or a plain array
const isPaginated = computed(() => {
    return props.data && typeof props.data === 'object' && Array.isArray(props.data.data);
});

const items = computed(() => {
    if (isPaginated.value) {
        return props.data.data;
    }
    return Array.isArray(props.data) ? props.data : [];
});

const paginationData = computed(() => {
    if (isPaginated.value) {
        return props.data;
    }
    return null;
});

const handleSearch = (val) => {
    emit('update:search', val);
    emit('search', val);
};

const handleSort = (key, sortable) => {
    if (!sortable) return;
    const newDir = props.sortKey === key && props.sortDirection === 'asc' ? 'desc' : 'asc';
    emit('sort', { key, direction: newDir });
};
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden transition-all duration-150">
        <!-- Toolbar: Search & Filters -->
        <div
            v-if="!hideSearch || $slots.filters || $slots.toolbarActions"
            class="p-4 sm:px-6 sm:py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3.5"
        >
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                <AppSearch
                    v-if="!hideSearch"
                    :modelValue="search"
                    :placeholder="searchPlaceholder"
                    :loading="loading"
                    @update:modelValue="handleSearch"
                />

                <div v-if="$slots.filters" class="flex items-center gap-2 flex-wrap">
                    <slot name="filters" />
                </div>
            </div>

            <div v-if="$slots.toolbarActions" class="flex items-center gap-2 self-start md:self-auto">
                <slot name="toolbarActions" />
            </div>
        </div>

        <!-- Table Container -->
        <div class="relative overflow-x-auto">
            <!-- Loading Overlay -->
            <div
                v-if="loading"
                class="absolute inset-0 bg-white/70 backdrop-blur-2xs z-10 flex items-center justify-center"
            >
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold shadow-lg">
                    <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Updating data...</span>
                </div>
            </div>

            <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                <thead class="bg-slate-50/80 text-slate-500 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-6 py-3.5',
                                col.align === 'center' ? 'text-center' : '',
                                col.align === 'right' ? 'text-right' : '',
                                col.sortable ? 'cursor-pointer select-none hover:text-slate-900 transition-colors' : '',
                                col.class || ''
                            ]"
                            @click="handleSort(col.key, col.sortable)"
                        >
                            <div
                                :class="[
                                    'inline-flex items-center gap-1.5',
                                    col.align === 'center' ? 'justify-center' : '',
                                    col.align === 'right' ? 'justify-end' : ''
                                ]"
                            >
                                <span>{{ col.label }}</span>
                                <span v-if="col.sortable" class="text-slate-400">
                                    <svg
                                        v-if="sortKey === col.key && sortDirection === 'asc'"
                                        class="w-3.5 h-3.5 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                    <svg
                                        v-else-if="sortKey === col.key && sortDirection === 'desc'"
                                        class="w-3.5 h-3.5 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-3.5 h-3.5 text-slate-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr
                        v-for="(item, rowIndex) in items"
                        :key="item.id || rowIndex"
                        class="hover:bg-slate-50/75 transition-colors"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-6 py-4 text-slate-700',
                                col.align === 'center' ? 'text-center' : '',
                                col.align === 'right' ? 'text-right' : '',
                                col.tdClass || ''
                            ]"
                        >
                            <!-- Custom Named Slot for column -->
                            <slot
                                :name="col.key"
                                :item="item"
                                :value="item[col.key]"
                                :index="rowIndex"
                            >
                                <!-- Default Rendering based on type -->
                                <template v-if="col.type === 'badge'">
                                    <AppBadge
                                        :variant="item[col.key] ? 'emerald' : 'slate'"
                                        dot
                                    >
                                        {{ item[col.key] ? (col.activeText || 'Active') : (col.inactiveText || 'Inactive') }}
                                    </AppBadge>
                                </template>

                                <template v-else-if="col.type === 'date'">
                                    <span class="text-xs text-slate-500 font-normal">
                                        {{ item[col.key] }}
                                    </span>
                                </template>

                                <template v-else>
                                    {{ item[col.key] }}
                                </template>
                            </slot>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="items.length === 0">
                        <td :colspan="columns.length" class="p-0">
                            <AppEmptyState
                                :title="search ? `No results found for &ldquo;${search}&rdquo;` : emptyTitle"
                                :description="search ? 'Try adjusting your search terms or filters.' : emptyDescription"
                                :icon="search ? 'search' : 'folder'"
                            >
                                <template #actions>
                                    <slot name="emptyActions" />
                                </template>
                            </AppEmptyState>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <DataTablePagination
            v-if="paginationData"
            :pagination="paginationData"
            :perPage="perPage"
            @page="emit('page', $event)"
            @update:perPage="emit('update:perPage', $event)"
        />
    </div>
</template>
