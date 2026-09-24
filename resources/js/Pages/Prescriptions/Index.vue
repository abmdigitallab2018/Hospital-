<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    FileText,
    Search,
    Printer,
    Eye,
    Calendar,
    User,
    Stethoscope
} from 'lucide-vue-next';

const props = defineProps({
    prescriptions: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const date = ref(props.filters.date || '');

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('prescriptions.index'),
        {
            search: search.value || undefined,
            date: date.value || undefined,
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
        <Head title="Prescriptions Registry" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Prescriptions Registry</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Digital prescriptions, medication items, instructions, and printable medical letterheads.
                    </p>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search by Rx number, patient name, UID..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>

                <input
                    type="date"
                    v-model="date"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                />
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Rx Number</th>
                                <th class="py-3.5 px-5">Date</th>
                                <th class="py-3.5 px-5">Patient</th>
                                <th class="py-3.5 px-5">Doctor</th>
                                <th class="py-3.5 px-5">Medicines Count</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="prescriptions.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                    No prescriptions found matching your search.
                                </td>
                            </tr>
                            <tr
                                v-for="rx in prescriptions.data"
                                :key="rx.id"
                                class="hover:bg-slate-50/70 transition"
                            >
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded-md bg-teal-50 text-teal-800 text-[11px] font-bold">
                                        {{ rx.prescription_number }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium whitespace-nowrap">
                                    {{ rx.date }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <Link :href="route('patients.show', rx.patient?.id)" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                        {{ rx.patient?.full_name }}
                                    </Link>
                                    <span class="text-[10px] text-slate-400">{{ rx.patient?.patient_uid }} • {{ rx.patient?.phone }}</span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800">Dr. {{ rx.doctor?.user?.name }}</div>
                                    <span class="text-[10px] text-slate-400">{{ rx.doctor?.specialization }}</span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ rx.items?.length || 0 }} Medications
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('prescriptions.print', rx.id)"
                                            target="_blank"
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition flex items-center gap-1.5"
                                        >
                                            <Printer class="w-3.5 h-3.5 text-slate-600" />
                                            <span>Print Official Rx</span>
                                        </Link>

                                        <Link
                                            v-if="rx.visit_id"
                                            :href="route('consultations.show', rx.visit_id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition"
                                            title="View Full Consultation"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="prescriptions.links && prescriptions.links.length > 3" class="px-5 py-3 border-t border-slate-100 flex justify-center text-xs">
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in prescriptions.links"
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
