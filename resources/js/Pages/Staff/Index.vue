<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    UserCheck,
    Plus,
    X,
    Trash2,
    Edit3,
    UserCircle,
    Shield
} from 'lucide-vue-next';

const props = defineProps({
    staff: Array,
});

const createModalOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: 'receptionist',
    password: 'password',
});

const submit = () => {
    form.post(route('staff.store'), {
        onSuccess: () => {
            createModalOpen.value = false;
            form.reset();
        },
    });
};

const deleteStaff = (s) => {
    if (confirm(`Remove staff member ${s.name}?`)) {
        router.delete(route('staff.destroy', s.id));
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Staff & Permissions" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Clinic Staff & Roles</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Manage receptionist desk operators, accountants, and clinic administrative officers.
                    </p>
                </div>

                <button
                    type="button"
                    @click="createModalOpen = true"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 self-start sm:self-auto"
                >
                    <Plus class="w-4 h-4" />
                    <span>Add Staff Member</span>
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-5">Staff Member</th>
                                <th class="py-3.5 px-5">Assigned Role</th>
                                <th class="py-3.5 px-5">Contact Details</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="s in staff" :key="s.id" class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-900">{{ s.name }}</div>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ s.email }}</span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                            s.role === 'clinic_admin' ? 'bg-purple-100 text-purple-800' : '',
                                            s.role === 'receptionist' ? 'bg-amber-100 text-amber-800' : '',
                                            s.role === 'accountant' ? 'bg-blue-100 text-blue-800' : '',
                                        ]"
                                    >
                                        {{ s.role.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium">
                                    {{ s.phone || 'No phone recorded' }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase', s.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400']">
                                        {{ s.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <button
                                        v-if="s.id !== $page.props.auth.user?.id"
                                        type="button"
                                        @click="deleteStaff(s)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                                        title="Remove Staff"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Staff Modal -->
        <div v-if="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-slate-900">Add Staff Account</h3>
                    <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Full Name *</label>
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            placeholder="e.g. Priya Sharma"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Email Address *</label>
                        <input
                            type="email"
                            v-model="form.email"
                            required
                            placeholder="staff@clinic.com"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Assigned Role *</label>
                        <select
                            v-model="form.role"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="receptionist">Receptionist (Desk, Queue, Appointments)</option>
                            <option value="accountant">Accountant (Invoices, Receipts, Expenses)</option>
                            <option value="clinic_admin">Clinic Administrator (Full Access)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
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
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
