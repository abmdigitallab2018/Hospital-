<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    BarChart3,
    Calendar,
    Download,
    CreditCard,
    Receipt,
    Users,
    TrendingUp,
    AlertCircle,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    summary: Object,
    paymentMethods: Array,
    doctorStats: Array,
    recentPayments: Array,
    filters: Object,
});

const range = ref(props.filters.range || 'this_month');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const applyRange = () => {
    router.get(
        route('reports.index'),
        {
            range: range.value,
            start_date: range.value === 'custom' ? startDate.value : undefined,
            end_date: range.value === 'custom' ? endDate.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <AppLayout>
        <Head title="Clinic Financial & Clinical Reports" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Financial & Clinical Reports</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Revenue audit, payment method collections, doctor consultation volume, and operational metrics.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a
                        :href="route('reports.export', { range, start_date: startDate, end_date: endDate })"
                        class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-xs"
                    >
                        <Download class="w-3.5 h-3.5 text-slate-500" />
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- Date Range Controls -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <span class="text-xs font-bold text-slate-700">Period:</span>
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="r in [
                            { key: 'today', label: 'Today' },
                            { key: 'this_week', label: 'This Week' },
                            { key: 'this_month', label: 'This Month' },
                            { key: 'last_30_days', label: 'Last 30 Days' },
                            { key: 'custom', label: 'Custom Range' },
                        ]"
                        :key="r.key"
                        type="button"
                        @click="range = r.key; applyRange()"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition',
                            range === r.key ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                        ]"
                    >
                        {{ r.label }}
                    </button>
                </div>

                <div v-if="range === 'custom'" class="flex items-center gap-2 text-xs ml-auto">
                    <input
                        type="date"
                        v-model="startDate"
                        class="py-1 px-2.5 rounded-lg border border-slate-200 focus:outline-none"
                    />
                    <span class="text-slate-400">to</span>
                    <input
                        type="date"
                        v-model="endDate"
                        class="py-1 px-2.5 rounded-lg border border-slate-200 focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="applyRange"
                        class="px-3 py-1 bg-emerald-600 text-white rounded-lg font-bold"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <!-- 5 Financial Summary Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Total Invoiced</span>
                    <div class="text-xl font-black text-slate-900 mt-2 font-mono">
                        ₹{{ Number(summary.total_invoiced).toLocaleString('en-IN') }}
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Gross services billed</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Total Collected</span>
                    <div class="text-xl font-black text-emerald-600 mt-2 font-mono">
                        ₹{{ Number(summary.total_collected).toLocaleString('en-IN') }}
                    </div>
                    <span class="text-[10px] text-emerald-700 mt-1 block font-semibold">Cash, UPI, Card</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Outstanding Balances</span>
                    <div class="text-xl font-black text-amber-600 mt-2 font-mono">
                        ₹{{ Number(summary.total_outstanding).toLocaleString('en-IN') }}
                    </div>
                    <span class="text-[10px] text-amber-700 mt-1 block font-semibold">Receivables due</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Clinic Expenses</span>
                    <div class="text-xl font-black text-red-600 mt-2 font-mono">
                        ₹{{ Number(summary.total_expenses).toLocaleString('en-IN') }}
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Supplies & Utilities</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Net Surplus / Earnings</span>
                    <div class="text-xl font-black text-indigo-600 mt-2 font-mono">
                        ₹{{ Number(summary.net_earnings).toLocaleString('en-IN') }}
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Collections - Expenses</span>
                </div>
            </div>

            <!-- Two-Column Breakdown: Payment Methods & Appointment Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Collections by Payment Method -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <CreditCard class="w-4 h-4 text-emerald-600" />
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Collections by Payment Method</h3>
                        </div>
                    </div>

                    <div v-if="paymentMethods.length === 0" class="py-8 text-center text-slate-400 text-xs">
                        No transactions recorded for this period.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="pm in paymentMethods"
                            :key="pm.payment_method"
                            class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs"
                        >
                            <div>
                                <span class="font-bold text-slate-900 uppercase">{{ pm.payment_method }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ pm.count }} receipts generated</span>
                            </div>
                            <span class="font-black text-emerald-700 text-sm font-mono">
                                ₹{{ Number(pm.total).toLocaleString('en-IN') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Appointment Volume & Cancellation Analytics -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <Calendar class="w-4 h-4 text-emerald-600" />
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Appointment Volume & Retention</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="block text-slate-400 text-[10px] font-bold uppercase">Total Bookings</span>
                            <span class="text-2xl font-black text-slate-900 block mt-1">{{ summary.total_appointments }}</span>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                            <span class="block text-emerald-800 text-[10px] font-bold uppercase">Completed Consultations</span>
                            <span class="text-2xl font-black text-emerald-700 block mt-1">{{ summary.completed_appointments }}</span>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-red-50/70 border border-red-100">
                            <span class="block text-red-800 text-[10px] font-bold uppercase">Cancelled Visits</span>
                            <span class="text-2xl font-black text-red-600 block mt-1">{{ summary.cancelled_appointments }}</span>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-slate-100 border border-slate-200">
                            <span class="block text-slate-600 text-[10px] font-bold uppercase">No-Shows</span>
                            <span class="text-2xl font-black text-slate-700 block mt-1">{{ summary.no_show_appointments }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Performance Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Users class="w-4 h-4 text-emerald-600" />
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Doctor Clinical Consultations & Volume</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="py-3 px-5">Doctor Name</th>
                                <th class="py-3 px-5">Specialization</th>
                                <th class="py-3 px-5 text-center">Consultations Conducted</th>
                                <th class="py-3 px-5 text-center">Completed Appointments</th>
                                <th class="py-3 px-5 text-right">Fee Rate</th>
                                <th class="py-3 px-5 text-right">Est. Consultation Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="d in doctorStats" :key="d.doctor_id" class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-5 font-bold text-slate-900">{{ d.name }}</td>
                                <td class="py-3.5 px-5 text-slate-600">{{ d.specialization }}</td>
                                <td class="py-3.5 px-5 text-center font-bold text-emerald-700">{{ d.visits_count }}</td>
                                <td class="py-3.5 px-5 text-center text-slate-700">{{ d.completed_appointments }}</td>
                                <td class="py-3.5 px-5 text-right font-mono">₹{{ Number(d.consultation_fee).toFixed(0) }}</td>
                                <td class="py-3.5 px-5 text-right font-black text-slate-900 font-mono">
                                    ₹{{ Number(d.estimated_revenue).toLocaleString('en-IN') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
