<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import {
    FileText,
    Printer,
    Pill,
    Calendar,
    User,
    Clock,
    AlertCircle,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';

defineProps({
    patient: Object,
    prescriptions: Object,
});

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <PatientPortalLayout>
        <Head title="My Prescriptions" />

        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                        <FileText class="w-6 h-6 text-teal-600" />
                        My Digital Prescriptions (Rx)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        View clinical prescriptions, medication dosages, advice, and print official clinic copies.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                        {{ prescriptions.total || 0 }} Total Prescriptions
                    </span>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!prescriptions.data || prescriptions.data.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mx-auto mb-3">
                    <Pill class="w-7 h-7" />
                </div>
                <h3 class="text-sm font-bold text-slate-800">No Prescriptions Issued Yet</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Once your consulting doctor completes a visit and issues a prescription, it will appear here for you to view or print.
                </p>
            </div>

            <!-- Prescriptions List -->
            <div v-else class="space-y-4">
                <div
                    v-for="rx in prescriptions.data"
                    :key="rx.id"
                    class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 hover:shadow-md transition"
                >
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                                <FileText class="w-6 h-6" />
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-teal-700">{{ rx.prescription_number }}</span>
                                    <span class="text-[11px] font-semibold text-slate-400">• {{ formatDate(rx.date) }}</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 mt-0.5">
                                    Dr. {{ rx.doctor?.user?.name || 'Assigned Physician' }}
                                </h3>
                                <p class="text-xs text-slate-500">
                                    {{ rx.doctor?.specialization || 'General Medicine' }}
                                    <span v-if="rx.doctor?.qualification" class="text-slate-400">({{ rx.doctor.qualification }})</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a
                                :href="route('prescriptions.print', rx.id)"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition"
                            >
                                <Printer class="w-4 h-4" />
                                Print Official Rx
                            </a>
                        </div>
                    </div>

                    <!-- Clinical Findings & Advice -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 py-4 text-xs">
                        <div v-if="rx.visit?.diagnosis" class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Diagnosis</span>
                            <span class="font-semibold text-slate-800">{{ rx.visit.diagnosis }}</span>
                        </div>
                        <div v-if="rx.visit?.chief_complaint" class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Chief Complaints</span>
                            <span class="font-semibold text-slate-800">{{ rx.visit.chief_complaint }}</span>
                        </div>
                    </div>

                    <!-- Medicines Table -->
                    <div class="mt-2">
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <Pill class="w-3.5 h-3.5 text-teal-600" />
                            Prescribed Medications ({{ rx.items?.length || 0 }})
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-100 bg-slate-50/50">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="bg-slate-100/80 text-[11px] font-bold text-slate-500 uppercase">
                                    <tr>
                                        <th class="py-2.5 px-4">#</th>
                                        <th class="py-2.5 px-4">Medicine Name</th>
                                        <th class="py-2.5 px-4">Dosage</th>
                                        <th class="py-2.5 px-4">Frequency</th>
                                        <th class="py-2.5 px-4">Duration</th>
                                        <th class="py-2.5 px-4">Instructions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="(item, idx) in rx.items" :key="item.id" class="hover:bg-slate-50/60">
                                        <td class="py-2.5 px-4 font-mono text-slate-400">{{ idx + 1 }}</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">{{ item.medicine_name }}</td>
                                        <td class="py-2.5 px-4 text-slate-600">{{ item.dosage || '—' }}</td>
                                        <td class="py-2.5 px-4 font-medium text-emerald-700">{{ item.frequency || '—' }}</td>
                                        <td class="py-2.5 px-4 text-slate-600">{{ item.duration || '—' }}</td>
                                        <td class="py-2.5 px-4 text-slate-500 italic">{{ item.instructions || 'As directed' }}</td>
                                    </tr>
                                    <tr v-if="!rx.items || rx.items.length === 0">
                                        <td colspan="6" class="py-3 px-4 text-center text-slate-400">
                                            No itemized medicines recorded for this prescription.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- General Advice / Notes -->
                    <div v-if="rx.advice" class="mt-4 p-3 rounded-2xl bg-amber-50/70 border border-amber-100 text-xs text-amber-900 flex items-start gap-2">
                        <AlertCircle class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                        <div>
                            <span class="font-bold">Doctor's Advice / Diet:</span> {{ rx.advice }}
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="prescriptions.links && prescriptions.links.length > 3" class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200/80">
                    <span class="text-xs text-slate-500">
                        Showing {{ prescriptions.from || 0 }} to {{ prescriptions.to || 0 }} of {{ prescriptions.total }} prescriptions
                    </span>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in prescriptions.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold transition',
                                link.active ? 'bg-teal-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url && 'opacity-40 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </PatientPortalLayout>
</template>
