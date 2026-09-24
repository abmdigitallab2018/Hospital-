<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    service: {
        type: Object,
        required: true
    }
});

const form = useForm({
    title: props.service.title || '',
    description: props.service.description || '',
    icon: props.service.icon || '⚡',
    sort_order: props.service.sort_order ?? 0,
    is_active: Boolean(props.service.is_active),
});

const submit = () => {
    form.put(route('admin.services.update', props.service.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit ${service.title} - Admin Panel`" />

        <div class="max-w-2xl mx-auto">
            <AdminPageHeader
                :title="`Edit Service`"
                :description="`Update information for '${service.title}'.`"
                :breadcrumbs="[
                    { label: 'Services', href: route('admin.services.index') },
                    { label: 'Edit' }
                ]"
            >
                <template #actions>
                    <AppButton
                        :href="route('admin.services.index')"
                        variant="ghost"
                        size="sm"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1.5" />
                        <span>Back</span>
                    </AppButton>
                </template>
            </AdminPageHeader>

            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <AppInput
                        v-model="form.title"
                        label="Service Title"
                        placeholder="e.g. Full-Stack Web Development"
                        :error="form.errors.title"
                        required
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AppInput
                            v-model="form.icon"
                            label="Icon / Symbol"
                            placeholder="⚡, 🚀, 💻, 🛡️, 🌐"
                            hint="Single emoji or short symbol"
                            :error="form.errors.icon"
                        />

                        <AppInput
                            v-model.number="form.sort_order"
                            type="number"
                            label="Display Order"
                            hint="Lower numbers appear first"
                            :error="form.errors.sort_order"
                        />
                    </div>

                    <AppTextarea
                        v-model="form.description"
                        label="Description"
                        rows="4"
                        placeholder="Detail what you build, technologies used, and client outcomes delivered..."
                        :error="form.errors.description"
                        required
                    />

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Active (Visible on public portfolio)
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                        <AppButton
                            :href="route('admin.services.index')"
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
                            Update Service
                        </AppButton>
                    </div>
                </form>
            </AppCard>
        </div>
    </AdminLayout>
</template>
