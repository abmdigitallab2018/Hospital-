<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppMediaUploader from '@/Components/UI/AppMediaUploader.vue';
import { Download, FileText } from 'lucide-vue-next';

const props = defineProps({
    about: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    hero_tagline: props.about.hero_tagline || '',
    bio: props.about.bio || '',
    years_experience: props.about.years_experience ?? 5,
    completed_projects: props.about.completed_projects ?? 45,
    resume: null,
    resume_path: props.about.resume_path || '',
    documents: [],
});

const submit = () => {
    form.transform(data => ({
        ...data,
        _method: 'put'
    })).post(route('admin.about.update'));
};
</script>

<template>
    <AdminLayout>
        <Head title="About & Bio Settings - Admin Panel" />

        <div class="max-w-3xl mx-auto">
            <AdminPageHeader
                title="About & Biography Settings"
                description="Control your core headline, experience counters, professional biography, and downloadable resume."
                :breadcrumbs="[
                    { label: 'Admin', href: route('admin.dashboard') },
                    { label: 'About Settings' }
                ]"
            />

            <AppCard>
                <form @submit.prevent="submit" class="space-y-6">
                    <AppInput
                        v-model="form.hero_tagline"
                        label="Hero Tagline / Subtitle"
                        placeholder="e.g. Senior Full-Stack Engineer & Solutions Architect"
                        hint="Displayed prominently in the hero section"
                        :error="form.errors.hero_tagline"
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AppInput
                            v-model.number="form.years_experience"
                            type="number"
                            label="Years of Experience"
                            :error="form.errors.years_experience"
                            required
                        />

                        <AppInput
                            v-model.number="form.completed_projects"
                            type="number"
                            label="Completed Projects"
                            :error="form.errors.completed_projects"
                            required
                        />
                    </div>

                    <AppTextarea
                        v-model="form.bio"
                        label="Professional Bio"
                        rows="6"
                        placeholder="Describe your technical background, domain specializations, framework proficiencies, and engineering passion..."
                        :error="form.errors.bio"
                    />

                    <!-- Resume Upload Section -->
                    <div class="p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                    <FileText class="w-4 h-4 text-indigo-600" />
                                    <span>Resume / CV Document</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Visitors can download this directly from the hero and about sections.</p>
                            </div>
                            <a
                                v-if="about.resume_url"
                                :href="about.resume_url"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-indigo-600 hover:text-indigo-800 shadow-2xs self-start transition"
                            >
                                <Download class="w-3.5 h-3.5" />
                                <span>Download Current</span>
                            </a>
                        </div>

                    <!-- Resume Upload Section with Laravel Media Library -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <AppMediaUploader
                            v-model="form.resume"
                            label="Resume / Curriculum Vitae (CV)"
                            hint="Upload your official resume or CV document (PDF, DOC, DOCX)"
                            accept=".pdf,.doc,.docx"
                            :existingMedia="about.resume_url"
                            :error="form.errors.resume"
                        />

                        <AppInput
                            v-model="form.resume_path"
                            label="Or External Resume URL"
                            placeholder="https://drive.google.com/..."
                            :error="form.errors.resume_path"
                        />

                        <!-- Additional Certifications & Documents (Multiple Files) -->
                        <AppMediaUploader
                            v-model="form.documents"
                            multiple
                            label="Professional Certifications & Diplomas (Multiple Files)"
                            hint="Upload degrees, certificates, recommendation letters, or work verification PDFs at once"
                            :existingMedia="about.documents || []"
                            :error="form.errors.documents"
                        />
                    </div>
                </div>

                    <div class="flex items-center justify-end pt-5 border-t border-slate-100">
                        <AppButton
                            type="submit"
                            variant="primary"
                            size="md"
                            :loading="form.processing"
                        >
                            Save Settings
                        </AppButton>
                    </div>
                </form>
            </AppCard>
        </div>
    </AdminLayout>
</template>
