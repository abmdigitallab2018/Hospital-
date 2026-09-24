<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $activeClinic = null;
        $availableClinics = [];

        if ($user) {
            $activeClinicId = session('active_clinic_id') ?? $user->clinic_id;
            if ($activeClinicId) {
                $activeClinic = Clinic::find($activeClinicId);
            }

            if ($user->isSuperAdmin()) {
                $availableClinics = Clinic::select('id', 'name', 'city', 'status')->get();
            } elseif ($user->clinics()->exists()) {
                $availableClinics = $user->clinics()->select('clinics.id', 'clinics.name', 'clinics.city')->get();
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'clinic_id' => $user->clinic_id,
                    'doctor_id' => $user->doctor?->id,
                    'doctor_specialization' => $user->doctor?->specialization,
                ] : null,
                'clinic' => $activeClinic ? [
                    'id' => $activeClinic->id,
                    'name' => $activeClinic->name,
                    'slug' => $activeClinic->slug,
                    'phone' => $activeClinic->phone,
                    'email' => $activeClinic->email,
                    'address' => $activeClinic->address,
                    'city' => $activeClinic->city,
                    'state' => $activeClinic->state,
                    'postal_code' => $activeClinic->postal_code,
                    'currency_symbol' => $activeClinic->currency_symbol ?? '₹',
                    'consultation_fee' => $activeClinic->consultation_fee,
                    'logo_path' => $activeClinic->logo_path,
                    'status' => $activeClinic->status,
                ] : null,
                'availableClinics' => $availableClinics,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'unreadNotificationsCount' => fn () => $user && $activeClinic
                ? Notification::where('clinic_id', $activeClinic->id)->where('status', 'pending')->count()
                : 0,
        ];
    }
}
