<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DataTableActions from '@/Components/DataTable/DataTableActions.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import { Plus, Newspaper, Calendar, Eye, Clock, FileText } from 'lucide-vue-next';

const props = defineProps({
    posts: {
        type: [Object, Array],
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            category: '',
            status: '',
            sort: 'created_at',
            direction: 'desc',
            per_page: 10
        })
    }
});

const currentFilters = reactive({
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    status: props.filters?.status || '',
    sort: props.filters?.sort || 'created_at',
    direction: props.filters?.direction || 'desc',
    per_page: props.filters?.per_page || 10
});

const loading = ref(false);

const applyFilters = () => {
    loading.value = true;
    router.get(
        route('admin.posts.index'),
        {
            search: currentFilters.search || undefined,
            category: currentFilters.category || undefined,
            status: currentFilters.status || undefined,
            sort: currentFilters.sort,
            direction: currentFilters.direction,
            per_page: currentFilters.per_page
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            }
        }
    );
};

const handleSearch = (val) => {
    currentFilters.search = val;
    applyFilters();
};

const handleSort = ({ key, direction }) => {
    currentFilters.sort = key;
    currentFilters.direction = direction;
    applyFilters();
};

const handlePage = (pageNumber) => {
    loading.value = true;
    router.get(
        route('admin.posts.index'),
        {
            ...currentFilters,
            page: pageNumber
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            }
        }
    );
};

const handlePerPage = (perPage) => {
    currentFilters.per_page = perPage;
    applyFilters();
};

// Deletion Confirmation Dialog State
const deleteModalOpen = ref(false);
const itemToDelete = ref(null);
const deleteLoading = ref(false);

const confirmDelete = (post) => {
    itemToDelete.value = post;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.posts.destroy', itemToDelete.value.id), {
        onSuccess: () => {
            deleteModalOpen.value = false;
            itemToDelete.value = null;
        },
        onFinish: () => {
            deleteLoading.value = false;
        }
    });
};

const columns = [
    { key: 'preview', label: 'Preview', class: 'w-16' },
    { key: 'title', label: 'Article / Post Details', sortable: true },
    { key: 'category', label: 'Category', sortable: true, class: 'w-32 hidden sm:table-cell' },
    { key: 'read_time', label: 'Read Time', class: 'w-28 hidden md:table-cell' },
    { key: 'is_published', label: 'Status', sortable: true, align: 'center', class: 'w-28' },
    { key: 'published_at', label: 'Published', sortable: true, class: 'w-32 hidden lg:table-cell' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-24' }
];
</script>

<template>
    <AdminLayout>
        <Head title="Daily Posts & Updates - Admin Panel" />

        <AdminPageHeader
            title="Daily Posts & Architecture Updates"
            description="Publish daily engineering insights, technical guides, system architecture breakdowns, and useful updates."
            :breadcrumbs="[
                { label: 'Admin', href: route('admin.dashboard') },
                { label: 'Posts' }
            ]"
        >
            <template #actions>
                <AppButton
                    :href="route('admin.posts.create')"
                    variant="primary"
                    size="sm"
                >
                    <Plus class="w-4 h-4 mr-1.5" />
                    <span>Create Daily Post</span>
                </AppButton>
            </template>
        </AdminPageHeader>

        <!-- DataTable -->
        <DataTable
            :columns="columns"
            :data="posts"
            :loading="loading"
            search-placeholder="Search posts by title, summary or category..."
            :initial-search="currentFilters.search"
            :initial-sort-key="currentFilters.sort"
            :initial-sort-direction="currentFilters.direction"
            :initial-per-page="currentFilters.per_page"
            @search="handleSearch"
            @sort="handleSort"
            @page-change="handlePage"
            @per-page-change="handlePerPage"
        >
            <template #filters>
                <div class="flex items-center gap-2">
                    <select
                        v-model="currentFilters.category"
                        @change="applyFilters"
                        class="block w-full sm:w-40 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    >
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">
                            {{ cat }}
                        </option>
                    </select>

                    <select
                        v-model="currentFilters.status"
                        @change="applyFilters"
                        class="block w-full sm:w-36 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    >
                        <option value="">All Statuses</option>
                        <option value="published">Published Only</option>
                        <option value="draft">Draft Only</option>
                    </select>
                </div>
            </template>

            <!-- Column: Thumbnail / Icon Preview -->
            <template #preview="{ item }">
                <div class="w-12 h-10 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                    <img
                        v-if="item.image_url"
                        :src="item.image_url"
                        :alt="item.title"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                    <FileText v-else class="w-5 h-5 text-slate-400" />
                </div>
            </template>

            <!-- Column: Title & Excerpt -->
            <template #title="{ item }">
                <div class="space-y-0.5">
                    <span class="font-bold text-slate-900 text-sm block hover:text-indigo-600 transition">
                        {{ item.title }}
                    </span>
                    <p class="text-xs text-slate-500 line-clamp-1">
                        {{ item.summary || 'No summary provided.' }}
                    </p>
                </div>
            </template>

            <!-- Column: Category -->
            <template #category="{ item }">
                <AppBadge variant="indigo" size="sm">
                    {{ item.category || 'General' }}
                </AppBadge>
            </template>

            <!-- Column: Read Time -->
            <template #read_time="{ item }">
                <span class="inline-flex items-center gap-1 text-xs text-slate-500 font-mono">
                    <Clock class="w-3.5 h-3.5 text-slate-400" />
                    <span>{{ item.read_time || '3 min' }}</span>
                </span>
            </template>

            <!-- Column: Status -->
            <template #is_published="{ item }">
                <AppBadge :variant="item.is_published ? 'success' : 'neutral'" size="sm">
                    {{ item.is_published ? 'Published' : 'Draft' }}
                </AppBadge>
            </template>

            <!-- Column: Published Date -->
            <template #published_at="{ item }">
                <span class="text-xs text-slate-600 font-mono">
                    {{ item.published_at || '—' }}
                </span>
            </template>

            <!-- Column: Actions -->
            <template #actions="{ item }">
                <DataTableActions
                    v-if="item"
                    @edit="router.get(route('admin.posts.edit', item.id))"
                    @delete="confirmDelete(item)"
                />
            </template>

            <template #empty>
                <div class="py-12 text-center">
                    <Newspaper class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <h3 class="text-sm font-bold text-slate-700">No daily posts found</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Share useful engineering updates, architecture lessons, or daily dev notes with your audience.
                    </p>
                    <div class="mt-4">
                        <AppButton
                            :href="route('admin.posts.create')"
                            variant="primary"
                            size="sm"
                        >
                            <Plus class="w-4 h-4 mr-1.5" />
                            <span>Create Daily Post</span>
                        </AppButton>
                    </div>
                </div>
            </template>
        </DataTable>

        <!-- Delete Confirmation Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Post"
            :message="`Are you sure you want to delete '${itemToDelete?.title}'? This action cannot be undone.`"
            confirm-text="Delete Post"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @cancel="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
