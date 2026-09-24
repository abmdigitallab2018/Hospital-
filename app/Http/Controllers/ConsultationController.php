<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionTemplate;
use App\Models\Service;
use App\Models\Visit;
use App\Models\Vital;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationController extends Controller
{
    /**
     * Display a listing of consultations.
     */
    public function index(Request $request): Response
    {
        $doctorId = $request->query('doctor_id');
        $search = $request->query('search');
        $date = $request->query('date');

        $user = $request->user();

        $query = Visit::with(['patient', 'doctor.user', 'vital', 'prescription.items']);

        if ($user->role === 'doctor' && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($doctorId && $doctorId !== 'all') {
            $query->where('doctor_id', $doctorId);
        }

        if ($date) {
            $query->whereDate('visit_date', $date);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                    ->orWhere('chief_complaints', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($p) use ($search) {
                        $p->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('patient_uid', 'like', "%{$search}%");
                    });
            });
        }

        $consultations = $query->latest('visit_date')->paginate(12)->withQueryString();
        $doctors = Doctor::with('user')->where('is_available', true)->get();

        return Inertia::render('Consultations/Index', [
            'consultations' => $consultations,
            'doctors' => $doctors,
            'filters' => [
                'doctor_id' => $doctorId,
                'search' => $search,
                'date' => $date,
            ],
        ]);
    }

    /**
     * Show form to start or record a consultation.
     */
    public function create(Request $request): Response
    {
        $patientId = $request->query('patient_id');
        $appointmentId = $request->query('appointment_id');
        $user = $request->user();

        $patient = null;
        $appointment = null;

        if ($appointmentId) {
            $appointment = Appointment::with(['patient', 'doctor.user'])->find($appointmentId);
            if ($appointment) {
                $patient = $appointment->patient;
            }
        } elseif ($patientId) {
            $patient = Patient::find($patientId);
        }

        if (!$patient) {
            $patients = Patient::where('status', 'active')->select('id', 'first_name', 'last_name', 'patient_uid', 'blood_group', 'date_of_birth')->get();
        } else {
            $patients = [$patient];
            // Load previous visits for history
            $patient->load([
                'visits' => fn($q) => $q->with(['doctor.user', 'vital', 'prescription.items'])->latest('visit_date')->take(5)
            ]);
        }

        $doctors = Doctor::with('user')->where('is_available', true)->get();
        $templates = PrescriptionTemplate::all();
        $services = Service::where('is_active', true)->get();

        return Inertia::render('Consultations/Create', [
            'selectedPatient' => $patient,
            'selectedAppointment' => $appointment,
            'patients' => $patients,
            'doctors' => $doctors,
            'templates' => $templates,
            'services' => $services,
            'currentDoctorId' => $user->doctor?->id,
        ]);
    }

    /**
     * Store a completed consultation with vitals, prescription, and auto-billing.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'chief_complaints' => 'required|string',
            'symptoms' => 'nullable|string',
            'examination_notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'clinical_advice' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
            'follow_up_instructions' => 'nullable|string',

            // Vitals
            'vitals.temperature' => 'nullable|numeric|between:90,110',
            'vitals.blood_pressure_systolic' => 'nullable|integer|between:60,260',
            'vitals.blood_pressure_diastolic' => 'nullable|integer|between:30,160',
            'vitals.pulse_rate' => 'nullable|integer|between:30,220',
            'vitals.respiratory_rate' => 'nullable|integer|between:8,60',
            'vitals.oxygen_saturation' => 'nullable|integer|between:50,100',
            'vitals.weight_kg' => 'nullable|numeric|between:1,300',
            'vitals.height_cm' => 'nullable|numeric|between:30,250',

            // Prescription Items
            'prescriptions' => 'nullable|array',
            'prescriptions.*.medicine_name' => 'required_with:prescriptions|string|max:200',
            'prescriptions.*.dosage' => 'required_with:prescriptions|string|max:100',
            'prescriptions.*.frequency' => 'required_with:prescriptions|string|max:50',
            'prescriptions.*.duration' => 'required_with:prescriptions|string|max:50',
            'prescriptions.*.instructions' => 'nullable|string|max:200',

            // Optional Billing
            'generate_invoice' => 'nullable|boolean',
            'invoice_services' => 'nullable|array',
            'invoice_services.*.service_id' => 'nullable|exists:services,id',
            'invoice_services.*.description' => 'required_with:invoice_services|string',
            'invoice_services.*.unit_price' => 'required_with:invoice_services|numeric|min:0',
            'invoice_services.*.quantity' => 'required_with:invoice_services|integer|min:1',
        ]);

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $clinic = Clinic::find($activeClinicId);

        // 1. Create Visit Record
        $visit = Visit::create([
            'clinic_id' => $activeClinicId,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'visit_date' => $validated['visit_date'],
            'chief_complaints' => $validated['chief_complaints'],
            'symptoms' => $validated['symptoms'] ?? null,
            'examination_notes' => $validated['examination_notes'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'clinical_advice' => $validated['clinical_advice'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'follow_up_instructions' => $validated['follow_up_instructions'] ?? null,
            'status' => 'completed',
        ]);

        // 2. Save Vitals
        $vitalsData = $request->input('vitals', []);
        $weight = isset($vitalsData['weight_kg']) ? (float)$vitalsData['weight_kg'] : null;
        $height = isset($vitalsData['height_cm']) ? (float)$vitalsData['height_cm'] : null;
        $bmi = null;

        if ($weight && $height && $height > 0) {
            $heightM = $height / 100;
            $bmi = round($weight / ($heightM * $heightM), 1);
        }

        Vital::create([
            'clinic_id' => $activeClinicId,
            'visit_id' => $visit->id,
            'patient_id' => $validated['patient_id'],
            'temperature' => $vitalsData['temperature'] ?? null,
            'blood_pressure_systolic' => $vitalsData['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $vitalsData['blood_pressure_diastolic'] ?? null,
            'pulse_rate' => $vitalsData['pulse_rate'] ?? null,
            'respiratory_rate' => $vitalsData['respiratory_rate'] ?? null,
            'oxygen_saturation' => $vitalsData['oxygen_saturation'] ?? null,
            'weight_kg' => $weight,
            'height_cm' => $height,
            'bmi' => $bmi,
            'recorded_at' => Carbon::now(),
        ]);

        // 3. Save Prescription & Items if provided
        $prescriptionItems = $request->input('prescriptions', []);
        if (!empty($prescriptionItems)) {
            $rxNumber = 'RX-' . Carbon::parse($validated['visit_date'])->format('Ymd') . '-' . sprintf('%03d', $visit->id);

            $prescription = Prescription::create([
                'clinic_id' => $activeClinicId,
                'visit_id' => $visit->id,
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'prescription_number' => $rxNumber,
                'date' => $validated['visit_date'],
                'notes' => $validated['clinical_advice'] ?? null,
                'disclaimer' => $clinic?->prescription_disclaimer,
                'is_signed' => true,
            ]);

            foreach ($prescriptionItems as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_name' => $item['medicine_name'],
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration'],
                    'instructions' => $item['instructions'] ?? null,
                ]);
            }
        }

        // 4. Save Follow-Up if requested
        if (!empty($validated['follow_up_date'])) {
            FollowUp::create([
                'clinic_id' => $activeClinicId,
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'visit_id' => $visit->id,
                'follow_up_date' => $validated['follow_up_date'],
                'status' => 'pending',
                'notes' => $validated['follow_up_instructions'] ?? 'Scheduled follow-up review.',
            ]);
        }

        // 5. Update linked Appointment status to completed
        if (!empty($validated['appointment_id'])) {
            $appointment = Appointment::find($validated['appointment_id']);
            if ($appointment) {
                $appointment->update([
                    'status' => 'completed',
                    'completed_at' => Carbon::now(),
                ]);
            }
        }

        // 6. Optional Invoice Generation
        if ($request->boolean('generate_invoice')) {
            $invoiceServices = $request->input('invoice_services', []);
            $subtotal = 0;
            $itemsToCreate = [];

            if (empty($invoiceServices)) {
                // Default consultation fee of the doctor
                $doctor = Doctor::find($validated['doctor_id']);
                $fee = $doctor ? $doctor->consultation_fee : ($clinic->consultation_fee ?? 500);
                $subtotal += $fee;
                $itemsToCreate[] = [
                    'service_id' => null,
                    'description' => 'Doctor Consultation (' . ($doctor?->user?->name ?? 'Doctor') . ')',
                    'unit_price' => $fee,
                    'quantity' => 1,
                    'total' => $fee,
                ];
            } else {
                foreach ($invoiceServices as $svc) {
                    $itemTotal = (float)$svc['unit_price'] * (int)$svc['quantity'];
                    $subtotal += $itemTotal;
                    $itemsToCreate[] = [
                        'service_id' => $svc['service_id'] ?? null,
                        'description' => $svc['description'],
                        'unit_price' => $svc['unit_price'],
                        'quantity' => $svc['quantity'],
                        'total' => $itemTotal,
                    ];
                }
            }

            $prefix = $clinic ? $clinic->invoice_prefix : 'INV';
            $invCount = Invoice::where('clinic_id', $activeClinicId)->count() + 1;
            $invNumber = sprintf('%s-%s-%04d', $prefix, Carbon::today()->format('Y'), $invCount);

            $invoice = Invoice::create([
                'clinic_id' => $activeClinicId,
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'] ?? null,
                'visit_id' => $visit->id,
                'invoice_number' => $invNumber,
                'invoice_date' => Carbon::today()->format('Y-m-d'),
                'due_date' => Carbon::today()->format('Y-m-d'),
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'total_amount' => $subtotal,
                'paid_amount' => 0.00,
                'balance_amount' => $subtotal,
                'payment_status' => 'unpaid',
                'notes' => 'Generated automatically from consultation.',
            ]);

            foreach ($itemsToCreate as $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
            }
        }

        AuditLog::log('created', $visit, null, $visit->toArray());

        return redirect()->route('consultations.show', $visit->id)->with('success', 'Consultation recorded successfully!');
    }

    /**
     * Display the specified consultation details.
     */
    public function show(Visit $consultation): Response
    {
        $consultation->load([
            'patient',
            'doctor.user',
            'vital',
            'prescription.items',
            'invoice.items',
            'followUp',
        ]);

        return Inertia::render('Consultations/Show', [
            'consultation' => $consultation,
        ]);
    }
}
