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
import { Plus, ExternalLink, Tags } from 'lucide-vue-next';

const props = defineProps({
    portfolios: {
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
        route('admin.portfolios.index'),
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
        route('admin.portfolios.index'),
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

const confirmDelete = (item) => {
    itemToDelete.value = item;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.portfolios.destroy', itemToDelete.value.id), {
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
    { key: 'preview', label: 'Preview', class: 'w-20' },
    { key: 'title', label: 'Project Details', sortable: true },
    { key: 'category', label: 'Category', sortable: true, class: 'w-32' },
    { key: 'project_url', label: 'Live Link', class: 'w-40' },
    { key: 'is_active', label: 'Status', sortable: true, type: 'badge', align: 'center', class: 'w-28' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-28' }
];
</script>

<template>
    <AdminLayout>
        <Head title="Portfolio Projects - Admin Panel" />

        <AdminPageHeader
            title="Portfolio Projects"
            description="Showcase your web applications, APIs, client systems, and architecture work."
        >
            <template #actions>
                <div class="flex items-center gap-2">
                    <AppButton
                        :href="route('admin.categories.index')"
                        variant="secondary"
                        size="sm"
                    >
                        <Tags class="w-4 h-4 mr-1.5" />
                        <span>Category Master</span>
                    </AppButton>
                    <AppButton
                        :href="route('admin.portfolios.create')"
                        variant="primary"
                        size="sm"
                    >
                        <Plus class="w-4 h-4 mr-1.5" />
                        <span>Add Project</span>
                    </AppButton>
                </div>
            </template>
        </AdminPageHeader>

        <!-- DataTable -->
        <DataTable
            :data="portfolios"
            :columns="columns"
            :search="currentFilters.search"
            searchPlaceholder="Search projects..."
            :sortKey="currentFilters.sort"
            :sortDirection="currentFilters.direction"
            :perPage="currentFilters.per_page"
            :loading="loading"
            emptyTitle="No portfolio projects found"
            emptyDescription="Showcase your best engineering achievements by adding your first project."
            @update:search="handleSearch"
            @sort="handleSort"
            @page="handlePage"
            @update:perPage="handlePerPage"
        >
            <!-- Filters: Category and Status -->
            <template #filters>
                <select
                    v-model="currentFilters.category"
                    @change="applyFilters"
                    class="text-xs rounded-xl border-slate-300 py-2 pl-3 pr-8 bg-white text-slate-700 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-2xs"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">
                        {{ cat }}
                    </option>
                </select>

                <select
                    v-model="currentFilters.status"
                    @change="applyFilters"
                    class="text-xs rounded-xl border-slate-300 py-2 pl-3 pr-8 bg-white text-slate-700 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-2xs"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </template>

            <!-- Custom Preview Column -->
            <template #preview="{ item }">
                <div class="w-14 h-10 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center shadow-2xs">
                    <img
                        v-if="item.image_url"
                        :src="item.image_url"
                        :alt="item.title"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                    <span v-else class="text-[10px] text-slate-400 font-medium">No img</span>
                </div>
            </template>

            <!-- Custom Title Column -->
            <template #title="{ item }">
                <div class="font-bold text-slate-900">{{ item.title }}</div>
                <div class="text-xs text-slate-500 line-clamp-1 mt-0.5 max-w-sm">
                    {{ item.description || 'No description provided' }}
                </div>
            </template>

            <!-- Custom Category Column -->
            <template #category="{ item }">
                <AppBadge variant="slate" size="sm">
                    {{ item.category || 'General' }}
                </AppBadge>
            </template>

            <!-- Custom Live Link Column -->
            <template #project_url="{ item }">
                <a
                    v-if="item.project_url"
                    :href="item.project_url"
                    target="_blank"
                    class="text-xs text-indigo-600 hover:text-indigo-800 underline inline-flex items-center gap-1 font-medium"
                >
                    <span class="truncate max-w-[120px]">{{ item.project_url }}</span>
                    <ExternalLink class="w-3 h-3 flex-shrink-0" />
                </a>
                <span v-else class="text-slate-400 text-xs">&mdash;</span>
            </template>

            <!-- Custom Actions Column -->
            <template #actions="{ item }">
                <DataTableActions
                    v-if="item"
                    :item="item"
                    :actions="['edit', 'delete']"
                    :editHref="item?.id ? route('admin.portfolios.edit', item.id) : null"
                    @delete="confirmDelete"
                />
            </template>

            <!-- Empty State Action -->
            <template #emptyActions>
                <AppButton
                    :href="route('admin.portfolios.create')"
                    variant="primary"
                    size="sm"
                >
                    <Plus class="w-4 h-4 mr-1.5" />
                    <span>Create Project</span>
                </AppButton>
            </template>
        </DataTable>

        <!-- Delete Confirmation Modal Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Project"
            :message="`Are you sure you want to delete project '${itemToDelete?.title}'? This action cannot be undone.`"
            confirmText="Delete Project"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @cancel="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
