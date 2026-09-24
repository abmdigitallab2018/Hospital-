<script setup>
import { Head } from '@inertiajs/vue3';
import { Printer, ArrowLeft, HeartPulse } from 'lucide-vue-next';

defineProps({
    prescription: Object,
});

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head :title="'Prescription - ' + prescription.prescription_number" />

    <div class="min-h-screen bg-slate-100 p-4 sm:p-8 flex flex-col items-center print:bg-white print:p-0">
        <!-- Floating Print Toolbar (Hidden on print) -->
        <div class="max-w-3xl w-full mb-6 flex items-center justify-between print:hidden">
            <button
                type="button"
                @click="window.close()"
                class="px-4 py-2 bg-white text-slate-700 font-bold text-xs rounded-xl shadow-xs border border-slate-200 hover:bg-slate-50 transition flex items-center gap-1.5"
            >
                <ArrowLeft class="w-4 h-4" />
                <span>Close</span>
            </button>

            <button
                type="button"
                @click="triggerPrint"
                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center gap-2"
            >
                <Printer class="w-4 h-4" />
                <span>Print or Save as PDF</span>
            </button>
        </div>

        <!-- Official Prescription Letterhead Sheet (A4 Proportion) -->
        <div class="max-w-3xl w-full bg-white rounded-3xl shadow-xl print:shadow-none print:rounded-none border border-slate-200/80 print:border-none p-8 sm:p-12 space-y-6 text-slate-800 font-sans">
            <!-- 1. Hospital Letterhead Header -->
            <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-black flex-shrink-0 shadow-md">
                        <HeartPulse class="w-8 h-8 text-white" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none">
                            {{ prescription.clinic?.name || 'CarePulse Clinic' }}
                        </h1>
                        <p class="text-xs text-slate-600 mt-1">
                            {{ prescription.clinic?.address }}, {{ prescription.clinic?.city }}, {{ prescription.clinic?.state }} - {{ prescription.clinic?.postal_code }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Tel: {{ prescription.clinic?.phone }} • Emergency: {{ prescription.clinic?.emergency_phone || '108' }}
                        </p>
                        <p v-if="prescription.clinic?.registration_number" class="text-[10px] text-slate-400 font-mono mt-0.5">
                            Reg No: {{ prescription.clinic.registration_number }}
                        </p>
                    </div>
                </div>

                <!-- Doctor Credentials Header Right -->
                <div class="text-right">
                    <h2 class="text-base font-black text-slate-900 leading-tight">
                        Dr. {{ prescription.doctor?.user?.name }}
                    </h2>
                    <p class="text-xs font-semibold text-emerald-700 mt-0.5">
                        {{ prescription.doctor?.specialization }}
                    </p>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        {{ prescription.doctor?.qualification }}
                    </p>
                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                        Reg: {{ prescription.doctor?.license_number }}
                    </p>
                </div>
            </div>

            <!-- 2. Patient Demographics Strip -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Patient Name</span>
                    <strong class="text-slate-900 text-sm block">{{ prescription.patient?.full_name }}</strong>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Patient UID & Age</span>
                    <span class="font-bold text-slate-800 block">
                        {{ prescription.patient?.patient_uid }} • {{ prescription.patient?.gender }}
                        <span v-if="prescription.patient?.age">({{ prescription.patient.age }}y)</span>
                    </span>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Blood Group & Phone</span>
                    <span class="font-bold text-slate-800 block">
                        {{ prescription.patient?.blood_group }} • {{ prescription.patient?.phone }}
                    </span>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Date & Rx ID</span>
                    <span class="font-bold text-slate-800 block font-mono">
                        {{ prescription.date }} • {{ prescription.prescription_number }}
                    </span>
                </div>
            </div>

            <!-- Vitals & Diagnosis Strip if recorded -->
            <div v-if="prescription.visit?.vital || prescription.visit?.diagnosis" class="flex flex-wrap items-center justify-between gap-3 text-xs bg-emerald-50/50 p-3 rounded-2xl border border-emerald-100">
                <div v-if="prescription.visit?.diagnosis">
                    <strong class="text-emerald-950 font-bold">Clinical Diagnosis:</strong>
                    <span class="text-emerald-900 font-semibold ml-1.5">{{ prescription.visit.diagnosis }}</span>
                </div>

                <div v-if="prescription.visit?.vital" class="flex flex-wrap gap-2 text-[11px] font-mono text-slate-700">
                    <span v-if="prescription.visit.vital.blood_pressure_systolic">
                        BP: <strong>{{ prescription.visit.vital.blood_pressure_systolic }}/{{ prescription.visit.vital.blood_pressure_diastolic }}</strong> mmHg
                    </span>
                    <span v-if="prescription.visit.vital.pulse_rate">• Pulse: <strong>{{ prescription.visit.vital.pulse_rate }}</strong></span>
                    <span v-if="prescription.visit.vital.weight_kg">• Wt: <strong>{{ prescription.visit.vital.weight_kg }}kg</strong></span>
                </div>
            </div>

            <!-- 3. Classic Rx Symbol -->
            <div class="pt-2">
                <span class="font-serif text-3xl font-black text-slate-900">℞</span>
            </div>

            <!-- 4. Medicines Prescription Table -->
            <div class="overflow-x-auto min-h-[220px]">
                <table class="w-full text-left text-xs border border-slate-200 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4 w-10">#</th>
                            <th class="py-2.5 px-4">Medicine Name & Formulation</th>
                            <th class="py-2.5 px-4 w-28">Dosage</th>
                            <th class="py-2.5 px-4 w-28">Frequency</th>
                            <th class="py-2.5 px-4 w-28">Duration</th>
                            <th class="py-2.5 px-4">Instructions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(item, idx) in prescription.items" :key="item.id">
                            <td class="py-3 px-4 text-slate-400 font-mono">{{ idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900 text-sm">
                                {{ item.medicine_name }}
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ item.dosage }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-800">{{ item.frequency }}</td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ item.duration }}</td>
                            <td class="py-3 px-4 text-slate-600 font-medium">{{ item.instructions || 'As advised' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 5. Clinical Advice & Instructions -->
            <div v-if="prescription.notes || prescription.visit?.clinical_advice" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
                <span class="block font-bold text-slate-500 uppercase text-[10px] mb-1">General Advice & Precautions:</span>
                <p class="text-slate-800 leading-relaxed whitespace-pre-line">{{ prescription.notes || prescription.visit?.clinical_advice }}</p>
            </div>

            <!-- 6. Next Follow-up & Doctor Digital Signature -->
            <div class="pt-8 flex items-end justify-between border-t border-slate-200 text-xs">
                <div>
                    <span v-if="prescription.visit?.follow_up_date" class="block font-bold text-slate-800">
                        Next Review / Follow-up: <span class="text-emerald-700 underline">{{ prescription.visit.follow_up_date }}</span>
                    </span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Please bring this prescription for future visits.</span>
                </div>

                <div class="text-center w-60">
                    <div class="h-14 flex items-center justify-center">
                        <span class="font-serif italic text-slate-400 text-sm border-b border-dashed border-slate-400 px-8 py-1">
                            Dr. {{ prescription.doctor?.user?.name }}
                        </span>
                    </div>
                    <span class="block font-bold text-slate-800 text-xs mt-1">Authorized Medical Practitioner</span>
                    <span class="block text-[10px] text-slate-400 font-mono">Digitally Signed & Validated</span>
                </div>
            </div>

            <!-- 7. Disclaimer Legal Footer -->
            <div class="pt-4 border-t border-slate-100 text-[10px] text-slate-400 text-center leading-normal">
                {{ prescription.disclaimer || 'Valid for 15 days from date of issue. Substitution with generic equivalent molecules permitted.' }}
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        background: #ffffff !important;
    }
}
</style>
