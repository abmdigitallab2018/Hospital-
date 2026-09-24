<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import {
    Building2,
    Plus,
    Search,
    X,
    ExternalLink,
    CheckCircle2,
    ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
    clinics: Object,
    plans: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const onboardModalOpen = ref(false);

const form = useForm({
    clinic_name: '',
    email: '',
    phone: '',
    city: '',
    state: '',
    subscription_plan_id: props.plans[0]?.id || '',
    admin_name: '',
    admin_email: '',
    admin_password: 'password',
});

const submit = () => {
    form.post(route('superadmin.clinics.store'), {
        onSuccess: () => {
            onboardModalOpen.value = false;
            form.reset();
        },
    });
};

const toggleStatus = (clinic) => {
    router.post(route('superadmin.clinics.toggle', clinic.id));
};

const switchClinic = (clinicId) => {
    router.post(route('clinic.switch'), { clinic_id: clinicId });
};

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('superadmin.clinics'),
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
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
    <SuperAdminLayout>
        <Head title="Clinics Directory" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">Clinics Directory</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                        Manage SaaS clinic tenants, subscriptions, and administrative access.
                    </p>
                </div>

                <button
                    type="button"
                    @click="onboardModalOpen = true"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/25 transition flex items-center gap-1.5 self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Onboard Clinic</span>
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-slate-950 rounded-2xl border border-slate-800 p-4 flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search clinic name, city, email..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                    />
                </div>

                <select
                    v-model="status"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl bg-slate-900 border border-slate-800 text-slate-300 focus:outline-none"
                >
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/60 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                            <tr>
                                <th class="py-3.5 px-5">Clinic Name</th>
                                <th class="py-3.5 px-5">Location</th>
                                <th class="py-3.5 px-5">Plan</th>
                                <th class="py-3.5 px-5">Primary Contact</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="c in clinics.data" :key="c.id" class="hover:bg-slate-900/40 transition">
                                <td class="py-3.5 px-5">
                                    <span class="font-bold text-white block text-sm">{{ c.name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ c.slug }}</span>
                                </td>
                                <td class="py-3.5 px-5 text-slate-300">
                                    {{ c.city }}, {{ c.state }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        {{ c.subscriptions?.[0]?.plan?.name || 'Professional' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-slate-400">
                                    <div>{{ c.phone }}</div>
                                    <div class="text-[10px]">{{ c.email }}</div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <button
                                        type="button"
                                        @click="toggleStatus(c)"
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition',
                                            c.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-400 hover:bg-red-500/20'
                                        ]"
                                    >
                                        {{ c.status }} (Toggle)
                                    </button>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="switchClinic(c.id)"
                                        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1.5"
                                    >
                                        <span>Enter Clinic</span>
                                        <ExternalLink class="w-3.5 h-3.5 text-indigo-400" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Onboard Clinic Modal -->
        <div v-if="onboardModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
            <div class="bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-800 text-slate-100 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                    <h3 class="text-base font-black text-white">Onboard New Clinic Tenant</h3>
                    <button type="button" @click="onboardModalOpen = false" class="text-slate-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Clinic Name *</label>
                        <input
                            type="text"
                            v-model="form.clinic_name"
                            required
                            placeholder="e.g. LifeLine Polyclinic"
                            class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none focus:border-indigo-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Clinic Email *</label>
                            <input
                                type="email"
                                v-model="form.email"
                                required
                                class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Clinic Phone *</label>
                            <input
                                type="text"
                                v-model="form.phone"
                                required
                                class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-300 mb-1">City *</label>
                            <input
                                type="text"
                                v-model="form.city"
                                required
                                class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-300 mb-1">State *</label>
                            <input
                                type="text"
                                v-model="form.state"
                                required
                                class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Subscription Plan *</label>
                        <select
                            v-model="form.subscription_plan_id"
                            required
                            class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                        >
                            <option v-for="p in plans" :key="p.id" :value="p.id">
                                {{ p.name }} (₹{{ Number(p.price_monthly).toLocaleString('en-IN') }}/mo)
                            </option>
                        </select>
                    </div>

                    <!-- Administrator Account Setup -->
                    <div class="pt-4 border-t border-slate-800 space-y-3">
                        <span class="block font-bold text-indigo-400 uppercase text-[10px] tracking-wider">Clinic Administrator Login</span>

                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Admin Full Name *</label>
                            <input
                                type="text"
                                v-model="form.admin_name"
                                required
                                class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Admin Email *</label>
                                <input
                                    type="email"
                                    v-model="form.admin_email"
                                    required
                                    class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Password *</label>
                                <input
                                    type="password"
                                    v-model="form.admin_password"
                                    required
                                    class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 py-2 px-3 text-white focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button
                            type="button"
                            @click="onboardModalOpen = false"
                            class="px-4 py-2 font-bold text-slate-400 hover:text-white"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg transition"
                        >
                            Onboard Clinic
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SuperAdminLayout>
</template>
