<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeft,
    Printer,
    CreditCard,
    Plus,
    CheckCircle2,
    Clock,
    X,
    Receipt,
    User
} from 'lucide-vue-next';

const props = defineProps({
    invoice: Object,
});

const paymentModalOpen = ref(false);

const paymentForm = useForm({
    invoice_id: props.invoice.id,
    amount: props.invoice.balance_amount,
    payment_method: 'upi',
    transaction_reference: '',
    payment_date: new Date().toISOString().substring(0, 10),
    notes: '',
});

const submitPayment = () => {
    paymentForm.post(route('payments.store'), {
        onSuccess: () => {
            paymentModalOpen.value = false;
            paymentForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="'Invoice - ' + invoice.invoice_number" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('invoices.index')"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-black text-slate-900 tracking-tight">Invoice #{{ invoice.invoice_number }}</h1>
                            <span
                                :class="[
                                    'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    invoice.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : '',
                                    invoice.payment_status === 'partially_paid' ? 'bg-amber-100 text-amber-800' : '',
                                    invoice.payment_status === 'unpaid' ? 'bg-red-100 text-red-800' : '',
                                ]"
                            >
                                {{ invoice.payment_status.replace('_', ' ') }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">Date: {{ invoice.invoice_date }} • Due: {{ invoice.due_date || invoice.invoice_date }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="invoice.balance_amount > 0"
                        type="button"
                        @click="paymentModalOpen = true"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                    >
                        <CreditCard class="w-4 h-4" />
                        <span>Record Payment</span>
                    </button>

                    <Link
                        :href="route('invoices.print', invoice.id)"
                        target="_blank"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                    >
                        <Printer class="w-4 h-4 text-emerald-400" />
                        <span>Print Receipt</span>
                    </Link>
                </div>
            </div>

            <!-- Patient Information Banner -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 flex items-center justify-between">
                <div>
                    <span class="block text-slate-400 text-[10px] font-bold uppercase">Patient</span>
                    <Link :href="route('patients.show', invoice.patient?.id)" class="text-base font-bold text-slate-900 hover:text-emerald-600">
                        {{ invoice.patient?.full_name }}
                    </Link>
                    <span class="text-xs text-slate-500 ml-2">({{ invoice.patient?.patient_uid }} • {{ invoice.patient?.phone }})</span>
                </div>

                <div class="text-right">
                    <span class="block text-slate-400 text-[10px] font-bold uppercase">Balance Due</span>
                    <span class="text-lg font-black" :class="invoice.balance_amount > 0 ? 'text-amber-600' : 'text-slate-400'">
                        ₹{{ Number(invoice.balance_amount).toLocaleString('en-IN') }}
                    </span>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    Itemized Billing Services
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Service / Item Description</th>
                                <th class="py-2.5 px-3 text-right">Unit Price</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in invoice.items" :key="item.id">
                                <td class="py-3 px-3 text-slate-400">{{ idx + 1 }}</td>
                                <td class="py-3 px-3 font-bold text-slate-900">{{ item.description }}</td>
                                <td class="py-3 px-3 text-right font-medium text-slate-700">₹{{ Number(item.unit_price).toFixed(2) }}</td>
                                <td class="py-3 px-3 text-center text-slate-600">{{ item.quantity }}</td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900">₹{{ Number(item.total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Calculation Summary -->
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <div class="w-72 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal:</span>
                            <span class="font-bold text-slate-900">₹{{ Number(invoice.subtotal).toLocaleString('en-IN') }}</span>
                        </div>
                        <div v-if="invoice.discount_amount > 0" class="flex justify-between text-emerald-700">
                            <span>Discount:</span>
                            <span>-₹{{ Number(invoice.discount_amount).toLocaleString('en-IN') }}</span>
                        </div>
                        <div v-if="invoice.tax_amount > 0" class="flex justify-between text-slate-600">
                            <span>Tax / GST:</span>
                            <span>+₹{{ Number(invoice.tax_amount).toLocaleString('en-IN') }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-black text-slate-900">
                            <span>Total Billed:</span>
                            <span>₹{{ Number(invoice.total_amount).toLocaleString('en-IN') }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700 font-bold">
                            <span>Paid to Date:</span>
                            <span>₹{{ Number(invoice.paid_amount).toLocaleString('en-IN') }}</span>
                        </div>
                        <div class="flex justify-between text-base font-black pt-1 border-t border-dashed border-slate-200" :class="invoice.balance_amount > 0 ? 'text-amber-600' : 'text-slate-400'">
                            <span>Balance Due:</span>
                            <span>₹{{ Number(invoice.balance_amount).toLocaleString('en-IN') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments History Receipts -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                        Payment Receipts History ({{ invoice.payments?.length || 0 }})
                    </h3>
                </div>

                <div v-if="!invoice.payments || invoice.payments.length === 0" class="p-4 text-center text-slate-400 text-xs">
                    No payment recorded yet for this invoice.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">Receipt #</th>
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Method</th>
                                <th class="py-2.5 px-3">Amount</th>
                                <th class="py-2.5 px-3">Reference</th>
                                <th class="py-2.5 px-3">Received By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="pay in invoice.payments" :key="pay.id">
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ pay.payment_number }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ pay.payment_date }}</td>
                                <td class="py-2.5 px-3 uppercase font-bold text-slate-700">{{ pay.payment_method }}</td>
                                <td class="py-2.5 px-3 font-black text-emerald-700">₹{{ Number(pay.amount).toLocaleString('en-IN') }}</td>
                                <td class="py-2.5 px-3 text-slate-500 font-mono text-[11px]">{{ pay.transaction_reference || '—' }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ pay.received_by?.name || 'System' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Record Payment Modal -->
        <div v-if="paymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-slate-900">Record Payment</h3>
                    <button type="button" @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Amount to Collect (₹) *</label>
                        <input
                            type="number"
                            v-model="paymentForm.amount"
                            min="1"
                            :max="invoice.balance_amount"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Maximum balance due: ₹{{ invoice.balance_amount }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Payment Method *</label>
                        <select
                            v-model="paymentForm.payment_method"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / QR Code</option>
                            <option value="card">Debit / Credit Card</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Transaction Ref / Cheque No.</label>
                        <input
                            type="text"
                            v-model="paymentForm.transaction_reference"
                            placeholder="e.g. UPI Ref / Last 4 Digits"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Payment Date *</label>
                        <input
                            type="date"
                            v-model="paymentForm.payment_date"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="paymentModalOpen = false"
                            class="px-4 py-2 text-slate-500 hover:bg-slate-100 rounded-xl font-bold"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="paymentForm.processing"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition"
                        >
                            Confirm Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
