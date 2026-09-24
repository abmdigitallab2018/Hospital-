<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;
        $search = $request->query('search');
        $category = $request->query('category');
        $month = $request->query('month', now()->format('Y-m'));

        $query = Expense::where('clinic_id', $activeClinicId)
            ->with('createdBy');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($month) {
            $query->whereYear('expense_date', substr($month, 0, 4))
                  ->whereMonth('expense_date', substr($month, 5, 2));
        }

        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();

        $totalThisMonth = Expense::where('clinic_id', $activeClinicId)
            ->whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');

        $categories = Expense::where('clinic_id', $activeClinicId)
            ->distinct()->pluck('category');

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'totalThisMonth' => (float) $totalThisMonth,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'month' => $month,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $activeClinicId = session('active_clinic_id') ?? $request->user()->clinic_id;

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,card,bank_transfer',
            'vendor' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        $expense = Expense::create(array_merge($validated, [
            'clinic_id' => $activeClinicId,
            'created_by_user_id' => $request->user()->id,
        ]));

        AuditLog::log('expense_created', $expense, null, $expense->toArray());

        return back()->with('success', "Expense '{$expense->title}' recorded successfully.");
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,card,bank_transfer',
            'vendor' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldValues = $expense->toArray();
        $expense->update($validated);
        AuditLog::log('expense_updated', $expense, $oldValues, $expense->toArray());

        return back()->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        AuditLog::log('expense_deleted', $expense, $expense->toArray(), null);
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}
