<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import AppEmptyState from '@/Components/UI/AppEmptyState.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import {
    TrendingDown, Plus, Edit, Trash2, Search
} from 'lucide-vue-next';

const props = defineProps({
    expenses: Object,
    totalThisMonth: Number,
    categories: Array,
    filters: Object,
});

const addModal = ref(false);
const editModal = ref(false);
const confirmDelete = ref(false);
const selectedExpense = ref(null);

const search = ref(props.filters?.search || '');
const category = ref(props.filters?.category || 'all');
const month = ref(props.filters?.month || new Date().toISOString().substring(0, 7));

const applyFilters = () => {
    router.get(route('expenses.index'), {
        search: search.value || undefined,
        category: category.value !== 'all' ? category.value : undefined,
        month: month.value || undefined,
    }, { preserveState: true, replace: true });
};

const addForm = useForm({
    title: '',
    category: '',
    amount: '',
    expense_date: new Date().toISOString().substring(0, 10),
    payment_method: 'cash',
    vendor: '',
    notes: '',
});

const editForm = useForm({
    title: '',
    category: '',
    amount: '',
    expense_date: '',
    payment_method: 'cash',
    vendor: '',
    notes: '',
});

const openEdit = (expense) => {
    selectedExpense.value = expense;
    editForm.title = expense.title;
    editForm.category = expense.category;
    editForm.amount = expense.amount;
    editForm.expense_date = expense.expense_date;
    editForm.payment_method = expense.payment_method;
    editForm.vendor = expense.vendor || '';
    editForm.notes = expense.notes || '';
    editModal.value = true;
};

const openDelete = (expense) => {
    selectedExpense.value = expense;
    confirmDelete.value = true;
};

const submitAdd = () => {
    addForm.post(route('expenses.store'), {
        onSuccess: () => {
            addModal.value = false;
            addForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.put(route('expenses.update', selectedExpense.value.id), {
        onSuccess: () => {
            editModal.value = false;
        },
    });
};

const deleteExpense = () => {
    router.delete(route('expenses.destroy', selectedExpense.value.id), {
        onSuccess: () => { confirmDelete.value = false; },
    });
};

const paymentMethodLabel = (m) => {
    const map = { cash: 'Cash', upi: 'UPI', card: 'Card', bank_transfer: 'Bank Transfer' };
    return map[m] || m;
};

const defaultCategories = [
    'Medicines & Supplies', 'Equipment Maintenance', 'Utilities', 'Staff Salaries',
    'Rent', 'Marketing', 'Office Supplies', 'Insurance', 'Travel', 'Miscellaneous',
];

const categoryOptions = computed(() => {
    const extras = (props.categories || []).filter(c => !defaultCategories.includes(c));
    return [...defaultCategories, ...extras];
});
</script>

<template>
    <AppLayout>
        <Head title="Expenses" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Expenses</h1>
                    <p class="text-sm text-slate-500 mt-1">Track clinic operational costs and expenditures</p>
                </div>
                <AppButton @click="addModal = true">
                    <Plus class="w-4 h-4 mr-1" />
                    Record Expense
                </AppButton>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <AppCard class="bg-red-50 border-red-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <TrendingDown class="w-5 h-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-xs text-red-600 font-medium">This Month Expenses</p>
                            <p class="text-xl font-bold text-red-700">
                                ₹{{ (totalThisMonth ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }) }}
                            </p>
                        </div>
                    </div>
                </AppCard>
            </div>

            <!-- Filters -->
            <AppCard>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search expense or vendor..."
                            class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <select v-model="category" @change="applyFilters"
                        class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="all">All Categories</option>
                        <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                    <input
                        v-model="month"
                        @change="applyFilters"
                        type="month"
                        class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </AppCard>

            <!-- Table -->
            <AppCard :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" v-if="expenses.data.length > 0">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Date</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Title</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Category</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Vendor</th>
                                <th class="text-left px-4 py-3 font-semibold text-slate-600">Payment</th>
                                <th class="text-right px-4 py-3 font-semibold text-slate-600">Amount</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="expense in expenses.data" :key="expense.id"
                                class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ expense.expense_date }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ expense.title }}</p>
                                    <p v-if="expense.notes" class="text-xs text-slate-400 truncate max-w-xs">{{ expense.notes }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <AppBadge variant="slate">{{ expense.category }}</AppBadge>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ expense.vendor || '—' }}</td>
                                <td class="px-4 py-3">
                                    <AppBadge variant="blue">{{ paymentMethodLabel(expense.payment_method) }}</AppBadge>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-red-600">
                                    ₹{{ parseFloat(expense.amount).toLocaleString('en-IN', { minimumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEdit(expense)"
                                            class="p-1.5 rounded hover:bg-blue-50 text-blue-600 transition-colors">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="openDelete(expense)"
                                            class="p-1.5 rounded hover:bg-red-50 text-red-500 transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <AppEmptyState v-else
                        title="No expenses recorded"
                        description="Start tracking your clinic's operational expenses."
                        :action="{ label: 'Record Expense', handler: () => addModal = true }"
                    />
                </div>

                <!-- Pagination -->
                <div v-if="expenses.last_page > 1" class="px-4 py-3 border-t border-slate-100 flex justify-between items-center">
                    <p class="text-sm text-slate-500">Showing {{ expenses.from }}–{{ expenses.to }} of {{ expenses.total }}</p>
                    <div class="flex gap-1">
                        <Link v-for="page in expenses.links" :key="page.label"
                            :href="page.url || '#'"
                            :class="['px-3 py-1 text-sm rounded', page.active ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100', !page.url && 'opacity-40 pointer-events-none']"
                            v-html="page.label"
                        />
                    </div>
                </div>
            </AppCard>
        </div>

        <!-- Add Expense Modal -->
        <AppModal :show="addModal" title="Record Expense" @close="addModal = false">
            <form @submit.prevent="submitAdd" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
                    <input v-model="addForm.title" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. Monthly electricity bill" />
                    <p v-if="addForm.errors.title" class="text-red-500 text-xs mt-1">{{ addForm.errors.title }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category *</label>
                        <select v-model="addForm.category" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select category</option>
                            <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                        <p v-if="addForm.errors.category" class="text-red-500 text-xs mt-1">{{ addForm.errors.category }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Amount (₹) *</label>
                        <input v-model="addForm.amount" type="number" step="0.01" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                        <p v-if="addForm.errors.amount" class="text-red-500 text-xs mt-1">{{ addForm.errors.amount }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date *</label>
                        <input v-model="addForm.expense_date" type="date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method *</label>
                        <select v-model="addForm.payment_method" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Vendor / Payee</label>
                    <input v-model="addForm.vendor" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Optional" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea v-model="addForm.notes" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Optional notes"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="addModal = false"
                        class="flex-1 px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" :disabled="addForm.processing"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                        {{ addForm.processing ? 'Saving...' : 'Record Expense' }}
                    </button>
                </div>
            </form>
        </AppModal>

        <!-- Edit Expense Modal -->
        <AppModal :show="editModal" title="Edit Expense" @close="editModal = false">
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
                    <input v-model="editForm.title" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    <p v-if="editForm.errors.title" class="text-red-500 text-xs mt-1">{{ editForm.errors.title }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category *</label>
                        <select v-model="editForm.category" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Amount (₹) *</label>
                        <input v-model="editForm.amount" type="number" step="0.01" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date *</label>
                        <input v-model="editForm.expense_date" type="date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                        <select v-model="editForm.payment_method" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Vendor</label>
                    <input v-model="editForm.vendor" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea v-model="editForm.notes" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="editModal = false"
                        class="flex-1 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" :disabled="editForm.processing"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                        {{ editForm.processing ? 'Saving...' : 'Update Expense' }}
                    </button>
                </div>
            </form>
        </AppModal>

        <!-- Confirm Delete -->
        <AppConfirmDialog
            :show="confirmDelete"
            title="Delete Expense"
            :message="`Are you sure you want to delete '${selectedExpense?.title}'? This action cannot be undone.`"
            confirm-label="Delete"
            confirm-variant="danger"
            @confirm="deleteExpense"
            @cancel="confirmDelete = false"
        />
    </AppLayout>
</template>
