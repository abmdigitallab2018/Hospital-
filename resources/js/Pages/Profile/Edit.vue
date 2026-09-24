<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Shield, User } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
</script>

<template>
    <AdminLayout>
        <Head title="Profile Settings" />

        <div class="space-y-6 max-w-4xl mx-auto">
            <!-- Top Header Card -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center font-extrabold text-sm shadow-sm shadow-indigo-500/25">
                        {{ user.name ? user.name.substring(0, 2).toUpperCase() : 'AD' }}
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
                            {{ user.name }}
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                            <span>{{ user.email }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="inline-flex items-center gap-1 text-indigo-600 font-semibold text-[11px]">
                                <Shield class="w-3 h-3" />
                                Administrator
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-2xs">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <!-- Update Password Section -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-2xs">
                <UpdatePasswordForm />
            </div>
        </div>
    </AdminLayout>
</template>
