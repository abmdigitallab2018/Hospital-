<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    HeartPulse,
    Calendar,
    FileText,
    Receipt,
    User,
    LogOut,
    Menu,
    X,
    ChevronDown,
    Home
} from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const mobileNavOpen = ref(false);
const authUser = computed(() => page.props.auth?.user || { name: 'Patient' });
const clinic = computed(() => page.props.auth?.clinic || { name: 'Apollo Care' });

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col">
        <AppToast />

        <!-- Navigation Bar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-8">
                        <Link :href="route('patient.dashboard')" class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-emerald-500 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-500/20">
                                <HeartPulse class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <span class="block font-bold text-slate-900 text-sm leading-none">{{ clinic.name }}</span>
                                <span class="block text-[10px] font-bold text-emerald-600 uppercase tracking-wider mt-0.5">Patient Portal</span>
                            </div>
                        </Link>

                        <!-- Desktop Navigation -->
                        <nav class="hidden md:flex space-x-1">
                            <Link
                                :href="route('patient.dashboard')"
                                :class="[
                                    'px-3 py-2 rounded-xl text-xs font-semibold transition',
                                    route().current('patient.dashboard') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                                ]"
                            >
                                Overview
                            </Link>

                            <Link
                                :href="route('patient.appointments')"
                                :class="[
                                    'px-3 py-2 rounded-xl text-xs font-semibold transition',
                                    route().current('patient.appointments*') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                                ]"
                            >
                                Appointments
                            </Link>

                            <Link
                                :href="route('patient.prescriptions')"
                                :class="[
                                    'px-3 py-2 rounded-xl text-xs font-semibold transition',
                                    route().current('patient.prescriptions*') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                                ]"
                            >
                                Prescriptions
                            </Link>

                            <Link
                                :href="route('patient.invoices')"
                                :class="[
                                    'px-3 py-2 rounded-xl text-xs font-semibold transition',
                                    route().current('patient.invoices*') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                                ]"
                            >
                                Invoices & Bills
                            </Link>
                        </nav>
                    </div>

                    <!-- Right User Actions -->
                    <div class="hidden md:flex items-center gap-4">
                        <div class="text-right">
                            <span class="block text-xs font-bold text-slate-900">{{ authUser.name }}</span>
                            <span class="block text-[11px] text-slate-400">{{ authUser.email }}</span>
                        </div>

                        <button
                            type="button"
                            @click="logout"
                            class="p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                            title="Sign Out"
                        >
                            <LogOut class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center md:hidden">
                        <button
                            type="button"
                            @click="mobileNavOpen = !mobileNavOpen"
                            class="p-2 rounded-xl text-slate-500 hover:bg-slate-100"
                        >
                            <Menu v-if="!mobileNavOpen" class="w-6 h-6" />
                            <X v-else class="w-6 h-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div v-if="mobileNavOpen" class="md:hidden border-t border-slate-100 bg-white px-4 pt-2 pb-4 space-y-1">
                <Link
                    :href="route('patient.dashboard')"
                    class="block px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100"
                    @click="mobileNavOpen = false"
                >
                    Overview
                </Link>
                <Link
                    :href="route('patient.appointments')"
                    class="block px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100"
                    @click="mobileNavOpen = false"
                >
                    Appointments
                </Link>
                <Link
                    :href="route('patient.prescriptions')"
                    class="block px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100"
                    @click="mobileNavOpen = false"
                >
                    Prescriptions
                </Link>
                <Link
                    :href="route('patient.invoices')"
                    class="block px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100"
                    @click="mobileNavOpen = false"
                >
                    Invoices & Bills
                </Link>
                <button
                    type="button"
                    @click="logout"
                    class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50"
                >
                    Sign Out
                </button>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
            <slot />
        </main>
    </div>
</template>
