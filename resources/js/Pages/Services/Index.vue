<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Layers,
    Plus,
    Search,
    Edit3,
    Trash2,
    X,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    services: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || 'all');

const modalOpen = ref(false);
const editingService = ref(null);

const form = useForm({
    name: '',
    code: '',
    category: 'General',
    description: '',
    price: 0,
    is_active: true,
});

const openCreate = () => {
    editingService.value = null;
    form.reset();
    form.is_active = true;
    modalOpen.value = true;
};

const openEdit = (svc) => {
    editingService.value = svc;
    form.name = svc.name;
    form.code = svc.code || '';
    form.category = svc.category || 'General';
    form.description = svc.description || '';
    form.price = svc.price;
    form.is_active = Boolean(svc.is_active);
    modalOpen.value = true;
};

const submit = () => {
    if (editingService.value) {
        form.put(route('services.update', editingService.value.id), {
            onSuccess: () => modalOpen.value = false,
        });
    } else {
        form.post(route('services.store'), {
            onSuccess: () => modalOpen.value = false,
        });
    }
};

const deleteService = (svc) => {
    if (confirm(`Remove ${svc.name} from catalog?`)) {
        router.delete(route('services.destroy', svc.id));
    }
};

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('services.index'),
        {
            search: search.value || undefined,
            category: category.value !== 'all' ? category.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});
</script>

<template>
    <AppLayout>
        <Head title="Clinical Services Catalog" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Clinical Services & Fee Catalog</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Define procedures, lab diagnostics, nursing care, and standard consultation charges.
                    </p>
                </div>

                <button
                    type="button"
                    @click="openCreate"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Add Service / Procedure</span>
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search service name, code..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>

                <select
                    v-model="category"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Categories</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">
                        {{ cat }}
                    </option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Service Name & Code</th>
                                <th class="py-3.5 px-5">Category</th>
                                <th class="py-3.5 px-5">Standard Rate (₹)</th>
                                <th class="py-3.5 px-5">Description</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="services.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                    No services found in catalog.
                                </td>
                            </tr>
                            <tr
                                v-for="svc in services.data"
                                :key="svc.id"
                                class="hover:bg-slate-50/70 transition"
                            >
                                <td class="py-3.5 px-5">
                                    <span class="font-bold text-slate-900 block text-sm">{{ svc.name }}</span>
                                    <span v-if="svc.code" class="text-[10px] font-mono text-slate-400">{{ svc.code }}</span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ svc.category }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 font-black text-slate-900 text-sm font-mono">
                                    ₹{{ Number(svc.price).toFixed(2) }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-500 max-w-xs truncate">
                                    {{ svc.description || '—' }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase', svc.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400']">
                                        {{ svc.is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openEdit(svc)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                            title="Edit Rate"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteService(svc)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Remove"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="services.links && services.links.length > 3" class="px-5 py-3 border-t border-slate-100 flex justify-center text-xs">
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in services.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-2.5 py-1 rounded-lg text-xs font-semibold transition',
                                link.active ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-slate-900">
                        {{ editingService ? 'Edit Clinical Service' : 'Add New Service / Test' }}
                    </h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Service / Procedure Name *</label>
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            placeholder="e.g. Nebulization Therapy"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Code / Shortcode</label>
                            <input
                                type="text"
                                v-model="form.code"
                                placeholder="PROC-NEB"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Category *</label>
                            <input
                                type="text"
                                v-model="form.category"
                                required
                                placeholder="e.g. Procedure, Lab Test"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Standard Rate / Fee (₹) *</label>
                        <input
                            type="number"
                            v-model="form.price"
                            required
                            min="0"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Clinical Description</label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Brief description of the test or clinical procedure..."
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="rounded text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>Active in Invoicing Catalog</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="modalOpen = false"
                            class="px-4 py-2 font-bold text-slate-500 hover:bg-slate-100 rounded-xl"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition"
                        >
                            Save Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
