<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import { Sliders, Check, X, GripVertical } from 'lucide-vue-next';

const props = defineProps({
    menuSettings: {
        type: Array,
        required: true
    }
});

const form = useForm({
    menu_settings: JSON.parse(JSON.stringify(props.menuSettings))
});

const submit = () => {
    form.transform(data => ({
        ...data,
        _method: 'put'
    })).post(route('admin.settings.menu.update'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Master Menu Settings - Admin Panel" />

        <div class="max-w-3xl mx-auto space-y-6">
            <AdminPageHeader
                title="Master Menu & Section Settings"
                description="Enable or disable specific sections from both the public navigation bar and the homepage body."
                :breadcrumbs="[
                    { label: 'Admin', href: route('admin.dashboard') },
                    { label: 'Settings' },
                    { label: 'Menu' }
                ]"
            />

            <AppCard>
                <form @submit.prevent="submit" class="space-y-6">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Toggle any section below. When a section is disabled, its navigation link is hidden from the header and its corresponding content section is removed from the public website.
                    </p>

                    <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-2xl overflow-hidden bg-slate-50/50">
                        <div
                            v-for="(item, index) in form.menu_settings"
                            :key="item.key"
                            class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white transition-colors"
                        >
                            <!-- Left: Key & Toggle -->
                            <div class="flex items-center gap-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="item.is_enabled"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900 capitalize">{{ item.key }} Section</span>
                                        <AppBadge :variant="item.is_enabled ? 'emerald' : 'slate'" size="sm" dot>
                                            {{ item.is_enabled ? 'Enabled' : 'Disabled' }}
                                        </AppBadge>
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-mono">#{{ item.key }}</span>
                                </div>
                            </div>

                            <!-- Right: Label and Order Inputs -->
                            <div class="flex items-center gap-3">
                                <div class="w-48 sm:w-56">
                                    <AppInput
                                        v-model="item.label"
                                        label="Navigation Label"
                                        placeholder="e.g. Services"
                                        required
                                    />
                                </div>

                                <div class="w-20">
                                    <AppInput
                                        v-model.number="item.order"
                                        type="number"
                                        label="Order"
                                        placeholder="1"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <AppButton
                            type="submit"
                            variant="primary"
                            size="md"
                            :loading="form.processing"
                        >
                            Save Menu Settings
                        </AppButton>
                    </div>
                </form>
            </AppCard>
        </div>
    </AdminLayout>
</template>
