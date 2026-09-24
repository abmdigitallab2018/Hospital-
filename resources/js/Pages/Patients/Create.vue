<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, Save, User, Phone, MapPin, HeartPulse, ShieldAlert } from 'lucide-vue-next';

const form = useForm({
    first_name: '',
    last_name: '',
    date_of_birth: '',
    gender: 'male',
    blood_group: 'Unknown',
    phone: '',
    email: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    medical_history: '',
    allergies: '',
    existing_conditions: '',
    notes: '',
});

const submit = () => {
    form.post(route('patients.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="Register New Patient" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('patients.index')"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">Register New Patient</h1>
                        <p class="text-xs text-slate-500">Create permanent clinical health record and demographic profile</p>
                    </div>
                </div>
            </div>

            <!-- Registration Form Card -->
            <form @submit.prevent="submit" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-8">
                <!-- Section 1: Demographics -->
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
                                placeholder="e.g. Ramesh"
                            />
                            <p v-if="form.errors.first_name" class="mt-1 text-[11px] text-red-600">{{ form.errors.first_name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Last Name *</label>
                            <input
                                type="text"
                                v-model="form.last_name"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="e.g. Patel"
                            />
                            <p v-if="form.errors.last_name" class="mt-1 text-[11px] text-red-600">{{ form.errors.last_name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth</label>
                            <input
                                type="date"
                                v-model="form.date_of_birth"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                            <p v-if="form.errors.date_of_birth" class="mt-1 text-[11px] text-red-600">{{ form.errors.date_of_birth }}</p>
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
                            <p v-if="form.errors.gender" class="mt-1 text-[11px] text-red-600">{{ form.errors.gender }}</p>
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
                    </div>
                </div>

                <!-- Section 2: Contact Information -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <Phone class="w-4 h-4 text-emerald-600" />
                        <span>Contact & Address</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone *</label>
                            <input
                                type="text"
                                v-model="form.phone"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="+91 98765 43210"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-[11px] text-red-600">{{ form.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                            <input
                                type="email"
                                v-model="form.email"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="patient@example.com"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-[11px] text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Residential Address</label>
                            <input
                                type="text"
                                v-model="form.address"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Street, Flat/House no."
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                            <input
                                type="text"
                                v-model="form.city"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="e.g. Mumbai"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
                            <input
                                type="text"
                                v-model="form.state"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="e.g. Maharashtra"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Postal Code</label>
                            <input
                                type="text"
                                v-model="form.postal_code"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="400001"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Emergency Contact -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <ShieldAlert class="w-4 h-4 text-emerald-600" />
                        <span>Emergency Contact</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact Person</label>
                            <input
                                type="text"
                                v-model="form.emergency_contact_name"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="e.g. Sunita Patel (Spouse)"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact Phone</label>
                            <input
                                type="text"
                                v-model="form.emergency_contact_phone"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="+91 98765 00000"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 4: Clinical History & Allergies -->
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        <HeartPulse class="w-4 h-4 text-emerald-600" />
                        <span>Medical Background & Allergies</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Drug Allergies & Adverse Reactions</label>
                            <input
                                type="text"
                                v-model="form.allergies"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="e.g. Penicillin, NSAIDs, Sulfa (or 'None')"
                            />
                            <p class="text-[11px] text-slate-400 mt-1">Highlighted prominently on doctor consultation screens.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Known Chronic Conditions</label>
                            <input
                                type="text"
                                v-model="form.existing_conditions"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="e.g. Type 2 Diabetes, Hypertension, Asthma"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Past Medical / Surgical History</label>
                            <textarea
                                v-model="form.medical_history"
                                rows="3"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Summary of major surgeries, hospitalizations, or family history..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <Link
                        :href="route('patients.index')"
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
                        <span>Register Patient</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
