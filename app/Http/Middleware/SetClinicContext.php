<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetClinicContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // If user has a default clinic and no session clinic set, default to user's clinic
            if (!session()->has('active_clinic_id') && $user->clinic_id) {
                session(['active_clinic_id' => $user->clinic_id]);
            }

            // Check if clinic is active
            $activeClinicId = session('active_clinic_id') ?? $user->clinic_id;
            if ($activeClinicId && !$user->isSuperAdmin()) {
                $clinic = Clinic::find($activeClinicId);
                if ($clinic && $clinic->status === 'suspended') {
                    abort(403, 'Your clinic account is currently suspended. Please contact platform support.');
                }
            }
        }

        return $next($request);
    }
}
