<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Users,
    Search,
    Plus,
    Download,
    Eye,
    Edit3,
    Calendar,
    Filter,
    X,
    ChevronLeft,
    ChevronRight,
    Trash2
} from 'lucide-vue-next';

const props = defineProps({
    patients: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const bloodGroup = ref(props.filters.blood_group || 'all');
const gender = ref(props.filters.gender || 'all');
const status = ref(props.filters.status || 'all');

let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('patients.index'),
        {
            search: search.value || undefined,
            blood_group: bloodGroup.value !== 'all' ? bloodGroup.value : undefined,
            gender: gender.value !== 'all' ? gender.value : undefined,
            status: status.value !== 'all' ? status.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

const resetFilters = () => {
    search.value = '';
    bloodGroup.value = 'all';
    gender.value = 'all';
    status.value = 'all';
    applyFilters();
};

const deletePatient = (patient) => {
    if (confirm(`Are you sure you want to archive patient ${patient.full_name}?`)) {
        router.delete(route('patients.destroy', patient.id));
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Patient Management" />

        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Patient Directory</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Manage demographics, medical history, visit timelines, and contact profiles.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        :href="route('patients.export')"
                        class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-xs"
                    >
                        <Download class="w-3.5 h-3.5 text-slate-500" />
                        <span>Export CSV</span>
                    </a>

                    <Link
                        :href="route('patients.create')"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Register Patient</span>
                    </Link>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[240px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search by name, phone, UID (e.g. APL-P-001)..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    />
                </div>

                <!-- Gender Filter -->
                <select
                    v-model="gender"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Genders</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>

                <!-- Blood Group Filter -->
                <select
                    v-model="bloodGroup"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Blood Groups</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>

                <!-- Status Filter -->
                <select
                    v-model="status"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <button
                    v-if="search || bloodGroup !== 'all' || gender !== 'all' || status !== 'all'"
                    type="button"
                    @click="resetFilters"
                    class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition"
                    title="Clear filters"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Patients Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Patient UID</th>
                                <th class="py-3.5 px-5">Name & Demographics</th>
                                <th class="py-3.5 px-5">Blood Group</th>
                                <th class="py-3.5 px-5">Contact Details</th>
                                <th class="py-3.5 px-5">Allergies & Alerts</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="patients.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                    No patients found matching your search.
                                </td>
                            </tr>
                            <tr
                                v-for="p in patients.data"
                                :key="p.id"
                                class="hover:bg-slate-50/70 transition group"
                            >
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px]">
                                        {{ p.patient_uid }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <Link :href="route('patients.show', p.id)" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                        {{ p.full_name }}
                                    </Link>
                                    <span class="text-[11px] text-slate-500 capitalize">
                                        {{ p.gender }} <span v-if="p.age">({{ p.age }} yrs)</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-red-50 text-red-700 border border-red-100">
                                        {{ p.blood_group }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="font-medium text-slate-800">{{ p.phone }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[150px]">{{ p.city || p.email || '—' }}</div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span v-if="p.allergies && p.allergies !== 'None'" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-50 text-amber-800 border border-amber-200">
                                        ⚠️ {{ p.allergies }}
                                    </span>
                                    <span v-else class="text-[11px] text-slate-400">None</span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                            p.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
                                        ]"
                                    >
                                        {{ p.status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('patients.show', p.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition"
                                            title="View Medical Profile"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <Link
                                            :href="route('patients.edit', p.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                            title="Edit Demographics"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>
                                        <button
                                            type="button"
                                            @click="deletePatient(p)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Archive Patient"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="patients.links && patients.links.length > 3" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Showing <strong>{{ patients.from || 0 }}</strong> to <strong>{{ patients.to || 0 }}</strong> of <strong>{{ patients.total }}</strong> patients
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in patients.links"
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
    </AppLayout>
</template>
