<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Stethoscope,
    Plus,
    Search,
    Calendar,
    FileText,
    Eye,
    Printer,
    User,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    consultations: Object,
    doctors: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const doctorId = ref(props.filters.doctor_id || 'all');
const date = ref(props.filters.date || '');

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('consultations.index'),
        {
            search: search.value || undefined,
            doctor_id: doctorId.value !== 'all' ? doctorId.value : undefined,
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
        <Head title="Consultations & Electronic Health Records" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Clinical Consultations</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Electronic medical records, vital signs history, diagnoses, and digital prescriptions.
                    </p>
                </div>

                <Link
                    :href="route('consultations.create')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Start Consultation</span>
                </Link>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search by diagnosis, complaints, patient..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>

                <select
                    v-model="doctorId"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Doctors</option>
                    <option v-for="d in doctors" :key="d.id" :value="d.id">
                        Dr. {{ d.user?.name }} ({{ d.specialization }})
                    </option>
                </select>

                <input
                    type="date"
                    v-model="date"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                />
            </div>

            <!-- Consultations Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-if="consultations.data.length === 0" class="col-span-full bg-white rounded-3xl p-12 text-center text-slate-400 text-xs border border-slate-200/80">
                    No consultations found matching your filters.
                </div>

                <div
                    v-for="c in consultations.data"
                    :key="c.id"
                    class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 hover:border-emerald-300 transition flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[11px] font-bold text-slate-400 font-mono">{{ c.visit_date }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 uppercase">
                                {{ c.status }}
                            </span>
                        </div>

                        <Link :href="route('patients.show', c.patient?.id)" class="font-bold text-slate-900 text-sm hover:text-emerald-600 block">
                            {{ c.patient?.full_name }}
                        </Link>
                        <span class="text-[10px] text-slate-400">{{ c.patient?.gender }} • {{ c.patient?.phone }}</span>

                        <div class="mt-3 space-y-1.5 text-xs">
                            <p class="text-slate-600 line-clamp-2">
                                <strong class="text-slate-700">Complaints:</strong> {{ c.chief_complaints }}
                            </p>
                            <p class="text-emerald-800 font-semibold line-clamp-1">
                                <strong>Dx:</strong> {{ c.diagnosis || 'Clinical evaluation' }}
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Consulting: <strong>Dr. {{ c.doctor?.user?.name }}</strong>
                            </p>
                        </div>

                        <!-- Vitals Pill -->
                        <div v-if="c.vital" class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 text-[10px] text-slate-600">
                            <span v-if="c.vital.blood_pressure_systolic" class="bg-slate-100 px-2 py-0.5 rounded font-mono">
                                BP: {{ c.vital.blood_pressure_systolic }}/{{ c.vital.blood_pressure_diastolic }}
                            </span>
                            <span v-if="c.vital.pulse_rate" class="bg-slate-100 px-2 py-0.5 rounded font-mono">
                                HR: {{ c.vital.pulse_rate }} bpm
                            </span>
                            <span v-if="c.vital.temperature" class="bg-slate-100 px-2 py-0.5 rounded font-mono">
                                Temp: {{ c.vital.temperature }}°F
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <Link
                            v-if="c.prescription"
                            :href="route('prescriptions.print', c.prescription.id)"
                            target="_blank"
                            class="text-[11px] font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1"
                        >
                            <Printer class="w-3.5 h-3.5" />
                            <span>Print Rx</span>
                        </Link>
                        <span v-else class="text-[11px] text-slate-400">No Rx</span>

                        <Link
                            :href="route('consultations.show', c.id)"
                            class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl transition"
                        >
                            View Record →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="consultations.links && consultations.links.length > 3" class="flex justify-center mt-6">
                <div class="flex items-center gap-1">
                    <Link
                        v-for="(link, i) in consultations.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-semibold transition',
                            link.active ? 'bg-emerald-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 bg-white border border-slate-200'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
