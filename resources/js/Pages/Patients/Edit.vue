<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, Save, User, Phone, MapPin, HeartPulse, ShieldAlert } from 'lucide-vue-next';

const props = defineProps({
    patient: Object,
});

const form = useForm({
    first_name: props.patient.first_name,
    last_name: props.patient.last_name,
    date_of_birth: props.patient.date_of_birth || '',
    gender: props.patient.gender,
    blood_group: props.patient.blood_group || 'Unknown',
    phone: props.patient.phone,
    email: props.patient.email || '',
    address: props.patient.address || '',
    city: props.patient.city || '',
    state: props.patient.state || '',
    postal_code: props.patient.postal_code || '',
    emergency_contact_name: props.patient.emergency_contact_name || '',
    emergency_contact_phone: props.patient.emergency_contact_phone || '',
    medical_history: props.patient.medical_history || '',
    allergies: props.patient.allergies || '',
    existing_conditions: props.patient.existing_conditions || '',
    notes: props.patient.notes || '',
    status: props.patient.status,
});

const submit = () => {
    form.put(route('patients.update', props.patient.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="'Edit Patient - ' + patient.full_name" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('patients.show', patient.id)"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Edit Patient Demographics</h1>
                    <p class="text-xs text-slate-500">Updating UID: {{ patient.patient_uid }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-8">
                <!-- Demographics -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <User class="w-4 h-4 text-emerald-600" />
                        <span>Basic Demographics</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">First Name *</label>
                            <input
                                type="text"
                                v-model="form.first_name"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Last Name *</label>
                            <input
                                type="text"
                                v-model="form.last_name"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth</label>
                            <input
                                type="date"
                                v-model="form.date_of_birth"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Gender *</label>
                            <select
                                v-model="form.gender"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Blood Group</label>
                            <select
                                v-model="form.blood_group"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="Unknown">Unknown</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                            <select
                                v-model="form.status"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Details -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <Phone class="w-4 h-4 text-emerald-600" />
                        <span>Contact Information</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Phone *</label>
                            <input
                                type="text"
                                v-model="form.phone"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                            <input
                                type="email"
                                v-model="form.email"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                            <input
                                type="text"
                                v-model="form.city"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Medical History -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <HeartPulse class="w-4 h-4 text-emerald-600" />
                        <span>Allergies & Medical Profile</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Drug Allergies</label>
                            <input
                                type="text"
                                v-model="form.allergies"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Chronic Conditions</label>
                            <input
                                type="text"
                                v-model="form.existing_conditions"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Medical History Notes</label>
                            <textarea
                                v-model="form.medical_history"
                                rows="3"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <Link
                        :href="route('patients.show', patient.id)"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>Update Patient Record</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
