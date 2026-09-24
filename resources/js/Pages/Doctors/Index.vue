<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    UserCheck,
    Plus,
    Calendar,
    Edit3,
    Clock,
    X,
    Stethoscope
} from 'lucide-vue-next';

const props = defineProps({
    doctors: Array,
});

const createModalOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: 'password',
    specialization: 'General Physician',
    qualification: 'MBBS, MD',
    license_number: '',
    experience_years: 5,
    consultation_fee: 500,
    room_number: 'OPD-101',
    bio: '',
});

const submit = () => {
    form.post(route('doctors.store'), {
        onSuccess: () => {
            createModalOpen.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Doctors & Specialist Profiles" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Doctors & Specialists</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Manage medical credentials, consultation fees, OPD room numbers, and working rosters.
                    </p>
                </div>

                <button
                    type="button"
                    @click="createModalOpen = true"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Onboard Doctor</span>
                </button>
            </div>

            <!-- Doctor Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="doc in doctors"
                    :key="doc.id"
                    class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-start justify-between">
                            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 font-black text-lg flex items-center justify-center shadow-xs">
                                {{ doc.user?.name ? doc.user.name.split(' ').map(n=>n[0]).join('') : 'DR' }}
                            </div>
                            <span
                                :class="[
                                    'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    doc.is_available ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
                                ]"
                            >
                                {{ doc.is_available ? 'Available' : 'On Leave' }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <h3 class="text-base font-bold text-slate-900 leading-tight">{{ doc.user?.name }}</h3>
                            <p class="text-xs font-semibold text-emerald-700 mt-0.5">{{ doc.specialization }}</p>
                            <p class="text-[11px] text-slate-500">{{ doc.qualification }}</p>
                            <p class="text-[10px] text-slate-400 font-mono mt-1">Lic: {{ doc.license_number }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span class="block text-[10px] text-slate-400 uppercase font-bold">Consult Fee</span>
                                <span class="text-sm font-black text-slate-900">₹{{ Number(doc.consultation_fee).toFixed(0) }}</span>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span class="block text-[10px] text-slate-400 uppercase font-bold">Room No.</span>
                                <span class="text-sm font-black text-slate-900">{{ doc.room_number || 'OPD' }}</span>
                            </div>
                        </div>

                        <p v-if="doc.bio" class="mt-3 text-[11px] text-slate-500 line-clamp-2">
                            {{ doc.bio }}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <Link
                            :href="route('doctors.schedule', doc.id)"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition flex items-center gap-1.5"
                        >
                            <Clock class="w-3.5 h-3.5 text-slate-600" />
                            <span>Working Roster</span>
                        </Link>

                        <span class="text-[11px] text-slate-400 font-medium">
                            {{ doc.experience_years }} yrs exp
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onboard Doctor Modal -->
        <div v-if="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-slate-900">Onboard New Doctor</h3>
                    <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Doctor Full Name *</label>
                            <input
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="e.g. Dr. Siddharth Verma"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email (For Login) *</label>
                            <input
                                type="email"
                                v-model="form.email"
                                required
                                placeholder="doctor@clinic.com"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Phone</label>
                            <input
                                type="text"
                                v-model="form.phone"
                                placeholder="+91 98765 00000"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Login Password *</label>
                            <input
                                type="password"
                                v-model="form.password"
                                required
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Specialization *</label>
                            <input
                                type="text"
                                v-model="form.specialization"
                                required
                                placeholder="e.g. Cardiologist, Dermatologist"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Qualification *</label>
                            <input
                                type="text"
                                v-model="form.qualification"
                                required
                                placeholder="e.g. MBBS, MD, DNB"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">MCI / State Medical License # *</label>
                            <input
                                type="text"
                                v-model="form.license_number"
                                required
                                placeholder="e.g. MMC-2018-9921"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Consultation Fee (₹) *</label>
                            <input
                                type="number"
                                v-model="form.consultation_fee"
                                required
                                min="0"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Room / Cabin Number</label>
                            <input
                                type="text"
                                v-model="form.room_number"
                                placeholder="e.g. OPD-105"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Years of Experience</label>
                            <input
                                type="number"
                                v-model="form.experience_years"
                                min="0"
                                class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Professional Bio</label>
                        <textarea
                            v-model="form.bio"
                            rows="2"
                            placeholder="Summary of experience, publications, or clinical focus..."
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="createModalOpen = false"
                            class="px-4 py-2 font-bold text-slate-500 hover:bg-slate-100 rounded-xl"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition"
                        >
                            Register Doctor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
