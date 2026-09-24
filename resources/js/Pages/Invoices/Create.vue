<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeft,
    Receipt,
    Plus,
    Trash2,
    Save,
    CreditCard,
    DollarSign,
    Percent
} from 'lucide-vue-next';

const props = defineProps({
    selectedPatient: Object,
    patients: Array,
    services: Array,
});

const form = useForm({
    patient_id: props.selectedPatient?.id || '',
    invoice_date: new Date().toISOString().substring(0, 10),
    due_date: new Date().toISOString().substring(0, 10),
    items: [
        {
            service_id: '',
            description: '',
            unit_price: 500,
            quantity: 1,
        }
    ],
    discount_amount: 0,
    tax_amount: 0,
    notes: '',

    initial_payment: {
        record: true,
        amount: 500,
        payment_method: 'upi',
        transaction_reference: '',
    },
});

const onServiceSelect = (item) => {
    if (!item.service_id) return;
    const svc = props.services.find(s => s.id == item.service_id);
    if (svc) {
        item.description = svc.name;
        item.unit_price = parseFloat(svc.price);
    }
};

const addItem = () => {
    form.items.push({
        service_id: '',
        description: '',
        unit_price: 0,
        quantity: 1,
    });
};

const removeItem = (idx) => {
    form.items.splice(idx, 1);
};

// Calculations
const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + ((parseFloat(item.unit_price) || 0) * (parseInt(item.quantity) || 1));
    }, 0);
});

const totalAmount = computed(() => {
    const sub = subtotal.value;
    const disc = parseFloat(form.discount_amount) || 0;
    const tax = parseFloat(form.tax_amount) || 0;
    return Math.max(0, sub - disc + tax);
});

const updateInitialPayment = () => {
    if (form.initial_payment.record) {
        form.initial_payment.amount = totalAmount.value;
    }
};

const submit = () => {
    form.post(route('invoices.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="Generate Invoice" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('invoices.index')"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Generate Patient Invoice</h1>
                    <p class="text-xs text-slate-500">Itemized billing for clinical consultations, lab tests, and procedures</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
                <!-- Patient & Dates -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Select Patient *</label>
                        <select
                            v-model="form.patient_id"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="" disabled>Choose patient...</option>
                            <option v-for="p in patients" :key="p.id" :value="p.id">
                                {{ p.first_name }} {{ p.last_name }} ({{ p.patient_uid }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Invoice Date *</label>
                        <input
                            type="date"
                            v-model="form.invoice_date"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Due Date</label>
                        <input
                            type="date"
                            v-model="form.due_date"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Itemized Services Table -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <label class="text-xs font-bold text-slate-900 uppercase tracking-wider">Itemized Line Items</label>
                        <button
                            type="button"
                            @click="addItem"
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Add Item</span>
                        </button>
                    </div>

                    <div
                        v-for="(item, idx) in form.items"
                        :key="idx"
                        class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs"
                    >
                        <div class="sm:col-span-4">
                            <label class="block font-semibold text-slate-600 mb-1">Pick Service / Procedure</label>
                            <select
                                v-model="item.service_id"
                                @change="onServiceSelect(item)"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="">Custom Entry...</option>
                                <option v-for="s in services" :key="s.id" :value="s.id">
                                    {{ s.name }} (₹{{ s.price }})
                                </option>
                            </select>
                        </div>

                        <div class="sm:col-span-4">
                            <label class="block font-semibold text-slate-600 mb-1">Description *</label>
                            <input
                                type="text"
                                v-model="item.description"
                                required
                                placeholder="Service or medicine description"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-600 mb-1">Rate (₹) *</label>
                            <input
                                type="number"
                                v-model="item.unit_price"
                                @input="updateInitialPayment"
                                required
                                min="0"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div class="sm:col-span-1">
                            <label class="block font-semibold text-slate-600 mb-1">Qty</label>
                            <input
                                type="number"
                                v-model="item.quantity"
                                @input="updateInitialPayment"
                                required
                                min="1"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-2 text-center focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div class="sm:col-span-1 text-right flex items-center justify-end gap-2 pt-4">
                            <button
                                v-if="form.items.length > 1"
                                type="button"
                                @click="removeItem(idx)"
                                class="p-1 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Totals Calculation Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Invoice Notes / Remarks</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Payment terms, insurance details, or cashier comments..."
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal:</span>
                            <span class="font-bold text-slate-900">₹{{ subtotal.toFixed(2) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Discount (₹):</span>
                            <input
                                type="number"
                                v-model="form.discount_amount"
                                @input="updateInitialPayment"
                                min="0"
                                class="w-24 text-right py-1 px-2 text-xs rounded-lg border border-slate-200 focus:outline-none"
                            />
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Taxes / GST (₹):</span>
                            <input
                                type="number"
                                v-model="form.tax_amount"
                                @input="updateInitialPayment"
                                min="0"
                                class="w-24 text-right py-1 px-2 text-xs rounded-lg border border-slate-200 focus:outline-none"
                            />
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex justify-between text-base font-black text-slate-900">
                            <span>Total Payable:</span>
                            <span class="text-emerald-700">₹{{ totalAmount.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Initial Payment Recording -->
                <div class="p-5 rounded-2xl bg-emerald-50/50 border border-emerald-100 space-y-3 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                        <input
                            type="checkbox"
                            v-model="form.initial_payment.record"
                            class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4"
                        />
                        <span>Collect Payment Now</span>
                    </label>

                    <div v-if="form.initial_payment.record" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Amount Paid (₹) *</label>
                            <input
                                type="number"
                                v-model="form.initial_payment.amount"
                                min="1"
                                :max="totalAmount"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-3 font-bold text-slate-900 bg-white focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Payment Method *</label>
                            <select
                                v-model="form.initial_payment.payment_method"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-3 bg-white focus:outline-none"
                            >
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="card">Debit / Credit Card</option>
                                <option value="bank_transfer">Bank Transfer / NEFT</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Transaction Ref / Cheque #</label>
                            <input
                                type="text"
                                v-model="form.initial_payment.transaction_reference"
                                placeholder="UPI Txn ID or Card Last 4"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-3 bg-white focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <Link
                        :href="route('invoices.index')"
                        class="px-4 py-2 font-bold text-xs text-slate-600 hover:bg-slate-100 rounded-xl"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2"
                    >
                        <Receipt class="w-4 h-4" />
                        <span>Save & Generate Invoice</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
