<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Layers,
    FolderKanban,
    Mail,
    UserCheck,
    User,
    Users,
    Sliders,
    ExternalLink,
    LogOut,
    Menu,
    X,
    Bell,
    ChevronDown,
    PanelLeftClose,
    PanelLeftOpen,
    Tags,
    Newspaper,
    CreditCard,
    Send
} from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const mobileSidebarOpen = ref(false);
const userMenuOpen = ref(false);

// Collapsible Desktop Sidebar state
const isSidebarCollapsed = ref(
    typeof window !== 'undefined' ? localStorage.getItem('admin_sidebar_collapsed') === 'true' : false
);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    if (typeof window !== 'undefined') {
        localStorage.setItem('admin_sidebar_collapsed', isSidebarCollapsed.value);
    }
};

const authUser = computed(() => page.props.auth?.user || { name: 'Admin', email: '' });
const unreadCount = computed(() => page.props.unreadInquiriesCount || 0);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-[#F1F5F9] text-slate-800 antialiased font-sans flex">
        <!-- Toast Notification System -->
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
                class="fixed inset-0 bg-slate-950/50 backdrop-blur-2xs z-40 lg:hidden"
                @click="mobileSidebarOpen = false"
            ></div>
        </Transition>

        <!-- Left Sidebar (Desktop + Mobile Drawer) -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col justify-between transition-all duration-200 ease-in-out lg:static',
                isSidebarCollapsed ? 'lg:w-20' : 'lg:w-64 xl:w-72',
                'w-64 sm:w-72',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Sidebar Header / Brand -->
            <div>
                <div :class="['h-16 border-b border-slate-100 flex items-center', isSidebarCollapsed ? 'justify-center px-2' : 'justify-between px-6']">
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center space-x-3 font-bold text-base text-slate-900 group"
                        :title="isSidebarCollapsed ? 'Bhavesh Admin Portal' : undefined"
                    >
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-sm shadow-indigo-500/25 font-extrabold text-xs tracking-wider group-hover:scale-105 transition-transform flex-shrink-0">
                            BM
                        </span>
                        <div v-if="!isSidebarCollapsed" class="leading-tight truncate">
                            <span class="block font-bold text-slate-900">Bhavesh</span>
                            <span class="block text-[11px] font-semibold text-indigo-600 tracking-wider uppercase">Admin Portal</span>
                        </div>
                    </Link>

                    <!-- Mobile Close Button -->
                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100"
                        aria-label="Close sidebar"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Sidebar Navigation Sections -->
                <div class="px-3 py-5 space-y-6 overflow-y-auto max-h-[calc(100vh-140px)]">
                    <!-- Section: MAIN -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            Main
                        </div>
                        <nav class="space-y-1">
                            <Link
                                :href="route('admin.dashboard')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.dashboard')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Dashboard' : undefined"
                            >
                                <LayoutDashboard class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Dashboard</span>
                            </Link>

                            <Link
                                :href="route('admin.services.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.services.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Services' : undefined"
                            >
                                <Layers class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Services</span>
                            </Link>

                            <Link
                                :href="route('admin.portfolios.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.portfolios.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Portfolio' : undefined"
                            >
                                <FolderKanban class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Portfolio</span>
                            </Link>

                            <Link
                                :href="route('admin.categories.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.categories.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Category Master' : undefined"
                            >
                                <Tags class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Category Master</span>
                            </Link>

                            <Link
                                :href="route('admin.posts.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.posts.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Daily Posts' : undefined"
                            >
                                <Newspaper class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Daily Posts</span>
                            </Link>

                            <Link
                                :href="route('admin.pricing.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.pricing.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Pricing Master' : undefined"
                            >
                                <CreditCard class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Pricing Master</span>
                            </Link>

                            <Link
                                :href="route('admin.contacts.index')"
                                :class="[
                                    'flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.contacts.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? `Inquiries (${unreadCount} unread)` : undefined"
                            >
                                <div class="flex items-center gap-3">
                                    <Mail class="w-4 h-4 text-current flex-shrink-0" />
                                    <span v-if="!isSidebarCollapsed">Inquiries</span>
                                </div>
                                <span
                                    v-if="unreadCount > 0 && !isSidebarCollapsed"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white shadow-2xs animate-pulse"
                                >
                                    {{ unreadCount }}
                                </span>
                                <span
                                    v-else-if="unreadCount > 0 && isSidebarCollapsed"
                                    class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500"
                                ></span>
                            </Link>
                        </nav>
                    </div>

                    <!-- Section: MANAGEMENT -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            Management
                        </div>
                        <nav class="space-y-1">
                            <Link
                                :href="route('admin.users.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.users.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Admin Users' : undefined"
                            >
                                <Users class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Admin Users</span>
                            </Link>

                            <Link
                                :href="route('admin.newsletter.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.newsletter.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Newsletter' : undefined"
                            >
                                <Send class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Newsletter</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- Section: MASTER SETTINGS -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            Settings
                        </div>
                        <nav class="space-y-1">
                            <Link
                                :href="route('admin.settings.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.settings.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Site Settings' : undefined"
                            >
                                <Sliders class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Site Settings</span>
                            </Link>

                            <Link
                                :href="route('admin.about.edit')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.about.*')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'About & Bio Settings' : undefined"
                            >
                                <UserCheck class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">About & Bio</span>
                            </Link>

                            <Link
                                :href="route('profile.edit')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('profile.edit')
                                        ? 'bg-indigo-50 text-indigo-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                                ]"
                                :title="isSidebarCollapsed ? 'Profile Settings' : undefined"
                            >
                                <User class="w-4 h-4 text-current flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Profile Settings</span>
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Sidebar Bottom: Public Site & Logout -->
            <div class="p-3 border-t border-slate-100 space-y-1">
                <a
                    :href="route('home')"
                    target="_blank"
                    :class="[
                        'flex items-center px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/70 transition-colors',
                        isSidebarCollapsed ? 'justify-center' : 'justify-between'
                    ]"
                    :title="isSidebarCollapsed ? 'View Public Site' : undefined"
                >
                    <span class="flex items-center gap-2.5">
                        <ExternalLink class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 flex-shrink-0" />
                        <span v-if="!isSidebarCollapsed">View Public Site</span>
                    </span>
                    <span v-if="!isSidebarCollapsed" class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">Live</span>
                </a>

                <button
                    type="button"
                    @click="logout"
                    :class="[
                        'w-full flex items-center px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50/80 transition-colors text-left',
                        isSidebarCollapsed ? 'justify-center' : 'gap-2.5'
                    ]"
                    :title="isSidebarCollapsed ? 'Log Out' : undefined"
                >
                    <LogOut class="w-4 h-4 text-rose-500 flex-shrink-0" />
                    <span v-if="!isSidebarCollapsed">Log Out</span>
                </button>
            </div>
        </aside>

        <!-- Main Layout Area: Header + Body Slot -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-2xs">
                <!-- Left: Sidebar Collapse/Expand Toggle & Breadcrumb -->
                <div class="flex items-center gap-3">
                    <!-- Desktop Sidebar Toggle Button -->
                    <button
                        type="button"
                        @click="toggleSidebar"
                        class="hidden lg:flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                        :title="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        aria-label="Toggle sidebar"
                    >
                        <PanelLeftOpen v-if="isSidebarCollapsed" class="w-5 h-5 text-indigo-600" />
                        <PanelLeftClose v-else class="w-5 h-5" />
                    </button>

                    <!-- Mobile Drawer Toggle -->
                    <button
                        type="button"
                        @click="mobileSidebarOpen = true"
                        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition"
                        aria-label="Open sidebar"
                    >
                        <Menu class="w-5 h-5" />
                    </button>

                    <!-- Breadcrumbs -->
                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400 font-medium">
                        <span class="text-slate-600 font-bold">Admin</span>
                        <span>/</span>
                        <span class="text-slate-900 capitalize">{{ route().current() ? route().current().split('.').pop() : 'Dashboard' }}</span>
                    </div>
                </div>

                <!-- Right: Notification, Quick Link, Profile Dropdown -->
                <div class="flex items-center gap-3">
                    <!-- Notifications -->
                    <Link
                        :href="route('admin.contacts.index')"
                        class="relative p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                        title="View inquiries"
                    >
                        <Bell class="w-5 h-5" />
                        <span
                            v-if="unreadCount > 0"
                            class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"
                        ></span>
                    </Link>

                    <!-- Public Site Quick Link (Desktop) -->
                    <a
                        :href="route('home')"
                        target="_blank"
                        class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 border border-slate-200/80 transition"
                    >
                        <span>Live Site</span>
                        <ExternalLink class="w-3.5 h-3.5" />
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none"
                        >
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                {{ authUser.name ? authUser.name.substring(0, 2).toUpperCase() : 'AD' }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <span class="block text-xs font-bold text-slate-800 leading-none">{{ authUser.name }}</span>
                                <span class="block text-[11px] text-slate-400 mt-0.5 leading-none truncate max-w-[120px]">{{ authUser.email }}</span>
                            </div>
                            <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                        </button>

                        <div
                            v-if="userMenuOpen"
                            @click.away="userMenuOpen = false"
                            class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50 animate-in fade-in slide-in-from-top-1"
                        >
                            <div class="px-4 py-2.5 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-900">{{ authUser.name }}</p>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ authUser.email }}</p>
                            </div>
                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition"
                                @click="userMenuOpen = false"
                            >
                                <User class="w-3.5 h-3.5 text-slate-400" />
                                <span>Profile Settings</span>
                            </Link>
                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <button
                                    type="button"
                                    @click="logout"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition text-left"
                                >
                                    <LogOut class="w-3.5 h-3.5 text-rose-500" />
                                    <span>Log Out</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>

            <!-- Compact Admin Footer -->
            <footer class="py-4 px-6 sm:px-8 border-t border-slate-200 bg-white/60 text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    &copy; {{ new Date().getFullYear() }} ABM Digital Lab &bull; Developer Portfolio & Admin Back-Office
                </div>
                <div class="text-[11px] text-slate-400">
                    Laravel 12 &bull; Inertia.js &bull; Vue 3 &bull; Tailwind CSS
                </div>
            </footer>
        </div>
    </div>
</template>
