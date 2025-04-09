// app/Http/Controllers/Accounting/PosPaymentController.php
namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\PosTerminal;
use App\Models\PosSession;
use App\Models\Payment;
use App\Models\Contact;
use App\Models\Invoice;
use App\Services\PosPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosPaymentController extends Controller
{
    protected $paymentService;
    
    public function __construct(PosPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    public function showForm()
    {
        // Get active terminals and sessions for the current user
        $terminals = PosTerminal::where('is_active', true)
            ->when(Auth::user()->school_id, fn($q) => $q->where('school_id', Auth::user()->school_id))
            ->get();
            
        $sessions = PosSession::where('status', 'active')
            ->whereIn('pos_terminal_id', $terminals->pluck('id'))
            ->get();
            
        if ($sessions->isEmpty()) {
            return redirect()->route('accounting.pos.sessions.index')
                ->with('error', 'You need to start a POS session before processing payments.');
        }
        
        // Get students/contacts for payment selection
        $contacts = Contact::where('type', 'student')
            ->orWhere('type', 'parent')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);
            
        return view('accounting.pos.payments.form', compact('sessions', 'contacts'));
    }
    
    public function getInvoices(Request $request)
    {
        $contactId = $request->input('contact_id');
        
        if (!$contactId) {
            return response()->json(['invoices' => []]);
        }
        
        // Get unpaid invoices for the contact
        $invoices = Invoice::where(function($query) use ($contactId) {
                $query->where('contact_id', $contactId)
                    ->orWhereHas('student', function($q) use ($contactId) {
                        // If invoice belongs to student whose parent is the contact
                        $q->where('parent_id', $contactId);
                    });
            })
            ->where('status', 'issued')
            ->whereRaw('total_amount > paid_amount')
            ->orderBy('due_date')
            ->get(['id', 'invoice_number', 'issue_date', 'due_date', 'total_amount', 'paid_amount']);
            
        return response()->json(['invoices' => $invoices]);
    }
    
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'pos_session_id' => 'required|exists:pos_sessions,id',
            'contact_id' => 'required|exists:contacts,id',
            'payment_method' => 'required|in:card,cash',
            'amount' => 'required|numeric|min:0.01',
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id',
            'notes' => 'nullable|string',
        ]);
        
        // Get the session
        $session = PosSession::findOrFail($validated['pos_session_id']);
        
        // Make sure the session is active
        if ($session->status !== 'active') {
            return back()->with('error', 'The selected POS session is not active.');
        }
        
        // Get the terminal
        $terminal = $session->terminal;
        
        // Generate a unique reference
        $reference = 'PAY-' . strtoupper(Str::random(8));
        
        DB::beginTransaction();
        
        try {
            // Prepare the payment record
            $paymentData = [
                'contact_id' => $validated['contact_id'],
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'payment_date' => now(),
                'reference' => $reference,
                'notes' => $validated['notes'],
                'school_id' => Auth::user()->school_id,
                'created_by' => Auth::id(),
                'status' => 'pending', // Will be updated based on payment result
            ];
            
            // Process card payment through the terminal if needed
            if ($validated['payment_method'] === 'card') {
                $metadata = [
                    'contact_id' => $validated['contact_id'],
                    'invoice_ids' => $validated['invoice_ids'],
                    'pos_session_id' => $session->id,
                ];
                
                $result = $this->paymentService->processCardPayment(
                    $terminal, 
                    $validated['amount'], 
                    $reference, 
                    $metadata
                );
                
                if (!$result['success']) {
                    return back()->withInput()->with('error', 'Payment failed: ' . $result['message']);
                }
                
                // Update payment data with transaction details
                $paymentData['status'] = 'completed';
                $paymentData['transaction_id'] = $result['transaction_id'];
                $paymentData['authorization_code'] = $result['authorization_code'];
            } else {
                // Cash payment is immediately successful
                $paymentData['status'] = 'completed';
            }
            
            // Create the payment record
            $payment = Payment::create($paymentData);
            
            // Associate the payment with invoices
            $invoices = Invoice::whereIn('id', $validated['invoice_ids'])->get();
            
            $remainingAmount = $validated['amount'];
            
            foreach ($invoices as $invoice) {
                $outstandingAmount = $invoice->total_amount - $invoice->paid_amount;
                
                if ($remainingAmount <= 0) {
                    break;
                }
                
                $allocationAmount = min($outstandingAmount, $remainingAmount);
                
                // Create payment allocation
                $payment->invoices()->attach($invoice->id, [
                    'amount' => $allocationAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Update invoice paid amount
                $invoice->paid_amount += $allocationAmount;
                
                if ($invoice->paid_amount >= $invoice->total_amount) {
                    $invoice->status = 'paid';
                    $invoice->paid_date = now();
                }
                
                $invoice->save();
                
                $remainingAmount -= $allocationAmount;
            }
            
            // Create POS transaction record
            $session->transactions()->create([
                'payment_id' => $payment->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference' => $reference,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'authorization_code' => $paymentData['authorization_code'] ?? null,
                'created_by' => Auth::id(),
            ]);
            
            DB::commit();
            
            // Redirect to the receipt page
            return redirect()->route('accounting.payments.receipt', $payment)
                ->with('success', 'Payment processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
}