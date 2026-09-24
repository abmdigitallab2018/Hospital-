<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    /**
     * Display a listing of services and procedures.
     */
    public function index(Request $request): Response
    {
        $category = $request->query('category');
        $search = $request->query('search');

        $query = Service::query();

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->latest()->paginate(15)->withQueryString();
        $categories = Service::distinct()->pluck('category')->filter()->values();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $service = Service::create($validated);

        AuditLog::log('service_created', $service, null, $service->toArray());

        return back()->with('success', "Service '{$service->name}' added to catalog.");
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $oldValues = $service->toArray();
        $service->update($validated);

        AuditLog::log('service_updated', $service, $oldValues, $service->toArray());

        return back()->with('success', "Service updated successfully.");
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Service $service): RedirectResponse
    {
        AuditLog::log('service_deleted', $service, $service->toArray(), null);
        $service->delete();

        return back()->with('success', "Service removed from catalog.");
    }
}
