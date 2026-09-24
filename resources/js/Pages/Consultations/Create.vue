<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeft,
    Stethoscope,
    HeartPulse,
    Activity,
    FileText,
    Plus,
    Trash2,
    Save,
    Calendar,
    Sparkles,
    AlertTriangle,
    Receipt
} from 'lucide-vue-next';

const props = defineProps({
    selectedPatient: Object,
    selectedAppointment: Object,
    patients: Array,
    doctors: Array,
    templates: Array,
    services: Array,
    currentDoctorId: Number,
});

const patient = ref(props.selectedPatient || null);
const selectedTemplate = ref('');

const form = useForm({
    patient_id: props.selectedPatient?.id || props.selectedAppointment?.patient_id || '',
    appointment_id: props.selectedAppointment?.id || '',
    doctor_id: props.selectedAppointment?.doctor_id || props.currentDoctorId || (props.doctors[0]?.id || ''),
    visit_date: new Date().toISOString().substring(0, 10),

    // Clinical Notes
    chief_complaints: props.selectedAppointment?.reason || '',
    symptoms: '',
    examination_notes: '',
    diagnosis: '',
    treatment_plan: '',
    clinical_advice: '',
    follow_up_date: '',
    follow_up_instructions: '',

    // Vitals
    vitals: {
        temperature: '',
        blood_pressure_systolic: '',
        blood_pressure_diastolic: '',
        pulse_rate: '',
        respiratory_rate: '',
        oxygen_saturation: '',
        weight_kg: '',
        height_cm: '',
    },

    // Prescriptions Array
    prescriptions: [
        {
            medicine_name: '',
            dosage: '1 Tablet',
            frequency: '1-0-1',
            duration: '5 Days',
            instructions: 'After meals',
        }
    ],

    // Auto-generate invoice
    generate_invoice: true,
});

// Live BMI Calculation
const calculatedBmi = computed(() => {
    const w = parseFloat(form.vitals.weight_kg);
    const h = parseFloat(form.vitals.height_cm);
    if (w > 0 && h > 0) {
        const hM = h / 100;
        return (w / (hM * hM)).toFixed(1);
    }
    return null;
});

const bmiCategory = computed(() => {
    const bmi = parseFloat(calculatedBmi.value);
    if (!bmi) return null;
    if (bmi < 18.5) return { label: 'Underweight', color: 'bg-blue-100 text-blue-800' };
    if (bmi < 25) return { label: 'Normal Weight', color: 'bg-emerald-100 text-emerald-800' };
    if (bmi < 30) return { label: 'Overweight', color: 'bg-amber-100 text-amber-800' };
    return { label: 'Obese', color: 'bg-red-100 text-red-800' };
});

// Add / Remove Medicine Rows
const addMedicineRow = () => {
    form.prescriptions.push({
        medicine_name: '',
        dosage: '1 Tablet',
        frequency: '1-0-1',
        duration: '5 Days',
        instructions: 'After meals',
    });
};

const removeMedicineRow = (index) => {
    form.prescriptions.splice(index, 1);
};

// Apply Prescription Template
const applyTemplate = () => {
    if (!selectedTemplate.value) return;
    const t = props.templates.find(item => item.id == selectedTemplate.value);
    if (t && Array.isArray(t.items)) {
        form.prescriptions = JSON.parse(JSON.stringify(t.items));
    }
};

const onPatientChange = () => {
    patient.value = props.patients.find(p => p.id == form.patient_id) || null;
};

const submit = () => {
    form.post(route('consultations.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="Conduct Clinical Consultation" />

        <div class="max-w-5xl mx-auto space-y-6">
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
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">Record Medical Consultation</h1>
                        <p class="text-xs text-slate-500">Capture vital signs, symptoms, clinical diagnosis, Rx, and advice.</p>
                    </div>
                </div>
            </div>

            <!-- Patient Information Banner -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5">
                <div v-if="patient" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 font-black text-base flex items-center justify-center">
                            {{ patient.first_name[0] }}{{ patient.last_name[0] }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 text-base">{{ patient.full_name }}</h3>
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                                    {{ patient.patient_uid }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-black text-[10px]">
                                    {{ patient.blood_group }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ patient.gender }} <span v-if="patient.age">({{ patient.age }} yrs)</span> • Phone: {{ patient.phone }}
                            </p>
                        </div>
                    </div>

                    <div v-if="patient.allergies && patient.allergies !== 'None'" class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                        <AlertTriangle class="w-4 h-4 text-amber-600 flex-shrink-0" />
                        <div>
                            <strong>Allergies:</strong> {{ patient.allergies }}
                        </div>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Patient *</label>
                        <select
                            v-model="form.patient_id"
                            @change="onPatientChange"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option value="" disabled>Select patient...</option>
                            <option v-for="p in patients" :key="p.id" :value="p.id">
                                {{ p.first_name }} {{ p.last_name }} ({{ p.patient_uid }} • {{ p.phone }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Attending Doctor *</label>
                        <select
                            v-model="form.doctor_id"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option v-for="d in doctors" :key="d.id" :value="d.id">
                                Dr. {{ d.user?.name }} ({{ d.specialization }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Section 1: Vital Signs & Measurements -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider">
                            <Activity class="w-4 h-4 text-emerald-600" />
                            <span>Vital Signs & Anthropometry</span>
                        </div>

                        <div v-if="calculatedBmi" class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-500">BMI: <strong class="text-slate-800">{{ calculatedBmi }}</strong></span>
                            <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase', bmiCategory?.color]">
                                {{ bmiCategory?.label }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block font-medium text-slate-600 mb-1">Temperature (°F)</label>
                            <input
                                type="number"
                                step="0.1"
                                v-model="form.vitals.temperature"
                                placeholder="98.6"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-600 mb-1">BP (Systolic / Diastolic)</label>
                            <div class="flex items-center gap-1">
                                <input
                                    type="number"
                                    v-model="form.vitals.blood_pressure_systolic"
                                    placeholder="120"
                                    class="w-1/2 rounded-xl border border-slate-200 py-2 px-2 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none text-center"
                                />
                                <span class="text-slate-400 font-bold">/</span>
                                <input
                                    type="number"
                                    v-model="form.vitals.blood_pressure_diastolic"
                                    placeholder="80"
                                    class="w-1/2 rounded-xl border border-slate-200 py-2 px-2 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none text-center"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-600 mb-1">Pulse Rate (bpm)</label>
                            <input
                                type="number"
                                v-model="form.vitals.pulse_rate"
                                placeholder="72"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-600 mb-1">SpO2 Oxygen (%)</label>
                            <input
                                type="number"
                                v-model="form.vitals.oxygen_saturation"
                                placeholder="99"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-600 mb-1">Respiratory Rate (/min)</label>
                            <input
                                type="number"
                                v-model="form.vitals.respiratory_rate"
                                placeholder="18"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-600 mb-1">Weight (kg)</label>
                            <input
                                type="number"
                                step="0.1"
                                v-model="form.vitals.weight_kg"
                                placeholder="70.5"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-600 mb-1">Height (cm)</label>
                            <input
                                type="number"
                                step="0.5"
                                v-model="form.vitals.height_cm"
                                placeholder="172"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Clinical Examination & Diagnosis -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">
                        <Stethoscope class="w-4 h-4 text-emerald-600" />
                        <span>Examination, Symptoms & Diagnosis</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Chief Complaints *</label>
                            <textarea
                                v-model="form.chief_complaints"
                                required
                                rows="2"
                                placeholder="e.g. Fever for 3 days, non-productive cough, body ache..."
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                            <p v-if="form.errors.chief_complaints" class="text-red-600 text-[11px] mt-1">{{ form.errors.chief_complaints }}</p>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Associated Symptoms & History</label>
                            <textarea
                                v-model="form.symptoms"
                                rows="2"
                                placeholder="e.g. Nausea, headache, chills, decreased appetite..."
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Physical Examination Findings</label>
                            <textarea
                                v-model="form.examination_notes"
                                rows="2"
                                placeholder="e.g. Throat congested, chest clear, soft abdomen, no organomegaly..."
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Clinical Diagnosis *</label>
                            <input
                                type="text"
                                v-model="form.diagnosis"
                                placeholder="e.g. Acute Viral Pharyngitis / Essential Hypertension Stage 1"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-emerald-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Treatment Plan & Directives</label>
                            <textarea
                                v-model="form.treatment_plan"
                                rows="2"
                                placeholder="e.g. Symptomatic antipyretic therapy, hydration, 5 days antibiotic course..."
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Clinical Advice & Diet</label>
                            <textarea
                                v-model="form.clinical_advice"
                                rows="2"
                                placeholder="e.g. Drink 3L warm fluids daily, salt-restricted diet, complete rest..."
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Digital Prescription Builder -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider">
                            <FileText class="w-4 h-4 text-emerald-600" />
                            <span>Prescribed Medicines (Rx)</span>
                        </div>

                        <!-- Template Quick Inject -->
                        <div v-if="templates.length > 0" class="flex items-center gap-2">
                            <span class="text-xs text-slate-400 font-semibold">Load Template:</span>
                            <select
                                v-model="selectedTemplate"
                                @change="applyTemplate"
                                class="py-1 px-2.5 text-xs rounded-xl border border-slate-200 text-slate-700 font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="">Choose standard protocol...</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">
                                    {{ t.title }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(med, idx) in form.prescriptions"
                            :key="idx"
                            class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs"
                        >
                            <div class="sm:col-span-4">
                                <label class="block font-bold text-slate-700 mb-1">Medicine Name & Formulation *</label>
                                <input
                                    type="text"
                                    v-model="med.medicine_name"
                                    placeholder="e.g. Paracetamol 650mg (Dolo)"
                                    class="w-full rounded-xl border border-slate-200 py-1.5 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none font-semibold"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Dosage</label>
                                <input
                                    type="text"
                                    v-model="med.dosage"
                                    placeholder="1 Tab / 5ml"
                                    class="w-full rounded-xl border border-slate-200 py-1.5 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Frequency</label>
                                <input
                                    type="text"
                                    v-model="med.frequency"
                                    placeholder="1-0-1 / TID"
                                    class="w-full rounded-xl border border-slate-200 py-1.5 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Duration</label>
                                <input
                                    type="text"
                                    v-model="med.duration"
                                    placeholder="5 Days / 1 Mo"
                                    class="w-full rounded-xl border border-slate-200 py-1.5 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                />
                            </div>

                            <div class="sm:col-span-2 flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block font-bold text-slate-700 mb-1">Instructions</label>
                                    <input
                                        type="text"
                                        v-model="med.instructions"
                                        placeholder="After food"
                                        class="w-full rounded-xl border border-slate-200 py-1.5 px-2 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                    />
                                </div>
                                <button
                                    v-if="form.prescriptions.length > 1"
                                    type="button"
                                    @click="removeMedicineRow(idx)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 mt-5 transition"
                                    title="Remove medicine"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="addMedicineRow"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5"
                        >
                            <Plus class="w-3.5 h-3.5 text-emerald-600" />
                            <span>Add Another Medicine</span>
                        </button>
                    </div>
                </div>

                <!-- Section 4: Follow-up & Auto-Billing Options -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Follow-Up -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-3 text-xs">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                            <Calendar class="w-4 h-4 text-emerald-600" />
                            <span>Scheduled Follow-Up</span>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Follow-Up Date</label>
                            <input
                                type="date"
                                v-model="form.follow_up_date"
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Follow-Up Instructions / Tests</label>
                            <input
                                type="text"
                                v-model="form.follow_up_instructions"
                                placeholder="e.g. Repeat fasting blood sugar test before visit..."
                                class="w-full rounded-xl border border-slate-200 py-2 px-3 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Auto Billing Checkbox -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4 text-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                                <Receipt class="w-4 h-4 text-emerald-600" />
                                <span>Automatic Invoicing</span>
                            </div>

                            <label class="mt-3 flex items-start gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.generate_invoice"
                                    class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 mt-0.5"
                                />
                                <div>
                                    <span class="font-bold text-slate-800">Generate Consultation Invoice</span>
                                    <p class="text-slate-500 text-[11px] mt-0.5">
                                        Automatically generates an invoice for the doctor's consultation fee so reception / accounts can collect payment.
                                    </p>
                                </div>
                            </label>
                        </div>

                        <div class="pt-2 text-[11px] text-slate-400">
                            Invoice number and itemized receipt will be generated upon submission.
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <Link
                        :href="route('consultations.index')"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-100 transition"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-emerald-600/25 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>Complete & Save Consultation</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
