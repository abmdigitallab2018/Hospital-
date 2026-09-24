<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import {
    Receipt,
    Printer,
    CreditCard,
    CheckCircle2,
    Clock,
    AlertCircle,
    ChevronDown,
    ChevronUp,
    ShieldCheck,
    Smartphone
} from 'lucide-vue-next';

const props = defineProps({
    patient: Object,
    invoices: Object,
});

const expandedInvoices = ref({});
const toggleExpand = (id) => {
    expandedInvoices.value[id] = !expandedInvoices.value[id];
};

// Payment Modal State
const showPayModal = ref(false);
const activeInvoice = ref(null);
const payForm = useForm({
    payment_method: 'upi',
});

const openPaymentModal = (invoice) => {
    activeInvoice.value = invoice;
    payForm.reset();
    payForm.payment_method = 'upi';
    showPayModal.value = true;
};

const submitPayment = () => {
    if (!activeInvoice.value) return;
    payForm.post(route('patient.invoices.pay', activeInvoice.value.id), {
        onSuccess: () => {
            showPayModal.value = false;
            activeInvoice.value = null;
        }
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const totalBilled = computed(() => {
    return (props.invoices.data || []).reduce((acc, inv) => acc + Number(inv.total_amount || 0), 0);
});

const totalOutstanding = computed(() => {
    return (props.invoices.data || []).reduce((acc, inv) => acc + Number(inv.balance_amount || 0), 0);
});
</script>

<template>
    <PatientPortalLayout>
        <Head title="My Invoices & Bills" />

        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                        <Receipt class="w-6 h-6 text-blue-600" />
                        Invoices & Billing Statements
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Track your clinical consultation fees, laboratory tests, procedures, and make secure online payments.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Outstanding</span>
                        <span class="text-base font-black" :class="totalOutstanding > 0 ? 'text-amber-600' : 'text-emerald-600'">
                            ₹{{ totalOutstanding.toLocaleString('en-IN') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!invoices.data || invoices.data.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                    <Receipt class="w-7 h-7" />
                </div>
                <h3 class="text-sm font-bold text-slate-800">No Invoices Found</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    You have no billing records or pending invoices linked to your account.
                </p>
            </div>

            <!-- Invoices List -->
            <div v-else class="space-y-4">
                <div
                    v-for="inv in invoices.data"
                    :key="inv.id"
                    class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden hover:shadow-md transition"
                >
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <!-- Left: Invoice Details -->
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <Receipt class="w-6 h-6" />
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-sm font-bold text-blue-700">{{ inv.invoice_number }}</span>
                                        <span class="text-[11px] font-medium text-slate-400">• {{ formatDate(inv.invoice_date) }}</span>
                                        
                                        <span
                                            v-if="inv.payment_status === 'paid'"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                        >
                                            <CheckCircle2 class="w-3 h-3" /> Fully Paid
                                        </span>
                                        <span
                                            v-else-if="inv.payment_status === 'partially_paid'"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                                        >
                                            <Clock class="w-3 h-3" /> Partially Paid
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                                        >
                                            <AlertCircle class="w-3 h-3" /> Unpaid
                                        </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-6 gap-y-1 text-xs text-slate-600">
                                        <div>
                                            <span class="text-slate-400">Total Billed:</span>
                                            <strong class="text-slate-900 ml-1">₹{{ Number(inv.total_amount).toLocaleString('en-IN') }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Paid:</span>
                                            <strong class="text-emerald-700 ml-1">₹{{ Number(inv.paid_amount).toLocaleString('en-IN') }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Balance:</span>
                                            <strong class="ml-1" :class="Number(inv.balance_amount) > 0 ? 'text-rose-600' : 'text-slate-900'">
                                                ₹{{ Number(inv.balance_amount).toLocaleString('en-IN') }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Actions -->
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="toggleExpand(inv.id)"
                                    class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 flex items-center gap-1.5"
                                >
                                    <span>Details</span>
                                    <ChevronDown v-if="!expandedInvoices[inv.id]" class="w-3.5 h-3.5" />
                                    <ChevronUp v-else class="w-3.5 h-3.5" />
                                </button>

                                <a
                                    :href="route('invoices.print', inv.id)"
                                    target="_blank"
                                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition"
                                >
                                    <Printer class="w-3.5 h-3.5 text-slate-500" />
                                    Print Receipt
                                </a>

                                <button
                                    v-if="Number(inv.balance_amount) > 0"
                                    type="button"
                                    @click="openPaymentModal(inv)"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5"
                                >
                                    <CreditCard class="w-3.5 h-3.5" />
                                    Pay Online (₹{{ Number(inv.balance_amount).toLocaleString('en-IN') }})
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Expandable Itemized Breakdown & Payment History -->
                    <div v-if="expandedInvoices[inv.id]" class="border-t border-slate-100 bg-slate-50/60 p-6 space-y-4">
                        <div>
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Itemized Services & Items</h4>
                            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                                        <tr>
                                            <th class="py-2.5 px-4">Item / Service</th>
                                            <th class="py-2.5 px-4 text-center">Qty</th>
                                            <th class="py-2.5 px-4 text-right">Unit Price</th>
                                            <th class="py-2.5 px-4 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-700">
                                        <tr v-for="item in inv.items" :key="item.id">
                                            <td class="py-2.5 px-4 font-medium">{{ item.item_name }}</td>
                                            <td class="py-2.5 px-4 text-center">{{ item.quantity }}</td>
                                            <td class="py-2.5 px-4 text-right font-mono">₹{{ Number(item.unit_price).toFixed(2) }}</td>
                                            <td class="py-2.5 px-4 text-right font-mono font-bold">₹{{ Number(item.total).toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Prior Payments Log -->
                        <div v-if="inv.payments && inv.payments.length > 0">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Transactions</h4>
                            <div class="space-y-1.5">
                                <div
                                    v-for="p in inv.payments"
                                    :key="p.id"
                                    class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between text-xs"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-emerald-700 font-bold">{{ p.payment_number }}</span>
                                        <span class="text-slate-400">• {{ formatDate(p.payment_date) }}</span>
                                        <span class="uppercase text-[10px] font-bold px-2 py-0.5 bg-slate-100 rounded text-slate-600">{{ p.payment_method }}</span>
                                        <span v-if="p.transaction_reference" class="font-mono text-[10px] text-slate-400">Ref: {{ p.transaction_reference }}</span>
                                    </div>
                                    <div class="font-mono font-bold text-emerald-700">
                                        +₹{{ Number(p.amount).toFixed(2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="invoices.links && invoices.links.length > 3" class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200/80">
                    <span class="text-xs text-slate-500">
                        Showing {{ invoices.from || 0 }} to {{ invoices.to || 0 }} of {{ invoices.total }} invoices
                    </span>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in invoices.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold transition',
                                link.active ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url && 'opacity-40 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Secure Online Payment Simulation Modal -->
        <AppModal
            :show="showPayModal"
            title="Instant Online Payment"
            @close="showPayModal = false"
        >
            <div v-if="activeInvoice" class="space-y-5">
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-blue-700 font-bold block">Invoice #{{ activeInvoice.invoice_number }}</span>
                        <span class="text-xs text-slate-500">Amount Due for Immediate Settlement</span>
                    </div>
                    <div class="text-2xl font-black text-blue-900">
                        ₹{{ Number(activeInvoice.balance_amount).toLocaleString('en-IN') }}
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                        Select Payment Method
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label
                            :class="[
                                'flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition',
                                payForm.payment_method === 'upi' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'
                            ]"
                        >
                            <input type="radio" value="upi" v-model="payForm.payment_method" class="sr-only" />
                            <Smartphone class="w-5 h-5 text-emerald-600" />
                            <div class="text-left text-xs">
                                <div class="font-bold">UPI / QR Code</div>
                                <div class="text-[10px] text-slate-400 font-normal">GPay, PhonePe, Paytm</div>
                            </div>
                        </label>

                        <label
                            :class="[
                                'flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition',
                                payForm.payment_method === 'card' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'
                            ]"
                        >
                            <input type="radio" value="card" v-model="payForm.payment_method" class="sr-only" />
                            <CreditCard class="w-5 h-5 text-blue-600" />
                            <div class="text-left text-xs">
                                <div class="font-bold">Debit / Credit Card</div>
                                <div class="text-[10px] text-slate-400 font-normal">Visa, Mastercard, RuPay</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-500 flex items-center gap-2">
                    <ShieldCheck class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>256-bit encrypted medical transaction simulated gateway. Instant clinic receipt generated.</span>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="showPayModal = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="submitPayment"
                        :disabled="payForm.processing"
                        class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition disabled:opacity-50"
                    >
                        {{ payForm.processing ? 'Processing Payment...' : 'Confirm & Pay ₹' + Number(activeInvoice.balance_amount).toLocaleString('en-IN') }}
                    </button>
                </div>
            </div>
        </AppModal>
    </PatientPortalLayout>
</template>
