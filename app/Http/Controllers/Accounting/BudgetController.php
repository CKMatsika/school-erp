<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        
        // Query budgets with proper filtering and relationships
        try {
            if (Schema::hasTable('budgets')) {
                $budgets = DB::table('budgets')
                    ->when($school_id, fn($query) => $query->where('school_id', $school_id))
                    ->orderBy('fiscal_year', 'desc')
                    ->orderBy('name')
                    ->get();
            } else {
                // If table doesn't exist, use placeholder data
                $budgets = $this->getPlaceholderBudgets();
            }
        } catch (\Exception $e) {
            // Fallback to placeholders if there's any error
            $budgets = $this->getPlaceholderBudgets();
        }
        
        return view('accounting.budgets.index', compact('budgets'));
    }
    
    /**
     * Show the form for creating a new budget.
     */
    public function create()
    {
        return view('accounting.budgets.create');
    }
    
    /**
     * Store a newly created budget in storage.
     */
    public function store(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        
        // Basic validation rules that don't require table checking
        $validationRules = [
            'name' => 'required|string|max:255',
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,draft,closed',
            'description' => 'nullable|string',
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|string|max:255',
            'category_amounts' => 'required|array|min:1',
            'category_amounts.*' => 'required|numeric|min:0',
        ];
        
        // Add unique rule only if the budgets table exists
        if (Schema::hasTable('budgets')) {
            $validationRules['name'] = [
                'required', 
                'string', 
                'max:255',
                Rule::unique('budgets')->where(function ($query) use ($school_id) {
                    return $query->where('school_id', $school_id)
                        ->where('fiscal_year', request('fiscal_year'));
                })
            ];
        }
        
        // Validate the budget data
        $validated = $request->validate($validationRules);
        
        try {
            DB::beginTransaction();
            
            // Check if the budgets table exists, create it if not
            if (!Schema::hasTable('budgets')) {
                $this->createBudgetTables();
            }
            
            // Create the budget record
            $budget = DB::table('budgets')->insertGetId([
                'school_id' => $school_id,
                'name' => $validated['name'],
                'fiscal_year' => $validated['fiscal_year'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_amount' => $validated['total_amount'],
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Create budget categories
            $categories = $request->input('categories');
            $amounts = $request->input('category_amounts');
            
            for ($i = 0; $i < count($categories); $i++) {
                if (!empty($categories[$i]) && isset($amounts[$i])) {
                    DB::table('budget_categories')->insert([
                        'budget_id' => $budget,
                        'school_id' => $school_id,
                        'name' => $categories[$i],
                        'amount' => $amounts[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('accounting.budgets.show', $budget)
                ->with('success', 'Budget created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create budget: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified budget.
     */
    public function show($id)
    {
        $school_id = Auth::user()?->school_id;
        
        try {
            // Check if the budgets table exists, if not use placeholder data
            if (!Schema::hasTable('budgets')) {
                $budget = $this->getPlaceholderBudget($id);
                return view('accounting.budgets.show', compact('budget'));
            }
            
            // Get the budget with its categories
            $budget = DB::table('budgets')
                ->where('id', $id)
                ->when($school_id, fn($query) => $query->where('school_id', $school_id))
                ->first();
                
            if (!$budget) {
                return redirect()->route('accounting.budgets.index')
                    ->with('error', 'Budget not found.');
            }
            
            // Get budget categories
            $categories = DB::table('budget_categories')
                ->where('budget_id', $id)
                ->get();
                
            // Calculate spending for each category (placeholder)
            foreach ($categories as $category) {
                $category->spent = 0; // In a real app, this would be calculated from transactions
            }
            
            $budget->categories = $categories;
            
            // Get transactions for this budget (placeholder)
            $budget->transactions = collect();
            
            // Calculate total spent
            $budget->spent_amount = $categories->sum('spent');
            
            return view('accounting.budgets.show', compact('budget'));
            
        } catch (\Exception $e) {
            // If there's an error, use placeholder data
            $budget = $this->getPlaceholderBudget($id);
            return view('accounting.budgets.show', compact('budget'));
        }
    }
    
    /**
     * Show the form for editing the specified budget.
     */
    public function edit($id)
    {
        $school_id = Auth::user()?->school_id;
        
        try {
            // Check if the budgets table exists, if not use placeholder data
            if (!Schema::hasTable('budgets')) {
                $budget = $this->getPlaceholderBudget($id);
                return view('accounting.budgets.edit', compact('budget'));
            }
            
            // Get the budget with its categories
            $budget = DB::table('budgets')
                ->where('id', $id)
                ->when($school_id, fn($query) => $query->where('school_id', $school_id))
                ->first();
                
            if (!$budget) {
                return redirect()->route('accounting.budgets.index')
                    ->with('error', 'Budget not found.');
            }
            
            // Get budget categories
            $budget->categories = DB::table('budget_categories')
                ->where('budget_id', $id)
                ->get();
            
            return view('accounting.budgets.edit', compact('budget'));
            
        } catch (\Exception $e) {
            // If there's an error, use placeholder data
            $budget = $this->getPlaceholderBudget($id);
            return view('accounting.budgets.edit', compact('budget'));
        }
    }
    
    /**
     * Update the specified budget in storage.
     */
    public function update(Request $request, $id)
    {
        $school_id = Auth::user()?->school_id;
        
        // Basic validation rules that don't require table checking
        $validationRules = [
            'name' => 'required|string|max:255',
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,draft,closed',
            'description' => 'nullable|string',
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|string|max:255',
            'category_amounts' => 'required|array|min:1',
            'category_amounts.*' => 'required|numeric|min:0',
        ];
        
        // Add unique rule only if the budgets table exists
        if (Schema::hasTable('budgets')) {
            $validationRules['name'] = [
                'required', 
                'string', 
                'max:255',
                Rule::unique('budgets')->where(function ($query) use ($school_id, $id) {
                    return $query->where('school_id', $school_id)
                        ->where('fiscal_year', request('fiscal_year'))
                        ->where('id', '!=', $id);
                })
            ];
        }
        
        // Validate the budget data
        $validated = $request->validate($validationRules);
        
        try {
            DB::beginTransaction();
            
            // Check if the budgets table exists, create it if not
            if (!Schema::hasTable('budgets')) {
                $this->createBudgetTables();
            }
            
            // Update the budget record
            DB::table('budgets')
                ->where('id', $id)
                ->when($school_id, fn($query) => $query->where('school_id', $school_id))
                ->update([
                    'name' => $validated['name'],
                    'fiscal_year' => $validated['fiscal_year'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'total_amount' => $validated['total_amount'],
                    'status' => $validated['status'],
                    'description' => $validated['description'] ?? null,
                    'updated_at' => now(),
                ]);
            
            // Delete existing categories
            DB::table('budget_categories')
                ->where('budget_id', $id)
                ->delete();
            
            // Create new budget categories
            $categories = $request->input('categories');
            $amounts = $request->input('category_amounts');
            
            for ($i = 0; $i < count($categories); $i++) {
                if (!empty($categories[$i]) && isset($amounts[$i])) {
                    DB::table('budget_categories')->insert([
                        'budget_id' => $id,
                        'school_id' => $school_id,
                        'name' => $categories[$i],
                        'amount' => $amounts[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('accounting.budgets.show', $id)
                ->with('success', 'Budget updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update budget: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified budget from storage.
     */
    public function destroy($id)
    {
        $school_id = Auth::user()?->school_id;
        
        try {
            // If budgets table doesn't exist, redirect with a message
            if (!Schema::hasTable('budgets')) {
                return redirect()->route('accounting.budgets.index')
                    ->with('warning', 'Budget system is not fully set up yet.');
            }
            
            // Check if there are related transactions or other dependencies
            // (This is a placeholder for actual dependency checking)
            $hasTransactions = false;
            
            if ($hasTransactions) {
                return back()->with('error', 'Cannot delete this budget because it has associated transactions.');
            }
            
            DB::beginTransaction();
            
            // Delete budget categories
            DB::table('budget_categories')
                ->where('budget_id', $id)
                ->delete();
            
            // Delete the budget
            DB::table('budgets')
                ->where('id', $id)
                ->when($school_id, fn($query) => $query->where('school_id', $school_id))
                ->delete();
            
            DB::commit();
            
            return redirect()->route('accounting.budgets.index')
                ->with('success', 'Budget deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete budget: ' . $e->getMessage());
        }
    }
    
    /**
     * Get a placeholder budget for testing purposes.
     */
    private function getPlaceholderBudget($id)
    {
        $budget = (object)[
            'id' => $id,
            'name' => 'Sample Budget ' . $id,
            'fiscal_year' => date('Y'),
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'total_amount' => 100000,
            'spent_amount' => 45000,
            'status' => 'active',
            'description' => 'This is a placeholder budget for demonstration purposes.',
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subWeeks(1),
        ];
        
        // Add sample categories
        $budget->categories = collect([
            (object)[
                'id' => 1,
                'budget_id' => $id,
                'name' => 'Salaries',
                'amount' => 60000,
                'spent' => 25000,
            ],
            (object)[
                'id' => 2,
                'budget_id' => $id,
                'name' => 'Supplies',
                'amount' => 15000,
                'spent' => 8000,
            ],
            (object)[
                'id' => 3,
                'budget_id' => $id,
                'name' => 'Facilities',
                'amount' => 25000,
                'spent' => 12000,
            ],
        ]);
        
        // Add sample transactions
        $budget->transactions = collect([
            (object)[
                'id' => 1,
                'date' => now()->subDays(30),
                'description' => 'Staff payment',
                'category' => 'Salaries',
                'amount' => -15000,
            ],
            (object)[
                'id' => 2,
                'date' => now()->subDays(20),
                'description' => 'Office supplies',
                'category' => 'Supplies',
                'amount' => -5000,
            ],
            (object)[
                'id' => 3,
                'date' => now()->subDays(10),
                'description' => 'Rent payment',
                'category' => 'Facilities',
                'amount' => -8000,
            ],
        ]);
        
        return $budget;
    }
    
    /**
     * Get placeholder budgets for the index page.
     */
    private function getPlaceholderBudgets()
    {
        return collect([
            (object)[
                'id' => 1,
                'name' => 'Annual Operating Budget',
                'fiscal_year' => date('Y'),
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'total_amount' => 250000,
                'status' => 'active'
            ],
            (object)[
                'id' => 2,
                'name' => 'Capital Improvements',
                'fiscal_year' => date('Y'),
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'total_amount' => 75000,
                'status' => 'draft'
            ]
        ]);
    }
    
    /**
     * Create budget tables if they don't exist.
     * This is a helper method for development/demo purposes.
     */
    private function createBudgetTables()
    {
        // Create budgets table
        Schema::create('budgets', function ($table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('name');
            $table->integer('fiscal_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('school_id');
        });
        
        // Create budget_categories table
        Schema::create('budget_categories', function ($table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            
            $table->index('budget_id');
            $table->index('school_id');
        });
    }
}