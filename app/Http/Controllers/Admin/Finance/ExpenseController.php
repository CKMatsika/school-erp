<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Expense::with('createdBy');
        
        // Apply filters
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }
        
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('min_amount') && $request->min_amount) {
            $query->where('amount', '>=', $request->min_amount);
        }
        
        if ($request->has('max_amount') && $request->max_amount) {
            $query->where('amount', '<=', $request->max_amount);
        }
        
        $expenses = $query->latest()->paginate(15);
        
        // Get categories for filter
        $categories = Expense::select('category')->distinct()->pluck('category');
        
        return view('admin.finance.expense.index', compact('expenses', 'categories'));
    }

    /**
     * Show the form for creating a new expense.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = [
            'office_supplies' => 'Office Supplies',
            'utilities' => 'Utilities',
            'rent' => 'Rent',
            'equipment' => 'Equipment',
            'maintenance' => 'Maintenance',
            'transportation' => 'Transportation',
            'food' => 'Food and Catering',
            'events' => 'Events',
            'training' => 'Training and Development',
            'marketing' => 'Marketing',
            'software' => 'Software and Subscriptions',
            'insurance' => 'Insurance',
            'taxes' => 'Taxes',
            'salary' => 'Salary',
            'miscellaneous' => 'Miscellaneous',
        ];
        
        $paymentMethods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'mobile_payment' => 'Mobile Payment',
            'online_payment' => 'Online Payment',
        ];
        
        return view('admin.finance.expense.create', compact('categories', 'paymentMethods'));
    }

    /**
     * Store a newly created expense in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:50',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'payee' => 'required|string|max:255',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $expense = new Expense($request->all());
        $expense->created_by = Auth::id();
        $expense->status = 'pending';
        
        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('expenses', 'public');
            $expense->receipt_path = $path;
        }
        
        $expense->save();

        return redirect()->route('admin.finance.expense.index')
            ->with('success', 'Expense created successfully.');
    }

    /**
     * Display the specified expense.
     *
     * @param  \App\Models\Admin\Expense  $expense
     * @return \Illuminate\View\View
     */
    public function show(Expense $expense)
    {
        $expense->load(['createdBy', 'approvedBy']);
        return view('admin.finance.expense.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     *
     * @param  \App\Models\Admin\Expense  $expense
     * @return \Illuminate\View\View
     */
    public function edit(Expense $expense)
    {
        // Don't allow editing if expense is already approved or rejected
        if (in_array($expense->status, ['approved', 'rejected'])) {
            return redirect()->route('admin.finance.expense.show', $expense)
                ->with('error', 'You cannot edit an expense that has been approved or rejected.');
        }
        
        $categories = [
            'office_supplies' => 'Office Supplies',
            'utilities' => 'Utilities',
            'rent' => 'Rent',
            'equipment' => 'Equipment',
            'maintenance' => 'Maintenance',
            'transportation' => 'Transportation',
            'food' => 'Food and Catering',
            'events' => 'Events',
            'training' => 'Training and Development',
            'marketing' => 'Marketing',
            'software' => 'Software and Subscriptions',
            'insurance' => 'Insurance',
            'taxes' => 'Taxes',
            'salary' => 'Salary',
            'miscellaneous' => 'Miscellaneous',
        ];
        
        $paymentMethods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'mobile_payment' => 'Mobile Payment',
            'online_payment' => 'Online Payment',
        ];
        
        return view('admin.finance.expense.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    /**
     * Update the specified expense in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Expense  $expense
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Expense $expense)
    {
        // Don't allow updating if expense is already approved or rejected
        if (in_array($expense->status, ['approved', 'rejected'])) {
            return redirect()->route('admin.finance.expense.show', $expense)
                ->with('error', 'You cannot update an expense that has been approved or rejected.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:50',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'payee' => 'required|string|max:255',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $expense->fill($request->all());
        
        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            
            $path = $request->file('receipt')->store('expenses', 'public');
            $expense->receipt_path = $path;
        }
        
        $expense->save();

        return redirect()->route('admin.finance.expense.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense from storage.
     *
     * @param  \App\Models\Admin\Expense  $expense
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Expense $expense)
    {
        // Don't allow deleting if expense is already approved
        if ($expense->status == 'approved') {
            return redirect()->route('admin.finance.expense.index')
                ->with('error', 'You cannot delete an approved expense.');
        }
        
        // Delete receipt file if exists
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }
        
        $expense->delete();

        return redirect()->route('admin.finance.expense.index')
            ->with('success', 'Expense deleted successfully.');
    }
    
    /**
     * Approve an expense.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Expense  $expense
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, Expense $expense)
    {
        // Only pending expenses can be approved
        if ($expense->status != 'pending') {
            return redirect()->route('admin.finance.expense.show', $expense)
                ->with('error', 'Only pending expenses can be approved.');
        }
        
        $request->validate([
            'approval_notes' => 'nullable|string|max:255',
        ]);
        
        $expense->status = 'approved';
        $expense->approved_by = Auth::id();
        $expense->approved_at = now();
        $expense->approval_notes = $request->approval_notes;
        $expense->save();
        
        // Here you might want to add logic for:
        // - Recording the expense in the accounting system
        // - Updating budget allocations
        // - Triggering payments
        
        return redirect()->route('admin.finance.expense.show', $expense)
            ->with('success', 'Expense approved successfully.');
    }
    
    /**
     * Reject an expense.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Expense  $expense
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, Expense $expense)
    {
        // Only pending expenses can be rejected
        if ($expense->status != 'pending') {
            return redirect()->route('admin.finance.expense.show', $expense)
                ->with('error', 'Only pending expenses can be rejected.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);
        
        $expense->status = 'rejected';
        $expense->rejected_by = Auth::id();
        $expense->rejected_at = now();
        $expense->rejection_reason = $request->rejection_reason;
        $expense->save();
        
        return redirect()->route('admin.finance.expense.show', $expense)
            ->with('success', 'Expense rejected successfully.');
    }
    
    /**
     * Generate expense report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $query = Expense::with('createdBy')
            ->whereDate('expense_date', '>=', $request->start_date)
            ->whereDate('expense_date', '<=', $request->end_date);
            
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $expenses = $query->get();
        
        // Calculate totals
        $totalAmount = $expenses->sum('amount');
        $approvedAmount = $expenses->where('status', 'approved')->sum('amount');
        $pendingAmount = $expenses->where('status', 'pending')->sum('amount');
        $rejectedAmount = $expenses->where('status', 'rejected')->sum('amount');
        
        // Group by category
        $expensesByCategory = $expenses->groupBy('category')
            ->map(function ($items, $key) {
                return [
                    'category' => $key,
                    'total' => $items->sum('amount'),
                    'count' => $items->count(),
                ];
            });
        
        // Get categories for filter
        $categories = Expense::select('category')->distinct()->pluck('category');
        
        return view('admin.finance.expense.report', compact(
            'expenses',
            'totalAmount',
            'approvedAmount',
            'pendingAmount',
            'rejectedAmount',
            'expensesByCategory',
            'categories'
        ));
    }
}