<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import { ArrowLeft, UserPlus } from 'lucide-vue-next';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Add New User - Admin Panel" />

        <div class="max-w-2xl mx-auto">
            <AdminPageHeader
                title="Create New Administrator"
                description="Grant a team member or collaborator administrative access to manage the website and inquiries."
                :breadcrumbs="[
                    { label: 'Admin', href: route('admin.dashboard') },
                    { label: 'Users', href: route('admin.users.index') },
                    { label: 'Create' }
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AppInput
                            v-model="form.password"
                            type="password"
                            label="Password"
                            placeholder="Minimum 8 characters"
                            :error="form.errors.password"
                            required
                        />

                        <AppInput
                            v-model="form.password_confirmation"
                            type="password"
                            label="Confirm Password"
                            placeholder="Re-enter password"
                            :error="form.errors.password_confirmation"
                            required
                        />
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
                            Create User
                        </AppButton>
                    </div>
                </form>
            </AppCard>
        </div>
    </AdminLayout>
</template>
