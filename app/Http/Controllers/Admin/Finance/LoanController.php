<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    /**
     * Display a listing of loans.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Loan::with(['user']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('loan_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('loan_date', '<=', $request->end_date);
        }
        
        $loans = $query->latest()->paginate(10);
        $users = User::all();
        
        return view('admin.finance.loan.index', compact('loans', 'users'));
    }

    /**
     * Show the form for creating a new loan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::all();
        return view('admin.finance.loan.create', compact('users'));
    }

    /**
     * Store a newly created loan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'loan_date' => 'required|date',
            'repayment_term' => 'required|integer|min:1',
            'repayment_type' => 'required|in:monthly,quarterly,annually',
            'purpose' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected,active,completed,defaulted',
        ]);

        // Check existing active loans
        $existingActiveLoans = Loan::where('user_id', $request->user_id)
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->count();
        
        if ($existingActiveLoans > 0) {
            throw ValidationException::withMessages([
                'user_id' => 'User already has an active or pending loan.',
            ]);
        }

        // Calculate loan end date based on repayment term
        $loanEndDate = match($request->repayment_type) {
            'monthly' => Carbon::parse($request->loan_date)->addMonths($request->repayment_term),
            'quarterly' => Carbon::parse($request->loan_date)->addMonths($request->repayment_term * 3),
            'annually' => Carbon::parse($request->loan_date)->addYears($request->repayment_term),
        };

        $loanData = $request->all();
        $loanData['loan_end_date'] = $loanEndDate;

        $loan = Loan::create($loanData);

        return redirect()->route('admin.finance.loan.index')
            ->with('success', 'Loan created successfully.');
    }

    /**
     * Display the specified loan.
     *
     * @param  \App\Models\Admin\Loan  $loan
     * @return \Illuminate\View\View
     */
    public function show(Loan $loan)
    {
        $loan->load('user');
        return view('admin.finance.loan.show', compact('loan'));
    }

    /**
     * Show the form for editing the specified loan.
     *
     * @param  \App\Models\Admin\Loan  $loan
     * @return \Illuminate\View\View
     */
    public function edit(Loan $loan)
    {
        $users = User::all();
        return view('admin.finance.loan.edit', compact('loan', 'users'));
    }

    /**
     * Update the specified loan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Loan  $loan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'loan_date' => 'required|date',
            'repayment_term' => 'required|integer|min:1',
            'repayment_type' => 'required|in:monthly,quarterly,annually',
            'purpose' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected,active,completed,defaulted',
        ]);

        // Calculate loan end date based on repayment term
        $loanEndDate = match($request->repayment_type) {
            'monthly' => Carbon::parse($request->loan_date)->addMonths($request->repayment_term),
            'quarterly' => Carbon::parse($request->loan_date)->addMonths($request->repayment_term * 3),
            'annually' => Carbon::parse($request->loan_date)->addYears($request->repayment_term),
        };

        $loanData = $request->all();
        $loanData['loan_end_date'] = $loanEndDate;

        $loan->update($loanData);

        return redirect()->route('admin.finance.loan.index')
            ->with('success', 'Loan updated successfully.');
    }

    /**
     * Remove the specified loan from storage.
     *
     * @param  \App\Models\Admin\Loan  $loan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Loan $loan)
    {
        // Prevent deletion of active loans
        if (in_array($loan->status, ['active', 'approved', 'pending'])) {
            return back()->withErrors(['Cannot delete an active or pending loan.']);
        }

        $loan->delete();

        return redirect()->route('admin.finance.loan.index')
            ->with('success', 'Loan deleted successfully.');
    }

    /**
     * Approve a loan application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        // Check if loan is already approved or active
        if (in_array($loan->status, ['approved', 'active', 'completed'])) {
            return back()->withErrors(['Loan is already approved or processed.']);
        }

        $loan->update([
            'status' => 'approved',
            'approved_date' => now(),
        ]);

        return redirect()->route('admin.finance.loan.index')
            ->with('success', 'Loan approved successfully.');
    }

    /**
     * Reject a loan application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'rejection_reason' => 'nullable|string',
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        // Check if loan is already processed
        if (in_array($loan->status, ['approved', 'active', 'completed', 'rejected'])) {
            return back()->withErrors(['Loan cannot be rejected at this stage.']);
        }

        $loan->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.finance.loan.index')
            ->with('success', 'Loan rejected successfully.');
    }

    /**
     * Mark loan as active.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        // Check if loan can be activated
        if ($loan->status !== 'approved') {
            return back()->withErrors(['Loan must be in approved status to activate.']);
        }

        $loan->update([
            'status' => 'active',
            'activation_date' => now(),
        ]);

        return redirect()->route('admin.finance.loan.index')
            ->with('success', 'Loan activated successfully.');
    }

    /**
     * Mark loan as completed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        // Check if loan can be completed
        if ($loan->status !== 'active') {
            return back()->withErrors(['Only active loans can be marked as completed.']);
        }

        $loan->update([
            'status' => 'completed',
            'completion_date' => now(),
        ]);

        return redirect()->route('admin.finance.loan.index')
            ->with('success', 'Loan completed successfully.');
    }
}