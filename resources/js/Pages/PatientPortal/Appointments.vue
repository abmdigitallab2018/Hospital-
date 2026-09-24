<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PatientPortalLayout from '@/Layouts/PatientPortalLayout.vue';
import { Calendar, Clock, Plus, X, Stethoscope } from 'lucide-vue-next';

const props = defineProps({
    patient: Object,
    appointments: Object,
    doctors: Array,
});

const bookModalOpen = ref(false);

const form = useForm({
    doctor_id: props.doctors[0]?.id || '',
    appointment_date: new Date().toISOString().substring(0, 10),
    start_time: '10:00',
    reason: '',
});

const submit = () => {
    form.post(route('patient.appointments.request'), {
        onSuccess: () => {
            bookModalOpen.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <PatientPortalLayout>
        <Head title="My Appointments" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">My Appointments</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        View upcoming clinic appointments or request a new consultation.
                    </p>
                </div>

                <button
                    type="button"
                    @click="bookModalOpen = true"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                >
                    <Plus class="w-4 h-4" />
                    <span>Book Appointment</span>
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-5">Token #</th>
                                <th class="py-3 px-5">Date & Time</th>
                                <th class="py-3 px-5">Doctor</th>
                                <th class="py-3 px-5">Reason</th>
                                <th class="py-3 px-5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="appointments.data.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                    You have no appointments booked.
                                </td>
                            </tr>
                            <tr v-for="apt in appointments.data" :key="apt.id" class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-5 font-black text-slate-900 font-mono">
                                    #{{ apt.token_number }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-700 font-semibold">
                                    {{ apt.appointment_date }} at {{ apt.start_time?.substring(0, 5) }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-800">Dr. {{ apt.doctor?.user?.name }}</div>
                                    <span class="text-[10px] text-slate-400">{{ apt.doctor?.specialization }}</span>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600">{{ apt.reason || 'General Checkup' }}</td>
                                <td class="py-3.5 px-5 text-right">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                            apt.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'
                                        ]"
                                    >
                                        {{ apt.status.replace('_', ' ') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Book Modal -->
        <div v-if="bookModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 text-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-slate-900">Request Clinic Appointment</h3>
                    <button type="button" @click="bookModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Select Doctor *</label>
                        <select
                            v-model="form.doctor_id"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:outline-none"
                        >
                            <option v-for="d in doctors" :key="d.id" :value="d.id">
                                Dr. {{ d.user?.name }} ({{ d.specialization }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Preferred Date *</label>
                            <input
                                type="date"
                                v-model="form.appointment_date"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Time Slot *</label>
                            <input
                                type="time"
                                v-model="form.start_time"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Reason for Visit</label>
                        <textarea
                            v-model="form.reason"
                            rows="2"
                            placeholder="Describe symptoms or routine checkup..."
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="bookModalOpen = false"
                            class="px-4 py-2 font-bold text-slate-500 hover:bg-slate-100 rounded-xl"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition"
                        >
                            Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PatientPortalLayout>
</template>
