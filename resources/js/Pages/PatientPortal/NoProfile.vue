<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import { UserX, PhoneCall, ArrowLeft, LogOut } from 'lucide-vue-next';

defineProps({
    auth: Object,
});
</script>

<template>
    <PatientPortalLayout>
        <Head title="Patient Profile Setup" />

        <div class="max-w-xl mx-auto py-12 px-4 text-center">
            <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-sm space-y-6">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center shadow-inner">
                    <UserX class="w-8 h-8" />
                </div>

                <div class="space-y-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900">
                        Patient Profile Not Linked Yet
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                        Your online account is active, but we haven't linked it to an existing clinical patient file yet.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-left text-xs space-y-2 text-slate-600">
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <PhoneCall class="w-4 h-4 text-emerald-600" />
                        How to activate your portal records:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-500 pl-1">
                        <li>Visit or call the clinic front desk and provide your email address.</li>
                        <li>The receptionist will link your unique Patient UID (e.g., <span class="font-mono text-emerald-700">PAT-2026-0001</span>) to this account.</li>
                        <li>Once linked, refresh this page to access your appointments, prescriptions, and invoices.</li>
                    </ul>
                </div>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link
                        :href="route('home')"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center justify-center gap-2"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        Back to Home
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 flex items-center justify-center gap-2"
                    >
                        <LogOut class="w-4 h-4" />
                        Sign Out
                    </Link>
                </div>
            </div>
        </div>
    </PatientPortalLayout>
</template>
