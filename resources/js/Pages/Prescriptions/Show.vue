<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, Printer, FileText, Calendar, User, Stethoscope } from 'lucide-vue-next';

defineProps({
    prescription: Object,
});
</script>

<template>
    <AppLayout>
        <Head :title="'Prescription - ' + prescription.prescription_number" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('prescriptions.index')"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">Prescription #{{ prescription.prescription_number }}</h1>
                        <p class="text-xs text-slate-500">Date: {{ prescription.date }} • Dr. {{ prescription.doctor?.user?.name }}</p>
                    </div>
                </div>

                <Link
                    :href="route('prescriptions.print', prescription.id)"
                    target="_blank"
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                >
                    <Printer class="w-4 h-4 text-emerald-400" />
                    <span>Print Letterhead</span>
                </Link>
            </div>

            <!-- Patient Info Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 flex items-center justify-between">
                <div>
                    <span class="block text-slate-400 text-[10px] font-bold uppercase">Patient</span>
                    <Link :href="route('patients.show', prescription.patient?.id)" class="text-base font-bold text-slate-900 hover:text-emerald-600">
                        {{ prescription.patient?.full_name }}
                    </Link>
                    <span class="text-xs text-slate-500 ml-2">({{ prescription.patient?.patient_uid }})</span>
                </div>

                <div class="text-right">
                    <span class="block text-slate-400 text-[10px] font-bold uppercase">Consulting Specialist</span>
                    <span class="text-xs font-bold text-slate-800">Dr. {{ prescription.doctor?.user?.name }}</span>
                    <span class="block text-[11px] text-slate-500">{{ prescription.doctor?.specialization }}</span>
                </div>
            </div>

            <!-- Medicines Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    Prescribed Medications
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Medicine</th>
                                <th class="py-2.5 px-3">Dosage</th>
                                <th class="py-2.5 px-3">Frequency</th>
                                <th class="py-2.5 px-3">Duration</th>
                                <th class="py-2.5 px-3">Instructions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in prescription.items" :key="item.id">
                                <td class="py-3 px-3 text-slate-400">{{ idx + 1 }}</td>
                                <td class="py-3 px-3 font-bold text-slate-900">{{ item.medicine_name }}</td>
                                <td class="py-3 px-3 text-slate-700">{{ item.dosage }}</td>
                                <td class="py-3 px-3 font-semibold text-emerald-700">{{ item.frequency }}</td>
                                <td class="py-3 px-3 text-slate-700">{{ item.duration }}</td>
                                <td class="py-3 px-3 text-slate-500">{{ item.instructions || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="prescription.notes" class="pt-4 border-t border-slate-100 text-xs">
                    <strong class="text-slate-700">Special Instructions:</strong>
                    <p class="text-slate-600 mt-1">{{ prescription.notes }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
