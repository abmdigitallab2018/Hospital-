<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import {
    Layers,
    FolderKanban,
    Mail,
    Inbox,
    Sliders,
    Users,
    UserCheck,
    ArrowRight,
    Newspaper,
    Send
} from 'lucide-vue-next';

defineProps({
    servicesCount: {
        type: Number,
        default: 0
    },
    portfoliosCount: {
        type: Number,
        default: 0
    },
    postsCount: {
        type: Number,
        default: 0
    },
    subscribersCount: {
        type: Number,
        default: 0
    },
    unreadContactsCount: {
        type: Number,
        default: 0
    },
    totalContactsCount: {
        type: Number,
        default: 0
    },
    recentContacts: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard - Admin Panel" />

        <div class="space-y-8">
            <!-- Header Banner -->
            <AdminPageHeader
                title="Admin Dashboard"
                description="Manage your live portfolio services, showcase projects, and client inquiries from one central control panel."
            />

            <!-- 6 Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- Services -->
                <StatCard
                    title="Active Services"
                    :value="servicesCount"
                    description="Tech offerings"
                    :href="route('admin.services.index')"
                    hrefText="Manage"
                    variant="indigo"
                >
                    <template #icon>
                        <Layers class="w-6 h-6" />
                    </template>
                </StatCard>

                <!-- Portfolio Items -->
                <StatCard
                    title="Portfolio Projects"
                    :value="portfoliosCount"
                    description="Showcase work"
                    :href="route('admin.portfolios.index')"
                    hrefText="Manage"
                    variant="emerald"
                >
                    <template #icon>
                        <FolderKanban class="w-6 h-6" />
                    </template>
                </StatCard>

                <!-- Daily Posts -->
                <StatCard
                    title="Daily Posts"
                    :value="postsCount"
                    description="Technical updates"
                    :href="route('admin.posts.index')"
                    hrefText="Manage"
                    variant="purple"
                >
                    <template #icon>
                        <Newspaper class="w-6 h-6" />
                    </template>
                </StatCard>

                <!-- Newsletter Subscribers -->
                <StatCard
                    title="Subscribers"
                    :value="subscribersCount"
                    description="Active newsletter"
                    :href="route('admin.newsletter.index')"
                    hrefText="Manage"
                    variant="indigo"
                >
                    <template #icon>
                        <Send class="w-6 h-6" />
                    </template>
                </StatCard>

                <!-- Unread Inquiries -->
                <StatCard
                    title="Unread Inquiries"
                    :value="unreadContactsCount"
                    description="Awaiting reply"
                    :href="route('admin.contacts.index')"
                    hrefText="Review"
                    variant="amber"
                >
                    <template #icon>
                        <Inbox class="w-6 h-6" />
                    </template>
                </StatCard>

                <!-- Total Inquiries -->
                <StatCard
                    title="Total Inquiries"
                    :value="totalContactsCount"
                    description="All-time leads"
                    :href="route('admin.contacts.index')"
                    hrefText="View all"
                    variant="purple"
                >
                    <template #icon>
                        <Mail class="w-6 h-6" />
                    </template>
                </StatCard>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
                <Link
                    :href="route('admin.settings.index')"
                    class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-indigo-300 hover:shadow-sm transition group"
                >
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <Sliders class="w-5 h-5" />
                    </div>
                    <p class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Site Settings</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Config & branding</p>
                </Link>

                <Link
                    :href="route('admin.posts.index')"
                    class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-purple-300 hover:shadow-sm transition group"
                >
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <Newspaper class="w-5 h-5" />
                    </div>
                    <p class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition-colors">Daily Posts</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Publish insights</p>
                </Link>

                <Link
                    :href="route('admin.newsletter.index')"
                    class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-blue-300 hover:shadow-sm transition group"
                >
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <Send class="w-5 h-5" />
                    </div>
                    <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Newsletter</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Manage audience</p>
                </Link>

                <Link
                    :href="route('admin.users.index')"
                    class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-sm transition group"
                >
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <Users class="w-5 h-5" />
                    </div>
                    <p class="text-xs font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Admin Users</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Manage access</p>
                </Link>

                <Link
                    :href="route('admin.contacts.index')"
                    class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-amber-300 hover:shadow-sm transition group"
                >
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <Inbox class="w-5 h-5" />
                    </div>
                    <p class="text-xs font-bold text-slate-900 group-hover:text-amber-600 transition-colors">Inquiries</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Check messages</p>
                </Link>

                <Link
                    :href="route('admin.about.edit')"
                    class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-rose-300 hover:shadow-sm transition group"
                >
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <UserCheck class="w-5 h-5" />
                    </div>
                    <p class="text-xs font-bold text-slate-900 group-hover:text-rose-600 transition-colors">Edit About</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bio & resume</p>
                </Link>
            </div>

            <!-- Recent Inquiries Section -->
            <AppCard
                title="Recent Inquiries"
                subtitle="Messages submitted through your public portfolio contact form."
                :padding="false"
            >
                <template #actions>
                    <Link
                        :href="route('admin.contacts.index')"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1 transition-colors"
                    >
                        <span>View all ({{ totalContactsCount }})</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </template>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm text-left">
                        <thead class="bg-slate-50/80 text-slate-500 font-semibold uppercase text-[11px] tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">Sender</th>
                                <th class="px-6 py-3.5">Subject</th>
                                <th class="px-6 py-3.5">Received</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr
                                v-for="contact in recentContacts"
                                :key="contact.id"
                                :class="!contact.is_read ? 'bg-amber-50/30' : 'hover:bg-slate-50/75'"
                                class="transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ contact.name }}</div>
                                    <div class="text-xs text-slate-500">{{ contact.email }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-700 max-w-xs truncate font-medium">
                                    {{ contact.subject || 'No subject' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-mono">
                                    {{ contact.created_at_human }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <AppBadge
                                        :variant="!contact.is_read ? 'amber' : 'slate'"
                                        dot
                                    >
                                        {{ !contact.is_read ? 'Unread' : 'Read' }}
                                    </AppBadge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('admin.contacts.show', contact.id)"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 py-1 px-2 rounded-lg hover:bg-indigo-50 transition"
                                    >
                                        <span>View</span>
                                        <ArrowRight class="w-3 h-3" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="recentContacts.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs">
                                    No contact inquiries received yet. Submissions on your portfolio contact form will appear here.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>
        </div>
    </AdminLayout>
</template>
