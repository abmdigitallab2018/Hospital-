<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Receipt,
    Plus,
    Search,
    Printer,
    Eye,
    CreditCard,
    CheckCircle2,
    Calendar,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    invoices: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const date = ref(props.filters.date || '');

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('invoices.index'),
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
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
        <Head title="Billing & Invoices" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Invoices & Billing</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Patient billing, partial payments, receipts, and receivables reconciliation.
                    </p>
                </div>

                <Link
                    :href="route('invoices.create')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Create Invoice</span>
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search invoice number, patient name, UID..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>

                <select
                    v-model="status"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Payment Status</option>
                    <option value="paid">Paid in Full</option>
                    <option value="partially_paid">Partially Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>

                <input
                    type="date"
                    v-model="date"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                />
            </div>

            <!-- Invoices Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Invoice Number</th>
                                <th class="py-3.5 px-5">Date</th>
                                <th class="py-3.5 px-5">Patient</th>
                                <th class="py-3.5 px-5">Total Billed</th>
                                <th class="py-3.5 px-5">Paid</th>
                                <th class="py-3.5 px-5">Balance Due</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="invoices.data.length === 0">
                                <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                    No invoices found matching your filters.
                                </td>
                            </tr>
                            <tr
                                v-for="inv in invoices.data"
                                :key="inv.id"
                                class="hover:bg-slate-50/70 transition"
                            >
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                    <Link :href="route('invoices.show', inv.id)" class="hover:text-emerald-600">
                                        {{ inv.invoice_number }}
                                    </Link>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 whitespace-nowrap">{{ inv.invoice_date }}</td>
                                <td class="py-3.5 px-5">
                                    <Link :href="route('patients.show', inv.patient?.id)" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                        {{ inv.patient?.full_name }}
                                    </Link>
                                    <span class="text-[10px] text-slate-400">{{ inv.patient?.patient_uid }} • {{ inv.patient?.phone }}</span>
                                </td>
                                <td class="py-3.5 px-5 font-black text-slate-900">
                                    ₹{{ Number(inv.total_amount).toLocaleString('en-IN') }}
                                </td>
                                <td class="py-3.5 px-5 font-bold text-emerald-600">
                                    ₹{{ Number(inv.paid_amount).toLocaleString('en-IN') }}
                                </td>
                                <td class="py-3.5 px-5 font-bold" :class="inv.balance_amount > 0 ? 'text-amber-600' : 'text-slate-400'">
                                    ₹{{ Number(inv.balance_amount).toLocaleString('en-IN') }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                            inv.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : '',
                                            inv.payment_status === 'partially_paid' ? 'bg-amber-100 text-amber-800' : '',
                                            inv.payment_status === 'unpaid' ? 'bg-red-100 text-red-800' : '',
                                        ]"
                                    >
                                        {{ inv.payment_status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('invoices.print', inv.id)"
                                            target="_blank"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition"
                                            title="Print Invoice Receipt"
                                        >
                                            <Printer class="w-4 h-4" />
                                        </Link>

                                        <Link
                                            :href="route('invoices.show', inv.id)"
                                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition"
                                        >
                                            Details
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="invoices.links && invoices.links.length > 3" class="px-5 py-3 border-t border-slate-100 flex justify-center text-xs">
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in invoices.links"
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
