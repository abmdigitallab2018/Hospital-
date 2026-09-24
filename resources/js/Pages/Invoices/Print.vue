<script setup>
import { Head } from '@inertiajs/vue3';
import { Printer, ArrowLeft, HeartPulse } from 'lucide-vue-next';

defineProps({
    invoice: Object,
});

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head :title="'Invoice Receipt - ' + invoice.invoice_number" />

    <div class="min-h-screen bg-slate-100 p-4 sm:p-8 flex flex-col items-center print:bg-white print:p-0">
        <!-- Print Toolbar -->
        <div class="max-w-3xl w-full mb-6 flex items-center justify-between print:hidden">
            <button
                type="button"
                @click="window.close()"
                class="px-4 py-2 bg-white text-slate-700 font-bold text-xs rounded-xl shadow-xs border border-slate-200 hover:bg-slate-50 transition flex items-center gap-1.5"
            >
                <ArrowLeft class="w-4 h-4" />
                <span>Close</span>
            </button>

            <button
                type="button"
                @click="triggerPrint"
                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center gap-2"
            >
                <Printer class="w-4 h-4" />
                <span>Print Invoice Receipt</span>
            </button>
        </div>

        <!-- Sheet -->
        <div class="max-w-3xl w-full bg-white rounded-3xl shadow-xl print:shadow-none print:rounded-none border border-slate-200/80 print:border-none p-8 sm:p-12 space-y-6 text-slate-800 font-sans">
            <!-- Header -->
            <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-black flex-shrink-0 shadow-md">
                        <HeartPulse class="w-8 h-8 text-white" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none">
                            {{ invoice.clinic?.name || 'CarePulse Clinic' }}
                        </h1>
                        <p class="text-xs text-slate-600 mt-1">
                            {{ invoice.clinic?.address }}, {{ invoice.clinic?.city }}, {{ invoice.clinic?.state }} - {{ invoice.clinic?.postal_code }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Tel: {{ invoice.clinic?.phone }} • Email: {{ invoice.clinic?.email }}
                        </p>
                        <p v-if="invoice.clinic?.tax_number" class="text-[10px] text-slate-400 font-mono mt-0.5">
                            GSTIN / Tax ID: {{ invoice.clinic.tax_number }}
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-black text-xs uppercase tracking-wider">
                        Medical Invoice
                    </span>
                    <h2 class="text-base font-black text-slate-900 font-mono mt-2">
                        {{ invoice.invoice_number }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Date: <strong>{{ invoice.invoice_date }}</strong>
                    </p>
                    <p class="text-[11px] font-bold mt-1 uppercase" :class="invoice.payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700'">
                        {{ invoice.payment_status.replace('_', ' ') }}
                    </p>
                </div>
            </div>

            <!-- Billed To Patient -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Billed Patient</span>
                    <strong class="text-slate-900 text-sm block">{{ invoice.patient?.full_name }}</strong>
                    <span class="text-slate-500 text-[11px]">{{ invoice.patient?.gender }} • {{ invoice.patient?.age ? invoice.patient.age + ' yrs' : '' }}</span>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Patient UID & Contact</span>
                    <span class="font-bold text-slate-800 block">{{ invoice.patient?.patient_uid }}</span>
                    <span class="text-slate-600 text-[11px] block">{{ invoice.patient?.phone }}</span>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Billing Address</span>
                    <span class="text-slate-700 text-[11px] block">
                        {{ invoice.patient?.city || 'Local Patient' }}, {{ invoice.patient?.state }}
                    </span>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="overflow-x-auto min-h-[160px]">
                <table class="w-full text-left text-xs border border-slate-200 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4 w-10">#</th>
                            <th class="py-2.5 px-4">Service Description</th>
                            <th class="py-2.5 px-4 text-right w-24">Unit Rate</th>
                            <th class="py-2.5 px-4 text-center w-16">Qty</th>
                            <th class="py-2.5 px-4 text-right w-28">Amount (INR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(item, idx) in invoice.items" :key="item.id">
                            <td class="py-3 px-4 text-slate-400 font-mono">{{ idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ item.description }}</td>
                            <td class="py-3 px-4 text-right text-slate-700 font-mono">₹{{ Number(item.unit_price).toFixed(2) }}</td>
                            <td class="py-3 px-4 text-center text-slate-600">{{ item.quantity }}</td>
                            <td class="py-3 px-4 text-right font-black text-slate-900 font-mono">₹{{ Number(item.total).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Financial Totals -->
            <div class="flex justify-end pt-2">
                <div class="w-72 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal:</span>
                        <span class="font-bold text-slate-900 font-mono">₹{{ Number(invoice.subtotal).toLocaleString('en-IN') }}</span>
                    </div>
                    <div v-if="invoice.discount_amount > 0" class="flex justify-between text-emerald-700">
                        <span>Discount:</span>
                        <span class="font-mono">-₹{{ Number(invoice.discount_amount).toLocaleString('en-IN') }}</span>
                    </div>
                    <div v-if="invoice.tax_amount > 0" class="flex justify-between text-slate-600">
                        <span>Tax / GST:</span>
                        <span class="font-mono">+₹{{ Number(invoice.tax_amount).toLocaleString('en-IN') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-900 flex justify-between text-sm font-black text-slate-900">
                        <span>Grand Total:</span>
                        <span class="font-mono">₹{{ Number(invoice.total_amount).toLocaleString('en-IN') }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-700 font-bold">
                        <span>Amount Paid:</span>
                        <span class="font-mono">₹{{ Number(invoice.paid_amount).toLocaleString('en-IN') }}</span>
                    </div>
                    <div class="flex justify-between text-base font-black pt-1 border-t border-dashed border-slate-300" :class="invoice.balance_amount > 0 ? 'text-amber-700' : 'text-slate-400'">
                        <span>Balance Due:</span>
                        <span class="font-mono">₹{{ Number(invoice.balance_amount).toLocaleString('en-IN') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Receipts Log -->
            <div v-if="invoice.payments?.length > 0" class="pt-4 border-t border-slate-200">
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Receipts Received</h4>
                <div class="divide-y divide-slate-100 bg-slate-50 rounded-2xl p-3 text-[11px]">
                    <div v-for="p in invoice.payments" :key="p.id" class="py-1 flex justify-between">
                        <span>{{ p.payment_number }} ({{ p.payment_date }}) • {{ p.payment_method.toUpperCase() }} <span v-if="p.transaction_reference">[{{ p.transaction_reference }}]</span></span>
                        <strong class="text-emerald-700 font-mono">₹{{ Number(p.amount).toLocaleString('en-IN') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Signatures Footer -->
            <div class="pt-12 flex items-end justify-between border-t border-slate-200 text-xs">
                <div>
                    <span class="block text-slate-400 text-[10px]">Payment terms: Due upon receipt.</span>
                    <span class="block text-slate-400 text-[10px]">Computer generated invoice. No physical signature required.</span>
                </div>

                <div class="text-center w-56">
                    <div class="h-10 border-b border-slate-300"></div>
                    <span class="block font-bold text-slate-800 text-xs mt-1">Authorized Billing Desk</span>
                    <span class="block text-[10px] text-slate-400 font-mono">{{ invoice.clinic?.name }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        background: #ffffff !important;
    }
}
</style>
