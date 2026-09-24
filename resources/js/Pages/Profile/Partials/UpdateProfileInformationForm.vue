<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppInput from '@/Components/UI/AppInput.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import { User, Mail, Save, CheckCircle2, AlertCircle } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const updateProfile = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <User class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">
                        Profile Information
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Update your account's display name and primary administrative email.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="updateProfile" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <AppInput
                        id="profile-name"
                        v-model="form.name"
                        label="Full Name"
                        placeholder="e.g. ABM Digital Lab"
                        :error="form.errors.name"
                        required
                        autocomplete="name"
                    >
                        <template #prefix>
                            <User class="w-4 h-4 text-slate-400" />
                        </template>
                    </AppInput>
                </div>

                <div>
                    <AppInput
                        id="profile-email"
                        v-model="form.email"
                        type="email"
                        label="Email Address"
                        placeholder="admin@example.com"
                        :error="form.errors.email"
                        required
                        autocomplete="username"
                    >
                        <template #prefix>
                            <Mail class="w-4 h-4 text-slate-400" />
                        </template>
                    </AppInput>
                </div>
            </div>

            <!-- Email verification notice if applicable -->
            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-3"
            >
                <AlertCircle class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <p class="font-semibold">Your email address is unverified.</p>
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-indigo-600 font-semibold underline hover:text-indigo-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                    <p v-if="status === 'verification-link-sent'" class="text-emerald-600 font-semibold">
                        A new verification link has been sent to your email address.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <Transition
                    enter-active-class="transition ease-in-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    leave-active-class="transition ease-in-out duration-150"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="form.recentlySuccessful"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200/60"
                    >
                        <CheckCircle2 class="w-3.5 h-3.5" />
                        <span>Profile updated successfully!</span>
                    </div>
                    <div v-else></div>
                </Transition>

                <AppButton
                    type="submit"
                    variant="primary"
                    size="md"
                    :loading="form.processing"
                >
                    <Save class="w-4 h-4" />
                    <span>Save Changes</span>
                </AppButton>
            </div>
        </form>
    </div>
</template>
