<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     */
    public function index(Request $request): Response
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;

        $staff = User::where('clinic_id', $activeClinicId)
            ->whereIn('role', ['clinic_admin', 'receptionist', 'accountant'])
            ->latest()
            ->get();

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
        ]);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:25',
            'role' => 'required|in:clinic_admin,receptionist,accountant',
            'password' => 'required|string|min:6',
        ]);

        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'clinic_id' => $activeClinicId,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $clinic = Clinic::find($activeClinicId);
        if ($clinic) {
            $clinic->users()->attach($user->id, ['role' => $validated['role']]);
        }

        AuditLog::log('staff_created', $user, null, $user->toArray());

        return back()->with('success', "Staff member {$validated['name']} registered with role: " . ucfirst(str_replace('_', ' ', $validated['role'])));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:25',
            'role' => 'required|in:clinic_admin,receptionist,accountant',
            'is_active' => 'required|boolean',
        ]);

        $oldValues = $staff->toArray();
        $staff->update($validated);

        AuditLog::log('staff_updated', $staff, $oldValues, $staff->toArray());

        return back()->with('success', 'Staff details updated successfully.');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff): RedirectResponse
    {
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        AuditLog::log('staff_deleted', $staff, $staff->toArray(), null);
        $staff->delete();

        return back()->with('success', 'Staff account removed.');
    }
}
