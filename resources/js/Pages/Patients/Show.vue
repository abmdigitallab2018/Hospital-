<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeft,
    Calendar,
    Stethoscope,
    Receipt,
    FileText,
    HeartPulse,
    AlertTriangle,
    Clock,
    Printer,
    Edit3,
    Plus,
    CreditCard,
    Phone,
    MapPin,
    ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
    patient: Object,
});

const activeTab = ref('overview');
</script>

<template>
    <AppLayout>
        <Head :title="patient.full_name + ' - Patient Profile'" />

        <div class="space-y-6">
            <!-- Back & Header -->
            <div class="flex items-center gap-3">
                <Link
                    :href="route('patients.index')"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                            {{ patient.patient_uid }}
                        </span>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ patient.full_name }}</h1>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Registered on {{ new Date(patient.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                    </p>
                </div>
            </div>

            <!-- Profile Summary Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-emerald-600/20">
                            {{ patient.first_name[0] }}{{ patient.last_name[0] }}
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-slate-900 text-lg">{{ patient.full_name }}</span>
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-red-50 text-red-700 border border-red-200">
                                    {{ patient.blood_group }}
                                </span>
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 uppercase">
                                    {{ patient.status }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                <span>{{ patient.gender }} <strong v-if="patient.age">({{ patient.age }} yrs)</strong></span>
                                <span>•</span>
                                <span>{{ patient.phone }}</span>
                                <span v-if="patient.city">•</span>
                                <span v-if="patient.city">{{ patient.city }}, {{ patient.state }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="route('appointments.index', { search: patient.patient_uid })"
                            class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                        >
                            <Calendar class="w-3.5 h-3.5" />
                            <span>Book Appointment</span>
                        </Link>

                        <Link
                            :href="route('consultations.create', { patient_id: patient.id })"
                            class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                        >
                            <Stethoscope class="w-3.5 h-3.5" />
                            <span>Start Consultation</span>
                        </Link>

                        <Link
                            :href="route('invoices.create', { patient_id: patient.id })"
                            class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                        >
                            <Receipt class="w-3.5 h-3.5" />
                            <span>New Invoice</span>
                        </Link>

                        <Link
                            :href="route('patients.edit', patient.id)"
                            class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition"
                            title="Edit Details"
                        >
                            <Edit3 class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <!-- Allergy Warning Banner -->
                <div v-if="patient.allergies && patient.allergies !== 'None'" class="mt-4 p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                    <AlertTriangle class="w-4 h-4 text-amber-600 flex-shrink-0" />
                    <div>
                        <strong class="font-bold">Drug Allergies & Precautions:</strong> {{ patient.allergies }}
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-bold">
                <button
                    type="button"
                    @click="activeTab = 'overview'"
                    :class="[
                        'pb-3 px-3 transition border-b-2 -mb-px',
                        activeTab === 'overview' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Medical Overview
                </button>

                <button
                    type="button"
                    @click="activeTab = 'appointments'"
                    :class="[
                        'pb-3 px-3 transition border-b-2 -mb-px',
                        activeTab === 'appointments' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Appointments ({{ patient.appointments?.length || 0 }})
                </button>

                <button
                    type="button"
                    @click="activeTab = 'consultations'"
                    :class="[
                        'pb-3 px-3 transition border-b-2 -mb-px',
                        activeTab === 'consultations' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Consultations & Prescriptions ({{ patient.visits?.length || 0 }})
                </button>

                <button
                    type="button"
                    @click="activeTab = 'invoices'"
                    :class="[
                        'pb-3 px-3 transition border-b-2 -mb-px',
                        activeTab === 'invoices' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Billing & Invoices ({{ patient.invoices?.length || 0 }})
                </button>

                <button
                    type="button"
                    @click="activeTab = 'followups'"
                    :class="[
                        'pb-3 px-3 transition border-b-2 -mb-px',
                        activeTab === 'followups' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Follow-Ups ({{ patient.follow_ups?.length || 0 }})
                </button>
            </div>

            <!-- Tab 1: Medical Overview -->
            <div v-if="activeTab === 'overview'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Medical Background -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <HeartPulse class="w-4 h-4 text-emerald-600" />
                        <span>Clinical Profile</span>
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="block text-slate-400 font-medium">Known Conditions:</span>
                            <span class="font-bold text-slate-800">{{ patient.existing_conditions || 'None reported' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-medium">Drug Allergies:</span>
                            <span class="font-bold text-slate-800">{{ patient.allergies || 'No known drug allergies' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-medium">Past Medical & Surgical History:</span>
                            <p class="text-slate-700 leading-relaxed mt-1">{{ patient.medical_history || 'No prior surgeries or significant hospitalization recorded.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact & Demographics -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <Phone class="w-4 h-4 text-emerald-600" />
                        <span>Contact & Emergency Information</span>
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="block text-slate-400 font-medium">Primary Phone:</span>
                            <span class="font-bold text-slate-800">{{ patient.phone }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-medium">Email:</span>
                            <span class="font-bold text-slate-800">{{ patient.email || 'Not provided' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-medium">Full Address:</span>
                            <span class="text-slate-700">{{ patient.address || '—' }}, {{ patient.city }}, {{ patient.state }} {{ patient.postal_code }}</span>
                        </div>
                        <div class="pt-3 border-t border-slate-100">
                            <span class="block text-slate-400 font-medium">Emergency Contact Person:</span>
                            <span class="font-bold text-slate-800">{{ patient.emergency_contact_name || 'Not provided' }} ({{ patient.emergency_contact_phone || '—' }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Appointments -->
            <div v-if="activeTab === 'appointments'" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div v-if="patient.appointments.length === 0" class="p-8 text-center text-slate-400 text-xs">
                    No appointments scheduled yet.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Number & Token</th>
                                <th class="py-3 px-4">Date & Time</th>
                                <th class="py-3 px-4">Doctor</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="apt in patient.appointments" :key="apt.id" class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    {{ apt.appointment_number }} (Token #{{ apt.token_number }})
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    {{ apt.appointment_date }} at {{ apt.start_time?.substring(0, 5) }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    Dr. {{ apt.doctor?.user?.name }}
                                </td>
                                <td class="py-3 px-4 capitalize text-slate-600">
                                    {{ apt.type.replace('_', ' ') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            apt.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'
                                        ]"
                                    >
                                        {{ apt.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500">{{ apt.reason || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: Consultations & Prescriptions -->
            <div v-if="activeTab === 'consultations'" class="space-y-4">
                <div v-if="patient.visits.length === 0" class="bg-white rounded-3xl p-8 text-center text-slate-400 text-xs border border-slate-200/80">
                    No consultations recorded yet.
                </div>
                <div
                    v-for="v in patient.visits"
                    :key="v.id"
                    class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-xs font-bold text-slate-400">{{ v.visit_date }}</span>
                            <h3 class="text-sm font-bold text-slate-900">Doctor: Dr. {{ v.doctor?.user?.name }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                v-if="v.prescription"
                                :href="route('prescriptions.print', v.prescription.id)"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition flex items-center gap-1.5"
                                target="_blank"
                            >
                                <Printer class="w-3.5 h-3.5 text-slate-600" />
                                <span>Print Rx</span>
                            </Link>

                            <Link
                                :href="route('consultations.show', v.id)"
                                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl transition"
                            >
                                Details →
                            </Link>
                        </div>
                    </div>

                    <!-- Vitals Strip -->
                    <div v-if="v.vital" class="flex flex-wrap gap-2 text-[11px]">
                        <span v-if="v.vital.blood_pressure_systolic" class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200 font-semibold">
                            BP: <strong>{{ v.vital.blood_pressure_systolic }}/{{ v.vital.blood_pressure_diastolic }}</strong> mmHg
                        </span>
                        <span v-if="v.vital.pulse_rate" class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200 font-semibold">
                            Pulse: <strong>{{ v.vital.pulse_rate }}</strong> bpm
                        </span>
                        <span v-if="v.vital.temperature" class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200 font-semibold">
                            Temp: <strong>{{ v.vital.temperature }}</strong> °F
                        </span>
                        <span v-if="v.vital.oxygen_saturation" class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200 font-semibold">
                            SpO2: <strong>{{ v.vital.oxygen_saturation }}%</strong>
                        </span>
                        <span v-if="v.vital.weight_kg" class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200 font-semibold">
                            Weight: <strong>{{ v.vital.weight_kg }}</strong> kg (BMI: {{ v.vital.bmi }})
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="block font-bold text-slate-500 uppercase text-[10px]">Chief Complaints</span>
                            <p class="text-slate-800 mt-0.5">{{ v.chief_complaints }}</p>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-500 uppercase text-[10px]">Clinical Diagnosis</span>
                            <p class="font-bold text-emerald-800 mt-0.5">{{ v.diagnosis || 'Clinical evaluation' }}</p>
                        </div>
                    </div>

                    <!-- Prescribed Medicines -->
                    <div v-if="v.prescription?.items?.length > 0" class="pt-3 border-t border-slate-100">
                        <span class="block font-bold text-slate-500 uppercase text-[10px] mb-2">Prescribed Medicines</span>
                        <div class="divide-y divide-slate-100 bg-slate-50/70 rounded-2xl p-3 text-xs">
                            <div v-for="item in v.prescription.items" :key="item.id" class="py-1.5 flex justify-between">
                                <div>
                                    <strong class="text-slate-900">{{ item.medicine_name }}</strong>
                                    <span class="text-slate-500 text-[11px] ml-2">({{ item.dosage }})</span>
                                </div>
                                <div class="text-right text-slate-600">
                                    <span class="font-semibold">{{ item.frequency }}</span> • <span>{{ item.duration }}</span>
                                    <span class="text-slate-400 text-[10px] ml-1">[{{ item.instructions }}]</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Invoices -->
            <div v-if="activeTab === 'invoices'" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div v-if="patient.invoices.length === 0" class="p-8 text-center text-slate-400 text-xs">
                    No billing records found.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Invoice #</th>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4">Total</th>
                                <th class="py-3 px-4">Paid</th>
                                <th class="py-3 px-4">Balance</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="inv in patient.invoices" :key="inv.id" class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ inv.invoice_number }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ inv.invoice_date }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">₹{{ Number(inv.total_amount).toLocaleString('en-IN') }}</td>
                                <td class="py-3 px-4 text-emerald-600 font-semibold">₹{{ Number(inv.paid_amount).toLocaleString('en-IN') }}</td>
                                <td class="py-3 px-4 font-bold" :class="inv.balance_amount > 0 ? 'text-amber-600' : 'text-slate-400'">
                                    ₹{{ Number(inv.balance_amount).toLocaleString('en-IN') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            inv.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : '',
                                            inv.payment_status === 'partially_paid' ? 'bg-amber-100 text-amber-800' : '',
                                            inv.payment_status === 'unpaid' ? 'bg-red-100 text-red-800' : ''
                                        ]"
                                    >
                                        {{ inv.payment_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="route('invoices.show', inv.id)" class="px-2 py-1 text-slate-600 hover:text-emerald-700 font-bold">
                                            View
                                        </Link>
                                        <Link :href="route('invoices.print', inv.id)" target="_blank" class="p-1 text-slate-400 hover:text-slate-700">
                                            <Printer class="w-3.5 h-3.5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 5: Follow-ups -->
            <div v-if="activeTab === 'followups'" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-3">
                <div v-if="patient.follow_ups.length === 0" class="text-center py-6 text-slate-400 text-xs">
                    No follow-ups recorded for this patient.
                </div>
                <div
                    v-for="fu in patient.follow_ups"
                    :key="fu.id"
                    class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs"
                >
                    <div>
                        <div class="font-bold text-slate-900">Due: {{ fu.follow_up_date }}</div>
                        <div class="text-[11px] text-slate-500">Dr. {{ fu.doctor?.user?.name }} • {{ fu.notes }}</div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                        {{ fu.status }}
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
