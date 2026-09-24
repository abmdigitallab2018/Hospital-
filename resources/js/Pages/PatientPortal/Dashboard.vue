<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import {
    Calendar,
    FileText,
    Receipt,
    Printer,
    ArrowRight,
    HeartPulse,
    Clock
} from 'lucide-vue-next';

defineProps({
    patient: Object,
    upcomingAppointments: Array,
    recentPrescriptions: Array,
    recentInvoices: Array,
    totalDue: Number,
});
</script>

<template>
    <PatientPortalLayout>
        <Head title="Patient Portal" />

        <div class="space-y-6">
            <!-- Welcome Header Banner -->
            <div class="bg-gradient-to-r from-teal-600 to-emerald-700 rounded-3xl p-6 sm:p-8 text-white shadow-lg shadow-emerald-900/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="text-emerald-200 text-xs font-bold uppercase tracking-wider">Patient Self-Service Portal</span>
                    <h1 class="text-2xl sm:text-3xl font-black mt-1">Hello, {{ patient.first_name }}!</h1>
                    <p class="text-emerald-100 text-xs sm:text-sm mt-1">
                        Patient UID: <strong>{{ patient.patient_uid }}</strong> • Blood Group: <strong>{{ patient.blood_group }}</strong>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="route('patient.appointments')"
                        class="px-4 py-2.5 bg-white text-emerald-800 font-bold rounded-xl text-xs shadow-md hover:bg-emerald-50 transition"
                    >
                        Book Appointment
                    </Link>
                </div>
            </div>

            <!-- Quick Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase">Upcoming Visits</span>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ upcomingAppointments.length }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <Calendar class="w-5 h-5" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase">Digital Prescriptions</span>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ recentPrescriptions.length }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                        <FileText class="w-5 h-5" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase">Balance Due</span>
                        <div class="text-2xl font-black" :class="totalDue > 0 ? 'text-amber-600' : 'text-slate-900'">
                            ₹{{ Number(totalDue).toLocaleString('en-IN') }}
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <Receipt class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Two-Column: Upcoming Appointments & Prescriptions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Appointments -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-sm font-bold text-slate-900">Upcoming Appointments</h2>
                        <Link :href="route('patient.appointments')" class="text-xs font-bold text-emerald-600">View All</Link>
                    </div>

                    <div v-if="upcomingAppointments.length === 0" class="py-8 text-center text-slate-400 text-xs">
                        No upcoming visits scheduled.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="apt in upcomingAppointments"
                            :key="apt.id"
                            class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center">
                                    #{{ apt.token_number }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ apt.appointment_date }} at {{ apt.start_time?.substring(0, 5) }}</div>
                                    <div class="text-slate-500 text-[11px]">Dr. {{ apt.doctor?.user?.name }} ({{ apt.doctor?.specialization }})</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                                {{ apt.status.replace('_', ' ') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Prescriptions -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-sm font-bold text-slate-900">Recent Prescriptions</h2>
                        <Link :href="route('patient.prescriptions')" class="text-xs font-bold text-emerald-600">View All</Link>
                    </div>

                    <div v-if="recentPrescriptions.length === 0" class="py-8 text-center text-slate-400 text-xs">
                        No prescriptions issued yet.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="rx in recentPrescriptions"
                            :key="rx.id"
                            class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs"
                        >
                            <div>
                                <div class="font-bold text-slate-900">{{ rx.prescription_number }}</div>
                                <div class="text-slate-500 text-[11px]">{{ rx.date }} • Dr. {{ rx.doctor?.user?.name }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ rx.items?.length }} medicines prescribed</div>
                            </div>

                            <Link
                                :href="route('prescriptions.print', rx.id)"
                                target="_blank"
                                class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-800 font-bold rounded-xl border border-slate-200 flex items-center gap-1.5 transition"
                            >
                                <Printer class="w-3.5 h-3.5 text-emerald-600" />
                                <span>Print Rx</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PatientPortalLayout>
</template>
