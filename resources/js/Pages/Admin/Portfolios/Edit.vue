<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppMediaUploader from '@/Components/UI/AppMediaUploader.vue';
import { ArrowLeft, Plus, Tags, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
    portfolio: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    }
});

const localCategories = ref([...props.categories]);

const form = useForm({
    title: props.portfolio.title || '',
    category_id: props.portfolio.category_id || '',
    category: props.portfolio.category || '',
    image: null,
    image_url: props.portfolio.image_path && props.portfolio.image_path.startsWith('http') ? props.portfolio.image_path : '',
    documents: [],
    project_url: props.portfolio.project_url || '',
    description: props.portfolio.description || '',
    is_active: Boolean(props.portfolio.is_active),
});

const previewUrl = ref(props.portfolio.image_url || null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const handleCategorySelect = (e) => {
    const selectedId = e.target.value;
    form.category_id = selectedId;
    const found = localCategories.value.find(c => String(c.id) === String(selectedId));
    if (found) {
        form.category = found.name;
    } else {
        form.category = '';
    }
};

// Quick Add Category Modal
const quickModalOpen = ref(false);
const quickCategoryName = ref('');
const quickCategoryDesc = ref('');
const quickLoading = ref(false);
const quickError = ref('');

const openQuickAddCategory = () => {
    quickCategoryName.value = '';
    quickCategoryDesc.value = '';
    quickError.value = '';
    quickModalOpen.value = true;
};

const submitQuickCategory = async () => {
    if (!quickCategoryName.value.trim()) {
        quickError.value = 'Category name is required.';
        return;
    }

    quickLoading.value = true;
    quickError.value = '';

    try {
        const response = await axios.post(route('admin.categories.store'), {
            name: quickCategoryName.value.trim(),
            description: quickCategoryDesc.value.trim() || null,
            is_active: true,
        }, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.data && response.data.category) {
            const newCat = response.data.category;
            localCategories.value.push(newCat);
            form.category_id = newCat.id;
            form.category = newCat.name;
            quickModalOpen.value = false;
        }
    } catch (err) {
        if (err.response && err.response.data && err.response.data.errors) {
            const firstKey = Object.keys(err.response.data.errors)[0];
            quickError.value = err.response.data.errors[firstKey][0];
        } else {
            quickError.value = 'Failed to create category. Please try again.';
        }
    } finally {
        quickLoading.value = false;
    }
};

const submit = () => {
    form.transform(data => ({
        ...data,
        _method: 'put'
    })).post(route('admin.portfolios.update', props.portfolio.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit Project - ${portfolio.title}`" />

        <div class="max-w-2xl mx-auto">
            <AdminPageHeader
                title="Edit Portfolio Project"
                :description="`Update information for '${portfolio.title}'.`"
                :breadcrumbs="[
                    { label: 'Portfolio', href: route('admin.portfolios.index') },
                    { label: 'Edit' }
                ]"
            >
                <template #actions>
                    <AppButton
                        :href="route('admin.portfolios.index')"
                        variant="ghost"
                        size="sm"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1.5" />
                        <span>Back</span>
                    </AppButton>
                </template>
            </AdminPageHeader>

            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <AppInput
                        v-model="form.title"
                        label="Project Title"
                        placeholder="e.g. Multi-Tenant E-Commerce SaaS"
                        :error="form.errors.title"
                        required
                    />

                    <!-- Dynamic Category Master Selector -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="category_id" class="block text-xs font-semibold text-slate-700">
                                Project Category (Category Master) <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="openQuickAddCategory"
                                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition"
                                >
                                    <Plus class="w-3.5 h-3.5" />
                                    <span>Quick Add Category</span>
                                </button>
                                <span class="text-slate-300">•</span>
                                <Link
                                    :href="route('admin.categories.index')"
                                    target="_blank"
                                    class="text-xs font-medium text-slate-500 hover:text-indigo-600 flex items-center gap-0.5 transition"
                                >
                                    <span>Manage Master</span>
                                    <ExternalLink class="w-3 h-3" />
                                </Link>
                            </div>
                        </div>

                        <div class="relative">
                            <select
                                id="category_id"
                                :value="form.category_id"
                                @change="handleCategorySelect"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 shadow-2xs focus:border-indigo-500 focus:outline-hidden focus:ring-3 focus:ring-indigo-500/20"
                            >
                                <option value="">-- Select Category from Master --</option>
                                <option
                                    v-for="cat in localCategories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }} {{ cat.description ? `(${cat.description})` : '' }}
                                </option>
                            </select>
                        </div>
                        <p v-if="form.errors.category_id || form.errors.category" class="text-xs text-red-600 mt-1">
                            {{ form.errors.category_id || form.errors.category }}
                        </p>
                        <p class="text-[11px] text-slate-400">
                            Dynamic category controls public portfolio filtering and badges.
                        </p>
                    </div>

                    <!-- Thumbnail Image / Media Uploader -->
                    <AppMediaUploader
                        v-model="form.image"
                        label="Project Thumbnail / Screenshot Photo"
                        hint="Upload a new screenshot or cover image (PNG, JPG, WEBP, SVG)"
                        accept="image/*"
                        :existingMedia="portfolio.image_url"
                        :error="form.errors.image"
                    />

                    <AppInput
                        v-model="form.image_url"
                        label="Or External Image URL"
                        placeholder="https://images.unsplash.com/..."
                        :error="form.errors.image_url"
                    />

                    <!-- Documents & Attachments Uploader (Multi-document import) -->
                    <AppMediaUploader
                        v-model="form.documents"
                        multiple
                        label="Project Documents & Specifications (Multiple Files)"
                        hint="Upload project briefs, PDF architectures, design files, or spreadsheets at once"
                        :existingMedia="portfolio.documents || []"
                        :error="form.errors.documents"
                    />

                    <AppInput
                        v-model="form.project_url"
                        type="url"
                        label="Live Demo or GitHub Repository URL"
                        placeholder="https://github.com/bhavesh57/project or https://liveapp.com"
                        :error="form.errors.project_url"
                    />

                    <AppTextarea
                        v-model="form.description"
                        label="Project Description"
                        rows="4"
                        placeholder="Explain key architecture, metrics achieved, challenges overcome..."
                        :error="form.errors.description"
                    />

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Active (Visible on public portfolio)
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                        <AppButton
                            :href="route('admin.portfolios.index')"
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
                            Update Project
                        </AppButton>
                    </div>
                </form>
            </AppCard>
        </div>

        <!-- Quick Add Category Modal -->
        <AppModal
            :show="quickModalOpen"
            title="Quick Add Category"
            maxWidth="sm"
            @close="quickModalOpen = false"
        >
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Create a new category in the Category Master right now. It will be immediately selected for this project.
                </p>

                <AppInput
                    v-model="quickCategoryName"
                    label="Category Name"
                    placeholder="e.g. Distributed Systems"
                    :error="quickError"
                    required
                />

                <AppInput
                    v-model="quickCategoryDesc"
                    label="Description (Optional)"
                    placeholder="Brief description..."
                />

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <AppButton
                        type="button"
                        @click="quickModalOpen = false"
                        variant="secondary"
                        size="sm"
                    >
                        Cancel
                    </AppButton>
                    <AppButton
                        type="button"
                        @click="submitQuickCategory"
                        variant="primary"
                        size="sm"
                        :loading="quickLoading"
                    >
                        Add & Select
                    </AppButton>
                </div>
            </div>
        </AppModal>
    </AdminLayout>
</template>
