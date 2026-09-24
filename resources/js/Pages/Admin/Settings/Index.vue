<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import AppEmptyState from '@/Components/UI/AppEmptyState.vue';
import {
    Sliders,
    Building2,
    Home,
    Menu as MenuIcon,
    Plus,
    Search,
    Save,
    Upload,
    Trash2,
    Image as ImageIcon,
    FileText,
    List,
    CheckCircle2,
    X,
    Copy,
    Check,
    Settings2,
    Sparkles,
    RefreshCw,
    FolderKanban,
    Share2
} from 'lucide-vue-next';

const props = defineProps({
    settings: {
        type: Array,
        default: () => []
    },
    settingTypes: {
        type: Array,
        default: () => ['organization', 'social', 'menu', 'home', 'general']
    },
    currentType: {
        type: String,
        default: 'all'
    },
    search: {
        type: String,
        default: ''
    }
});

// Category Filter & Search
const activeType = ref(props.currentType || 'all');
const searchQuery = ref(props.search || '');

// Card Form State (Individual form state per setting)
const cardForms = reactive({});
const savingId = ref(null);
const copiedName = ref(null);

// Initialize or update card forms from props
const initCardForms = () => {
    props.settings.forEach(s => {
        if (!cardForms[s.id]) {
            cardForms[s.id] = {
                title: s.title,
                value: s.value !== null && s.value !== undefined ? s.value : '',
                file: null,
                filePreview: null,
                clear_upload: false,
                isDirty: false
            };
        } else {
            // Keep user edits if dirty, otherwise sync
            if (!cardForms[s.id].isDirty) {
                cardForms[s.id].title = s.title;
                cardForms[s.id].value = s.value !== null && s.value !== undefined ? s.value : '';
                cardForms[s.id].file = null;
                cardForms[s.id].filePreview = null;
                cardForms[s.id].clear_upload = false;
            }
        }
    });
};

initCardForms();
watch(() => props.settings, initCardForms, { deep: true });

// Copy setting key to clipboard
const copyKey = async (name) => {
    try {
        await navigator.clipboard.writeText(name);
        copiedName.value = name;
        setTimeout(() => {
            if (copiedName.value === name) copiedName.value = null;
        }, 1500);
    } catch {
        // Fallback
    }
};

// Filtered Settings Computed
const filteredSettings = computed(() => {
    return props.settings.filter(s => {
        // Category filter
        if (activeType.value !== 'all' && s.setting_type !== activeType.value) {
            return false;
        }

        // Search query filter
        if (searchQuery.value.trim()) {
            const query = searchQuery.value.toLowerCase().trim();
            const matchTitle = (s.title || '').toLowerCase().includes(query);
            const matchName = (s.name || '').toLowerCase().includes(query);
            const matchVal = (s.value || '').toLowerCase().includes(query);
            const matchType = (s.setting_type || '').toLowerCase().includes(query);
            return matchTitle || matchName || matchVal || matchType;
        }

        return true;
    });
});

// Category counts for pills
const categoryCounts = computed(() => {
    const counts = { all: props.settings.length };
    props.settings.forEach(s => {
        counts[s.setting_type] = (counts[s.setting_type] || 0) + 1;
    });
    return counts;
});

// Format Select Options for AppSelect / Native Select
const getNormalizedOptions = (options) => {
    if (!options) return [];
    if (Array.isArray(options)) {
        return options.map(opt => {
            if (typeof opt === 'object' && opt !== null) {
                return { value: opt.value ?? opt.id, label: opt.label ?? opt.name ?? opt.value };
            }
            return { value: opt, label: opt };
        });
    }
    if (typeof options === 'object') {
        return Object.entries(options).map(([k, v]) => ({ value: k, label: v }));
    }
    return [];
};

// Handle File Selection for Upload Field
const handleFileChange = (settingId, event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!cardForms[settingId]) {
        cardForms[settingId] = { value: '', file: null, filePreview: null, clear_upload: false, isDirty: true };
    }

    cardForms[settingId].file = file;
    cardForms[settingId].clear_upload = false;
    cardForms[settingId].isDirty = true;

    // Create preview if it's an image
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            cardForms[settingId].filePreview = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        cardForms[settingId].filePreview = null;
    }
};

// Mark upload for removal
const clearUploadedFile = (settingId) => {
    if (cardForms[settingId]) {
        cardForms[settingId].file = null;
        cardForms[settingId].filePreview = null;
        cardForms[settingId].clear_upload = true;
        cardForms[settingId].value = '';
        cardForms[settingId].isDirty = true;
    }
};

// Save a SINGLE Setting at a time
const saveSingleSetting = (setting) => {
    const form = cardForms[setting.id];
    if (!form) return;

    savingId.value = setting.id;

    // Build form data for single update
    const formData = new FormData();
    formData.append('_method', 'POST');

    if (setting.field_type === 'upload') {
        if (form.clear_upload) {
            formData.append('clear_upload', '1');
        } else if (form.file) {
            formData.append('file', form.file);
        } else {
            formData.append('value', form.value || '');
        }
    } else {
        formData.append('value', form.value !== null && form.value !== undefined ? form.value : '');
    }

    if (form.title && form.title !== setting.title) {
        formData.append('title', form.title);
    }

    router.post(route('admin.settings.updateSingle', setting.id), formData, {
        preserveScroll: true,
        onSuccess: () => {
            savingId.value = null;
            if (cardForms[setting.id]) {
                cardForms[setting.id].isDirty = false;
                cardForms[setting.id].file = null;
            }
        },
        onError: () => {
            savingId.value = null;
        }
    });
};

// ==================== ADD SETTING MODAL STATE ====================
const showAddModal = ref(false);
const isAddingSetting = ref(false);
const addForm = reactive({
    title: '',
    name: '',
    setting_type: 'organization',
    custom_setting_type: '',
    field_type: 'text',
    options_raw: '',
    value: '',
    file: null
});

const openAddModal = () => {
    addForm.title = '';
    addForm.name = '';
    addForm.setting_type = activeType.value !== 'all' ? activeType.value : 'organization';
    addForm.custom_setting_type = '';
    addForm.field_type = 'text';
    addForm.options_raw = 'enabled:Enabled, disabled:Disabled';
    addForm.value = '';
    addForm.file = null;
    showAddModal.value = true;
};

// Auto-generate slug name from title
const onAddTitleChange = () => {
    if (!addForm.name || addForm.name.startsWith('setting_')) {
        addForm.name = addForm.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }
};

const handleAddFileChange = (e) => {
    addForm.file = e.target.files[0] || null;
};

const submitAddSetting = () => {
    isAddingSetting.value = true;

    const formData = new FormData();
    formData.append('title', addForm.title);
    formData.append('name', addForm.name);

    const typeToUse = addForm.setting_type === '__custom__' ? addForm.custom_setting_type : addForm.setting_type;
    formData.append('setting_type', typeToUse);
    formData.append('field_type', addForm.field_type);

    if (addForm.field_type === 'select' && addForm.options_raw) {
        formData.append('options', addForm.options_raw);
    }

    if (addForm.field_type === 'upload' && addForm.file) {
        formData.append('file', addForm.file);
    } else {
        formData.append('value', addForm.value || '');
    }

    router.post(route('admin.settings.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            isAddingSetting.value = false;
            showAddModal.value = false;
        },
        onError: () => {
            isAddingSetting.value = false;
        }
    });
};

// ==================== DELETE SETTING CONFIRMATION ====================
const showDeleteModal = ref(false);
const settingToDelete = ref(null);
const isDeleting = ref(false);

const confirmDelete = (setting) => {
    settingToDelete.value = setting;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    if (!settingToDelete.value) return;
    isDeleting.value = true;

    router.delete(route('admin.settings.destroy', settingToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleting.value = false;
            showDeleteModal.value = false;
            settingToDelete.value = null;
        },
        onError: () => {
            isDeleting.value = false;
        }
    });
};

// Category styling helpers
const getCategoryBadgeVariant = (type) => {
    switch (type) {
        case 'organization': return 'indigo';
        case 'social': return 'blue';
        case 'menu': return 'amber';
        case 'home': return 'purple';
        case 'general': return 'emerald';
        default: return 'slate';
    }
};

const getCategoryIcon = (type) => {
    switch (type) {
        case 'organization': return Building2;
        case 'social': return Share2;
        case 'menu': return MenuIcon;
        case 'home': return Home;
        default: return Sliders;
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Site Settings" />

        <div class="space-y-6">
            <!-- ==================== HEADER & TOP ACTION BAR ==================== -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-xs">
                            <Sliders class="w-5 h-5" />
                        </div>
                        <div>
                            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Site Settings</h1>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Dynamic config-based settings. Updates are saved individually per card.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <AppButton
                        type="button"
                        variant="primary"
                        size="md"
                        @click="openAddModal"
                        class="shadow-xs shadow-indigo-500/20"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Add Setting</span>
                    </AppButton>
                </div>
            </div>

            <!-- ==================== FILTER TABS & SEARCH BAR ==================== -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Category Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                    <button
                        type="button"
                        @click="activeType = 'all'"
                        :class="[
                            'px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-150 flex items-center gap-2',
                            activeType === 'all'
                                ? 'bg-indigo-600 text-white shadow-xs'
                                : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'
                        ]"
                    >
                        <span>All</span>
                        <span
                            :class="[
                                'px-1.5 py-0.2 rounded-full text-[10px] font-bold',
                                activeType === 'all' ? 'bg-indigo-700/80 text-white' : 'bg-slate-100 text-slate-500'
                            ]"
                        >
                            {{ categoryCounts.all || 0 }}
                        </span>
                    </button>

                    <button
                        v-for="type in settingTypes"
                        :key="type"
                        type="button"
                        @click="activeType = type"
                        :class="[
                            'px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap capitalize transition-all duration-150 flex items-center gap-2',
                            activeType === type
                                ? 'bg-indigo-600 text-white shadow-xs'
                                : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'
                        ]"
                    >
                        <span>{{ type }}</span>
                        <span
                            v-if="categoryCounts[type]"
                            :class="[
                                'px-1.5 py-0.2 rounded-full text-[10px] font-bold',
                                activeType === type ? 'bg-indigo-700/80 text-white' : 'bg-slate-100 text-slate-500'
                            ]"
                        >
                            {{ categoryCounts[type] }}
                        </span>
                    </button>
                </div>

                <!-- Real-time Search Input -->
                <div class="relative w-full md:w-72">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search settings..."
                        class="w-full pl-9 pr-8 py-2 bg-white text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-slate-400"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="searchQuery = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>

            <!-- ==================== 4x4 CARD LAYOUT GRID ==================== -->
            <div v-if="filteredSettings.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                <div
                    v-for="setting in filteredSettings"
                    :key="setting.id"
                    :class="[
                        'bg-white rounded-2xl border transition-all duration-200 flex flex-col justify-between overflow-hidden shadow-2xs hover:shadow-md group',
                        cardForms[setting.id]?.isDirty
                            ? 'border-indigo-400/80 ring-2 ring-indigo-500/10'
                            : 'border-slate-200/80 hover:border-slate-300'
                    ]"
                >
                    <!-- Card Top Header -->
                    <div class="p-4 sm:p-5 border-b border-slate-100/90 space-y-2.5 bg-slate-50/50">
                        <div class="flex items-center justify-between gap-2">
                            <!-- Category Badge -->
                            <div class="flex items-center gap-1.5">
                                <AppBadge :variant="getCategoryBadgeVariant(setting.setting_type)" size="sm">
                                    <component :is="getCategoryIcon(setting.setting_type)" class="w-3 h-3 mr-1 inline" />
                                    {{ setting.setting_type }}
                                </AppBadge>

                                <!-- Field Type Badge -->
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-medium bg-slate-100 text-slate-600 border border-slate-200 flex items-center gap-1">
                                    <FileText v-if="setting.field_type === 'text'" class="w-2.5 h-2.5 text-slate-500" />
                                    <List v-else-if="setting.field_type === 'select'" class="w-2.5 h-2.5 text-slate-500" />
                                    <ImageIcon v-else-if="setting.field_type === 'upload'" class="w-2.5 h-2.5 text-slate-500" />
                                    {{ setting.field_type }}
                                </span>
                            </div>

                            <!-- Delete Option (for custom or any setting) -->
                            <button
                                type="button"
                                @click="confirmDelete(setting)"
                                class="text-slate-300 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition"
                                title="Delete setting"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <!-- Setting Title & Key Slug -->
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 tracking-tight line-clamp-1 group-hover:text-indigo-600 transition-colors" :title="setting.title">
                                {{ setting.title }}
                            </h3>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[11px] font-mono text-slate-400 truncate max-w-[170px]" :title="setting.name">
                                    {{ setting.name }}
                                </span>
                                <button
                                    type="button"
                                    @click="copyKey(setting.name)"
                                    class="text-slate-400 hover:text-slate-700 p-0.5 rounded"
                                    :title="copiedName === setting.name ? 'Copied!' : 'Copy key'"
                                >
                                    <Check v-if="copiedName === setting.name" class="w-3 h-3 text-emerald-600" />
                                    <Copy v-else class="w-3 h-3" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body: Dynamic Field Generator -->
                    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-center space-y-3">
                        <!-- DYNAMIC FIELD: TEXT -->
                        <div v-if="setting.field_type === 'text'" class="w-full">
                            <!-- Multiline textarea if long or bio/desc -->
                            <textarea
                                v-if="(cardForms[setting.id]?.value && cardForms[setting.id].value.length > 50) || setting.name.includes('description') || setting.name.includes('bio') || setting.name.includes('text')"
                                v-model="cardForms[setting.id].value"
                                @input="cardForms[setting.id].isDirty = true"
                                rows="3"
                                class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none font-sans text-slate-800 placeholder:text-slate-400"
                                :placeholder="`Enter ${setting.title}...`"
                            ></textarea>

                            <!-- Single line text input -->
                            <input
                                v-else
                                type="text"
                                v-model="cardForms[setting.id].value"
                                @input="cardForms[setting.id].isDirty = true"
                                class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-sans text-slate-800 placeholder:text-slate-400"
                                :placeholder="`Enter ${setting.title}...`"
                            />
                        </div>

                        <!-- DYNAMIC FIELD: SELECT -->
                        <div v-else-if="setting.field_type === 'select'" class="w-full">
                            <div class="relative">
                                <select
                                    v-model="cardForms[setting.id].value"
                                    @change="cardForms[setting.id].isDirty = true"
                                    class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 pr-8 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition appearance-none text-slate-800"
                                >
                                    <option
                                        v-for="opt in getNormalizedOptions(setting.options)"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- DYNAMIC FIELD: UPLOAD -->
                        <div v-else-if="setting.field_type === 'upload'" class="w-full space-y-2">
                            <!-- Preview Thumbnail if exists -->
                            <div
                                v-if="(cardForms[setting.id]?.filePreview || setting.preview_url) && !cardForms[setting.id]?.clear_upload"
                                class="relative rounded-xl border border-slate-200 bg-slate-50 overflow-hidden h-24 flex items-center justify-center group/preview"
                            >
                                <img
                                    :src="cardForms[setting.id]?.filePreview || setting.preview_url"
                                    :alt="setting.title"
                                    class="max-h-full max-w-full object-contain p-1"
                                />

                                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover/preview:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        @click="clearUploadedFile(setting.id)"
                                        class="p-1.5 rounded-lg bg-rose-600 text-white text-xs hover:bg-rose-700 shadow-sm"
                                        title="Remove file"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>

                            <div v-else-if="cardForms[setting.id]?.clear_upload" class="p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-[11px] text-center">
                                File will be removed upon saving
                            </div>

                            <!-- Upload Button & File Input -->
                            <div>
                                <label
                                    :for="`file-${setting.id}`"
                                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-dashed border-slate-300 hover:border-indigo-400 hover:bg-indigo-50/50 text-xs font-semibold text-slate-600 hover:text-indigo-600 cursor-pointer transition"
                                >
                                    <Upload class="w-3.5 h-3.5 text-indigo-500" />
                                    <span class="truncate">{{ cardForms[setting.id]?.file ? cardForms[setting.id].file.name : 'Choose file...' }}</span>
                                </label>
                                <input
                                    :id="`file-${setting.id}`"
                                    type="file"
                                    class="hidden"
                                    @change="handleFileChange(setting.id, $event)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: Save Single Setting Button -->
                    <div class="p-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                        <span class="text-[10px] text-slate-400">
                            {{ setting.updated_at_human || 'Updated' }}
                        </span>

                        <AppButton
                            type="button"
                            size="xs"
                            :variant="cardForms[setting.id]?.isDirty ? 'primary' : 'secondary'"
                            :loading="savingId === setting.id"
                            @click="saveSingleSetting(setting)"
                            class="text-xs"
                        >
                            <Save class="w-3.5 h-3.5" />
                            <span>Save</span>
                        </AppButton>
                    </div>
                </div>
            </div>

            <!-- Empty State if no settings match filter -->
            <AppEmptyState
                v-else
                title="No settings found"
                :description="searchQuery ? `No settings match your query '${searchQuery}'` : 'No settings in this category yet.'"
            >
                <template #action>
                    <AppButton
                        v-if="searchQuery"
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="searchQuery = ''; activeType = 'all'"
                    >
                        Reset Filters
                    </AppButton>
                    <AppButton
                        v-else
                        type="button"
                        variant="primary"
                        size="sm"
                        @click="openAddModal"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Add New Setting</span>
                    </AppButton>
                </template>
            </AppEmptyState>
        </div>

        <!-- ==================== ADD SETTING MODAL ==================== -->
        <AppModal
            :show="showAddModal"
            title="Create New Setting"
            maxWidth="md"
            @close="showAddModal = false"
        >
            <form @submit.prevent="submitAddSetting" class="space-y-4">
                <!-- Setting Title -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Setting Title <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="addForm.title"
                        @input="onAddTitleChange"
                        required
                        placeholder="e.g. Support Phone Number"
                        class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    />
                </div>

                <!-- Setting Name (Unique Key) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        System Key Name <span class="text-rose-500">*</span>
                        <span class="text-[10px] text-slate-400 font-normal ml-1">(letters, numbers, underscore)</span>
                    </label>
                    <input
                        type="text"
                        v-model="addForm.name"
                        required
                        placeholder="e.g. support_phone"
                        class="w-full text-xs font-mono rounded-xl border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    />
                </div>

                <!-- Category / Setting Type -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Category / Setting Type <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="addForm.setting_type"
                            class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 capitalize"
                        >
                            <option v-for="t in settingTypes" :key="t" :value="t">{{ t }}</option>
                            <option value="__custom__">+ Custom Category...</option>
                        </select>
                    </div>

                    <!-- Field Type -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Field Type <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="addForm.field_type"
                            class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 capitalize"
                        >
                            <option value="text">Text (Input / Bio)</option>
                            <option value="select">Select (Dropdown)</option>
                            <option value="upload">Upload (Image / File)</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Setting Type Input (if __custom__ chosen) -->
                <div v-if="addForm.setting_type === '__custom__'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        New Category Name <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="addForm.custom_setting_type"
                        required
                        placeholder="e.g. integrations"
                        class="w-full text-xs font-mono rounded-xl border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    />
                </div>

                <!-- Dynamic Field: Select Options input -->
                <div v-if="addForm.field_type === 'select'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Dropdown Options <span class="text-rose-500">*</span>
                        <span class="text-[10px] text-slate-400 font-normal ml-1">(Format: key:Label, key2:Label2)</span>
                    </label>
                    <input
                        type="text"
                        v-model="addForm.options_raw"
                        required
                        placeholder="enabled:Enabled, disabled:Disabled"
                        class="w-full text-xs font-mono rounded-xl border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    />
                </div>

                <!-- Dynamic Initial Value Input -->
                <div v-if="addForm.field_type === 'upload'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Initial File / Image</label>
                    <input
                        type="file"
                        @change="handleAddFileChange"
                        class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 focus:outline-none"
                    />
                </div>

                <div v-else>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Initial Value</label>
                    <input
                        type="text"
                        v-model="addForm.value"
                        placeholder="Default setting value"
                        class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    />
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <AppButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="showAddModal = false"
                    >
                        Cancel
                    </AppButton>
                    <AppButton
                        type="submit"
                        variant="primary"
                        size="sm"
                        :loading="isAddingSetting"
                    >
                        Create Setting
                    </AppButton>
                </div>
            </form>
        </AppModal>

        <!-- ==================== DELETE CONFIRMATION MODAL ==================== -->
        <AppConfirmDialog
            :show="showDeleteModal"
            title="Delete Setting"
            :message="`Are you sure you want to delete the setting '${settingToDelete?.title}' (${settingToDelete?.name})? This action cannot be undone.`"
            confirm-text="Delete Setting"
            confirm-variant="danger"
            :loading="isDeleting"
            @confirm="executeDelete"
            @cancel="showDeleteModal = false"
        />
    </AdminLayout>
</template>
