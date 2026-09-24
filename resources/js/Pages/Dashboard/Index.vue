<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Calendar,
    Users,
    CreditCard,
    Clock,
    Stethoscope,
    FileText,
    ArrowUpRight,
    CheckCircle2,
    Play,
    UserCheck,
    Receipt,
    Plus,
    CalendarClock,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    todayAppointments: Array,
    doctorWorkload: Array,
    followUpsDue: Array,
    recentVisits: Array,
});

const checkIn = (appointmentId) => {
    router.post(route('appointments.checkIn', appointmentId));
};

const updateStatus = (appointmentId, status) => {
    router.put(route('appointments.updateStatus', appointmentId), { status });
};
</script>

<template>
    <AppLayout>
        <Head title="Clinic Dashboard" />

        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg shadow-emerald-900/10">
                <div>
                    <h1 class="text-2xl font-black tracking-tight">Today's Clinic Overview</h1>
                    <p class="text-emerald-100 text-xs sm:text-sm mt-1">
                        Live real-time monitoring of patients, doctor consultations, waiting queue, and billing.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2.5">
                    <Link
                        :href="route('appointments.index')"
                        class="px-4 py-2 bg-white text-emerald-800 rounded-xl text-xs font-bold shadow-xs hover:bg-emerald-50 transition flex items-center gap-1.5"
                    >
                        <Calendar class="w-4 h-4 text-emerald-600" />
                        <span>Book Appointment</span>
                    </Link>
                    <Link
                        :href="route('patients.create')"
                        class="px-4 py-2 bg-emerald-800/60 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold border border-emerald-400/30 transition flex items-center gap-1.5"
                    >
                        <Plus class="w-4 h-4" />
                        <span>New Patient</span>
                    </Link>
                    <Link
                        :href="route('queue.index')"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5"
                    >
                        <Clock class="w-4 h-4 text-slate-950" />
                        <span>Queue Screen</span>
                    </Link>
                </div>
            </div>

            <!-- 4 Core Metric Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Today Appointments -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Appointments</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <Calendar class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-slate-900">{{ stats.today_appointments }}</span>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            {{ stats.completed_today }} Done
                        </span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span>Waiting: <strong class="text-amber-600 font-bold">{{ stats.waiting_count }}</strong></span>
                        <span>In Room: <strong class="text-indigo-600 font-bold">{{ stats.in_consultation_count }}</strong></span>
                        <span>Scheduled: <strong class="text-slate-700 font-bold">{{ stats.scheduled_count }}</strong></span>
                    </div>
                </div>

                <!-- Active Patients -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Patients</span>
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <Users class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-slate-900">{{ stats.total_patients }}</span>
                        <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-full">
                            +{{ stats.new_patients_month }} this mo
                        </span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500">
                        Complete digital medical records & allergies
                    </div>
                </div>

                <!-- Today Revenue -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Collections</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <CreditCard class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-slate-900">₹{{ Number(stats.today_revenue).toLocaleString('en-IN') }}</span>
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                            ₹{{ Number(stats.month_revenue).toLocaleString('en-IN') }} Mo
                        </span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500">
                        Cash, UPI, Card & Net banking receipts
                    </div>
                </div>

                <!-- Pending Receivables -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Receivables</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <Receipt class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-amber-600">₹{{ Number(stats.pending_receivables).toLocaleString('en-IN') }}</span>
                        <Link :href="route('invoices.index', { status: 'unpaid' })" class="text-xs font-bold text-amber-700 underline">
                            View Bills
                        </Link>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500">
                        Unpaid & partially paid patient balances
                    </div>
                </div>
            </div>

            <!-- Two-Column Layout: Queue Appointments Table & Right Sidebar Summaries -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Today's Queue & Appointments (Span 2) -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Today's Appointment Queue</h2>
                            <p class="text-xs text-slate-500">Real-time status progression from arrival to completed consultation</p>
                        </div>
                        <Link
                            :href="route('appointments.index')"
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1"
                        >
                            <span>Full Calendar</span>
                            <ArrowUpRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div v-if="todayAppointments.length === 0" class="p-8 text-center text-slate-400 text-xs">
                        No appointments scheduled for today. Click "Book Appointment" to add one.
                    </div>

                    <div v-else class="divide-y divide-slate-100 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-3 px-4">Token</th>
                                    <th class="py-3 px-4">Patient</th>
                                    <th class="py-3 px-4">Doctor & Room</th>
                                    <th class="py-3 px-4">Time</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="apt in todayAppointments"
                                    :key="apt.id"
                                    class="hover:bg-slate-50/70 transition"
                                >
                                    <td class="py-3 px-4 font-black text-slate-900">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-slate-100 text-slate-800">
                                            #{{ apt.token_number }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link :href="route('patients.show', apt.patient?.id)" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                            {{ apt.patient?.full_name }}
                                        </Link>
                                        <span class="text-[10px] text-slate-400">{{ apt.patient?.phone }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-800">{{ apt.doctor?.user?.name }}</div>
                                        <span class="text-[10px] text-emerald-600 font-bold">{{ apt.doctor?.room_number || 'OPD' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-medium whitespace-nowrap">
                                        {{ apt.start_time?.substring(0, 5) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span
                                            :class="[
                                                'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                                apt.status === 'scheduled' ? 'bg-slate-100 text-slate-700' : '',
                                                apt.status === 'checked_in' ? 'bg-amber-100 text-amber-800' : '',
                                                apt.status === 'in_consultation' ? 'bg-indigo-100 text-indigo-800' : '',
                                                apt.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '',
                                                apt.status === 'cancelled' ? 'bg-red-100 text-red-800' : '',
                                            ]"
                                        >
                                            {{ apt.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <!-- Actions based on status -->
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                v-if="apt.status === 'scheduled'"
                                                type="button"
                                                @click="checkIn(apt.id)"
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg text-[11px] transition"
                                            >
                                                Check In
                                            </button>

                                            <button
                                                v-if="apt.status === 'checked_in'"
                                                type="button"
                                                @click="updateStatus(apt.id, 'in_consultation')"
                                                class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-[11px] transition flex items-center gap-1"
                                            >
                                                <Play class="w-3 h-3" />
                                                <span>Call In</span>
                                            </button>

                                            <Link
                                                v-if="apt.status === 'in_consultation' || apt.status === 'checked_in'"
                                                :href="route('consultations.create', { appointment_id: apt.id })"
                                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[11px] transition flex items-center gap-1"
                                            >
                                                <Stethoscope class="w-3 h-3" />
                                                <span>Consult</span>
                                            </Link>

                                            <span v-if="apt.status === 'completed'" class="text-emerald-600 font-bold text-xs flex items-center gap-1">
                                                <CheckCircle2 class="w-4 h-4" />
                                                Done
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Sidebar: Doctor Workload & Follow-ups -->
                <div class="space-y-6">
                    <!-- Doctor Workload -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Doctor Workload Today</h3>
                            <Link :href="route('doctors.index')" class="text-[11px] font-bold text-emerald-600">Roster</Link>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="doc in doctorWorkload"
                                :key="doc.id"
                                class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between"
                            >
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ doc.name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ doc.specialization }} ({{ doc.room_number || 'OPD' }})</div>
                                </div>
                                <div class="text-right">
                                    <span class="block font-black text-sm text-slate-900">
                                        {{ doc.completed_appointments }} / {{ doc.total_appointments }}
                                    </span>
                                    <span class="block text-[10px] text-slate-400 font-semibold">Patients</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Follow-ups due -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Follow-Ups Due Soon</h3>
                            <Link :href="route('follow-ups.index')" class="text-[11px] font-bold text-emerald-600">View All</Link>
                        </div>

                        <div v-if="followUpsDue.length === 0" class="text-center py-4 text-xs text-slate-400">
                            No follow-ups due in the next 48 hours.
                        </div>

                        <div v-else class="space-y-2.5">
                            <div
                                v-for="fu in followUpsDue"
                                :key="fu.id"
                                class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs"
                            >
                                <div>
                                    <div class="font-bold text-slate-900">{{ fu.patient?.full_name }}</div>
                                    <div class="text-[10px] text-slate-400">Due: {{ fu.follow_up_date }}</div>
                                </div>
                                <Link
                                    :href="route('follow-ups.index')"
                                    class="px-2 py-1 bg-white hover:bg-emerald-50 text-emerald-700 font-bold rounded-lg border border-slate-200 text-[10px] transition"
                                >
                                    Review
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Consultation Visits -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Recent Completed Consultations</h2>
                        <p class="text-xs text-slate-500">Electronic health records, diagnosis, and issued prescriptions</p>
                    </div>
                    <Link :href="route('consultations.index')" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                        <span>All Consultations</span>
                        <ArrowUpRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="v in recentVisits"
                        :key="v.id"
                        class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold text-slate-400">{{ v.visit_date }}</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Completed</span>
                            </div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ v.patient?.full_name }}</h4>
                            <p class="text-xs text-slate-600 mt-1 line-clamp-1"><strong>Dx:</strong> {{ v.diagnosis || 'Clinical review' }}</p>
                            <p class="text-[11px] text-slate-500 mt-1">Dr. {{ v.doctor?.user?.name }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
                            <span v-if="v.prescription" class="text-[10px] font-bold text-teal-600 flex items-center gap-1">
                                <FileText class="w-3 h-3" />
                                Rx Issued
                            </span>
                            <span v-else class="text-[10px] text-slate-400">No Rx</span>

                            <Link
                                :href="route('consultations.show', v.id)"
                                class="text-xs font-bold text-emerald-600 hover:text-emerald-700"
                            >
                                View Record →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
