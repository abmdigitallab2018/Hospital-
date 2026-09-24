<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppMediaUploader from '@/Components/UI/AppMediaUploader.vue';
import { Building2, Globe, Mail, Phone, MapPin, Share2 } from 'lucide-vue-next';

const props = defineProps({
    settings: {
        type: Object,
        required: true
    }
});

const form = useForm({
    site_title: props.settings.site_title || '',
    site_subtitle: props.settings.site_subtitle || '',
    logo_text: props.settings.logo_text || '',
    logo_file: null,
    logo_image: props.settings.logo_image || '',
    contact_email: props.settings.contact_email || '',
    contact_phone: props.settings.contact_phone || '',
    location: props.settings.location || '',
    github_url: props.settings.github_url || '',
    linkedin_url: props.settings.linkedin_url || '',
    twitter_url: props.settings.twitter_url || '',
    footer_text: props.settings.footer_text || '',
    copyright_text: props.settings.copyright_text || '',
});

const logoPreview = ref(
    props.settings.logo_image
        ? (props.settings.logo_image.startsWith('http') ? props.settings.logo_image : `/storage/${props.settings.logo_image}`)
        : null
);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo_file = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.transform(data => ({
        ...data,
        _method: 'put'
    })).post(route('admin.settings.organization.update'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Organization & Branding Settings - Admin Panel" />

        <div class="max-w-4xl mx-auto space-y-6">
            <AdminPageHeader
                title="Organization & Branding Settings"
                description="Manage your brand identity, logo, site titles, contact details, social links, and footer information."
                :breadcrumbs="[
                    { label: 'Admin', href: route('admin.dashboard') },
                    { label: 'Settings' },
                    { label: 'Organization' }
                ]"
            />

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Branding & Identity -->
                <AppCard title="Brand & Identity" subtitle="Define the name, monogram, and logo shown in the navigation and metadata.">
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AppInput
                                v-model="form.site_title"
                                label="Site / Brand Title"
                                placeholder="e.g. ABM Digital Lab"
                                :error="form.errors.site_title"
                                required
                            />

                            <AppInput
                                v-model="form.site_subtitle"
                                label="Subtitle / Tagline"
                                placeholder="e.g. Software Architect"
                                :error="form.errors.site_subtitle"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AppInput
                                v-model="form.logo_text"
                                label="Logo Monogram / Text"
                                placeholder="e.g. BM"
                                hint="Short 2-4 letter badge icon"
                                :error="form.errors.logo_text"
                            />

                            <AppMediaUploader
                                v-model="form.logo_file"
                                label="Custom Logo Image (Optional)"
                                hint="Upload your company logo or monogram image (PNG, SVG, JPG, WEBP)"
                                accept="image/*"
                                :existingMedia="form.logo_image"
                                :error="form.errors.logo_file"
                            />
                        </div>
                    </div>
                </AppCard>

                <!-- Contact Information -->
                <AppCard title="Contact Information" subtitle="Public contact credentials presented to visitors on the website.">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <AppInput
                            v-model="form.contact_email"
                            type="email"
                            label="Contact Email"
                            placeholder="bhavesh@example.com"
                            :error="form.errors.contact_email"
                            required
                        />

                        <AppInput
                            v-model="form.contact_phone"
                            label="Contact Phone (Optional)"
                            placeholder="+91 98765 43210"
                            :error="form.errors.contact_phone"
                        />

                        <AppInput
                            v-model="form.location"
                            label="Location / Timezone"
                            placeholder="Remote / Worldwide"
                            :error="form.errors.location"
                        />
                    </div>
                </AppCard>

                <!-- Social Profiles -->
                <AppCard title="Social Profiles" subtitle="Direct links to your public engineering presence.">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <AppInput
                            v-model="form.github_url"
                            type="url"
                            label="GitHub URL"
                            placeholder="https://github.com/..."
                            :error="form.errors.github_url"
                        />

                        <AppInput
                            v-model="form.linkedin_url"
                            type="url"
                            label="LinkedIn URL"
                            placeholder="https://linkedin.com/in/..."
                            :error="form.errors.linkedin_url"
                        />

                        <AppInput
                            v-model="form.twitter_url"
                            type="url"
                            label="Twitter / X URL"
                            placeholder="https://twitter.com/..."
                            :error="form.errors.twitter_url"
                        />
                    </div>
                </AppCard>

                <!-- Footer Details -->
                <AppCard title="Footer Details" subtitle="Customize the concluding information and copyright text.">
                    <div class="space-y-4">
                        <AppTextarea
                            v-model="form.footer_text"
                            label="Footer Description / Bio Snippet"
                            rows="2"
                            placeholder="e.g. Senior Full-Stack Software Engineer specializing in Laravel & Vue..."
                            :error="form.errors.footer_text"
                        />

                        <AppInput
                            v-model="form.copyright_text"
                            label="Copyright Notice"
                            placeholder="© 2026 ABM Digital Lab. All rights reserved."
                            :error="form.errors.copyright_text"
                        />
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
                        Save Organization Settings
                    </AppButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
