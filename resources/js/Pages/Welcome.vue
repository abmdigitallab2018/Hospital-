<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import {
    HeartPulse,
    Shield,
    Users,
    Calendar,
    Stethoscope,
    FileText,
    Receipt,
    CheckCircle2,
    ArrowRight,
    Sparkles,
    UserCheck,
    CreditCard,
    Building2,
    Clock,
    Lock
} from 'lucide-vue-next';

defineProps({
    plans: Array,
});

const quickLogin = (email, password) => {
    router.post(route('login'), {
        email: email,
        password: password,
        remember: true,
    });
};
</script>

<template>
    <Head title="CarePulse - Clinic Management & SaaS Platform" />

    <div class="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-emerald-500 selection:text-white">
        <!-- Top Navigation -->
        <header class="border-b border-slate-800 bg-slate-950/60 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center font-black shadow-lg shadow-emerald-500/20">
                        <HeartPulse class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <span class="block font-black text-lg text-white tracking-tight">CarePulse<span class="text-emerald-400">OS</span></span>
                        <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Clinic Cloud</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition flex items-center gap-2"
                    >
                        <span>Open Dashboard</span>
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-800 transition"
                        >
                            Sign In
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition"
                        >
                            Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-16 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.emerald.900),theme(colors.slate.900))] opacity-40"></div>

            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold mb-6">
                    <Sparkles class="w-3.5 h-3.5" />
                    <span>Multi-Tenant Healthcare SaaS Built for Laravel 12</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
                    Smart, connected care for <br />
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400">
                        modern clinics & doctors.
                    </span>
                </h1>

                <p class="mt-6 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    CarePulse streamlines patient registrations, live waiting queues, consultations with electronic vitals, customizable prescriptions, and full itemized billing with UPI & Card payments.
                </p>

                <!-- Quick Action Buttons -->
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('login')"
                        class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-xl shadow-emerald-500/25 transition flex items-center gap-2"
                    >
                        <span>Launch Demo Portal</span>
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                    <a
                        href="#demo-accounts"
                        class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm border border-slate-700 transition"
                    >
                        View Demo Accounts
                    </a>
                </div>
            </div>
        </section>

        <!-- One-Click Demo Accounts Switcher -->
        <section id="demo-accounts" class="py-12 bg-slate-950 border-y border-slate-800 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-black text-white">Experience All 6 Roles In One Click</h2>
                    <p class="mt-2 text-xs sm:text-sm text-slate-400">
                        Click on any role card below to instantly sign in and explore the tailored workspace.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Super Admin -->
                    <div class="bg-slate-900 border border-slate-800 hover:border-indigo-500/50 p-5 rounded-2xl transition group relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-400 text-[10px] font-bold uppercase tracking-wider">Super Admin</span>
                                <Shield class="w-5 h-5 text-indigo-400" />
                            </div>
                            <h3 class="font-bold text-white text-base">Platform Super Admin</h3>
                            <p class="text-xs text-slate-400 mt-1">Manage multiple clinics, subscriptions, plans and cross-tenant overview.</p>
                            <div class="mt-3 text-[11px] font-mono text-slate-400 bg-slate-950 p-2 rounded-lg">
                                <div><span class="text-slate-500">Email:</span> superadmin@cms.com</div>
                                <div><span class="text-slate-500">Pass:</span> password</div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="quickLogin('superadmin@cms.com', 'password')"
                            class="mt-4 w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <span>Sign In as Super Admin</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Clinic Admin -->
                    <div class="bg-slate-900 border border-slate-800 hover:border-emerald-500/50 p-5 rounded-2xl transition group relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-wider">Clinic Admin</span>
                                <Building2 class="w-5 h-5 text-emerald-400" />
                            </div>
                            <h3 class="font-bold text-white text-base">Apollo Care Admin</h3>
                            <p class="text-xs text-slate-400 mt-1">Full control over Apollo Care clinic, doctors, staff, fees, reports, settings.</p>
                            <div class="mt-3 text-[11px] font-mono text-slate-400 bg-slate-950 p-2 rounded-lg">
                                <div><span class="text-slate-500">Email:</span> admin@apollocare.com</div>
                                <div><span class="text-slate-500">Pass:</span> password</div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="quickLogin('admin@apollocare.com', 'password')"
                            class="mt-4 w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <span>Sign In as Clinic Admin</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Doctor -->
                    <div class="bg-slate-900 border border-slate-800 hover:border-teal-500/50 p-5 rounded-2xl transition group relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full bg-teal-500/20 text-teal-400 text-[10px] font-bold uppercase tracking-wider">Doctor (Cardiologist)</span>
                                <Stethoscope class="w-5 h-5 text-teal-400" />
                            </div>
                            <h3 class="font-bold text-white text-base">Dr. Rajesh Sharma</h3>
                            <p class="text-xs text-slate-400 mt-1">Conduct consultations, enter vitals, diagnosis, prescribe medicines, and print Rx.</p>
                            <div class="mt-3 text-[11px] font-mono text-slate-400 bg-slate-950 p-2 rounded-lg">
                                <div><span class="text-slate-500">Email:</span> doctor.sharma@apollocare.com</div>
                                <div><span class="text-slate-500">Pass:</span> password</div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="quickLogin('doctor.sharma@apollocare.com', 'password')"
                            class="mt-4 w-full py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <span>Sign In as Doctor</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Receptionist -->
                    <div class="bg-slate-900 border border-slate-800 hover:border-amber-500/50 p-5 rounded-2xl transition group relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-400 text-[10px] font-bold uppercase tracking-wider">Receptionist</span>
                                <Clock class="w-5 h-5 text-amber-400" />
                            </div>
                            <h3 class="font-bold text-white text-base">Sunita Rao (Desk)</h3>
                            <p class="text-xs text-slate-400 mt-1">Patient registration, slot booking, check-in, token generation, queue management.</p>
                            <div class="mt-3 text-[11px] font-mono text-slate-400 bg-slate-950 p-2 rounded-lg">
                                <div><span class="text-slate-500">Email:</span> reception@apollocare.com</div>
                                <div><span class="text-slate-500">Pass:</span> password</div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="quickLogin('reception@apollocare.com', 'password')"
                            class="mt-4 w-full py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <span>Sign In as Receptionist</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Accountant -->
                    <div class="bg-slate-900 border border-slate-800 hover:border-blue-500/50 p-5 rounded-2xl transition group relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-400 text-[10px] font-bold uppercase tracking-wider">Accountant</span>
                                <Receipt class="w-5 h-5 text-blue-400" />
                            </div>
                            <h3 class="font-bold text-white text-base">Kunal Verma (Accounts)</h3>
                            <p class="text-xs text-slate-400 mt-1">Invoices, partial payments, receipts, outstanding balances, financial reports.</p>
                            <div class="mt-3 text-[11px] font-mono text-slate-400 bg-slate-950 p-2 rounded-lg">
                                <div><span class="text-slate-500">Email:</span> accounts@apollocare.com</div>
                                <div><span class="text-slate-500">Pass:</span> password</div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="quickLogin('accounts@apollocare.com', 'password')"
                            class="mt-4 w-full py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <span>Sign In as Accountant</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Patient Portal -->
                    <div class="bg-slate-900 border border-slate-800 hover:border-cyan-500/50 p-5 rounded-2xl transition group relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-400 text-[10px] font-bold uppercase tracking-wider">Patient Portal</span>
                                <Users class="w-5 h-5 text-cyan-400" />
                            </div>
                            <h3 class="font-bold text-white text-base">Rahul Deshmukh</h3>
                            <p class="text-xs text-slate-400 mt-1">View personal appointment history, prescriptions, bills, and simulate payment.</p>
                            <div class="mt-3 text-[11px] font-mono text-slate-400 bg-slate-950 p-2 rounded-lg">
                                <div><span class="text-slate-500">Email:</span> patient@example.com</div>
                                <div><span class="text-slate-500">Pass:</span> password</div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="quickLogin('patient@example.com', 'password')"
                            class="mt-4 w-full py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <span>Sign In as Patient</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Plans Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-black text-white">SaaS Subscription Plans</h2>
                <p class="mt-3 text-sm text-slate-400 max-w-xl mx-auto">
                    Flexible subscription tiers designed for individual practitioner rooms up to multi-location healthcare institutions.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'rounded-3xl p-6 flex flex-col justify-between transition-all duration-200 border',
                        plan.is_featured
                            ? 'bg-slate-800/90 border-emerald-500/60 ring-2 ring-emerald-500/20 shadow-2xl shadow-emerald-500/10'
                            : 'bg-slate-950/70 border-slate-800 hover:border-slate-700'
                    ]"
                >
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white">{{ plan.name }}</h3>
                            <span v-if="plan.is_featured" class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase bg-emerald-500 text-slate-950 rounded-full">
                                Recommended
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mb-6 min-h-[36px]">{{ plan.description }}</p>

                        <div class="mb-6 flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white">₹{{ Number(plan.price_monthly).toLocaleString('en-IN') }}</span>
                            <span class="text-xs text-slate-400 font-semibold">/ month</span>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-slate-800 text-xs text-slate-300">
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 flex-shrink-0" />
                                <span>Up to <strong>{{ plan.max_doctors }}</strong> Doctor{{ plan.max_doctors > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 flex-shrink-0" />
                                <span>Up to <strong>{{ plan.max_patients_monthly }}</strong> Patients / mo</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 flex-shrink-0" />
                                <span>Up to <strong>{{ plan.max_staff }}</strong> Staff Accounts</span>
                            </div>

                            <div v-for="(feat, idx) in plan.features" :key="idx" class="flex items-center gap-2">
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 flex-shrink-0" />
                                <span>{{ feat }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <Link
                            :href="route('register')"
                            :class="[
                                'w-full py-2.5 rounded-xl text-xs font-bold flex items-center justify-center transition',
                                plan.is_featured
                                    ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/20'
                                    : 'bg-slate-800 hover:bg-slate-700 text-white'
                            ]"
                        >
                            Choose Plan
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-800/80 py-8 bg-slate-950 text-center text-xs text-slate-500">
            <p>© {{ new Date().getFullYear() }} CarePulse Clinic OS. Multi-tenant Medical SaaS platform.</p>
        </footer>
    </div>
</template>
