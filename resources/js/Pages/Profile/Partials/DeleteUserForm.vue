<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppInput from '@/Components/UI/AppInput.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import { Trash2, AlertTriangle, Lock, ShieldAlert } from 'lucide-vue-next';

const confirmingUserDeletion = ref(false);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-5">
        <div class="flex items-start justify-between gap-4 border-b border-rose-100 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <ShieldAlert class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">
                        Delete Account
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Permanently delete your account and all associated administrative records.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200/80 flex items-start justify-between flex-col sm:flex-row gap-4">
            <div class="space-y-1 text-xs text-rose-900">
                <p class="font-bold flex items-center gap-1.5">
                    <AlertTriangle class="w-4 h-4 text-rose-600" />
                    <span>Warning: This action is irreversible</span>
                </p>
                <p class="text-slate-600 text-xs leading-relaxed max-w-xl">
                    Once your account is deleted, all access to this administrative back-office and your personalized configurations will be wiped permanently.
                </p>
            </div>

            <AppButton
                type="button"
                variant="danger"
                size="sm"
                @click="confirmUserDeletion"
                class="whitespace-nowrap flex-shrink-0"
            >
                <Trash2 class="w-3.5 h-3.5" />
                <span>Delete Account</span>
            </AppButton>
        </div>

        <!-- Confirm Deletion Modal -->
        <AppModal
            :show="confirmingUserDeletion"
            title="Confirm Account Deletion"
            maxWidth="md"
            @close="closeModal"
        >
            <form @submit.prevent="deleteUser" class="space-y-4">
                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <AlertTriangle class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" />
                    <p class="leading-relaxed">
                        Are you sure you want to permanently delete your account? Please enter your current password to authorize this action.
                    </p>
                </div>

                <div>
                    <AppInput
                        id="delete_account_password"
                        v-model="form.password"
                        type="password"
                        label="Enter Password to Confirm"
                        placeholder="••••••••"
                        :error="form.errors.password"
                        required
                        autocomplete="current-password"
                    >
                        <template #prefix>
                            <Lock class="w-4 h-4 text-slate-400" />
                        </template>
                    </AppInput>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <AppButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="closeModal"
                    >
                        Cancel
                    </AppButton>

                    <AppButton
                        type="submit"
                        variant="danger"
                        size="sm"
                        :loading="form.processing"
                    >
                        Permanently Delete
                    </AppButton>
                </div>
            </form>
        </AppModal>
    </div>
</template>
