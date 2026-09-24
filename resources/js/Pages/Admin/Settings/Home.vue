<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppButton from '@/Components/UI/AppButton.vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true
    }
});

const form = useForm({
    hero_badge: props.settings.hero_badge || '',
    hero_title: props.settings.hero_title || '',
    hero_highlight: props.settings.hero_highlight || '',
    hero_description: props.settings.hero_description || '',
    cta_primary_text: props.settings.cta_primary_text || '',
    cta_primary_link: props.settings.cta_primary_link || '',
    cta_secondary_text: props.settings.cta_secondary_text || '',
    cta_secondary_link: props.settings.cta_secondary_link || '',
    availability_status: props.settings.availability_status || '',
    resume_button_enabled: Boolean(props.settings.resume_button_enabled),
    resume_button_text: props.settings.resume_button_text || '',
});

const submit = () => {
    form.transform(data => ({
        ...data,
        _method: 'put'
    })).post(route('admin.settings.home.update'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Front Home Setup - Admin Panel" />

        <div class="max-w-4xl mx-auto space-y-6">
            <AdminPageHeader
                title="Front Home & Hero Setup"
                description="Customize the main hero banner headlines, badges, call-to-action buttons, and availability status on your homepage."
                :breadcrumbs="[
                    { label: 'Admin', href: route('admin.dashboard') },
                    { label: 'Settings' },
                    { label: 'Home Setup' }
                ]"
            />

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Hero Headlines & Badge -->
                <AppCard title="Hero Headline & Messaging" subtitle="The primary value proposition displayed at the top of the homepage.">
                    <div class="space-y-5">
                        <AppInput
                            v-model="form.hero_badge"
                            label="Hero Badge Text"
                            placeholder="e.g. Senior Full-Stack Developer & Solutions Architect"
                            hint="Small highlighted pill above the main headline"
                            :error="form.errors.hero_badge"
                        />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AppInput
                                v-model="form.hero_title"
                                label="Main Headline"
                                placeholder="e.g. Building Digital Products That Scale"
                                :error="form.errors.hero_title"
                                required
                            />

                            <AppInput
                                v-model="form.hero_highlight"
                                label="Highlighted Headline Text"
                                placeholder="e.g. Products That Scale"
                                hint="Rendered with the vibrant violet/indigo gradient"
                                :error="form.errors.hero_highlight"
                            />
                        </div>

                        <AppTextarea
                            v-model="form.hero_description"
                            label="Hero Supporting Description"
                            rows="4"
                            placeholder="Describe your technical expertise, frameworks, and architecture strengths..."
                            :error="form.errors.hero_description"
                        />

                        <AppInput
                            v-model="form.availability_status"
                            label="Availability Status Notice"
                            placeholder="e.g. Available for contract & consulting"
                            :error="form.errors.availability_status"
                        />
                    </div>
                </AppCard>

                <!-- Call to Action Buttons -->
                <AppCard title="Call-to-Action Buttons" subtitle="Configure the primary and secondary CTA buttons in the hero banner.">
                    <div class="space-y-5">
                        <!-- Primary CTA -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AppInput
                                v-model="form.cta_primary_text"
                                label="Primary Button Text"
                                placeholder="Hire Me"
                                :error="form.errors.cta_primary_text"
                            />

                            <AppInput
                                v-model="form.cta_primary_link"
                                label="Primary Button Target Link"
                                placeholder="#contact"
                                :error="form.errors.cta_primary_link"
                            />
                        </div>

                        <!-- Secondary CTA -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AppInput
                                v-model="form.cta_secondary_text"
                                label="Secondary Button Text"
                                placeholder="View Projects"
                                :error="form.errors.cta_secondary_text"
                            />

                            <AppInput
                                v-model="form.cta_secondary_link"
                                label="Secondary Button Target Link"
                                placeholder="#portfolio"
                                :error="form.errors.cta_secondary_link"
                            />
                        </div>

                        <!-- Resume Button Toggle -->
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <input
                                    id="resume_button_enabled"
                                    type="checkbox"
                                    v-model="form.resume_button_enabled"
                                    class="h-4 w-4 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label for="resume_button_enabled" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                    Show "Download CV" Button in Hero Banner
                                </label>
                            </div>

                            <div class="w-full sm:w-64">
                                <AppInput
                                    v-model="form.resume_button_text"
                                    label="Resume Button Label"
                                    placeholder="Download CV"
                                    :error="form.errors.resume_button_text"
                                />
                            </div>
                        </div>
                    </div>
                </AppCard>

                <!-- Save Action -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <AppButton
                        type="submit"
                        variant="primary"
                        size="md"
                        :loading="form.processing"
                    >
                        Save Front Home Settings
                    </AppButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
