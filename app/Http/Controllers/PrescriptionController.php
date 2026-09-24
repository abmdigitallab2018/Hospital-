<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Prescription;
use App\Models\PrescriptionTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $date = $request->query('date');
        $user = $request->user();

        $query = Prescription::with(['patient', 'doctor.user', 'items', 'visit']);

        if ($user->role === 'doctor' && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        }

        if ($date) {
            $query->whereDate('date', $date);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($p) use ($search) {
                        $p->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('patient_uid', 'like', "%{$search}%");
                    });
            });
        }

        $prescriptions = $query->latest('date')->paginate(12)->withQueryString();

        return Inertia::render('Prescriptions/Index', [
            'prescriptions' => $prescriptions,
            'filters' => [
                'search' => $search,
                'date' => $date,
            ],
        ]);
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription): Response
    {
        $prescription->load(['patient', 'doctor.user', 'items', 'visit.vital', 'clinic']);

        return Inertia::render('Prescriptions/Show', [
            'prescription' => $prescription,
        ]);
    }

    /**
     * Printable view of prescription with hospital letterhead and Rx layout.
     */
    public function print(Prescription $prescription): Response
    {
        $prescription->load(['patient', 'doctor.user', 'items', 'visit.vital', 'clinic']);

        AuditLog::log('printed', $prescription);

        return Inertia::render('Prescriptions/Print', [
            'prescription' => $prescription,
        ]);
    }

    /**
     * Store new prescription template.
     */
    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.medicine_name' => 'required|string|max:150',
            'items.*.dosage' => 'required|string|max:100',
            'items.*.frequency' => 'required|string|max:50',
            'items.*.duration' => 'required|string|max:50',
            'items.*.instructions' => 'nullable|string|max:200',
        ]);

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;

        PrescriptionTemplate::create([
            'clinic_id' => $activeClinicId,
            'doctor_id' => $request->user()->doctor?->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'items' => $validated['items'],
        ]);

        return back()->with('success', 'Prescription template saved successfully!');
    }
}
