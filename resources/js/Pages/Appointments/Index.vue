<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Calendar,
    Clock,
    Plus,
    Search,
    User,
    CheckCircle2,
    Play,
    Stethoscope,
    X,
    Filter,
    ArrowRight,
    AlertCircle,
    RotateCcw
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    appointments: Object,
    doctors: Array,
    patients: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const doctorId = ref(props.filters.doctor_id || 'all');
const status = ref(props.filters.status || 'all');
const date = ref(props.filters.date || new Date().toISOString().substring(0, 10));

const bookModalOpen = ref(false);
const rescheduleModalOpen = ref(false);
const selectedAptForReschedule = ref(null);

// Booking Form
const bookForm = useForm({
    patient_id: '',
    doctor_id: '',
    appointment_date: new Date().toISOString().substring(0, 10),
    start_time: '',
    end_time: '',
    type: 'in_person',
    reason: '',
});

// Available slots state
const availableSlots = ref([]);
const loadingSlots = ref(false);
const slotsMessage = ref('');

const fetchSlots = async () => {
    if (!bookForm.doctor_id || !bookForm.appointment_date) {
        availableSlots.value = [];
        return;
    }
    loadingSlots.value = true;
    slotsMessage.value = '';
    try {
        const res = await axios.get(route('appointments.availableSlots'), {
            params: {
                doctor_id: bookForm.doctor_id,
                date: bookForm.appointment_date,
            },
        });
        availableSlots.value = res.data.slots || [];
        if (availableSlots.value.length === 0) {
            slotsMessage.value = res.data.message || 'No available slots for this doctor on this day.';
        }
    } catch (e) {
        slotsMessage.value = 'Failed to load doctor slots.';
    } finally {
        loadingSlots.value = false;
    }
};

watch([() => bookForm.doctor_id, () => bookForm.appointment_date], () => {
    bookForm.start_time = '';
    fetchSlots();
});

const selectSlot = (slot) => {
    if (slot.is_booked) return;
    bookForm.start_time = slot.start_time;
    bookForm.end_time = slot.end_time;
};

const submitBooking = () => {
    bookForm.post(route('appointments.store'), {
        onSuccess: () => {
            bookModalOpen.value = false;
            bookForm.reset();
        },
    });
};

// Reschedule Form
const rescheduleForm = useForm({
    appointment_date: '',
    start_time: '',
    end_time: '',
});

const openRescheduleModal = (apt) => {
    selectedAptForReschedule.value = apt;
    rescheduleForm.appointment_date = apt.appointment_date;
    rescheduleForm.start_time = apt.start_time?.substring(0, 5);
    rescheduleModalOpen.value = true;
};

const submitReschedule = () => {
    if (!selectedAptForReschedule.value) return;
    rescheduleForm.put(route('appointments.reschedule', selectedAptForReschedule.value.id), {
        onSuccess: () => {
            rescheduleModalOpen.value = false;
        },
    });
};

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('appointments.index'),
        {
            search: search.value || undefined,
            doctor_id: doctorId.value !== 'all' ? doctorId.value : undefined,
            status: status.value !== 'all' ? status.value : undefined,
            date: date.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

const checkIn = (aptId) => {
    router.post(route('appointments.checkIn', aptId));
};

const updateStatus = (aptId, newStatus) => {
    let reason = null;
    if (newStatus === 'cancelled') {
        reason = prompt('Enter cancellation reason (optional):');
    }
    router.put(route('appointments.updateStatus', aptId), {
        status: newStatus,
        cancellation_reason: reason,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Appointment Management" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Appointments & Roster</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Time-slot management, token numbers, and double-booking conflict prevention.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link
                        :href="route('queue.index')"
                        class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                    >
                        <Clock class="w-3.5 h-3.5 text-slate-950" />
                        <span>Waiting Queue</span>
                    </Link>

                    <button
                        type="button"
                        @click="bookModalOpen = true"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Book Appointment</span>
                    </button>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <!-- Date Picker -->
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                    <Calendar class="w-4 h-4 text-emerald-600" />
                    <input
                        type="date"
                        v-model="date"
                        @change="applyFilters"
                        class="py-2 px-3 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>

                <!-- Doctor Filter -->
                <select
                    v-model="doctorId"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Doctors</option>
                    <option v-for="d in doctors" :key="d.id" :value="d.id">
                        Dr. {{ d.user?.name }} ({{ d.specialization }})
                    </option>
                </select>

                <!-- Status Filter -->
                <select
                    v-model="status"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Statuses</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="checked_in">Checked In (Queue)</option>
                    <option value="in_consultation">In Consultation</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="no_show">No Show</option>
                </select>

                <!-- Search -->
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search patient name, UID..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-4">Token & Num</th>
                                <th class="py-3.5 px-4">Patient</th>
                                <th class="py-3.5 px-4">Doctor & Room</th>
                                <th class="py-3.5 px-4">Time Slot</th>
                                <th class="py-3.5 px-4">Type</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Queue Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="appointments.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                    No appointments scheduled for this date and filters.
                                </td>
                            </tr>
                            <tr
                                v-for="apt in appointments.data"
                                :key="apt.id"
                                class="hover:bg-slate-50/70 transition"
                            >
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center font-black text-xs">
                                            #{{ apt.token_number }}
                                        </span>
                                        <span class="text-[11px] text-slate-500">{{ apt.appointment_number }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <Link :href="route('patients.show', apt.patient?.id)" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                        {{ apt.patient?.full_name }}
                                    </Link>
                                    <span class="text-[10px] text-slate-400">{{ apt.patient?.phone }} ({{ apt.patient?.patient_uid }})</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800">{{ apt.doctor?.user?.name }}</div>
                                    <span class="text-[10px] text-emerald-600 font-bold">{{ apt.doctor?.specialization }} • Room {{ apt.doctor?.room_number || 'OPD' }}</span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-900">{{ apt.start_time?.substring(0, 5) }}</span>
                                    <span class="text-slate-400 text-[11px]"> - {{ apt.end_time?.substring(0, 5) }}</span>
                                </td>
                                <td class="py-3.5 px-4 capitalize font-medium text-slate-600">
                                    {{ apt.type.replace('_', ' ') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                            apt.status === 'scheduled' ? 'bg-slate-100 text-slate-700' : '',
                                            apt.status === 'checked_in' ? 'bg-amber-100 text-amber-800' : '',
                                            apt.status === 'in_consultation' ? 'bg-indigo-100 text-indigo-800' : '',
                                            apt.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '',
                                            apt.status === 'cancelled' ? 'bg-red-100 text-red-800' : '',
                                            apt.status === 'no_show' ? 'bg-zinc-100 text-zinc-700' : '',
                                        ]"
                                    >
                                        {{ apt.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Check in button -->
                                        <button
                                            v-if="apt.status === 'scheduled'"
                                            type="button"
                                            @click="checkIn(apt.id)"
                                            class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg text-[11px] transition shadow-xs"
                                        >
                                            Check In
                                        </button>

                                        <!-- Call to consultation -->
                                        <button
                                            v-if="apt.status === 'checked_in'"
                                            type="button"
                                            @click="updateStatus(apt.id, 'in_consultation')"
                                            class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-[11px] transition flex items-center gap-1 shadow-xs"
                                        >
                                            <Play class="w-3 h-3" />
                                            <span>Call In</span>
                                        </button>

                                        <!-- Start Consultation -->
                                        <Link
                                            v-if="apt.status === 'in_consultation' || apt.status === 'checked_in'"
                                            :href="route('consultations.create', { appointment_id: apt.id })"
                                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[11px] transition flex items-center gap-1 shadow-xs"
                                        >
                                            <Stethoscope class="w-3 h-3" />
                                            <span>Consult</span>
                                        </Link>

                                        <!-- Reschedule button -->
                                        <button
                                            v-if="apt.status === 'scheduled'"
                                            type="button"
                                            @click="openRescheduleModal(apt)"
                                            class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                            title="Reschedule Appointment"
                                        >
                                            <RotateCcw class="w-3.5 h-3.5" />
                                        </button>

                                        <!-- Cancel button -->
                                        <button
                                            v-if="apt.status === 'scheduled' || apt.status === 'checked_in'"
                                            type="button"
                                            @click="updateStatus(apt.id, 'cancelled')"
                                            class="p-1 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Cancel Appointment"
                                        >
                                            <X class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="appointments.links && appointments.links.length > 3" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Showing <strong>{{ appointments.from || 0 }}</strong> to <strong>{{ appointments.to || 0 }}</strong> of <strong>{{ appointments.total }}</strong>
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in appointments.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-2.5 py-1 rounded-lg text-xs font-semibold transition',
                                link.active ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Book Appointment Modal with Interactive Slot Picker -->
        <div v-if="bookModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-slate-900">Book New Patient Appointment</h3>
                    <button type="button" @click="bookModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitBooking" class="space-y-4 text-xs">
                    <!-- Patient Select -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Select Patient *</label>
                        <select
                            v-model="bookForm.patient_id"
                            required
                            class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="" disabled>Select patient from registry...</option>
                            <option v-for="p in patients" :key="p.id" :value="p.id">
                                {{ p.first_name }} {{ p.last_name }} ({{ p.patient_uid }} • {{ p.phone }})
                            </option>
                        </select>
                        <p v-if="bookForm.errors.patient_id" class="text-red-600 text-[11px] mt-1">{{ bookForm.errors.patient_id }}</p>
                    </div>

                    <!-- Doctor Select -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Consulting Doctor *</label>
                        <select
                            v-model="bookForm.doctor_id"
                            required
                            class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="" disabled>Select doctor...</option>
                            <option v-for="d in doctors" :key="d.id" :value="d.id">
                                Dr. {{ d.user?.name }} ({{ d.specialization }} • Fee: ₹{{ d.consultation_fee }})
                            </option>
                        </select>
                    </div>

                    <!-- Date & Type -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Appointment Date *</label>
                            <input
                                type="date"
                                v-model="bookForm.appointment_date"
                                required
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Visit Type</label>
                            <select
                                v-model="bookForm.type"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="in_person">In Person Consultation</option>
                                <option value="follow_up">Follow Up Review</option>
                                <option value="emergency">Emergency Walk-in</option>
                            </select>
                        </div>
                    </div>

                    <!-- Interactive Slot Picker (Prevents Double Booking) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-bold text-slate-700">Available Time Slot *</label>
                            <span v-if="loadingSlots" class="text-[11px] text-emerald-600 font-semibold animate-pulse">Calculating slots...</span>
                        </div>

                        <div v-if="slotsMessage" class="p-3 bg-amber-50 rounded-xl text-amber-800 text-xs">
                            {{ slotsMessage }}
                        </div>

                        <div v-else-if="availableSlots.length > 0" class="grid grid-cols-4 sm:grid-cols-5 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-50 rounded-2xl border border-slate-100">
                            <button
                                v-for="(slot, idx) in availableSlots"
                                :key="idx"
                                type="button"
                                @click="selectSlot(slot)"
                                :disabled="slot.is_booked"
                                :class="[
                                    'py-1.5 px-2 rounded-xl text-xs font-bold transition text-center',
                                    slot.is_booked
                                        ? 'bg-slate-200 text-slate-400 cursor-not-allowed line-through'
                                        : bookForm.start_time === slot.start_time
                                            ? 'bg-emerald-600 text-white shadow-xs'
                                            : 'bg-white text-slate-700 border border-slate-200 hover:border-emerald-500'
                                ]"
                            >
                                {{ slot.display }}
                            </button>
                        </div>
                        <p v-if="!bookForm.doctor_id" class="text-slate-400 text-[11px]">Select a doctor and date to load available working time slots.</p>
                        <p v-if="bookForm.errors.start_time" class="text-red-600 text-[11px] mt-1">{{ bookForm.errors.start_time }}</p>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chief Reason for Appointment</label>
                        <textarea
                            v-model="bookForm.reason"
                            rows="2"
                            placeholder="e.g. Mild chest heaviness, routine checkup, fever..."
                            class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="bookModalOpen = false"
                            class="px-4 py-2 text-slate-500 hover:bg-slate-100 rounded-xl font-bold"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="bookForm.processing || !bookForm.start_time"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 disabled:opacity-50 transition"
                        >
                            Confirm & Generate Token
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reschedule Modal -->
        <div v-if="rescheduleModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-slate-900">Reschedule Appointment</h3>
                    <button type="button" @click="rescheduleModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitReschedule" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">New Date *</label>
                        <input
                            type="date"
                            v-model="rescheduleForm.appointment_date"
                            required
                            class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">New Start Time (24h HH:mm) *</label>
                        <input
                            type="time"
                            v-model="rescheduleForm.start_time"
                            required
                            class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="rescheduleModalOpen = false"
                            class="px-4 py-2 text-slate-500 hover:bg-slate-100 rounded-xl font-bold"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="rescheduleForm.processing"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition"
                        >
                            Save New Time
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
