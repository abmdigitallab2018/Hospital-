<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import { Plus, Edit, ToggleLeft, ToggleRight, Star, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    plans: Array,
});

const addModal = ref(false);
const editModal = ref(false);
const selectedPlan = ref(null);

const addForm = useForm({
    name: '',
    slug: '',
    price_monthly: '',
    price_yearly: '',
    max_doctors: '',
    max_patients_monthly: '',
    max_staff: '',
    features: '',
    is_active: true,
    is_featured: false,
    description: '',
});

const editForm = useForm({
    name: '',
    slug: '',
    price_monthly: '',
    price_yearly: '',
    max_doctors: '',
    max_patients_monthly: '',
    max_staff: '',
    features: '',
    is_active: true,
    is_featured: false,
    description: '',
});

const openEdit = (plan) => {
    selectedPlan.value = plan;
    Object.assign(editForm, {
        name: plan.name,
        slug: plan.slug,
        price_monthly: plan.price_monthly,
        price_yearly: plan.price_yearly,
        max_doctors: plan.max_doctors,
        max_patients_monthly: plan.max_patients_monthly,
        max_staff: plan.max_staff,
        features: Array.isArray(plan.features) ? plan.features.join('\n') : (plan.features || ''),
        is_active: plan.is_active,
        is_featured: plan.is_featured,
        description: plan.description || '',
    });
    editModal.value = true;
};

const submitAdd = () => {
    addForm.post(route('superadmin.plans.store'), {
        onSuccess: () => {
            addModal.value = false;
            addForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.put(route('superadmin.plans.update', selectedPlan.value.id), {
        onSuccess: () => { editModal.value = false; },
    });
};

const togglePlan = (plan) => {
    router.post(route('superadmin.plans.toggle', plan.id));
};
</script>

<template>
    <SuperAdminLayout>
        <Head title="Subscription Plans" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Subscription Plans</h1>
                    <p class="text-slate-400 text-sm mt-1">Manage platform subscription tiers and pricing</p>
                </div>
                <AppButton @click="addModal = true">
                    <Plus class="w-4 h-4 mr-1" /> New Plan
                </AppButton>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="plan in plans" :key="plan.id"
                    :class="['bg-slate-950 rounded-3xl border p-6 flex flex-col justify-between transition-all', plan.is_active ? 'border-indigo-700/60' : 'border-slate-800 opacity-70']">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <Star class="w-4 h-4 text-amber-400" v-if="plan.is_featured" />
                                    <h3 class="font-bold text-white text-lg">{{ plan.name }}</h3>
                                </div>
                                <p v-if="plan.description" class="text-xs text-slate-400 mt-0.5">{{ plan.description }}</p>
                            </div>
                            <span :class="['px-2 py-0.5 text-[10px] font-bold uppercase rounded-full', plan.is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-400']">
                                {{ plan.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="text-center py-4 bg-slate-900 rounded-xl">
                            <div>
                                <span class="text-3xl font-black text-white">
                                    {{ parseFloat(plan.price_monthly) === 0 ? 'Free' : ('₹' + parseInt(plan.price_monthly).toLocaleString('en-IN')) }}
                                </span>
                                <span v-if="parseFloat(plan.price_monthly) > 0" class="text-slate-400 text-sm">/mo</span>
                            </div>
                            <div v-if="parseFloat(plan.price_yearly) > 0" class="text-xs text-slate-500 mt-1">
                                ₹{{ parseInt(plan.price_yearly).toLocaleString('en-IN') }}/yr
                            </div>
                        </div>

                        <div class="space-y-2 text-sm pt-2 border-t border-slate-800">
                            <div class="flex justify-between text-slate-300">
                                <span class="text-slate-400">Max Doctors</span>
                                <span class="font-semibold">{{ plan.max_doctors }}</span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span class="text-slate-400">Patients / mo</span>
                                <span class="font-semibold">{{ plan.max_patients_monthly?.toLocaleString('en-IN') }}</span>
                            </div>
                            <div v-if="plan.max_staff" class="flex justify-between text-slate-300">
                                <span class="text-slate-400">Max Staff</span>
                                <span class="font-semibold">{{ plan.max_staff }}</span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span class="text-slate-400">Active Subscribers</span>
                                <span class="font-semibold text-indigo-400">{{ plan.subscriptions_count ?? 0 }}</span>
                            </div>
                        </div>

                        <ul v-if="plan.features && Array.isArray(plan.features) && plan.features.length" class="space-y-1 pt-2">
                            <li v-for="(feature, i) in plan.features" :key="i"
                                class="flex items-center gap-2 text-xs text-slate-300">
                                <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" />
                                {{ feature }}
                            </li>
                        </ul>

                        <div class="flex gap-2 pt-2">
                            <button @click="openEdit(plan)"
                                class="flex-1 flex items-center justify-center gap-1 px-3 py-2 border border-slate-700 rounded-lg text-sm text-slate-300 hover:bg-slate-800 transition">
                                <Edit class="w-3.5 h-3.5" /> Edit
                            </button>
                            <button @click="togglePlan(plan)"
                                :class="['flex-1 flex items-center justify-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition', plan.is_active ? 'bg-red-900/40 text-red-400 hover:bg-red-900/60' : 'bg-emerald-900/40 text-emerald-400 hover:bg-emerald-900/60']">
                                <ToggleLeft v-if="plan.is_active" class="w-3.5 h-3.5" />
                                <ToggleRight v-else class="w-3.5 h-3.5" />
                                {{ plan.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!plans || plans.length === 0" class="col-span-3 text-center py-16 text-slate-500">
                    No subscription plans yet. Create your first plan.
                </div>
            </div>
        </div>

        <!-- Add Plan Modal -->
        <AppModal :show="addModal" title="Create Subscription Plan" @close="addModal = false" max-width="lg">
            <form @submit.prevent="submitAdd" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Plan Name *</label>
                        <input v-model="addForm.name" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="e.g. Starter" />
                        <p v-if="addForm.errors.name" class="text-red-500 text-xs mt-1">{{ addForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
                        <input v-model="addForm.slug" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="e.g. starter" />
                        <p v-if="addForm.errors.slug" class="text-red-500 text-xs mt-1">{{ addForm.errors.slug }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input v-model="addForm.description" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Brief plan description" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Price (₹)</label>
                        <input v-model="addForm.price_monthly" type="number" step="0.01" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0 for free" />
                        <p v-if="addForm.errors.price_monthly" class="text-red-500 text-xs mt-1">{{ addForm.errors.price_monthly }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Yearly Price (₹)</label>
                        <input v-model="addForm.price_yearly" type="number" step="0.01" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0 if no yearly plan" />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Max Doctors</label>
                        <input v-model="addForm.max_doctors" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Patients/mo</label>
                        <input v-model="addForm.max_patients_monthly" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Max Staff</label>
                        <input v-model="addForm.max_staff" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Features (one per line)</label>
                    <textarea v-model="addForm.features" rows="4" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Appointments&#10;Billing&#10;Reports"></textarea>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input v-model="addForm.is_active" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input v-model="addForm.is_featured" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        Featured
                    </label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="addModal = false" class="flex-1 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" :disabled="addForm.processing" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                        {{ addForm.processing ? 'Creating...' : 'Create Plan' }}
                    </button>
                </div>
            </form>
        </AppModal>

        <!-- Edit Plan Modal -->
        <AppModal :show="editModal" title="Edit Subscription Plan" @close="editModal = false" max-width="lg">
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Plan Name *</label>
                        <input v-model="editForm.name" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="editForm.errors.name" class="text-red-500 text-xs mt-1">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
                        <input v-model="editForm.slug" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input v-model="editForm.description" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Price (₹)</label>
                        <input v-model="editForm.price_monthly" type="number" step="0.01" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Yearly Price (₹)</label>
                        <input v-model="editForm.price_yearly" type="number" step="0.01" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Max Doctors</label>
                        <input v-model="editForm.max_doctors" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Patients/mo</label>
                        <input v-model="editForm.max_patients_monthly" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Max Staff</label>
                        <input v-model="editForm.max_staff" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Features (one per line)</label>
                    <textarea v-model="editForm.features" rows="4" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input v-model="editForm.is_active" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input v-model="editForm.is_featured" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        Featured
                    </label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="editModal = false" class="flex-1 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" :disabled="editForm.processing" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                        {{ editForm.processing ? 'Saving...' : 'Update Plan' }}
                    </button>
                </div>
            </form>
        </AppModal>
    </SuperAdminLayout>
</template>
