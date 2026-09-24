<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppInput from '@/Components/UI/AppInput.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import { Lock, KeyRound, Save, CheckCircle2 } from 'lucide-vue-next';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
            }
            if (form.errors.current_password) {
                form.reset('current_password');
            }
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center font-bold">
                    <KeyRound class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">
                        Update Password
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Ensure your administrative account uses a strong, random password to stay secure.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-5">
            <div class="max-w-md">
                <AppInput
                    id="current_password"
                    v-model="form.current_password"
                    type="password"
                    label="Current Password"
                    placeholder="••••••••"
                    :error="form.errors.current_password"
                    required
                    autocomplete="current-password"
                >
                    <template #prefix>
                        <Lock class="w-4 h-4 text-slate-400" />
                    </template>
                </AppInput>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl">
                <div>
                    <AppInput
                        id="new_password"
                        v-model="form.password"
                        type="password"
                        label="New Password"
                        placeholder="••••••••"
                        :error="form.errors.password"
                        hint="Minimum 8 characters"
                        required
                        autocomplete="new-password"
                    >
                        <template #prefix>
                            <KeyRound class="w-4 h-4 text-slate-400" />
                        </template>
                    </AppInput>
                </div>

                <div>
                    <AppInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        label="Confirm New Password"
                        placeholder="••••••••"
                        :error="form.errors.password_confirmation"
                        required
                        autocomplete="new-password"
                    >
                        <template #prefix>
                            <KeyRound class="w-4 h-4 text-slate-400" />
                        </template>
                    </AppInput>
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
                        <span>Password updated successfully!</span>
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
                    <span>Update Password</span>
                </AppButton>
            </div>
        </form>
    </div>
</template>
