<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Building2,
    CreditCard,
    ArrowLeft,
    LogOut,
    Menu,
    X,
    ShieldCheck,
    Layers,
    UserCircle,
    ChevronDown
} from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const mobileSidebarOpen = ref(false);
const userMenuOpen = ref(false);

const authUser = computed(() => page.props.auth?.user || { name: 'Super Admin', email: '' });

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 antialiased font-sans flex">
        <AppToast />

        <!-- Mobile Drawer Backdrop -->
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
                class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
                @click="mobileSidebarOpen = false"
            ></div>
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-slate-950 border-r border-slate-800 flex flex-col justify-between transition-all duration-200 ease-in-out lg:static w-64',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <div>
                <!-- Brand -->
                <div class="h-16 border-b border-slate-800/80 px-6 flex items-center justify-between">
                    <Link :href="route('superadmin.dashboard')" class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 text-white flex items-center justify-center font-black text-xs shadow-md shadow-indigo-500/30">
                            <ShieldCheck class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <span class="block font-bold text-white text-sm">CarePulse SaaS</span>
                            <span class="block text-[10px] font-semibold text-indigo-400 tracking-wider uppercase">Super Admin</span>
                        </div>
                    </Link>

                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        class="lg:hidden text-slate-400 hover:text-white"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="p-4 space-y-1">
                    <Link
                        :href="route('superadmin.dashboard')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors',
                            route().current('superadmin.dashboard')
                                ? 'bg-indigo-600 text-white font-bold'
                                : 'text-slate-400 hover:text-white hover:bg-slate-900'
                        ]"
                    >
                        <LayoutDashboard class="w-4 h-4 flex-shrink-0" />
                        <span>Platform Overview</span>
                    </Link>

                    <Link
                        :href="route('superadmin.clinics')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors',
                            route().current('superadmin.clinics*')
                                ? 'bg-indigo-600 text-white font-bold'
                                : 'text-slate-400 hover:text-white hover:bg-slate-900'
                        ]"
                    >
                        <Building2 class="w-4 h-4 flex-shrink-0" />
                        <span>Clinics Directory</span>
                    </Link>

                    <Link
                        :href="route('superadmin.plans')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors',
                            route().current('superadmin.plans*')
                                ? 'bg-indigo-600 text-white font-bold'
                                : 'text-slate-400 hover:text-white hover:bg-slate-900'
                        ]"
                    >
                        <CreditCard class="w-4 h-4 flex-shrink-0" />
                        <span>Subscription Plans</span>
                    </Link>
                </nav>
            </div>

            <!-- Footer Link back to Clinic workspace -->
            <div class="p-4 border-t border-slate-800 space-y-2">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:bg-slate-900 transition"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Go To Clinic Portal</span>
                </Link>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-900">
            <!-- Header -->
            <header class="h-16 bg-slate-950/70 backdrop-blur-md border-b border-slate-800 px-6 flex items-center justify-between sticky top-0 z-30">
                <button
                    type="button"
                    @click="mobileSidebarOpen = true"
                    class="lg:hidden text-slate-400 hover:text-white"
                >
                    <Menu class="w-5 h-5" />
                </button>

                <div class="flex items-center gap-3 ml-auto">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Platform Online
                    </div>

                    <button
                        type="button"
                        @click="logout"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 text-xs font-bold transition ml-4"
                    >
                        <LogOut class="w-3.5 h-3.5" />
                        <span>Sign Out</span>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 sm:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
