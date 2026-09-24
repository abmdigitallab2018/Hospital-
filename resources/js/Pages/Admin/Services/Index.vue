<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DataTableActions from '@/Components/DataTable/DataTableActions.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    services: {
        type: [Object, Array],
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            sort: 'sort_order',
            direction: 'asc',
            per_page: 10
        })
    }
});

const currentFilters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    sort: props.filters?.sort || 'sort_order',
    direction: props.filters?.direction || 'asc',
    per_page: props.filters?.per_page || 10
});

const loading = ref(false);

const applyFilters = () => {
    loading.value = true;
    router.get(
        route('admin.services.index'),
        {
            search: currentFilters.search || undefined,
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
        route('admin.services.index'),
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

const confirmDelete = (service) => {
    itemToDelete.value = service;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.services.destroy', itemToDelete.value.id), {
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
    { key: 'icon', label: 'Icon', align: 'center', class: 'w-16' },
    { key: 'title', label: 'Service & Description', sortable: true },
    { key: 'sort_order', label: 'Sort Order', sortable: true, align: 'center', class: 'w-28' },
    { key: 'is_active', label: 'Status', sortable: true, type: 'badge', align: 'center', class: 'w-28' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-28' }
];
</script>

<template>
    <AdminLayout>
        <Head title="Services - Admin Panel" />

        <AdminPageHeader
            title="Services"
            description="Define your capabilities, consulting offerings, and technical expertise displayed on your portfolio."
        >
            <template #actions>
                <AppButton
                    :href="route('admin.services.create')"
                    variant="primary"
                    size="sm"
                >
                    <Plus class="w-4 h-4 mr-1.5" />
                    <span>Add Service</span>
                </AppButton>
            </template>
        </AdminPageHeader>

        <!-- DataTable with Server-side Operations -->
        <DataTable
            :data="services"
            :columns="columns"
            :search="currentFilters.search"
            searchPlaceholder="Search services..."
            :sortKey="currentFilters.sort"
            :sortDirection="currentFilters.direction"
            :perPage="currentFilters.per_page"
            :loading="loading"
            emptyTitle="No services found"
            emptyDescription="Create your first service or adjust your search filter."
            @update:search="handleSearch"
            @sort="handleSort"
            @page="handlePage"
            @update:perPage="handlePerPage"
        >
            <!-- Filter Slot: Status Filter -->
            <template #filters>
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

            <!-- Custom Icon Column -->
            <template #icon="{ item }">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base shadow-2xs border border-indigo-100 mx-auto">
                    {{ item.icon ? item.icon.substring(0, 4) : '⚡' }}
                </div>
            </template>

            <!-- Custom Title & Description Column -->
            <template #title="{ item }">
                <div class="font-bold text-slate-900">{{ item.title }}</div>
                <div class="text-xs text-slate-500 line-clamp-1 mt-0.5 max-w-md">
                    {{ item.description }}
                </div>
            </template>

            <!-- Custom Actions Column -->
            <template #actions="{ item }">
                <DataTableActions
                    v-if="item"
                    :item="item"
                    :actions="['edit', 'delete']"
                    :editHref="item?.id ? route('admin.services.edit', item.id) : null"
                    @delete="confirmDelete"
                />
            </template>

            <!-- Empty State Action -->
            <template #emptyActions>
                <AppButton
                    :href="route('admin.services.create')"
                    variant="primary"
                    size="sm"
                >
                    <Plus class="w-4 h-4 mr-1.5" />
                    <span>Create Service</span>
                </AppButton>
            </template>
        </DataTable>

        <!-- Delete Confirmation Modal Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Service"
            :message="`Are you sure you want to delete '${itemToDelete?.title}'? This action cannot be undone.`"
            confirmText="Delete Service"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @cancel="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
