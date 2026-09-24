<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import {
    Building2,
    CreditCard,
    Users,
    Stethoscope,
    ArrowUpRight,
    Plus,
    CheckCircle2,
    ShieldAlert,
    ExternalLink
} from 'lucide-vue-next';

defineProps({
    stats: Object,
    clinics: Array,
    plans: Array,
});

const switchClinic = (clinicId) => {
    router.post(route('clinic.switch'), { clinic_id: clinicId });
};
</script>

<template>
    <SuperAdminLayout>
        <Head title="Super Admin Platform Overview" />

        <div class="space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">SaaS Platform Overview</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Cross-tenant infrastructure, active clinic subscriptions, and platform utilization.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="route('superadmin.clinics')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/25 transition flex items-center gap-1.5"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Onboard New Clinic</span>
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-950 p-5 rounded-3xl border border-slate-800">
                    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                        <span>Total Clinics</span>
                        <Building2 class="w-5 h-5 text-indigo-400" />
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-white">{{ stats.total_clinics }}</span>
                        <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">
                            {{ stats.active_clinics }} Active
                        </span>
                    </div>
                </div>

                <div class="bg-slate-950 p-5 rounded-3xl border border-slate-800">
                    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                        <span>Active Subscriptions</span>
                        <CreditCard class="w-5 h-5 text-emerald-400" />
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-white">{{ stats.active_subscriptions }}</span>
                        <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">100% Health</span>
                    </div>
                </div>

                <div class="bg-slate-950 p-5 rounded-3xl border border-slate-800">
                    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                        <span>Total Doctors</span>
                        <Stethoscope class="w-5 h-5 text-teal-400" />
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-white">{{ stats.total_doctors }}</span>
                        <span class="text-xs text-slate-400">Across clinics</span>
                    </div>
                </div>

                <div class="bg-slate-950 p-5 rounded-3xl border border-slate-800">
                    <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                        <span>Total Patients</span>
                        <Users class="w-5 h-5 text-cyan-400" />
                    </div>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-black text-white">{{ stats.total_patients }}</span>
                        <span class="text-xs text-slate-400">EHR records</span>
                    </div>
                </div>
            </div>

            <!-- Clinics Table -->
            <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-white">Clinic Tenants</h2>
                        <p class="text-xs text-slate-400">Onboarded polyclinics and solo practitioners</p>
                    </div>

                    <Link :href="route('superadmin.clinics')" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1">
                        <span>Directory</span>
                        <ArrowUpRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/60 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-5">Clinic Name & City</th>
                                <th class="py-3 px-5">Active Plan</th>
                                <th class="py-3 px-5">Doctors</th>
                                <th class="py-3 px-5">Contact</th>
                                <th class="py-3 px-5">Status</th>
                                <th class="py-3 px-5 text-right">Switch Workspace</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="c in clinics" :key="c.id" class="hover:bg-slate-900/40 transition">
                                <td class="py-3.5 px-5">
                                    <span class="font-bold text-white block text-sm">{{ c.name }}</span>
                                    <span class="text-[11px] text-slate-400">{{ c.city }}, {{ c.state }}</span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        {{ c.subscriptions?.[0]?.plan?.name || 'Pro Plan' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 font-bold text-slate-300">
                                    {{ c.doctors?.length || 0 }} Doctors
                                </td>
                                <td class="py-3.5 px-5 text-slate-400">
                                    {{ c.phone || c.email }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase', c.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400']">
                                        {{ c.status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <button
                                        type="button"
                                        @click="switchClinic(c.id)"
                                        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1.5"
                                    >
                                        <span>Open Clinic Portal</span>
                                        <ExternalLink class="w-3.5 h-3.5 text-indigo-400" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
