<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppMediaUploader from '@/Components/UI/AppMediaUploader.vue';
import { ArrowLeft, Plus, Trash2, Check, X, Sparkles } from 'lucide-vue-next';

const form = useForm({
    name: '',
    slug: '',
    tagline: '',
    price: '',
    currency: '$',
    billing_period: 'project',
    description: '',
    features: [''],
    not_included: [''],
    badge: '',
    is_featured: false,
    is_active: true,
    sort_order: 0,
    cta_text: 'Get Started',
    cta_url: '#contact',
    documents: [],
});

const addFeature = () => {
    form.features.push('');
};

const removeFeature = (index) => {
    if (form.features.length > 1) {
        form.features.splice(index, 1);
    } else {
        form.features[0] = '';
    }
};

const addNotIncluded = () => {
    form.not_included.push('');
};

const removeNotIncluded = (index) => {
    if (form.not_included.length > 1) {
        form.not_included.splice(index, 1);
    } else {
        form.not_included[0] = '';
    }
};

const submit = () => {
    form.post(route('admin.pricing.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Pricing Plan - Admin Panel" />

        <div class="max-w-4xl mx-auto">
            <AdminPageHeader
                title="Create Pricing Plan"
                description="Define a new service package, fee structure, and client deliverable features."
                :breadcrumbs="[
                    { label: 'Admin', href: route('admin.dashboard') },
                    { label: 'Pricing Plans', href: route('admin.pricing.index') },
                    { label: 'Create' }
                ]"
            >
                <template #actions>
                    <AppButton
                        :href="route('admin.pricing.index')"
                        variant="ghost"
                        size="sm"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1.5" />
                        <span>Back to Plans</span>
                    </AppButton>
                </template>
            </AdminPageHeader>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Plan Information -->
                <AppCard title="Plan Overview & Pricing" subtitle="Set core plan title, rates, and billing duration.">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AppInput
                                v-model="form.name"
                                label="Plan Name"
                                placeholder="e.g. Professional MVP"
                                :error="form.errors.name"
                                required
                            />

                            <AppInput
                                v-model="form.badge"
                                label="Highlight Badge (Optional)"
                                placeholder="e.g. Most Popular, Best Value"
                                :error="form.errors.badge"
                            />
                        </div>

                        <AppInput
                            v-model="form.tagline"
                            label="Tagline / Short Hook"
                            placeholder="e.g. Ideal for startups & creators launching high-impact apps"
                            :error="form.errors.tagline"
                        />

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <AppInput
                                v-model="form.currency"
                                label="Currency Symbol"
                                placeholder="$"
                                :error="form.errors.currency"
                            />

                            <AppInput
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                label="Price"
                                placeholder="499.00"
                                :error="form.errors.price"
                                required
                            />

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Cycle</label>
                                <select
                                    v-model="form.billing_period"
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                >
                                    <option value="project">Per Project</option>
                                    <option value="month">Per Month</option>
                                    <option value="year">Per Year</option>
                                    <option value="one-time">One-Time Fee</option>
                                </select>
                            </div>
                        </div>

                        <AppTextarea
                            v-model="form.description"
                            label="Description (Optional)"
                            placeholder="Detailed overview of what this tier accomplishes..."
                            rows="2"
                            :error="form.errors.description"
                        />
                    </div>
                </AppCard>

                <!-- Features & Deliverables Checklist -->
                <AppCard title="Included Features" subtitle="List what clients receive in this package.">
                    <div class="space-y-3">
                        <div
                            v-for="(feature, idx) in form.features"
                            :key="idx"
                            class="flex items-center gap-2"
                        >
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-200">
                                <Check class="w-4 h-4" />
                            </div>
                            <input
                                v-model="form.features[idx]"
                                type="text"
                                placeholder="e.g. Responsive Vue 3 Frontend, Stripe Payment Gateway"
                                class="flex-1 px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            />
                            <button
                                type="button"
                                @click="removeFeature(idx)"
                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition"
                                title="Remove feature"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="pt-2">
                            <AppButton
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="addFeature"
                            >
                                <Plus class="w-4 h-4 mr-1.5" />
                                <span>Add Feature Line</span>
                            </AppButton>
                        </div>
                    </div>
                </AppCard>

                <!-- Not Included Features (Optional) -->
                <AppCard title="Exclusions / Not Included (Optional)" subtitle="Clarify scope boundaries (optional).">
                    <div class="space-y-3">
                        <div
                            v-for="(item, idx) in form.not_included"
                            :key="idx"
                            class="flex items-center gap-2"
                        >
                            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center flex-shrink-0 border border-slate-200">
                                <X class="w-4 h-4" />
                            </div>
                            <input
                                v-model="form.not_included[idx]"
                                type="text"
                                placeholder="e.g. 24/7 SLA Hotline (Enterprise only)"
                                class="flex-1 px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            />
                            <button
                                type="button"
                                @click="removeNotIncluded(idx)"
                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition"
                                title="Remove item"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="pt-2">
                            <AppButton
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="addNotIncluded"
                            >
                                <Plus class="w-4 h-4 mr-1.5" />
                                <span>Add Exclusion</span>
                            </AppButton>
                        </div>
                    </div>
                </AppCard>

                <!-- Plan Documents (Common Media Uploader) -->
                <AppCard title="Plan Documents & Brochures" subtitle="Attach rate cards, service level agreements, or contract templates.">
                    <AppMediaUploader
                        v-model="form.documents"
                        multiple
                        label="Plan Documents & PDFs (Multiple Files)"
                        hint="Attach proposal PDF templates, terms of service, or specification documents"
                        :error="form.errors.documents"
                    />
                </AppCard>

                <!-- CTA & Publishing Settings -->
                <AppCard title="Action Button & Visibility" subtitle="Configure the button link and layout position.">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <AppInput
                                v-model="form.cta_text"
                                label="Button Text"
                                placeholder="e.g. Choose Plan"
                                :error="form.errors.cta_text"
                            />

                            <AppInput
                                v-model="form.cta_url"
                                label="Button URL / Anchor"
                                placeholder="e.g. #contact or /contact"
                                :error="form.errors.cta_url"
                            />

                            <AppInput
                                v-model="form.sort_order"
                                type="number"
                                label="Display Order"
                                placeholder="0"
                                :error="form.errors.sort_order"
                            />
                        </div>

                        <div class="flex items-center gap-6 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    v-model="form.is_featured"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                                    Highlight as Featured / Most Popular Plan
                                </span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-xs font-semibold text-slate-700">
                                    Publish on Website (Active)
                                </span>
                            </label>
                        </div>
                    </div>
                </AppCard>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <AppButton
                        :href="route('admin.pricing.index')"
                        variant="secondary"
                    >
                        Cancel
                    </AppButton>

                    <AppButton
                        type="submit"
                        variant="primary"
                        :loading="form.processing"
                    >
                        Create Plan
                    </AppButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
