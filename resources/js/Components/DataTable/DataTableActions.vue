<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    actions: {
        type: Array,
        default: () => ['view', 'edit', 'delete']
    },
    viewHref: {
        type: String,
        default: null
    },
    editHref: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['view', 'edit', 'delete']);
const menuOpen = ref(false);

const handleView = () => emit('view', props.item);
const handleEdit = () => emit('edit', props.item);
const handleDelete = () => emit('delete', props.item);
</script>

<template>
    <div class="flex items-center justify-end gap-1">
        <!-- Desktop Action Buttons -->
        <div class="hidden sm:flex items-center gap-1">
            <!-- View Action -->
            <template v-if="actions.includes('view')">
                <Link
                    v-if="viewHref"
                    :href="viewHref"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition duration-150"
                    title="View details"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </Link>
                <button
                    v-else
                    type="button"
                    @click="handleView"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition duration-150"
                    title="View details"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </template>

            <!-- Edit Action -->
            <template v-if="actions.includes('edit')">
                <Link
                    v-if="editHref"
                    :href="editHref"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition duration-150"
                    title="Edit record"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </Link>
                <button
                    v-else
                    type="button"
                    @click="handleEdit"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition duration-150"
                    title="Edit record"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
            </template>

            <!-- Delete Action -->
            <button
                v-if="actions.includes('delete')"
                type="button"
                @click="handleDelete"
                class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition duration-150"
                title="Delete record"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>

        <!-- Mobile Action Menu -->
        <div class="sm:hidden relative">
            <button
                type="button"
                @click="menuOpen = !menuOpen"
                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100"
                aria-label="Actions"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>

            <div
                v-if="menuOpen"
                @click.away="menuOpen = false"
                class="absolute right-0 mt-1 w-32 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-20 text-xs"
            >
                <template v-if="actions.includes('view')">
                    <Link
                        v-if="viewHref"
                        :href="viewHref"
                        class="block px-3 py-2 text-slate-700 hover:bg-slate-50"
                    >
                        View
                    </Link>
                    <button
                        v-else
                        type="button"
                        @click="handleView(); menuOpen = false"
                        class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-50"
                    >
                        View
                    </button>
                </template>

                <template v-if="actions.includes('edit')">
                    <Link
                        v-if="editHref"
                        :href="editHref"
                        class="block px-3 py-2 text-slate-700 hover:bg-slate-50"
                    >
                        Edit
                    </Link>
                    <button
                        v-else
                        type="button"
                        @click="handleEdit(); menuOpen = false"
                        class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-50"
                    >
                        Edit
                    </button>
                </template>

                <button
                    v-if="actions.includes('delete')"
                    type="button"
                    @click="handleDelete(); menuOpen = false"
                    class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 font-medium"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>
