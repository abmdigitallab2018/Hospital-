<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Clock,
    Play,
    CheckCircle2,
    Stethoscope,
    Users,
    Volume2,
    Calendar,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    inConsultation: Array,
    waiting: Array,
    upcoming: Array,
    completed: Array,
    doctors: Array,
    selectedDoctorId: String,
});

const doctorId = ref(props.selectedDoctorId || 'all');

const filterDoctor = () => {
    router.get(route('queue.index'), {
        doctor_id: doctorId.value !== 'all' ? doctorId.value : undefined,
    }, { preserveState: true, replace: true });
};

const callPatient = (aptId) => {
    router.put(route('appointments.updateStatus', aptId), {
        status: 'in_consultation',
    });
};

const completeConsultation = (aptId) => {
    router.put(route('appointments.updateStatus', aptId), {
        status: 'completed',
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Live Waiting Room Queue & Tokens" />

        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Live Waiting Room Queue</h1>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Real-time token call-out display and OPD waiting room queue management.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="doctorId"
                        @change="filterDoctor"
                        class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 bg-white font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-xs"
                    >
                        <option value="all">All Doctors & Rooms</option>
                        <option v-for="d in doctors" :key="d.id" :value="d.id">
                            Dr. {{ d.user?.name }} (Room {{ d.room_number || 'OPD' }})
                        </option>
                    </select>

                    <Link
                        :href="route('appointments.index')"
                        class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition"
                    >
                        Full Calendar
                    </Link>
                </div>
            </div>

            <!-- In-Consultation Rooms Grid (Big TV-Style Callout Cards) -->
            <div>
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                    Currently In Consultation Room
                </h2>

                <div v-if="inConsultation.length === 0" class="bg-white rounded-3xl border border-dashed border-slate-300 p-8 text-center text-slate-400 text-xs">
                    No doctor currently has an active patient inside consultation. Ready to call next token from the waiting list below.
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="apt in inConsultation"
                        :key="apt.id"
                        class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between"
                    >
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>

                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 font-extrabold text-[11px] uppercase tracking-wider border border-indigo-500/30 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Room: {{ apt.doctor?.room_number || 'OPD-101' }}</span>
                                </span>
                                <span class="text-4xl font-black text-amber-400 tracking-tight">
                                    #{{ apt.token_number }}
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-white truncate">{{ apt.patient?.full_name }}</h3>
                            <p class="text-xs text-indigo-200 mt-1">UID: {{ apt.patient?.patient_uid }} • {{ apt.patient?.phone }}</p>
                            <p class="text-xs text-indigo-300/80 mt-2 font-semibold">
                                Dr. {{ apt.doctor?.user?.name }} ({{ apt.doctor?.specialization }})
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-indigo-800/60 flex items-center justify-between">
                            <Link
                                :href="route('consultations.create', { appointment_id: apt.id })"
                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5"
                            >
                                <Stethoscope class="w-3.5 h-3.5" />
                                <span>Record Consultation</span>
                            </Link>

                            <button
                                type="button"
                                @click="completeConsultation(apt.id)"
                                class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs transition"
                            >
                                Mark Done
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Queue Tables: Waiting Next vs Scheduled Upcoming -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Waiting Queue (Checked-In) -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h3 class="text-sm font-bold text-slate-900">Waiting Patients ({{ waiting.length }})</h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">Order by Token</span>
                    </div>

                    <div v-if="waiting.length === 0" class="p-8 text-center text-slate-400 text-xs">
                        No checked-in patients waiting right now.
                    </div>

                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="apt in waiting"
                            :key="apt.id"
                            class="p-4 flex items-center justify-between hover:bg-slate-50 transition"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-900 border border-amber-200 font-black text-base flex items-center justify-center shadow-xs">
                                    #{{ apt.token_number }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ apt.patient?.full_name }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        Dr. {{ apt.doctor?.user?.name }} • Room {{ apt.doctor?.room_number || 'OPD' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Arrived at {{ new Date(apt.checked_in_at || apt.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="callPatient(apt.id)"
                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5"
                            >
                                <Volume2 class="w-3.5 h-3.5" />
                                <span>Call Next</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Today (Upcoming) -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <h3 class="text-sm font-bold text-slate-900">Scheduled Later Today ({{ upcoming.length }})</h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">Awaiting Arrival</span>
                    </div>

                    <div v-if="upcoming.length === 0" class="p-8 text-center text-slate-400 text-xs">
                        All patients for today have arrived or no more appointments scheduled.
                    </div>

                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="apt in upcoming"
                            :key="apt.id"
                            class="p-4 flex items-center justify-between hover:bg-slate-50 transition"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 font-bold text-sm flex items-center justify-center">
                                    #{{ apt.token_number }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ apt.patient?.full_name }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        Dr. {{ apt.doctor?.user?.name }} • Slot: {{ apt.start_time?.substring(0, 5) }}
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="router.post(route('appointments.checkIn', apt.id))"
                                class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs shadow-xs transition"
                            >
                                Check In
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
