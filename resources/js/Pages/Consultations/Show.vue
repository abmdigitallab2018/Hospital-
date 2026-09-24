<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeft,
    Printer,
    Stethoscope,
    FileText,
    Activity,
    Receipt,
    Calendar,
    CheckCircle2,
    HeartPulse,
    AlertTriangle
} from 'lucide-vue-next';

defineProps({
    consultation: Object,
});
</script>

<template>
    <AppLayout>
        <Head :title="'Consultation - ' + consultation.patient?.full_name" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('consultations.index')"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-black text-slate-900 tracking-tight">Clinical Consultation Record</h1>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                                {{ consultation.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">
                            Visit Date: {{ consultation.visit_date }} • Attending: Dr. {{ consultation.doctor?.user?.name }} ({{ consultation.doctor?.specialization }})
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        v-if="consultation.prescription"
                        :href="route('prescriptions.print', consultation.prescription.id)"
                        target="_blank"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                    >
                        <Printer class="w-4 h-4 text-emerald-400" />
                        <span>Print Prescription</span>
                    </Link>
                </div>
            </div>

            <!-- Patient Information Banner -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 font-black text-base flex items-center justify-center">
                        {{ consultation.patient?.first_name[0] }}{{ consultation.patient?.last_name[0] }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('patients.show', consultation.patient?.id)" class="font-bold text-slate-900 text-base hover:text-emerald-600">
                                {{ consultation.patient?.full_name }}
                            </Link>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                                {{ consultation.patient?.patient_uid }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-black text-[10px]">
                                {{ consultation.patient?.blood_group }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ consultation.patient?.gender }} <span v-if="consultation.patient?.age">({{ consultation.patient?.age }} yrs)</span> • Phone: {{ consultation.patient?.phone }}
                        </p>
                    </div>
                </div>

                <div v-if="consultation.patient?.allergies && consultation.patient?.allergies !== 'None'" class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                    <AlertTriangle class="w-4 h-4 text-amber-600 flex-shrink-0" />
                    <div>
                        <strong>Allergies:</strong> {{ consultation.patient?.allergies }}
                    </div>
                </div>
            </div>

            <!-- Vital Signs Recorded -->
            <div v-if="consultation.vital" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    <Activity class="w-4 h-4 text-emerald-600" />
                    <span>Vital Signs & Anthropometry</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-slate-400 text-[10px] font-bold uppercase">Blood Pressure</span>
                        <span class="text-base font-black text-slate-900 font-mono">
                            {{ consultation.vital.blood_pressure_systolic ? `${consultation.vital.blood_pressure_systolic}/${consultation.vital.blood_pressure_diastolic}` : '—' }}
                        </span>
                        <span class="text-[10px] text-slate-500 block">mmHg</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-slate-400 text-[10px] font-bold uppercase">Pulse Rate</span>
                        <span class="text-base font-black text-slate-900 font-mono">
                            {{ consultation.vital.pulse_rate || '—' }}
                        </span>
                        <span class="text-[10px] text-slate-500 block">bpm</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-slate-400 text-[10px] font-bold uppercase">Temperature</span>
                        <span class="text-base font-black text-slate-900 font-mono">
                            {{ consultation.vital.temperature ? `${consultation.vital.temperature}°F` : '—' }}
                        </span>
                        <span class="text-[10px] text-slate-500 block">Oral / Axillary</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-slate-400 text-[10px] font-bold uppercase">Oxygen (SpO2)</span>
                        <span class="text-base font-black text-slate-900 font-mono">
                            {{ consultation.vital.oxygen_saturation ? `${consultation.vital.oxygen_saturation}%` : '—' }}
                        </span>
                        <span class="text-[10px] text-slate-500 block">Room air</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-slate-400 text-[10px] font-bold uppercase">Weight & Height</span>
                        <span class="text-sm font-bold text-slate-900 font-mono">
                            {{ consultation.vital.weight_kg ? `${consultation.vital.weight_kg} kg` : '—' }} /
                            {{ consultation.vital.height_cm ? `${consultation.vital.height_cm} cm` : '—' }}
                        </span>
                        <span class="text-[10px] text-slate-500 block">Anthropometry</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-slate-400 text-[10px] font-bold uppercase">Body Mass Index (BMI)</span>
                        <span class="text-base font-black text-slate-900 font-mono">
                            {{ consultation.vital.bmi || '—' }}
                        </span>
                        <span class="text-[10px] text-emerald-600 font-bold block">kg/m²</span>
                    </div>
                </div>
            </div>

            <!-- Clinical Assessment & Plan -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    <Stethoscope class="w-4 h-4 text-emerald-600" />
                    <span>Clinical Assessment & Findings</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="block font-bold text-slate-500 uppercase text-[10px]">Chief Complaints</span>
                        <p class="text-slate-900 mt-1 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                            {{ consultation.chief_complaints }}
                        </p>
                    </div>

                    <div>
                        <span class="block font-bold text-slate-500 uppercase text-[10px]">Clinical Diagnosis</span>
                        <p class="font-bold text-emerald-900 mt-1 leading-relaxed bg-emerald-50/70 p-3 rounded-xl border border-emerald-100">
                            {{ consultation.diagnosis || 'Clinical evaluation completed' }}
                        </p>
                    </div>

                    <div v-if="consultation.symptoms">
                        <span class="block font-bold text-slate-500 uppercase text-[10px]">Associated Symptoms</span>
                        <p class="text-slate-800 mt-1 leading-relaxed">{{ consultation.symptoms }}</p>
                    </div>

                    <div v-if="consultation.examination_notes">
                        <span class="block font-bold text-slate-500 uppercase text-[10px]">Examination Notes</span>
                        <p class="text-slate-800 mt-1 leading-relaxed">{{ consultation.examination_notes }}</p>
                    </div>

                    <div v-if="consultation.treatment_plan" class="md:col-span-2">
                        <span class="block font-bold text-slate-500 uppercase text-[10px]">Treatment Directives & Plan</span>
                        <p class="text-slate-800 mt-1 leading-relaxed">{{ consultation.treatment_plan }}</p>
                    </div>

                    <div v-if="consultation.clinical_advice" class="md:col-span-2">
                        <span class="block font-bold text-slate-500 uppercase text-[10px]">Clinical Advice & Precautions</span>
                        <p class="text-slate-800 mt-1 leading-relaxed bg-amber-50/50 p-3 rounded-xl border border-amber-100">{{ consultation.clinical_advice }}</p>
                    </div>
                </div>
            </div>

            <!-- Prescribed Medications -->
            <div v-if="consultation.prescription?.items?.length > 0" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider">
                        <FileText class="w-4 h-4 text-emerald-600" />
                        <span>Prescription #{{ consultation.prescription.prescription_number }}</span>
                    </div>

                    <Link
                        :href="route('prescriptions.print', consultation.prescription.id)"
                        target="_blank"
                        class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1"
                    >
                        <Printer class="w-3.5 h-3.5" />
                        <span>Print Letterhead</span>
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Medicine Name</th>
                                <th class="py-2.5 px-3">Dosage</th>
                                <th class="py-2.5 px-3">Frequency</th>
                                <th class="py-2.5 px-3">Duration</th>
                                <th class="py-2.5 px-3">Instructions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in consultation.prescription.items" :key="item.id">
                                <td class="py-2.5 px-3 text-slate-400">{{ idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ item.medicine_name }}</td>
                                <td class="py-2.5 px-3 text-slate-700">{{ item.dosage }}</td>
                                <td class="py-2.5 px-3 font-semibold text-emerald-700">{{ item.frequency }}</td>
                                <td class="py-2.5 px-3 text-slate-700">{{ item.duration }}</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ item.instructions || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Follow-Up & Invoicing Links -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-if="consultation.follow_up" class="p-4 rounded-2xl bg-white border border-slate-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="block font-bold text-slate-900">Follow-Up Scheduled</span>
                        <span class="text-slate-500 text-[11px]">Due Date: {{ consultation.follow_up.follow_up_date }}</span>
                    </div>
                    <Link :href="route('follow-ups.index')" class="text-emerald-600 font-bold text-xs">
                        View Follow-Ups →
                    </Link>
                </div>

                <div v-if="consultation.invoice" class="p-4 rounded-2xl bg-white border border-slate-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="block font-bold text-slate-900">Invoice: {{ consultation.invoice.invoice_number }}</span>
                        <span class="text-slate-500 text-[11px]">
                            Total: ₹{{ Number(consultation.invoice.total_amount).toLocaleString('en-IN') }} (Status: {{ consultation.invoice.payment_status }})
                        </span>
                    </div>
                    <Link :href="route('invoices.show', consultation.invoice.id)" class="text-emerald-600 font-bold text-xs">
                        View Invoice →
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
