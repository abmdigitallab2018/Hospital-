<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DataTableActions from '@/Components/DataTable/DataTableActions.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';

const props = defineProps({
    inquiries: {
        type: [Object, Array],
        required: true
    },
    unreadCount: {
        type: Number,
        default: 0
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            sort: 'created_at',
            direction: 'desc',
            per_page: 10
        })
    }
});

const currentFilters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    sort: props.filters?.sort || 'created_at',
    direction: props.filters?.direction || 'desc',
    per_page: props.filters?.per_page || 10
});

const loading = ref(false);

const applyFilters = () => {
    loading.value = true;
    router.get(
        route('admin.contacts.index'),
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
        route('admin.contacts.index'),
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

const confirmDelete = (inquiry) => {
    itemToDelete.value = inquiry;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.contacts.destroy', itemToDelete.value.id), {
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
    { key: 'sender', label: 'Sender', sortable: true },
    { key: 'subject', label: 'Subject & Preview', sortable: true },
    { key: 'created_at', label: 'Received', sortable: true, class: 'w-36' },
    { key: 'is_read', label: 'Status', sortable: true, align: 'center', class: 'w-28' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-28' }
];
</script>

<template>
    <AdminLayout>
        <Head title="Client Inquiries - Admin Panel" />

        <AdminPageHeader
            title="Client Inquiries"
            description="Review messages and project opportunities submitted by potential clients and hiring managers."
        >
            <template #actions>
                <AppBadge v-if="unreadCount > 0" variant="amber" dot>
                    {{ unreadCount }} Unread Messages
                </AppBadge>
            </template>
        </AdminPageHeader>

        <!-- DataTable -->
        <DataTable
            :data="inquiries"
            :columns="columns"
            :search="currentFilters.search"
            searchPlaceholder="Search by name, email, or message..."
            :sortKey="currentFilters.sort"
            :sortDirection="currentFilters.direction"
            :perPage="currentFilters.per_page"
            :loading="loading"
            emptyTitle="Inbox is empty"
            emptyDescription="No contact inquiries match your criteria."
            @update:search="handleSearch"
            @sort="handleSort"
            @page="handlePage"
            @update:perPage="handlePerPage"
        >
            <!-- Status Filter -->
            <template #filters>
                <select
                    v-model="currentFilters.status"
                    @change="applyFilters"
                    class="text-xs rounded-xl border-slate-300 py-2 pl-3 pr-8 bg-white text-slate-700 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-2xs"
                >
                    <option value="">All Inquiries</option>
                    <option value="unread">Unread Only</option>
                    <option value="read">Read Only</option>
                </select>
            </template>

            <!-- Sender Column -->
            <template #sender="{ item }">
                <div class="font-bold text-slate-900">{{ item.name }}</div>
                <a
                    :href="`mailto:${item.email}`"
                    class="text-xs text-indigo-600 hover:underline block truncate max-w-[180px]"
                >
                    {{ item.email }}
                </a>
            </template>

            <!-- Subject & Preview Column -->
            <template #subject="{ item }">
                <div :class="['text-xs font-semibold', !item.is_read ? 'text-slate-900 font-bold' : 'text-slate-700']">
                    {{ item.subject || 'No subject' }}
                </div>
                <div class="text-xs text-slate-500 line-clamp-1 mt-0.5 max-w-sm font-normal">
                    {{ item.message }}
                </div>
            </template>

            <!-- Status Column -->
            <template #is_read="{ item }">
                <AppBadge
                    :variant="!item.is_read ? 'amber' : 'slate'"
                    dot
                >
                    {{ !item.is_read ? 'Unread' : 'Read' }}
                </AppBadge>
            </template>

            <!-- Actions Column -->
            <template #actions="{ item }">
                <DataTableActions
                    v-if="item"
                    :item="item"
                    :actions="['view', 'delete']"
                    :viewHref="item?.id ? route('admin.contacts.show', item.id) : null"
                    @delete="confirmDelete"
                />
            </template>
        </DataTable>

        <!-- Delete Confirmation Modal Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Inquiry"
            :message="`Are you sure you want to delete the message from '${itemToDelete?.name}'? This action cannot be undone.`"
            confirmText="Delete Message"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @cancel="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
