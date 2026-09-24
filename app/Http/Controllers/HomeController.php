<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the public SaaS landing page with features and pricing.
     */
    public function index(): Response
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return Inertia::render('Welcome', [
            'plans' => $plans,
        ]);
    }
}
