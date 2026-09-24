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
import { Plus, CreditCard, Sparkles, Check, CheckCircle2, XCircle } from 'lucide-vue-next';

const props = defineProps({
    plans: {
        type: [Object, Array],
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            sort: 'sort_order',
            direction: 'asc',
            per_page: 10,
        }),
    },
});

const currentFilters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    sort: props.filters?.sort || 'sort_order',
    direction: props.filters?.direction || 'asc',
    per_page: props.filters?.per_page || 10,
});

const loading = ref(false);

const applyFilters = () => {
    loading.value = true;
    router.get(
        route('admin.pricing.index'),
        {
            search: currentFilters.search || undefined,
            status: currentFilters.status || undefined,
            sort: currentFilters.sort,
            direction: currentFilters.direction,
            per_page: currentFilters.per_page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            },
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
        route('admin.pricing.index'),
        {
            ...currentFilters,
            page: pageNumber,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        }
    );
};

const handlePerPage = (perPage) => {
    currentFilters.per_page = perPage;
    applyFilters();
};

const deleteModalOpen = ref(false);
const itemToDelete = ref(null);
const deleteLoading = ref(false);

const confirmDelete = (plan) => {
    itemToDelete.value = plan;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.pricing.destroy', itemToDelete.value.id), {
        onSuccess: () => {
            deleteModalOpen.value = false;
            itemToDelete.value = null;
        },
        onFinish: () => {
            deleteLoading.value = false;
        },
    });
};

const toggleStatus = (plan) => {
    router.post(route('admin.pricing.toggle', plan.id), {}, {
        preserveScroll: true,
    });
};

const columns = [
    { key: 'plan', label: 'Plan Details', sortable: true },
    { key: 'price', label: 'Pricing & Billing', sortable: true, class: 'w-44' },
    { key: 'features', label: 'Deliverables', align: 'center', class: 'w-32' },
    { key: 'is_featured', label: 'Highlight', align: 'center', sortable: true, class: 'w-28' },
    { key: 'status', label: 'Status', align: 'center', class: 'w-28' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-28' },
];
</script>

<template>
    <AdminLayout>
        <Head title="Pricing Plans - Admin Panel" />

        <AdminPageHeader
            title="Pricing Master"
            description="Manage client project packages, recurring plans, billing cycles, and feature deliverables."
            :breadcrumbs="[
                { label: 'Admin', href: route('admin.dashboard') },
                { label: 'Pricing Plans' }
            ]"
        >
            <template #actions>
                <AppButton
                    :href="route('admin.pricing.create')"
                    variant="primary"
                    size="sm"
                >
                    <Plus class="w-4 h-4 mr-1.5" />
                    <span>Create Plan</span>
                </AppButton>
            </template>
        </AdminPageHeader>

        <!-- Filters Strip -->
        <div class="mb-4 flex items-center gap-3">
            <select
                v-model="currentFilters.status"
                @change="applyFilters"
                class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 shadow-2xs"
            >
                <option value="">All Statuses</option>
                <option value="active">Active Plans</option>
                <option value="inactive">Inactive / Drafts</option>
            </select>
        </div>

        <DataTable
            :data="plans"
            :columns="columns"
            :search="currentFilters.search"
            searchPlaceholder="Search plans by name, tagline, or description..."
            :sortKey="currentFilters.sort"
            :sortDirection="currentFilters.direction"
            :perPage="currentFilters.per_page"
            :loading="loading"
            emptyTitle="No pricing plans found"
            emptyDescription="Create your first client pricing plan to display on the frontend."
            @update:search="handleSearch"
            @sort="handleSort"
            @page="handlePage"
            @update:perPage="handlePerPage"
        >
            <!-- Plan Column -->
            <template #plan="{ item }">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/10 to-violet-500/10 border border-indigo-200 text-indigo-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <CreditCard class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-900 text-sm">{{ item.name }}</span>
                            <span
                                v-if="item.badge"
                                class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200"
                            >
                                {{ item.badge }}
                            </span>
                        </div>
                        <p v-if="item.tagline" class="text-xs text-slate-500 mt-0.5 line-clamp-1">
                            {{ item.tagline }}
                        </p>
                    </div>
                </div>
            </template>

            <!-- Price Column -->
            <template #price="{ item }">
                <div>
                    <div class="font-extrabold text-slate-900 text-base">
                        {{ item.formatted_price }}
                    </div>
                    <div class="text-[11px] text-slate-500 capitalize">
                        per {{ item.billing_period }}
                    </div>
                </div>
            </template>

            <!-- Features Count -->
            <template #features="{ item }">
                <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">
                    {{ item.features_count }} items
                </span>
            </template>

            <!-- Featured Highlight -->
            <template #is_featured="{ item }">
                <span
                    v-if="item.is_featured"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200"
                >
                    <Sparkles class="w-3 h-3 text-amber-500" />
                    Featured
                </span>
                <span v-else class="text-xs text-slate-400">—</span>
            </template>

            <!-- Status Column -->
            <template #status="{ item }">
                <button
                    type="button"
                    @click="toggleStatus(item)"
                    class="cursor-pointer transition hover:opacity-80"
                    :title="item.is_active ? 'Click to deactivate' : 'Click to activate'"
                >
                    <AppBadge :variant="item.is_active ? 'success' : 'slate'" dot>
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </AppBadge>
                </button>
            </template>

            <!-- Actions -->
            <template #actions="{ item }">
                <DataTableActions
                    v-if="item"
                    :editHref="item?.id ? route('admin.pricing.edit', item.id) : null"
                    @delete="confirmDelete(item)"
                />
            </template>
        </DataTable>

        <!-- Delete Confirmation Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Pricing Plan"
            :message="`Are you sure you want to permanently delete plan '${itemToDelete?.name}'? This action cannot be undone.`"
            confirmText="Delete Plan"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @close="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
