<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\ClinicSubscription;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminController extends Controller
{
    /**
     * Display Super Admin platform overview.
     */
    public function dashboard(): Response
    {
        $totalClinics = Clinic::count();
        $activeClinics = Clinic::where('status', 'active')->count();
        $totalDoctors = Doctor::withoutGlobalScopes()->count();
        $totalPatients = Patient::withoutGlobalScopes()->count();
        $activeSubscriptions = ClinicSubscription::where('status', 'active')->count();

        $clinics = Clinic::with(['subscriptions.plan', 'doctors', 'users'])
            ->latest()
            ->take(10)
            ->get();

        $plans = SubscriptionPlan::withCount('subscriptions')->get();

        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => [
                'total_clinics' => $totalClinics,
                'active_clinics' => $activeClinics,
                'total_doctors' => $totalDoctors,
                'total_patients' => $totalPatients,
                'active_subscriptions' => $activeSubscriptions,
            ],
            'clinics' => $clinics,
            'plans' => $plans,
        ]);
    }

    /**
     * List all clinics in the SaaS platform.
     */
    public function clinics(Request $request): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Clinic::with(['subscriptions.plan', 'doctors', 'users']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clinics = $query->latest()->paginate(10)->withQueryString();
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return Inertia::render('SuperAdmin/Clinics', [
            'clinics' => $clinics,
            'plans' => $plans,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Onboard / create a new clinic tenant with an admin user.
     */
    public function storeClinic(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'clinic_name' => 'required|string|max:150',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:25',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',

            // Admin User
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
        ]);

        $slug = Str::slug($validated['clinic_name']) . '-' . rand(100, 999);

        // 1. Create Clinic
        $clinic = Clinic::create([
            'name' => $validated['clinic_name'],
            'slug' => $slug,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'status' => 'active',
            'invoice_prefix' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $validated['clinic_name']), 0, 3)) . '-INV',
        ]);

        // 2. Create Clinic Admin
        $user = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'clinic_admin',
            'clinic_id' => $clinic->id,
            'phone' => $validated['phone'],
            'is_active' => true,
        ]);

        $clinic->users()->attach($user->id, ['role' => 'clinic_admin', 'is_primary' => true]);

        // 3. Attach Subscription
        ClinicSubscription::create([
            'clinic_id' => $clinic->id,
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addMonth(),
            'auto_renew' => true,
        ]);

        AuditLog::log('clinic_onboarded', $clinic, null, $clinic->toArray());

        return back()->with('success', "Clinic '{$clinic->name}' onboarded successfully with Admin ({$user->email}).");
    }

    /**
     * Toggle clinic status (active/suspended).
     */
    public function toggleClinicStatus(Clinic $clinic): RedirectResponse
    {
        $newStatus = $clinic->status === 'active' ? 'suspended' : 'active';
        $clinic->update(['status' => $newStatus]);

        return back()->with('success', "Clinic status changed to {$newStatus}.");
    }

    /**
     * Switch context to a specific clinic for Super Admin.
     */
    public function switchClinic(Request $request): RedirectResponse
    {
        $clinicId = $request->input('clinic_id');

        if ($clinicId === 'all' || empty($clinicId)) {
            session()->forget('active_clinic_id');
            return redirect()->route('superadmin.dashboard')->with('info', 'Switched to platform super-admin view.');
        }

        $clinic = Clinic::findOrFail($clinicId);
        session(['active_clinic_id' => $clinic->id]);

        return redirect()->route('dashboard')->with('success', "Switched workspace to {$clinic->name}.");
    }

    /**
     * Manage Subscription Plans.
     */
    public function plans(): Response
    {
        $plans = SubscriptionPlan::withCount('subscriptions')->orderBy('price_monthly')->get();

        return Inertia::render('SuperAdmin/Plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store a new subscription plan.
     */
    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'slug'                 => 'required|string|max:100|unique:subscription_plans,slug',
            'description'          => 'nullable|string|max:255',
            'price_monthly'        => 'required|numeric|min:0',
            'price_yearly'         => 'nullable|numeric|min:0',
            'max_doctors'          => 'required|integer|min:1',
            'max_patients_monthly' => 'required|integer|min:1',
            'max_staff'            => 'nullable|integer|min:1',
            'features'             => 'nullable|string',
            'is_active'            => 'boolean',
            'is_featured'          => 'boolean',
        ]);

        // Convert newline-delimited features string to array
        if (isset($validated['features']) && is_string($validated['features'])) {
            $validated['features'] = array_values(array_filter(
                array_map('trim', explode("\n", $validated['features']))
            ));
        }

        SubscriptionPlan::create($validated);

        return back()->with('success', "Plan '{$validated['name']}' created successfully.");
    }

    /**
     * Update an existing subscription plan.
     */
    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'slug'                 => 'required|string|max:100|unique:subscription_plans,slug,' . $plan->id,
            'description'          => 'nullable|string|max:255',
            'price_monthly'        => 'required|numeric|min:0',
            'price_yearly'         => 'nullable|numeric|min:0',
            'max_doctors'          => 'required|integer|min:1',
            'max_patients_monthly' => 'required|integer|min:1',
            'max_staff'            => 'nullable|integer|min:1',
            'features'             => 'nullable|string',
            'is_active'            => 'boolean',
            'is_featured'          => 'boolean',
        ]);

        // Convert newline-delimited features string to array
        if (isset($validated['features']) && is_string($validated['features'])) {
            $validated['features'] = array_values(array_filter(
                array_map('trim', explode("\n", $validated['features']))
            ));
        }

        $plan->update($validated);

        return back()->with('success', "Plan '{$plan->name}' updated successfully.");
    }

    /**
     * Toggle subscription plan active/inactive status.
     */
    public function togglePlan(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        $status = $plan->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Plan '{$plan->name}' {$status}.");
    }
}
