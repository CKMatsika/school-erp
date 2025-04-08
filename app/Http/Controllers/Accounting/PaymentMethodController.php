<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the payment methods.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        
        // Build query without eager loading the account relationship
        $query = PaymentMethod::when($school_id, fn($q, $id) => $q->where('school_id', $id));
            
        // Only order by display_order if the column exists
        if (Schema::hasColumn('payment_methods', 'display_order')) {
            $query->orderBy('display_order')->orderBy('name');
        } else {
            $query->orderBy('name');
        }
            
        $paymentMethods = $query->get();
            
        return view('accounting.payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new payment method.
     */
    public function create()
    {
        $school_id = Auth::user()?->school_id;
        
        // Get bank/cash accounts for dropdown
        // FIXED: Using account_type_id and proper filtering for bank accounts
        $accounts = ChartOfAccount::when($school_id, fn($q, $id) => $q->where('school_id', $id))
            ->where('is_bank_account', true)
            ->orderBy('name')
            ->get();
            
        return view('accounting.payment-methods.create', compact('accounts'));
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        
        // Build validation rules based on existing columns
        $rules = [
            'name' => [
                'required', 
                'string', 
                'max:100',
                Rule::unique('payment_methods')->where(function ($query) use ($school_id) {
                    return $query->where('school_id', $school_id);
                })
            ],
            'code' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('payment_methods')->where(function ($query) use ($school_id) {
                    return $query->where('school_id', $school_id);
                })
            ],
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'instructions' => 'nullable|string',
        ];
        
        // Add display_order rules if column exists
        if (Schema::hasColumn('payment_methods', 'display_order')) {
            $rules['display_order'] = 'nullable|integer|min:1';
        }
        
        // Add requires_reference rules if column exists
        if (Schema::hasColumn('payment_methods', 'requires_reference')) {
            $rules['requires_reference'] = 'boolean';
        }
        
        // Add account_id rules if column exists
        if (Schema::hasColumn('payment_methods', 'account_id')) {
            $rules['account_id'] = 'nullable|exists:chart_of_accounts,id';
        }
        
        $validated = $request->validate($rules);
        
        // Add school_id to the data
        $validated['school_id'] = $school_id;
        
        // Set default display order if column exists and value not provided
        if (Schema::hasColumn('payment_methods', 'display_order') && !isset($validated['display_order'])) {
            $maxOrder = PaymentMethod::where('school_id', $school_id)->max('display_order') ?? 0;
            $validated['display_order'] = $maxOrder + 1;
        }
        
        try {
            DB::beginTransaction();
            
            $paymentMethod = PaymentMethod::create($validated);
            
            DB::commit();
            
            return redirect()->route('accounting.payment-methods.index')
                ->with('success', 'Payment method created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create payment method: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment method.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        // Authorize access
        $this->authorizeSchoolAccess($paymentMethod);
        
        // Get account data if account_id exists and relation is defined
        $accountName = null;
        if (Schema::hasColumn('payment_methods', 'account_id') && $paymentMethod->account_id) {
            $account = ChartOfAccount::find($paymentMethod->account_id);
            if ($account) {
                $paymentMethod->accountName = $account->name;
                $paymentMethod->accountCode = $account->code;
            }
        }
        
        return view('accounting.payment-methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified payment method.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        // Authorize access
        $this->authorizeSchoolAccess($paymentMethod);
        
        $school_id = Auth::user()?->school_id;
        
        // Get bank/cash accounts for dropdown
        // FIXED: Using account_type_id and proper filtering for bank accounts
        $accounts = ChartOfAccount::when($school_id, fn($q, $id) => $q->where('school_id', $id))
            ->where('is_bank_account', true)
            ->orderBy('name')
            ->get();
            
        return view('accounting.payment-methods.edit', compact('paymentMethod', 'accounts'));
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        // Authorize access
        $this->authorizeSchoolAccess($paymentMethod);
        
        $school_id = Auth::user()?->school_id;
        
        // Build validation rules based on existing columns
        $rules = [
            'name' => [
                'required', 
                'string', 
                'max:100',
                Rule::unique('payment_methods')->where(function ($query) use ($school_id) {
                    return $query->where('school_id', $school_id);
                })->ignore($paymentMethod->id)
            ],
            'code' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('payment_methods')->where(function ($query) use ($school_id) {
                    return $query->where('school_id', $school_id);
                })->ignore($paymentMethod->id)
            ],
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'instructions' => 'nullable|string',
        ];
        
        // Add display_order rules if column exists
        if (Schema::hasColumn('payment_methods', 'display_order')) {
            $rules['display_order'] = 'nullable|integer|min:1';
        }
        
        // Add requires_reference rules if column exists
        if (Schema::hasColumn('payment_methods', 'requires_reference')) {
            $rules['requires_reference'] = 'boolean';
        }
        
        // Add account_id rules if column exists
        if (Schema::hasColumn('payment_methods', 'account_id')) {
            $rules['account_id'] = 'nullable|exists:chart_of_accounts,id';
        }
        
        $validated = $request->validate($rules);
        
        try {
            DB::beginTransaction();
            
            $paymentMethod->update($validated);
            
            DB::commit();
            
            return redirect()->route('accounting.payment-methods.index')
                ->with('success', 'Payment method updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update payment method: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment method from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Authorize access
        $this->authorizeSchoolAccess($paymentMethod);
        
        // Check if payment method is in use
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'payment_method_id')) {
            // Logic to check for existing payments using this method
            // This would need a relationship method, but for now, we'll use a raw query
            $paymentsCount = DB::table('payments')
                ->where('payment_method_id', $paymentMethod->id)
                ->count();
                
            if ($paymentsCount > 0) {
                return back()->with('error', "Cannot delete this payment method because it's associated with {$paymentsCount} payment(s).");
            }
        }
        
        try {
            DB::beginTransaction();
            
            $paymentMethod->delete();
            
            DB::commit();
            
            return redirect()->route('accounting.payment-methods.index')
                ->with('success', 'Payment method deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment method: ' . $e->getMessage());
        }
    }
    
    /**
     * Basic authorization helper
     */
    private function authorizeSchoolAccess($model) {
        $userSchoolId = Auth::user()?->school_id;
        if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action.');
        }
    }
}