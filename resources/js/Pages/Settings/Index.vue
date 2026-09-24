<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Settings,
    Building2,
    Save,
    Phone,
    MapPin,
    FileText,
    Receipt,
    Shield
} from 'lucide-vue-next';

const props = defineProps({
    clinic: Object,
});

const form = useForm({
    name: props.clinic.name,
    phone: props.clinic.phone || '',
    emergency_phone: props.clinic.emergency_phone || '',
    email: props.clinic.email || '',
    address: props.clinic.address || '',
    city: props.clinic.city || '',
    state: props.clinic.state || '',
    postal_code: props.clinic.postal_code || '',
    registration_number: props.clinic.registration_number || '',
    tax_number: props.clinic.tax_number || '',
    consultation_fee: props.clinic.consultation_fee || 500,
    invoice_prefix: props.clinic.invoice_prefix || 'INV',
    prescription_disclaimer: props.clinic.prescription_disclaimer || '',
    currency_symbol: props.clinic.currency_symbol || '₹',
});

const submit = () => {
    form.put(route('settings.clinic.update'));
};
</script>

<template>
    <AppLayout>
        <Head title="Clinic Settings & Profile" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Clinic Profile & Preferences</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Official clinic address, contact information, consultation fee baseline, and letterhead disclaimers.
                </p>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-8">
                <!-- Section 1: Clinic Identity -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <Building2 class="w-4 h-4 text-emerald-600" />
                        <span>Establishment Profile</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Clinic / Hospital Name *</label>
                            <input
                                type="text"
                                v-model="form.name"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Official Reception Phone</label>
                            <input
                                type="text"
                                v-model="form.phone"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Emergency 24x7 Hotline</label>
                            <input
                                type="text"
                                v-model="form.emergency_phone"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Clinic Email Address</label>
                            <input
                                type="email"
                                v-model="form.email"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Currency Symbol</label>
                            <input
                                type="text"
                                v-model="form.currency_symbol"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Address -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <MapPin class="w-4 h-4 text-emerald-600" />
                        <span>Address & Branch Location</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="sm:col-span-3">
                            <label class="block font-bold text-slate-700 mb-1">Street Address</label>
                            <input
                                type="text"
                                v-model="form.address"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">City</label>
                            <input
                                type="text"
                                v-model="form.city"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">State</label>
                            <input
                                type="text"
                                v-model="form.state"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Postal Code (PIN)</label>
                            <input
                                type="text"
                                v-model="form.postal_code"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Legal & Invoicing Defaults -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <Receipt class="w-4 h-4 text-emerald-600" />
                        <span>Regulatory & Invoicing Defaults</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Clinical Establishment Reg Number</label>
                            <input
                                type="text"
                                v-model="form.registration_number"
                                placeholder="e.g. MH-MED-2024-912"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">GSTIN / Tax Number</label>
                            <input
                                type="text"
                                v-model="form.tax_number"
                                placeholder="e.g. 27AAACA1234F1Z8"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Default Consultation Fee (₹)</label>
                            <input
                                type="number"
                                v-model="form.consultation_fee"
                                min="0"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Invoice Prefix</label>
                            <input
                                type="text"
                                v-model="form.invoice_prefix"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none uppercase font-mono"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Prescription Disclaimer & Footer</label>
                            <textarea
                                v-model="form.prescription_disclaimer"
                                rows="3"
                                placeholder="Printed at bottom of official prescription..."
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2"
                    >
                        <Save class="w-4 h-4" />
                        <span>Save Clinic Preferences</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
