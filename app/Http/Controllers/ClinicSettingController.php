<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClinicSettingController extends Controller
{
    /**
     * Display clinic settings screen.
     */
    public function index(Request $request): Response
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $clinic = Clinic::findOrFail($activeClinicId);

        return Inertia::render('Settings/Index', [
            'clinic' => $clinic,
        ]);
    }

    /**
     * Update clinic profile and configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $clinic = Clinic::findOrFail($activeClinicId);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:25',
            'emergency_phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'registration_number' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'consultation_fee' => 'required|numeric|min:0',
            'invoice_prefix' => 'required|string|max:10',
            'prescription_disclaimer' => 'nullable|string|max:1000',
            'currency_symbol' => 'required|string|max:5',
        ]);

        $oldValues = $clinic->toArray();
        $clinic->update($validated);

        AuditLog::log('clinic_settings_updated', $clinic, $oldValues, $clinic->toArray());

        return back()->with('success', 'Clinic profile and preferences updated successfully.');
    }
}
