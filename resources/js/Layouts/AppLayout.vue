<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    Calendar,
    Clock,
    Stethoscope,
    FileText,
    Receipt,
    CreditCard,
    CalendarClock,
    UserCheck,
    Settings,
    BarChart3,
    Layers,
    ShieldAlert,
    Building2,
    ChevronDown,
    LogOut,
    Menu,
    X,
    Bell,
    CheckCircle2,
    Sparkles,
    UserCircle,
    ArrowRightLeft,
    HeartPulse,
    TrendingDown
} from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const mobileSidebarOpen = ref(false);
const userMenuOpen = ref(false);
const clinicMenuOpen = ref(false);

const isSidebarCollapsed = ref(
    typeof window !== 'undefined' ? localStorage.getItem('clinic_sidebar_collapsed') === 'true' : false
);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    if (typeof window !== 'undefined') {
        localStorage.setItem('clinic_sidebar_collapsed', isSidebarCollapsed.value);
    }
};

const authUser = computed(() => page.props.auth?.user || { name: 'User', email: '', role: 'clinic_admin' });
const activeClinic = computed(() => page.props.auth?.clinic || { name: 'CarePulse Clinic', city: 'Mumbai', currency_symbol: '₹' });
const availableClinics = computed(() => page.props.auth?.availableClinics || []);
const unreadCount = computed(() => page.props.unreadNotificationsCount || 0);

const isSuperAdmin = computed(() => authUser.value.role === 'super_admin');
const isDoctor = computed(() => authUser.value.role === 'doctor');
const isReceptionist = computed(() => authUser.value.role === 'receptionist');
const isAccountant = computed(() => authUser.value.role === 'accountant');
const isClinicAdmin = computed(() => authUser.value.role === 'clinic_admin' || authUser.value.role === 'super_admin');

const switchClinic = (clinicId) => {
    clinicMenuOpen.value = false;
    router.post(route('clinic.switch'), { clinic_id: clinicId });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex">
        <!-- Toast Notification Alert -->
        <AppToast />

        <!-- Mobile Sidebar Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"
                @click="mobileSidebarOpen = false"
            ></div>
        </Transition>

        <!-- Left Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col justify-between transition-all duration-200 ease-in-out lg:static',
                isSidebarCollapsed ? 'lg:w-20' : 'lg:w-64 xl:w-72',
                'w-64 sm:w-72',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <div>
                <!-- Brand / Clinic Header -->
                <div :class="['h-16 border-b border-slate-100 flex items-center', isSidebarCollapsed ? 'justify-center px-2' : 'justify-between px-5']">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center space-x-3 group"
                        :title="isSidebarCollapsed ? activeClinic?.name : undefined"
                    >
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 via-emerald-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 font-black text-sm tracking-wider flex-shrink-0 group-hover:scale-105 transition-transform">
                            <HeartPulse class="w-5 h-5 text-white" />
                        </div>
                        <div v-if="!isSidebarCollapsed" class="leading-tight truncate">
                            <span class="block font-bold text-slate-900 text-sm truncate">{{ activeClinic?.name || 'CarePulse Clinic' }}</span>
                            <span class="block text-[10px] font-semibold text-emerald-600 tracking-wider uppercase">
                                {{ activeClinic?.city ? activeClinic.city + ' Branch' : 'Medical SaaS' }}
                            </span>
                        </div>
                    </Link>

                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100"
                        aria-label="Close sidebar"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="px-3 py-4 space-y-6 overflow-y-auto max-h-[calc(100vh-140px)]">
                    <!-- Core Clinic Operations -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Clinical Operations
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('dashboard')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('dashboard')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Dashboard' : undefined"
                            >
                                <LayoutDashboard class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Dashboard</span>
                            </Link>

                            <Link
                                :href="route('queue.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('queue.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Waiting Queue' : undefined"
                            >
                                <Clock class="w-4 h-4 flex-shrink-0 text-amber-500" />
                                <span v-if="!isSidebarCollapsed" class="flex-1">Waiting Queue</span>
                                <span v-if="!isSidebarCollapsed" class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">Live</span>
                            </Link>

                            <Link
                                :href="route('appointments.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('appointments.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Appointments' : undefined"
                            >
                                <Calendar class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Appointments</span>
                            </Link>

                            <Link
                                :href="route('patients.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('patients.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Patients' : undefined"
                            >
                                <Users class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Patients</span>
                            </Link>

                            <Link
                                :href="route('consultations.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('consultations.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Consultations' : undefined"
                            >
                                <Stethoscope class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Consultations</span>
                            </Link>

                            <Link
                                :href="route('prescriptions.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('prescriptions.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Prescriptions' : undefined"
                            >
                                <FileText class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Prescriptions</span>
                            </Link>

                            <Link
                                :href="route('follow-ups.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('follow-ups.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Follow-Ups' : undefined"
                            >
                                <CalendarClock class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Follow-Ups</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- Billing & Finance (Admin, Reception, Accountant) -->
                    <div v-if="!isDoctor">
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Billing & Finance
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('invoices.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('invoices.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Invoices & Bills' : undefined"
                            >
                                <Receipt class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Invoices</span>
                            </Link>

                            <Link
                                :href="route('payments.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('payments.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Payments' : undefined"
                            >
                                <CreditCard class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Payments</span>
                            </Link>

                            <Link
                                v-if="isClinicAdmin || isAccountant"
                                :href="route('expenses.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('expenses.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Expenses' : undefined"
                            >
                                <TrendingDown class="w-4 h-4 flex-shrink-0 text-red-500" />
                                <span v-if="!isSidebarCollapsed">Expenses</span>
                            </Link>

                            <Link
                                :href="route('services.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('services.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Services Catalog' : undefined"
                            >
                                <Layers class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Services Catalog</span>
                            </Link>

                            <Link
                                :href="route('reports.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('reports.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Reports & Analytics' : undefined"
                            >
                                <BarChart3 class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Reports & Analytics</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- Clinic Administration -->
                    <div v-if="isClinicAdmin">
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Clinic Management
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('doctors.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('doctors.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Doctors' : undefined"
                            >
                                <UserCheck class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Doctors & Schedules</span>
                            </Link>

                            <Link
                                :href="route('staff.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('staff.*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Clinic Staff' : undefined"
                            >
                                <UserCircle class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Staff & Permissions</span>
                            </Link>

                            <Link
                                :href="route('settings.clinic')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('settings.clinic*')
                                        ? 'bg-emerald-50 text-emerald-700 font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Clinic Settings' : undefined"
                            >
                                <Settings class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Clinic Settings</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- Super Admin SaaS Hub -->
                    <div v-if="isSuperAdmin">
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-1.5">
                            SaaS Platform
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('superadmin.dashboard')"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50/70 hover:bg-indigo-100 transition-colors"
                            >
                                <ShieldAlert class="w-4 h-4 flex-shrink-0 text-indigo-600" />
                                <span v-if="!isSidebarCollapsed">Super Admin Portal</span>
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Sidebar Footer with Collapse Toggle -->
            <div class="p-3 border-t border-slate-100 flex items-center justify-between">
                <button
                    type="button"
                    @click="toggleSidebar"
                    class="hidden lg:flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition w-full"
                    :title="isSidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                >
                    <span v-if="!isSidebarCollapsed" class="text-xs font-semibold text-slate-500 mr-2">Minimize</span>
                    <ArrowRightLeft class="w-4 h-4" />
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header Navigation -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="mobileSidebarOpen = true"
                        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100"
                        aria-label="Open sidebar"
                    >
                        <Menu class="w-5 h-5" />
                    </button>

                    <!-- Active Clinic Switcher for Super Admin or Multi-Branch -->
                    <div v-if="availableClinics.length > 1" class="relative">
                        <button
                            type="button"
                            @click="clinicMenuOpen = !clinicMenuOpen"
                            class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition"
                        >
                            <Building2 class="w-3.5 h-3.5 text-emerald-600" />
                            <span class="max-w-[150px] sm:max-w-[220px] truncate">{{ activeClinic?.name }}</span>
                            <ChevronDown class="w-3 h-3 text-slate-400" />
                        </button>

                        <div
                            v-if="clinicMenuOpen"
                            class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 animate-in fade-in zoom-in-95 duration-100"
                        >
                            <div class="px-3 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                Switch Clinic Tenant
                            </div>
                            <button
                                v-for="c in availableClinics"
                                :key="c.id"
                                @click="switchClinic(c.id)"
                                :class="[
                                    'w-full text-left px-3 py-2 text-xs flex items-center justify-between hover:bg-slate-50 transition',
                                    c.id === activeClinic?.id ? 'text-emerald-700 font-bold bg-emerald-50/50' : 'text-slate-700'
                                ]"
                            >
                                <span class="truncate">{{ c.name }}</span>
                                <CheckCircle2 v-if="c.id === activeClinic?.id" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 ml-2" />
                            </button>
                        </div>
                    </div>
                    <div v-else class="flex items-center gap-2">
                        <span class="hidden sm:inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-semibold text-slate-500 hidden sm:inline-block">CarePulse Health Cloud</span>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3">
                    <!-- Quick Action "+ Appointment" -->
                    <Link
                        :href="route('appointments.index')"
                        class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition"
                    >
                        <Calendar class="w-3.5 h-3.5" />
                        <span>Book Appointment</span>
                    </Link>

                    <!-- Role Badge -->
                    <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ authUser.role?.replace('_', ' ') }}
                    </div>

                    <!-- User Menu Dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition"
                        >
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ authUser.name ? authUser.name.charAt(0).toUpperCase() : 'U' }}
                            </div>
                            <div class="hidden md:block text-left leading-tight">
                                <span class="block text-xs font-bold text-slate-800">{{ authUser.name }}</span>
                                <span class="block text-[10px] text-slate-400 truncate max-w-[120px]">{{ authUser.email }}</span>
                            </div>
                            <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                        </button>

                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 animate-in fade-in zoom-in-95 duration-100"
                        >
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ authUser.name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ authUser.email }}</p>
                            </div>

                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition"
                                @click="userMenuOpen = false"
                            >
                                <UserCircle class="w-4 h-4 text-slate-400" />
                                <span>My Profile</span>
                            </Link>

                            <button
                                type="button"
                                @click="logout"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-red-600 hover:bg-red-50 transition text-left"
                            >
                                <LogOut class="w-4 h-4 text-red-500" />
                                <span>Sign Out</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
