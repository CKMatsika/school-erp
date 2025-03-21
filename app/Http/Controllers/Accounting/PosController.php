<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Contact;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentMethod;
use App\Models\Accounting\PosSession;
use App\Models\Accounting\PosTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display the POS index page.
     */
    public function index()
    {
        $terminals = PosTerminal::where('is_active', true)->get();
        $activeSessions = PosSession::where('status', 'open')->get();
        
        return view('accounting.pos.index', compact('terminals', 'activeSessions'));
    }

    /**
     * Show the POS sale screen.
     */
    public function sale()
    {
        // Check if there's an active session for the current user
        $activeSession = PosSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();
            
        if (!$activeSession) {
            return redirect()->route('accounting.pos.index')
                ->with('error', 'You need to start a POS session before making a sale.');
        }
        
        $terminal = $activeSession->terminal;
        $contacts = Contact::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        
        return view('accounting.pos.sale', compact('terminal', 'activeSession', 'contacts', 'paymentMethods'));
    }

    /**
     * Process a POS payment.
     */
    public function payment(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
            'session_id' => 'required|exists:pos_sessions,id',
        ]);

        // Check if the session is open and belongs to the current user
        $session = PosSession::find($request->session_id);
        
        if (!$session || $session->user_id !== auth()->id() || $session->status !== 'open') {
            return redirect()->back()
                ->with('error', 'Invalid or closed POS session.')
                ->withInput();
        }

        // Get terminal and account
        $terminal = $session->terminal;
        $account = $terminal->defaultPaymentMethod->is_cash
            ? ChartOfAccount::where('account_code', '1000')->first() // Cash account
            : ChartOfAccount::where('is_bank_account', true)->first(); // Default bank account
        
        if (!$account) {
            return redirect()->back()
                ->with('error', 'No valid bank account found for this payment method.')
                ->withInput();
        }

        // Get school ID if available
        $schoolId = null;
        if (auth()->user()->school) {
            $schoolId = auth()->user()->school->id;
        }

        // Generate payment number
        $paymentNumber = 'POS-' . date('Ymd') . '-' . Payment::count() + 1;

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the payment
            $payment = Payment::create([
                'school_id' => $schoolId,
                'contact_id' => $request->contact_id,
                'invoice_id' => null, // POS sales don't need invoice
                'payment_number' => $paymentNumber,
                'payment_method_id' => $request->payment_method_id,
                'reference' => $request->reference,
                'payment_date' => now()->format('Y-m-d'),
                'amount' => $request->amount,
                'notes' => $request->notes,
                'status' => 'completed',
                'account_id' => $account->id,
                'pos_terminal_id' => $terminal->id,
                'is_pos_transaction' => true,
                'created_by' => auth()->id(),
            ]);

            // Create journal entries for this payment
            $this->createJournalForPosPayment($payment);

            DB::commit();

            return redirect()->route('accounting.pos.receipt', $payment)
                ->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error processing payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the POS receipt.
     */
    public function receipt(Payment $payment)
    {
        if (!$payment->is_pos_transaction) {
            return redirect()->route('accounting.payments.show', $payment);
        }
        
        $payment->load(['contact', 'paymentMethod', 'account', 'creator', 'posTerminal']);
        return view('accounting.pos.receipt', compact('payment'));
    }

    /**
     * Show the Z-reading page.
     */
    public function zReading()
    {
        // Check if there's an active session for the current user
        $activeSession = PosSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();
            
        if (!$activeSession) {
            return redirect()->route('accounting.pos.index')
                ->with('error', 'You don\'t have an active POS session.');
        }
        
        $payments = Payment::where('created_by', auth()->id())
            ->where('is_pos_transaction', true)
            ->where('status', 'completed')
            ->whereDate('created_at', now())
            ->with(['contact', 'paymentMethod'])
            ->get();
            
        $totalAmount = $payments->sum('amount');
        $paymentMethodTotals = $payments->groupBy('payment_method_id')
            ->map(function ($group) {
                return [
                    'method' => $group->first()->paymentMethod->name,
                    'total' => $group->sum('amount')
                ];
            });
        
        return view('accounting.pos.z-reading', compact(
            'activeSession', 
            'payments', 
            'totalAmount', 
            'paymentMethodTotals'
        ));
    }

    /**
     * Close a POS session.
     */
    public function closeSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if the session is open and belongs to the current user
        $session = PosSession::find($request->session_id);
        
        if (!$session || $session->user_id !== auth()->id() || $session->status !== 'open') {
            return redirect()->back()
                ->with('error', 'Invalid or already closed POS session.')
                ->withInput();
        }

        // Calculate expected balance
        $totalPayments = Payment::where('created_by', auth()->id())
            ->where('is_pos_transaction', true)
            ->where('status', 'completed')
            ->where('created_at', '>=', $session->opening_time)
            ->sum('amount');
            
        $expectedBalance = $session->opening_balance + $totalPayments;
        
        // Update session with closing information
        $session->update([
            'closing_time' => now(),
            'closing_balance' => $request->closing_balance,
            'expected_balance' => $expectedBalance,
            'difference' => $request->closing_balance - $expectedBalance,
            'notes' => $request->notes,
            'status' => 'closed',
            'z_reading_number' => 'Z-' . date('Ymd') . '-' . $session->id,
        ]);

        return redirect()->route('accounting.pos.index')
            ->with('success', 'POS session closed successfully.');
    }

    /**
     * Open a new POS session.
     */
    public function openSession(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        // Check if the terminal is available
        $terminal = PosTerminal::find($request->terminal_id);
        
        if (!$terminal || !$terminal->is_active) {
            return redirect()->back()
                ->with('error', 'Selected terminal is not available.')
                ->withInput();
        }

        // Check if there's already an open session for this terminal
        $activeSession = PosSession::where('terminal_id', $terminal->id)
            ->where('status', 'open')
            ->first();
            
        if ($activeSession) {
            return redirect()->back()
                ->with('error', 'This terminal already has an active session.')
                ->withInput();
        }

        // Check if the user already has an open session
        $userSession = PosSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();
            
        if ($userSession) {
            return redirect()->back()
                ->with('error', 'You already have an active session on another terminal.')
                ->withInput();
        }

        // Create a new session
        $session = PosSession::create([
            'terminal_id' => $terminal->id,
            'user_id' => auth()->id(),
            'opening_time' => now(),
            'opening_balance' => $request->opening_balance,
            'status' => 'open',
        ]);

        return redirect()->route('accounting.pos.sale')
            ->with('success', 'POS session started successfully.');
    }

    /**
     * Create a journal entry for a POS payment.
     */
    private function createJournalForPosPayment(Payment $payment)
    {
        // If there's already a journal for this payment, delete it
        if ($payment->journal) {
            $payment->journal->entries()->delete();
            $payment->journal->delete();
        }

        // Create a new journal entry
        $journal = Journal::create([
            'school_id' => $payment->school_id,
            'reference' => 'POS Payment #' . $payment->payment_number,
            'journal_date' => $payment->payment_date,
            'description' => 'POS payment received from ' . $payment->contact->name,
            'status' => 'posted',
            'created_by' => auth()->id(),
            'document_type' => 'payment',
            'document_id' => $payment->id,
        ]);

        // Debit Cash/Bank Account
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $payment->account_id,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => 'POS payment received',
            'contact_id' => $payment->contact_id,
        ]);

        // Find a suitable income account
        $incomeAccount = ChartOfAccount::where('account_code', '4000')->first(); // Tuition Revenue

        // Credit Income Account
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $incomeAccount->id,
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'POS payment received',
            'contact_id' => $payment->contact_id,
        ]);

        return $journal;
    }
}