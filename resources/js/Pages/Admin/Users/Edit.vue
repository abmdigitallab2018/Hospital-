<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import { ArrowLeft, KeyRound } from 'lucide-vue-next';

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.user.name || '',
    username: props.user.username || '',
    email: props.user.email || '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit User - ${user.name}`" />

        <div class="max-w-2xl mx-auto">
            <AdminPageHeader
                :title="`Edit User - ${user.name}`"
                description="Update user profile information or reset their password credentials."
                :breadcrumbs="[
                    { label: 'Admin', href: route('admin.dashboard') },
                    { label: 'Users', href: route('admin.users.index') },
                    { label: 'Edit' }
                ]"
            >
                <template #actions>
                    <AppButton
                        :href="route('admin.users.index')"
                        variant="ghost"
                        size="sm"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1.5" />
                        <span>Back to Users</span>
                    </AppButton>
                </template>
            </AdminPageHeader>

            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <AppInput
                        v-model="form.name"
                        label="Full Name"
                        placeholder="e.g. John Doe"
                        :error="form.errors.name"
                        required
                    />

                    <AppInput
                        v-model="form.username"
                        label="Username"
                        placeholder="e.g. abmlab"
                        :error="form.errors.username"
                        hint="Can be used for quick login"
                    />

                    <AppInput
                        v-model="form.email"
                        type="email"
                        label="Email Address"
                        placeholder="john@company.com"
                        :error="form.errors.email"
                        required
                    />

                    <!-- Optional Password Reset Section -->
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 mb-3">
                            <KeyRound class="w-4 h-4 text-indigo-600" />
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">
                                Password Setup & Reset
                            </h4>
                        </div>
                        <p class="text-xs text-slate-400 mb-4">
                            Leave password fields blank if you do not wish to change the existing password.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AppInput
                                v-model="form.password"
                                type="password"
                                label="New Password"
                                placeholder="Minimum 8 characters"
                                :error="form.errors.password"
                            />

                            <AppInput
                                v-model="form.password_confirmation"
                                type="password"
                                label="Confirm New Password"
                                placeholder="Re-enter password"
                                :error="form.errors.password_confirmation"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                        <AppButton
                            :href="route('admin.users.index')"
                            variant="secondary"
                            size="sm"
                        >
                            Cancel
                        </AppButton>
                        <AppButton
                            type="submit"
                            variant="primary"
                            size="sm"
                            :loading="form.processing"
                        >
                            Update User
                        </AppButton>
                    </div>
                </form>
            </AppCard>
        </div>
    </AdminLayout>
</template>
