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
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import { Plus, Tags, Layers, FolderKanban } from 'lucide-vue-next';

const props = defineProps({
    categories: {
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
        route('admin.categories.index'),
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
        route('admin.categories.index'),
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

// Modal Form State for Add / Edit
const modalOpen = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
    slug: '',
    description: '',
    sort_order: 0,
    is_active: true,
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    form.sort_order = 0;
    modalOpen.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.clearErrors();
    form.name = category.name;
    form.slug = category.slug;
    form.description = category.description || '';
    form.sort_order = category.sort_order;
    form.is_active = Boolean(category.is_active);
    modalOpen.value = true;
};

const submitForm = () => {
    if (editingCategory.value) {
        form.put(route('admin.categories.update', editingCategory.value.id), {
            onSuccess: () => {
                modalOpen.value = false;
                editingCategory.value = null;
            }
        });
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => {
                modalOpen.value = false;
                form.reset();
            }
        });
    }
};

// Deletion Confirmation Dialog State
const deleteModalOpen = ref(false);
const itemToDelete = ref(null);
const deleteLoading = ref(false);

const confirmDelete = (category) => {
    itemToDelete.value = category;
    deleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    router.delete(route('admin.categories.destroy', itemToDelete.value.id), {
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
    { key: 'name', label: 'Category Name', sortable: true },
    { key: 'slug', label: 'Slug', sortable: true, class: 'w-36 hidden sm:table-cell' },
    { key: 'description', label: 'Description', class: 'hidden md:table-cell' },
    { key: 'sort_order', label: 'Sort Order', sortable: true, align: 'center', class: 'w-28' },
    { key: 'portfolios_count', label: 'Projects', sortable: true, align: 'center', class: 'w-24' },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', class: 'w-28' },
    { key: 'actions', label: 'Actions', align: 'right', class: 'w-24' }
];
</script>

<template>
    <AdminLayout>
        <Head title="Category Master - Admin Panel" />

        <AdminPageHeader
            title="Category Master"
            description="Manage dynamic project categories and taxonomy used for portfolio organization and public frontend filtering."
            :breadcrumbs="[
                { label: 'Portfolio', href: route('admin.portfolios.index') },
                { label: 'Category Master' }
            ]"
        >
            <template #actions>
                <div class="flex items-center gap-2">
                    <AppButton
                        :href="route('admin.portfolios.index')"
                        variant="secondary"
                        size="sm"
                    >
                        <FolderKanban class="w-4 h-4 mr-1.5" />
                        <span>View Projects</span>
                    </AppButton>
                    <AppButton
                        @click="openCreateModal"
                        variant="primary"
                        size="sm"
                    >
                        <Plus class="w-4 h-4 mr-1.5" />
                        <span>Add Category</span>
                    </AppButton>
                </div>
            </template>
        </AdminPageHeader>

        <!-- DataTable -->
        <DataTable
            :columns="columns"
            :data="categories"
            :loading="loading"
            search-placeholder="Search categories by name, slug or description..."
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
                    <option value="inactive">Inactive Only</option>
                </select>
            </template>

            <!-- Column: Name -->
            <template #name="{ item }">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0 font-bold text-xs">
                        <Tags class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 text-sm block">
                            {{ item.name }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-mono sm:hidden">
                            /{{ item.slug }}
                        </span>
                    </div>
                </div>
            </template>

            <!-- Column: Slug -->
            <template #slug="{ item }">
                <span class="font-mono text-xs text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-200/60">
                    {{ item.slug }}
                </span>
            </template>

            <!-- Column: Description -->
            <template #description="{ item }">
                <span class="text-xs text-slate-600 line-clamp-1 max-w-xs">
                    {{ item.description || '—' }}
                </span>
            </template>

            <!-- Column: Sort Order -->
            <template #sort_order="{ item }">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-slate-100 text-slate-700">
                    {{ item.sort_order }}
                </span>
            </template>

            <!-- Column: Projects Count -->
            <template #portfolios_count="{ item }">
                <AppBadge variant="neutral" size="sm">
                    {{ item.portfolios_count }} {{ item.portfolios_count === 1 ? 'project' : 'projects' }}
                </AppBadge>
            </template>

            <!-- Column: Status -->
            <template #is_active="{ item }">
                <AppBadge :variant="item.is_active ? 'success' : 'neutral'" size="sm">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                </AppBadge>
            </template>

            <!-- Column: Actions -->
            <template #actions="{ item }">
                <DataTableActions
                    v-if="item"
                    @edit="openEditModal(item)"
                    @delete="confirmDelete(item)"
                />
            </template>

            <template #empty>
                <div class="py-12 text-center">
                    <Tags class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <h3 class="text-sm font-bold text-slate-700">No categories found</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Get started by adding your first project category to organize portfolio works.
                    </p>
                    <div class="mt-4">
                        <AppButton
                            @click="openCreateModal"
                            variant="primary"
                            size="sm"
                        >
                            <Plus class="w-4 h-4 mr-1.5" />
                            <span>Add Category</span>
                        </AppButton>
                    </div>
                </div>
            </template>
        </DataTable>

        <!-- Create / Edit Category Modal -->
        <AppModal
            :show="modalOpen"
            :title="editingCategory ? 'Edit Category' : 'Add New Category'"
            maxWidth="md"
            @close="modalOpen = false"
        >
            <form @submit.prevent="submitForm" class="space-y-4">
                <AppInput
                    v-model="form.name"
                    label="Category Name"
                    placeholder="e.g. SaaS, PWA, Cloud Architecture"
                    :error="form.errors.name"
                    required
                />

                <AppInput
                    v-model="form.slug"
                    label="URL Slug (Optional)"
                    placeholder="e.g. saas, pwa"
                    hint="Leave blank to automatically generate from name."
                    :error="form.errors.slug"
                />

                <AppTextarea
                    v-model="form.description"
                    label="Description"
                    rows="2"
                    placeholder="Brief description of this category..."
                    :error="form.errors.description"
                />

                <div class="grid grid-cols-2 gap-4">
                    <AppInput
                        v-model="form.sort_order"
                        type="number"
                        label="Sort Order"
                        placeholder="0"
                        hint="Lower numbers appear first."
                        :error="form.errors.sort_order"
                    />

                    <div class="flex flex-col justify-end pb-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span class="text-xs font-semibold text-slate-700">
                                Active on Frontend
                            </span>
                        </label>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Inactive categories are hidden from filter tabs.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <AppButton
                        type="button"
                        @click="modalOpen = false"
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
                        {{ editingCategory ? 'Update Category' : 'Create Category' }}
                    </AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Delete Confirmation Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Category"
            :message="`Are you sure you want to delete '${itemToDelete?.name}'? Associated portfolio projects will not be deleted, but will be unlinked from this category.`"
            confirm-text="Delete Category"
            variant="danger"
            :loading="deleteLoading"
            @confirm="executeDelete"
            @cancel="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
