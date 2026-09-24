<script setup>
import { ref, reactive } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DataTableActions from '@/Components/DataTable/DataTableActions.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import {
    Mail,
    UserPlus,
    Users,
    CheckCircle2,
    XCircle,
    Calendar,
    Send,
    TrendingUp,
    RefreshCw
} from 'lucide-vue-next';

const props = defineProps({
    subscribers: {
        type: [Object, Array],
        required: true
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            unsubscribed: 0,
            recent: 0
        })
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            sort: 'subscribed_at',
            direction: 'desc',
            per_page: 10
        })
    }
});

const currentFilters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    sort: props.filters?.sort || 'subscribed_at',
    direction: props.filters?.direction || 'desc',
    per_page: props.filters?.per_page || 10
});

const loading = ref(false);

const applyFilters = () => {
    loading.value = true;
    router.get(
        route('admin.newsletter.index'),
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
        route('admin.newsletter.index'),
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

// Add Subscriber Modal State
const addModalOpen = ref(false);
const form = useForm({
    email: '',
    name: '',
    is_active: true,
});

const openAddModal = () => {
    form.reset();
    form.clearErrors();
    form.is_active = true;
    addModalOpen.value = true;
};

const submitAddSubscriber = () => {
    form.post(route('admin.newsletter.store'), {
        onSuccess: () => {
            addModalOpen.value = false;
            form.reset();
        }
    });
};

// Toggle Subscriber Status
const toggleStatus = (sub) => {
    router.post(route('admin.newsletter.toggle', sub.id), {}, {
        preserveScroll: true
    });
};

// Deletion Confirmation Dialog State
const deleteModalOpen = ref(false);
const itemToDelete = ref(null);
const deleteLoading = ref(false);

const confirmDelete = (sub) => {
    itemToDelete.value = sub;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.newsletter.destroy', itemToDelete.value.id), {
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
    { key: 'email', label: 'Subscriber Email', sortable: true },
    { key: 'name', label: 'Name', sortable: true, class: 'w-40 hidden sm:table-cell' },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', class: 'w-28' },
    { key: 'subscribed_at', label: 'Subscribed Date', sortable: true, class: 'w-40 hidden md:table-cell' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-32' }
];
</script>

<template>
    <AdminLayout>
        <Head title="Newsletter Subscribers - Admin Panel" />

        <AdminPageHeader
            title="Newsletter Subscribers Master"
            description="Manage your audience, mailing list subscribers, and email notification campaigns."
            :breadcrumbs="[
                { label: 'Admin', href: route('admin.dashboard') },
                { label: 'Newsletter' }
            ]"
        >
            <template #actions>
                <AppButton
                    @click="openAddModal"
                    variant="primary"
                    size="sm"
                >
                    <UserPlus class="w-4 h-4 mr-1.5" />
                    <span>Add Subscriber</span>
                </AppButton>
            </template>
        </AdminPageHeader>

        <!-- KPI Stats Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Audience</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <Users class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 font-mono mt-2">
                    {{ stats.total }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">All recorded email subscribers</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Subscribers</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <CheckCircle2 class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-600 font-mono mt-2">
                    {{ stats.active }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Receiving weekly engineering posts</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unsubscribed</span>
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center">
                        <XCircle class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-600 font-mono mt-2">
                    {{ stats.unsubscribed }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Opted out of email broadcasts</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Past 7 Days</span>
                    <div class="w-8 h-8 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                        <TrendingUp class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-violet-600 font-mono mt-2">
                    +{{ stats.recent }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">New signups this week</p>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable
            :columns="columns"
            :data="subscribers"
            :loading="loading"
            search-placeholder="Search subscribers by email or name..."
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
                <select
                    v-model="currentFilters.status"
                    @change="applyFilters"
                    class="block w-full sm:w-40 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="unsubscribed">Unsubscribed Only</option>
                </select>
            </template>

            <!-- Column: Email -->
            <template #email="{ item }">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                        <Mail class="w-4 h-4" />
                    </div>
                    <span class="font-bold text-slate-900 text-sm font-mono">
                        {{ item.email }}
                    </span>
                </div>
            </template>

            <!-- Column: Name -->
            <template #name="{ item }">
                <span class="text-xs text-slate-600">
                    {{ item.name || '—' }}
                </span>
            </template>

            <!-- Column: Status -->
            <template #is_active="{ item }">
                <AppBadge :variant="item.is_active ? 'success' : 'neutral'" size="sm">
                    {{ item.is_active ? 'Active' : 'Unsubscribed' }}
                </AppBadge>
            </template>

            <!-- Column: Subscribed Date -->
            <template #subscribed_at="{ item }">
                <span class="text-xs text-slate-500 font-mono">
                    {{ item.subscribed_at }}
                </span>
            </template>

            <!-- Column: Actions -->
            <template #actions="{ item }">
                <div v-if="item" class="flex items-center justify-end gap-1.5">
                    <button
                        type="button"
                        @click="toggleStatus(item)"
                        :title="item.is_active ? 'Mark as Unsubscribed' : 'Reactivate Subscriber'"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition border border-transparent hover:border-indigo-100"
                    >
                        <RefreshCw class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        @click="confirmDelete(item)"
                        title="Delete Subscriber"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition border border-transparent hover:border-rose-100"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>

            <template #empty>
                <div class="py-12 text-center">
                    <Send class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <h3 class="text-sm font-bold text-slate-700">No subscribers found</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Your mailing list subscribers who sign up from the public frontend will appear here.
                    </p>
                    <div class="mt-4">
                        <AppButton
                            @click="openAddModal"
                            variant="primary"
                            size="sm"
                        >
                            <UserPlus class="w-4 h-4 mr-1.5" />
                            <span>Add Subscriber</span>
                        </AppButton>
                    </div>
                </div>
            </template>
        </DataTable>

        <!-- Add Subscriber Modal -->
        <AppModal
            :show="addModalOpen"
            title="Add Newsletter Subscriber"
            maxWidth="sm"
            @close="addModalOpen = false"
        >
            <form @submit.prevent="submitAddSubscriber" class="space-y-4">
                <AppInput
                    v-model="form.email"
                    type="email"
                    label="Email Address"
                    placeholder="engineer@company.com"
                    :error="form.errors.email"
                    required
                />

                <AppInput
                    v-model="form.name"
                    label="Subscriber Name (Optional)"
                    placeholder="e.g. John Doe"
                    :error="form.errors.name"
                />

                <div class="flex items-center gap-2 pt-1">
                    <input
                        id="new_sub_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="new_sub_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Active Subscriber
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <AppButton
                        type="button"
                        @click="addModalOpen = false"
                        variant="secondary"
                        size="sm"
                    >
                        Cancel
                    </AppButton>
                    <AppButton
                        type="submit"
                        variant="primary"
                        size="sm"
                        :loading="form.processing"
                    >
                        Add Subscriber
                    </AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Delete Confirmation Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Subscriber"
            :message="`Are you sure you want to delete '${itemToDelete?.email}'? This action cannot be undone.`"
            confirm-text="Delete Subscriber"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @cancel="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
