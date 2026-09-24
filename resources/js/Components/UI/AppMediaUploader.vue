<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    UploadCloud,
    FileText,
    Image as ImageIcon,
    FileSpreadsheet,
    FileCode,
    File,
    X,
    Eye,
    Trash2,
    CheckCircle2,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: [Object, Array, File, null],
        default: null,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: 'Upload Photos or Documents',
    },
    hint: {
        type: String,
        default: 'Supports images (JPG, PNG, WEBP, SVG) and documents (PDF, DOC, DOCX, XLS, XLSX, TXT, CSV). Max 10MB per file.',
    },
    accept: {
        type: String,
        default: 'image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip',
    },
    maxSizeMb: {
        type: Number,
        default: 10,
    },
    existingMedia: {
        type: [Array, String, Object, null],
        default: () => [],
    },
    error: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change', 'delete-existing']);

const fileInputRef = ref(null);
const isDragging = ref(false);
const localError = ref(null);

// Selected new files queue with previews
const selectedFiles = ref([]);

const formatBytes = (bytes) => {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const getFileType = (file) => {
    const mime = file.type || '';
    const name = file.name || '';
    const ext = name.split('.').pop().toLowerCase();

    if (mime.startsWith('image/')) return 'image';
    if (mime === 'application/pdf' || ext === 'pdf') return 'pdf';
    if (ext === 'doc' || ext === 'docx') return 'word';
    if (ext === 'xls' || ext === 'xlsx' || ext === 'csv') return 'sheet';
    return 'doc';
};

const triggerFileInput = () => {
    if (props.disabled) return;
    fileInputRef.value?.click();
};

const handleDragOver = (e) => {
    if (props.disabled) return;
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e) => {
    if (props.disabled) return;
    e.preventDefault();
    isDragging.value = false;
    if (e.dataTransfer?.files?.length) {
        processFiles(Array.from(e.dataTransfer.files));
    }
};

const handleFileSelect = (e) => {
    if (e.target?.files?.length) {
        processFiles(Array.from(e.target.files));
    }
};

const processFiles = (rawFiles) => {
    localError.value = null;
    const validFiles = [];
    const maxBytes = props.maxSizeMb * 1024 * 1024;

    for (const file of rawFiles) {
        if (file.size > maxBytes) {
            localError.value = `File "${file.name}" exceeds the maximum limit of ${props.maxSizeMb}MB.`;
            continue;
        }

        const fileObj = {
            raw: file,
            name: file.name,
            size: formatBytes(file.size),
            type: getFileType(file),
            preview: null,
        };

        if (fileObj.type === 'image') {
            try {
                fileObj.preview = URL.createObjectURL(file);
            } catch (err) {
                // Ignore preview error
            }
        }

        validFiles.push(fileObj);
    }

    if (!validFiles.length) return;

    if (props.multiple) {
        selectedFiles.value = [...selectedFiles.value, ...validFiles];
        const allRaws = selectedFiles.value.map(f => f.raw);
        emit('update:modelValue', allRaws);
        emit('change', allRaws);
    } else {
        // Single file mode
        selectedFiles.value = [validFiles[0]];
        emit('update:modelValue', validFiles[0].raw);
        emit('change', validFiles[0].raw);
    }

    // Reset input value so re-selecting same file fires change
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const removeFile = (index) => {
    const removed = selectedFiles.value.splice(index, 1);
    if (removed[0]?.preview) {
        URL.revokeObjectURL(removed[0].preview);
    }

    if (props.multiple) {
        const remaining = selectedFiles.value.map(f => f.raw);
        emit('update:modelValue', remaining);
        emit('change', remaining);
    } else {
        emit('update:modelValue', null);
        emit('change', null);
    }
};

// Normalized existing media list
const normalizedExisting = computed(() => {
    if (!props.existingMedia) return [];
    if (typeof props.existingMedia === 'string') {
        if (!props.existingMedia.trim()) return [];
        return [{
            id: 'legacy',
            name: props.existingMedia.split('/').pop(),
            file_name: props.existingMedia.split('/').pop(),
            url: props.existingMedia.startsWith('http') || props.existingMedia.startsWith('/')
                ? props.existingMedia
                : `/storage/${props.existingMedia}`,
            type: props.existingMedia.match(/\.(jpeg|jpg|gif|png|webp|svg)$/i) ? 'image' : 'doc',
        }];
    }
    if (Array.isArray(props.existingMedia)) {
        return props.existingMedia.map(item => {
            if (typeof item === 'string') {
                return {
                    id: item,
                    name: item.split('/').pop(),
                    url: item.startsWith('http') || item.startsWith('/') ? item : `/storage/${item}`,
                    type: item.match(/\.(jpeg|jpg|gif|png|webp|svg)$/i) ? 'image' : 'doc',
                };
            }
            return {
                id: item.id,
                name: item.file_name || item.name || 'File',
                file_name: item.file_name || item.name || 'File',
                size: item.size || '',
                url: item.url,
                mime_type: item.mime_type || '',
                type: (item.mime_type && item.mime_type.startsWith('image/')) || (item.file_name && item.file_name.match(/\.(jpeg|jpg|gif|png|webp|svg)$/i)) ? 'image' : 'doc',
            };
        });
    }
    return [];
});

const deletingMediaId = ref(null);

const deleteExisting = (mediaItem) => {
    if (!confirm(`Are you sure you want to remove "${mediaItem.name}"?`)) return;

    if (mediaItem.id && typeof mediaItem.id === 'number') {
        deletingMediaId.value = mediaItem.id;
        router.delete(route('admin.media.destroy', mediaItem.id), {
            preserveScroll: true,
            onFinish: () => {
                deletingMediaId.value = null;
            },
        });
    }

    emit('delete-existing', mediaItem);
};
</script>

<template>
    <div class="space-y-3">
        <!-- Label and Hint -->
        <div v-if="label" class="flex items-center justify-between">
            <label class="block text-xs font-semibold text-slate-700">
                {{ label }}
                <span v-if="multiple" class="text-[11px] font-normal text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full ml-1.5 border border-indigo-200">
                    Multiple files supported
                </span>
            </label>
            <span class="text-[11px] text-slate-400">Max {{ maxSizeMb }}MB</span>
        </div>

        <!-- Hidden Native File Input -->
        <input
            ref="fileInputRef"
            type="file"
            :multiple="multiple"
            :accept="accept"
            class="hidden"
            @change="handleFileSelect"
            :disabled="disabled"
        />

        <!-- Dropzone Box -->
        <div
            @click="triggerFileInput"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            :class="[
                'border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 select-none relative',
                isDragging
                    ? 'border-indigo-500 bg-indigo-50/70 scale-[1.01]'
                    : 'border-slate-300 hover:border-indigo-400 bg-slate-50/60 hover:bg-slate-50',
                disabled ? 'opacity-60 cursor-not-allowed pointer-events-none' : ''
            ]"
        >
            <div class="flex flex-col items-center justify-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-xs border border-indigo-100">
                    <UploadCloud class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        Click to upload
                    </span>
                    <span class="text-sm text-slate-500"> or drag and drop</span>
                </div>
                <p class="text-xs text-slate-500 max-w-md">
                    {{ hint }}
                </p>
                <div class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400 pt-1">
                    <span class="px-2 py-0.5 rounded bg-slate-200/60 text-slate-600">Images</span>
                    <span class="px-2 py-0.5 rounded bg-slate-200/60 text-slate-600">PDFs</span>
                    <span class="px-2 py-0.5 rounded bg-slate-200/60 text-slate-600">Word</span>
                    <span class="px-2 py-0.5 rounded bg-slate-200/60 text-slate-600">Excel / CSV</span>
                </div>
            </div>
        </div>

        <!-- Validation / Size Error -->
        <div v-if="error || localError" class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-600">
            <AlertCircle class="w-4 h-4 flex-shrink-0" />
            <span>{{ error || localError }}</span>
        </div>

        <!-- New Files Staging Preview List -->
        <div v-if="selectedFiles.length" class="space-y-2 pt-1">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Ready to Upload ({{ selectedFiles.length }})
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div
                    v-for="(file, idx) in selectedFiles"
                    :key="idx"
                    class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200 shadow-2xs group hover:border-indigo-300 transition-colors"
                >
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <!-- Thumbnail Preview or File Icon -->
                        <div v-if="file.preview" class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 flex-shrink-0">
                            <img :src="file.preview" :alt="file.name" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                            :class="[
                                file.type === 'pdf' ? 'bg-red-50 text-red-600 border border-red-200' :
                                file.type === 'word' ? 'bg-blue-50 text-blue-600 border border-blue-200' :
                                file.type === 'sheet' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' :
                                'bg-slate-100 text-slate-600 border border-slate-200'
                            ]"
                        >
                            <FileText class="w-5 h-5" />
                        </div>

                        <!-- Name and Size -->
                        <div class="truncate">
                            <p class="text-xs font-semibold text-slate-800 truncate" :title="file.name">
                                {{ file.name }}
                            </p>
                            <p class="text-[10px] text-slate-400 font-mono">
                                {{ file.size }}
                            </p>
                        </div>
                    </div>

                    <!-- Remove Button -->
                    <button
                        type="button"
                        @click="removeFile(idx)"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                        title="Remove file"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Existing Uploaded Files List -->
        <div v-if="normalizedExisting.length" class="space-y-2 pt-2">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
                <span>Uploaded Media ({{ normalizedExisting.length }})</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div
                    v-for="item in normalizedExisting"
                    :key="item.id"
                    class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/90 shadow-2xs"
                >
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div v-if="item.type === 'image'" class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 bg-white flex-shrink-0">
                            <img :src="item.url" :alt="item.name" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center flex-shrink-0">
                            <FileText class="w-5 h-5" />
                        </div>
                        <div class="truncate">
                            <a
                                :href="item.url"
                                target="_blank"
                                class="text-xs font-semibold text-slate-800 hover:text-indigo-600 truncate block transition-colors"
                                :title="item.name"
                            >
                                {{ item.name }}
                            </a>
                            <span v-if="item.size" class="text-[10px] text-slate-400 font-mono">{{ item.size }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 flex-shrink-0">
                        <a
                            :href="item.url"
                            target="_blank"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                            title="View / Download"
                        >
                            <Eye class="w-4 h-4" />
                        </a>
                        <button
                            type="button"
                            @click="deleteExisting(item)"
                            :disabled="deletingMediaId === item.id"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors disabled:opacity-50"
                            title="Delete file"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
