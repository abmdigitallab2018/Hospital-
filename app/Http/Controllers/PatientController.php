<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $bloodGroup = $request->query('blood_group');
        $gender = $request->query('gender');
        $status = $request->query('status');

        $query = Patient::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('patient_uid', 'like', "%{$search}%");
            });
        }

        if ($bloodGroup && $bloodGroup !== 'all') {
            $query->where('blood_group', $bloodGroup);
        }

        if ($gender && $gender !== 'all') {
            $query->where('gender', $gender);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $patients = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters' => [
                'search' => $search,
                'blood_group' => $bloodGroup,
                'gender' => $gender,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): Response
    {
        return Inertia::render('Patients/Create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:25',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'existing_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $clinic = Clinic::find($activeClinicId);
        $prefix = $clinic ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $clinic->name), 0, 3)) : 'MED';

        $count = Patient::where('clinic_id', $activeClinicId)->count() + 1;
        $patientUid = sprintf('%s-P-%04d', $prefix, $count);

        $validated['clinic_id'] = $activeClinicId;
        $validated['patient_uid'] = $patientUid;
        $validated['status'] = 'active';

        $patient = Patient::create($validated);

        AuditLog::log('created', $patient, null, $patient->toArray());

        return redirect()->route('patients.show', $patient->id)->with('success', "Patient {$patient->full_name} ({$patientUid}) registered successfully.");
    }

    /**
     * Display the specified patient profile.
     */
    public function show(Patient $patient): Response
    {
        $patient->load([
            'appointments' => fn($q) => $q->with('doctor.user')->latest('appointment_date'),
            'visits' => fn($q) => $q->with(['doctor.user', 'vital', 'prescription.items'])->latest('visit_date'),
            'invoices' => fn($q) => $q->with(['items', 'payments'])->latest('invoice_date'),
            'followUps' => fn($q) => $q->with('doctor.user')->latest('follow_up_date'),
        ]);

        return Inertia::render('Patients/Show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient): Response
    {
        return Inertia::render('Patients/Edit', [
            'patient' => $patient,
        ]);
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:25',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'existing_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $oldValues = $patient->toArray();
        $patient->update($validated);

        AuditLog::log('updated', $patient, $oldValues, $patient->toArray());

        return redirect()->route('patients.show', $patient->id)->with('success', 'Patient details updated successfully.');
    }

    /**
     * Remove the specified patient from storage.
     */
    public function destroy(Patient $patient): RedirectResponse
    {
        AuditLog::log('deleted', $patient, $patient->toArray(), null);
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient record archived successfully.');
    }

    /**
     * Export patients roster to CSV.
     */
    public function export(): StreamedResponse
    {
        $fileName = 'patients_' . date('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Patient UID', 'First Name', 'Last Name', 'Phone', 'Email', 'Gender', 'Blood Group', 'DOB', 'City', 'Status', 'Registered Date']);

            Patient::chunk(100, function ($patients) use ($handle) {
                foreach ($patients as $p) {
                    fputcsv($handle, [
                        $p->patient_uid,
                        $p->first_name,
                        $p->last_name,
                        $p->phone,
                        $p->email,
                        ucfirst($p->gender),
                        $p->blood_group,
                        $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '',
                        $p->city,
                        ucfirst($p->status),
                        $p->created_at->format('Y-m-d'),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
