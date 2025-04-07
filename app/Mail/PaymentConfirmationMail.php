<?php

namespace App\Mail;

use App\Models\Accounting\Payment; // Adjust namespace if your model is elsewhere
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentConfirmationMail extends Mailable // Implement ShouldQueue for background sending
{
    use Queueable, SerializesModels;

    public Payment $payment; // Use PHP 8+ property promotion

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        // Eager load data needed for the email content
        $this->payment = $payment->loadMissing(['contact', 'invoice', 'paymentMethod']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $companyName = config('app.name', 'Your Company');
        return new Envelope(
            // from: new Address('payments@example.com', $companyName), // Optional: Specific from address
            subject: 'Payment Confirmation - Ref #' . $this->payment->payment_number . ' from ' . $companyName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Calculate balance details to pass to the view
        $invoiceBalance = null;
        $contactBalance = null; // Requires a method on Contact model, e.g., $this->payment->contact->calculateBalance()

        if ($this->payment->invoice) {
            $invoiceBalance = $this->payment->invoice->total - $this->payment->invoice->amount_paid;
        }
        // Placeholder for overall contact balance - you need to implement this calculation
        // if ($this->payment->contact) {
        //     $contactBalance = $this->payment->contact->calculateBalance(); // Implement this method
        // }


        return new Content(
            markdown: 'emails.payments.confirmation', // Path to the Blade view
            with: [
                'invoiceBalance' => $invoiceBalance, // Pass calculated invoice balance
                'contactBalance' => $contactBalance, // Pass overall contact balance (if calculated)
                // Optional: Add a URL to view the payment or invoice online
                // 'paymentUrl' => route('accounting.payments.show', $this->payment->id),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Usually payment confirmations don't have attachments unless you generate a receipt PDF
        return [];
    }
}