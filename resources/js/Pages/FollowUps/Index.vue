<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    CalendarClock,
    Search,
    Send,
    CheckCircle2,
    Clock,
    User,
    Check,
    X
} from 'lucide-vue-next';

const props = defineProps({
    followUps: Object,
    doctors: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const doctorId = ref(props.filters.doctor_id || 'all');
const date = ref(props.filters.date || '');

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route('follow-ups.index'),
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
            doctor_id: doctorId.value !== 'all' ? doctorId.value : undefined,
            date: date.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

const updateStatus = (fuId, newStatus) => {
    router.put(route('follow-ups.updateStatus', fuId), {
        status: newStatus,
    });
};

const sendReminder = (fuId, channel = 'whatsapp') => {
    router.post(route('follow-ups.remind', fuId), { channel });
};
</script>

<template>
    <AppLayout>
        <Head title="Follow-Ups & Reminders" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Follow-Ups & Care Tracking</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Track upcoming medical reviews, send WhatsApp/SMS reminders, and reduce missed visits.
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search patient, phone, notes..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    />
                </div>

                <select
                    v-model="status"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending Review</option>
                    <option value="completed">Completed</option>
                    <option value="missed">Missed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <select
                    v-model="doctorId"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                    <option value="all">All Doctors</option>
                    <option v-for="d in doctors" :key="d.id" :value="d.id">
                        Dr. {{ d.user?.name }}
                    </option>
                </select>

                <input
                    type="date"
                    v-model="date"
                    @change="applyFilters"
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                />
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Due Date</th>
                                <th class="py-3.5 px-5">Patient</th>
                                <th class="py-3.5 px-5">Doctor</th>
                                <th class="py-3.5 px-5">Instructions / Reason</th>
                                <th class="py-3.5 px-5">Reminder Status</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="followUps.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                    No follow-ups found matching your search.
                                </td>
                            </tr>
                            <tr
                                v-for="fu in followUps.data"
                                :key="fu.id"
                                class="hover:bg-slate-50/70 transition"
                            >
                                <td class="py-3.5 px-5 font-bold text-slate-900 whitespace-nowrap">
                                    {{ fu.follow_up_date }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <Link :href="route('patients.show', fu.patient?.id)" class="font-bold text-slate-900 hover:text-emerald-600 block">
                                        {{ fu.patient?.full_name }}
                                    </Link>
                                    <span class="text-[10px] text-slate-400">{{ fu.patient?.phone }}</span>
                                </td>
                                <td class="py-3.5 px-5 font-semibold text-slate-800">
                                    Dr. {{ fu.doctor?.user?.name }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 max-w-xs">
                                    {{ fu.notes || 'Routine follow-up' }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span v-if="fu.reminded_at" class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1 w-max">
                                        <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                                        Sent {{ new Date(fu.reminded_at).toLocaleDateString() }}
                                    </span>
                                    <span v-else class="text-[10px] font-semibold text-slate-400">
                                        Not sent yet
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                            fu.status === 'pending' ? 'bg-amber-100 text-amber-800' : '',
                                            fu.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '',
                                            fu.status === 'missed' ? 'bg-red-100 text-red-800' : '',
                                            fu.status === 'cancelled' ? 'bg-slate-100 text-slate-600' : ''
                                        ]"
                                    >
                                        {{ fu.status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Send WhatsApp Reminder -->
                                        <button
                                            v-if="fu.status === 'pending'"
                                            type="button"
                                            @click="sendReminder(fu.id, 'whatsapp')"
                                            class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg text-xs transition flex items-center gap-1"
                                            title="Send WhatsApp Reminder"
                                        >
                                            <Send class="w-3 h-3 text-emerald-600" />
                                            <span>WhatsApp</span>
                                        </button>

                                        <!-- Mark Complete -->
                                        <button
                                            v-if="fu.status === 'pending'"
                                            type="button"
                                            @click="updateStatus(fu.id, 'completed')"
                                            class="p-1 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                            title="Mark as Completed"
                                        >
                                            <Check class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="followUps.links && followUps.links.length > 3" class="px-5 py-3 border-t border-slate-100 flex justify-center text-xs">
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in followUps.links"
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
    </AppLayout>
</template>
