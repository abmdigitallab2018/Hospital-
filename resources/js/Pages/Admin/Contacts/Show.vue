<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import { ArrowLeft, Mail, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    contact: {
        type: Object,
        required: true
    }
});

const deleteModalOpen = ref(false);
const deleteLoading = ref(false);

const executeDelete = () => {
    deleteLoading.value = true;
    router.delete(route('admin.contacts.destroy', props.contact.id), {
        onFinish: () => {
            deleteLoading.value = false;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Inquiry from ${contact.name} - Admin Panel`" />

        <div class="max-w-3xl mx-auto">
            <AdminPageHeader
                :title="`Inquiry from ${contact.name}`"
                :description="`Received on ${contact.created_at} (${contact.created_at_human})`"
                :breadcrumbs="[
                    { label: 'Inquiries', href: route('admin.contacts.index') },
                    { label: contact.name }
                ]"
            >
                <template #actions>
                    <AppButton
                        :href="route('admin.contacts.index')"
                        variant="ghost"
                        size="sm"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1.5" />
                        <span>Back</span>
                    </AppButton>

                    <AppButton
                        :href="`mailto:${contact.email}?subject=Re: ${encodeURIComponent(contact.subject || 'Your inquiry')}`"
                        variant="primary"
                        size="sm"
                    >
                        <Mail class="w-4 h-4 mr-1.5" />
                        <span>Reply via Email</span>
                    </AppButton>

                    <AppButton
                        variant="danger"
                        size="sm"
                        @click="deleteModalOpen = true"
                    >
                        <Trash2 class="w-4 h-4 mr-1.5" />
                        <span>Delete</span>
                    </AppButton>
                </template>
            </AdminPageHeader>

            <AppCard :padding="false">
                <!-- Header Info -->
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 font-extrabold text-sm flex items-center justify-center border border-indigo-100 shadow-2xs">
                                {{ contact.name ? contact.name.substring(0, 2).toUpperCase() : '?' }}
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">{{ contact.name }}</h2>
                                <a :href="`mailto:${contact.email}`" class="text-xs text-indigo-600 hover:underline">
                                    {{ contact.email }}
                                </a>
                            </div>
                        </div>

                        <AppBadge variant="slate">
                            {{ contact.created_at_human }}
                        </AppBadge>
                    </div>

                    <div class="mt-4 pt-3.5 border-t border-slate-200/60 flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Subject:</span>
                        <span class="text-sm font-semibold text-slate-900">{{ contact.subject || 'No subject specified' }}</span>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="p-6 sm:p-8">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Message Body</div>
                    <div class="text-sm text-slate-800 leading-relaxed whitespace-pre-wrap bg-slate-50/80 p-5 rounded-2xl border border-slate-200/80 font-sans">
                        {{ contact.message }}
                    </div>
                </div>
            </AppCard>

            <AppConfirmDialog
                :show="deleteModalOpen"
                title="Delete Inquiry"
                :message="`Are you sure you want to delete this message from '${contact.name}'? This action cannot be undone.`"
                confirmText="Delete Message"
                variant="danger"
                :loading="deleteLoading"
                @confirm="executeDelete"
                @cancel="deleteModalOpen = false"
            />
        </div>
    </AdminLayout>
</template>
