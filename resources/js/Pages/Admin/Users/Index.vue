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
import { UserPlus, UserCheck, Shield } from 'lucide-vue-next';

const props = defineProps({
    users: {
        type: [Object, Array],
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            sort: 'created_at',
            direction: 'desc',
            per_page: 10
        })
    }
});

const currentFilters = reactive({
    search: props.filters?.search || '',
    sort: props.filters?.sort || 'created_at',
    direction: props.filters?.direction || 'desc',
    per_page: props.filters?.per_page || 10
});

const loading = ref(false);

const applyFilters = () => {
    loading.value = true;
    router.get(
        route('admin.users.index'),
        {
            search: currentFilters.search || undefined,
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
        route('admin.users.index'),
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

const confirmDelete = (user) => {
    itemToDelete.value = user;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.users.destroy', itemToDelete.value.id), {
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
    { key: 'user', label: 'Administrator', sortable: true },
    { key: 'role', label: 'Access Role', align: 'center', class: 'w-32' },
    { key: 'created_at', label: 'Joined Date', sortable: true, class: 'w-36' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-28' }
];
</script>

<template>
    <AdminLayout>
        <Head title="Admin Users - Admin Panel" />

        <AdminPageHeader
            title="User & Admin Management"
            description="Manage administrative accounts, credentials, and password access for the back-office panel."
            :breadcrumbs="[
                { label: 'Admin', href: route('admin.dashboard') },
                { label: 'Users' }
            ]"
        >
            <template #actions>
                <AppButton
                    :href="route('admin.users.create')"
                    variant="primary"
                    size="sm"
                >
                    <UserPlus class="w-4 h-4 mr-1.5" />
                    <span>Add New User</span>
                </AppButton>
            </template>
        </AdminPageHeader>

        <!-- DataTable -->
        <DataTable
            :data="users"
            :columns="columns"
            :search="currentFilters.search"
            searchPlaceholder="Search users by name or email..."
            :sortKey="currentFilters.sort"
            :sortDirection="currentFilters.direction"
            :perPage="currentFilters.per_page"
            :loading="loading"
            emptyTitle="No users found"
            emptyDescription="No administrative users match your search criteria."
            @update:search="handleSearch"
            @sort="handleSort"
            @page="handlePage"
            @update:perPage="handlePerPage"
        >
            <!-- User Column -->
            <template #user="{ item }">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold text-xs flex items-center justify-center shadow-2xs flex-shrink-0">
                        {{ item.name ? item.name.substring(0, 2).toUpperCase() : 'U' }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 flex items-center gap-2">
                            <span>{{ item.name }}</span>
                            <span v-if="item.is_current_user" class="text-[10px] font-semibold bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded border border-indigo-200">
                                You
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 flex items-center gap-1.5 flex-wrap">
                            <span v-if="item.username" class="font-mono text-indigo-600 bg-indigo-50 px-1 py-0.5 rounded text-[11px] font-medium">@{{ item.username }}</span>
                            <span>{{ item.email }}</span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Role Column -->
            <template #role>
                <AppBadge variant="indigo" dot>
                    Administrator
                </AppBadge>
            </template>

            <!-- Actions Column -->
            <template #actions="{ item }">
                <DataTableActions
                    v-if="item"
                    :item="item"
                    :actions="item?.is_current_user ? ['edit'] : ['edit', 'delete']"
                    :editHref="item?.id ? route('admin.users.edit', item.id) : null"
                    @delete="confirmDelete"
                />
            </template>

            <!-- Empty State Action -->
            <template #emptyActions>
                <AppButton
                    :href="route('admin.users.create')"
                    variant="primary"
                    size="sm"
                >
                    <UserPlus class="w-4 h-4 mr-1.5" />
                    <span>Create User</span>
                </AppButton>
            </template>
        </DataTable>

        <!-- Delete Confirmation Modal Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete User Account"
            :message="`Are you sure you want to permanently delete '${itemToDelete?.name}' (${itemToDelete?.email})? This user will immediately lose access to the admin dashboard.`"
            confirmText="Delete User"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @cancel="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
