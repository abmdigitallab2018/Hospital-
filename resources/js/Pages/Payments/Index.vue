<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { CreditCard, Search, Calendar, Download, User } from 'lucide-vue-next';

const props = defineProps({
    payments: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const method = ref(props.filters.payment_method || 'all');
const date = ref(props.filters.date || '');

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('payments.index'),
        {
            search: search.value || undefined,
            payment_method: method.value !== 'all' ? method.value : undefined,
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
        <Head title="Payments & Receipts Log" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Payments & Receipts Register</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Track cashier collections across Cash, UPI, Credit/Debit cards, and Bank transfers.
                    </p>
                </div>

                <Link
                    :href="route('invoices.index')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition"
                >
                    View Invoices
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search payment #, txn ref, patient..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>

                <select
                    v-model="method"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Payment Methods</option>
                    <option value="cash">Cash</option>
                    <option value="upi">UPI / QR</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                </select>

                <input
                    type="date"
                    v-model="date"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                />
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Receipt #</th>
                                <th class="py-3.5 px-5">Date</th>
                                <th class="py-3.5 px-5">Invoice #</th>
                                <th class="py-3.5 px-5">Patient</th>
                                <th class="py-3.5 px-5">Method</th>
                                <th class="py-3.5 px-5">Amount Collected</th>
                                <th class="py-3.5 px-5">Reference</th>
                                <th class="py-3.5 px-5">Cashier</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="payments.data.length === 0">
                                <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                    No payments found matching your search.
                                </td>
                            </tr>
                            <tr
                                v-for="p in payments.data"
                                :key="p.id"
                                class="hover:bg-slate-50/70 transition"
                            >
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">{{ p.payment_number }}</td>
                                <td class="py-3.5 px-5 text-slate-600 whitespace-nowrap">{{ p.payment_date }}</td>
                                <td class="py-3.5 px-5 font-mono font-bold">
                                    <Link :href="route('invoices.show', p.invoice_id)" class="text-emerald-700 hover:underline">
                                        {{ p.invoice?.invoice_number }}
                                    </Link>
                                </td>
                                <td class="py-3.5 px-5 font-bold text-slate-900">
                                    {{ p.invoice?.patient?.full_name }}
                                </td>
                                <td class="py-3.5 px-5 uppercase font-bold text-slate-700">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-[10px]">
                                        {{ p.payment_method }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 font-black text-emerald-700 text-sm">
                                    ₹{{ Number(p.amount).toLocaleString('en-IN') }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-500 font-mono text-[11px]">{{ p.transaction_reference || '—' }}</td>
                                <td class="py-3.5 px-5 text-slate-600">{{ p.received_by?.name || 'System' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="payments.links && payments.links.length > 3" class="px-5 py-3 border-t border-slate-100 flex justify-center text-xs">
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in payments.links"
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
