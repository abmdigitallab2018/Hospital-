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
import { ArrowLeft, Plus, ExternalLink, Newspaper, Clock, Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps({
    post: {
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
    title: props.post.title || '',
    slug: props.post.slug || '',
    category_id: props.post.category_id || '',
    category: props.post.category || '',
    summary: props.post.summary || '',
    content: props.post.content || '',
    image: null,
    image_url: props.post.image_path && props.post.image_path.startsWith('http') ? props.post.image_path : '',
    documents: [],
    read_time: props.post.read_time || '',
    is_published: Boolean(props.post.is_published),
    published_at: props.post.published_at || '',
});

const previewUrl = ref(props.post.image_url || null);

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
    form.category = found ? found.name : '';
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
    })).post(route('admin.posts.update', props.post.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit Post - ${post.title}`" />

        <div class="max-w-3xl mx-auto">
            <AdminPageHeader
                title="Edit Daily Post & Update"
                :description="`Update post '${post.title}'.`"
                :breadcrumbs="[
                    { label: 'Posts', href: route('admin.posts.index') },
                    { label: 'Edit' }
                ]"
            >
                <template #actions>
                    <AppButton
                        :href="route('admin.posts.index')"
                        variant="ghost"
                        size="sm"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1.5" />
                        <span>Back</span>
                    </AppButton>
                </template>
            </AdminPageHeader>

            <AppCard>
                <form @submit.prevent="submit" class="space-y-6">
                    <AppInput
                        v-model="form.title"
                        label="Post Title"
                        placeholder="e.g. Scaling Cache Invalidation in Distributed Systems"
                        :error="form.errors.title"
                        required
                    />

                    <!-- Dynamic Category Master Selector -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="category_id" class="block text-xs font-semibold text-slate-700">
                                Category (Category Master) <span class="text-rose-500">*</span>
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
                                {{ cat.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.category_id || form.errors.category" class="text-xs text-red-600 mt-1">
                            {{ form.errors.category_id || form.errors.category }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AppInput
                            v-model="form.slug"
                            label="URL Slug (Optional)"
                            placeholder="e.g. scaling-cache-invalidation"
                            :error="form.errors.slug"
                        />

                        <AppInput
                            v-model="form.read_time"
                            label="Estimated Read Time"
                            placeholder="e.g. 4 min read"
                            :error="form.errors.read_time"
                        />
                    </div>

                    <AppTextarea
                        v-model="form.summary"
                        label="Brief Summary / Excerpt"
                        rows="2"
                        placeholder="A concise preview displayed on the post card and feeds..."
                        :error="form.errors.summary"
                    />

                    <AppTextarea
                        v-model="form.content"
                        label="Full Post Content"
                        rows="10"
                        placeholder="Write your technical explanation, code snippets, architecture diagrams, and lessons learned..."
                        :error="form.errors.content"
                        required
                    />

                    <!-- Featured Cover Image -->
                    <AppMediaUploader
                        v-model="form.image"
                        label="Article Cover Image / Header Photo"
                        hint="Upload a new cover image or architectural diagram (PNG, JPG, WEBP, SVG)"
                        accept="image/*"
                        :existingMedia="post.image_url"
                        :error="form.errors.image"
                    />

                    <AppInput
                        v-model="form.image_url"
                        label="Or External Image URL"
                        placeholder="https://images.unsplash.com/..."
                        :error="form.errors.image_url"
                    />

                    <!-- Attached Documents / PDFs / Whitepapers (Multi-document import) -->
                    <AppMediaUploader
                        v-model="form.documents"
                        multiple
                        label="Post Attachments & Documents (Multiple Files)"
                        hint="Attach research papers, slide decks (PDF), whitepapers, or code files"
                        :existingMedia="post.documents || []"
                        :error="form.errors.documents"
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="flex items-center gap-2 pt-2">
                            <input
                                id="is_published"
                                v-model="form.is_published"
                                type="checkbox"
                                class="h-4 w-4 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label for="is_published" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                Published (Visible on frontend)
                            </label>
                        </div>

                        <AppInput
                            v-model="form.published_at"
                            type="date"
                            label="Publication Date"
                            :error="form.errors.published_at"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                        <AppButton
                            :href="route('admin.posts.index')"
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
                            Update Post
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
                    Add a category in the Category Master. It will be immediately selected for this post.
                </p>

                <AppInput
                    v-model="quickCategoryName"
                    label="Category Name"
                    placeholder="e.g. System Design"
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
