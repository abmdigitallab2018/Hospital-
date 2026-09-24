<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, Clock, Save, Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    doctor: Object,
});

const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const initialSchedules = props.doctor.schedules?.length > 0
    ? props.doctor.schedules.map(s => ({
        day_of_week: s.day_of_week,
        start_time: s.start_time?.substring(0, 5),
        end_time: s.end_time?.substring(0, 5),
        slot_duration_minutes: s.slot_duration_minutes || 15,
        max_patients: s.max_patients || 20,
        is_active: Boolean(s.is_active),
    }))
    : [1, 2, 3, 4, 5].map(day => ({
        day_of_week: day,
        start_time: '09:00',
        end_time: '13:00',
        slot_duration_minutes: 15,
        max_patients: 16,
        is_active: true,
    }));

const form = useForm({
    schedules: initialSchedules,
});

const addShift = () => {
    form.schedules.push({
        day_of_week: 1,
        start_time: '17:00',
        end_time: '20:30',
        slot_duration_minutes: 15,
        max_patients: 14,
        is_active: true,
    });
};

const removeShift = (idx) => {
    form.schedules.splice(idx, 1);
};

const submit = () => {
    form.put(route('doctors.schedule.update', props.doctor.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="'Working Roster - Dr. ' + doctor.user?.name" />

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('doctors.index')"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-white transition shadow-xs border border-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">Doctor Working Roster & Time Slots</h1>
                        <p class="text-xs text-slate-500">
                            Dr. {{ doctor.user?.name }} ({{ doctor.specialization }} • Room: {{ doctor.room_number || 'OPD' }})
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="addShift"
                    class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                >
                    <Plus class="w-4 h-4 text-emerald-400" />
                    <span>Add Shift / Slot</span>
                </button>
            </div>

            <!-- Schedule Editor Form -->
            <form @submit.prevent="submit" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-4">
                <div class="space-y-3">
                    <div
                        v-for="(sched, idx) in form.schedules"
                        :key="idx"
                        class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs"
                    >
                        <div class="sm:col-span-3">
                            <label class="block font-bold text-slate-700 mb-1">Day of Week</label>
                            <select
                                v-model="sched.day_of_week"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option v-for="(day, dIdx) in dayNames" :key="dIdx" :value="dIdx">
                                    {{ day }}
                                </option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Start Time</label>
                            <input
                                type="time"
                                v-model="sched.start_time"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">End Time</label>
                            <input
                                type="time"
                                v-model="sched.end_time"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Slot Duration</label>
                            <select
                                v-model="sched.slot_duration_minutes"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option :value="10">10 mins</option>
                                <option :value="15">15 mins</option>
                                <option :value="20">20 mins</option>
                                <option :value="30">30 mins</option>
                                <option :value="45">45 mins</option>
                                <option :value="60">60 mins</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Max Cap</label>
                            <input
                                type="number"
                                v-model="sched.max_patients"
                                min="1"
                                class="w-full text-xs rounded-xl border border-slate-200 py-1.5 px-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div class="sm:col-span-1 text-right flex items-center justify-end pt-5">
                            <button
                                v-if="form.schedules.length > 1"
                                type="button"
                                @click="removeShift(idx)"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <Link
                        :href="route('doctors.index')"
                        class="px-4 py-2 font-bold text-xs text-slate-600 hover:bg-slate-100 rounded-xl"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2"
                    >
                        <Save class="w-4 h-4" />
                        <span>Save Working Schedule</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
