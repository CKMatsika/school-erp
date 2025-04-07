<?php

namespace App\Mail;

use App\Models\Accounting\Invoice; // Correct namespace for Invoice
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment; // For attaching
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf; // Use the PDF facade
use Illuminate\Support\Facades\Log; // Optional: for logging errors

class InvoiceMail extends Mailable // Optionally implement ShouldQueue for background sending
{
    use Queueable, SerializesModels;

    public Invoice $invoice; // Use PHP 8+ property promotion

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        // Eager load data needed for email subject, view, and PDF attachment
        $this->invoice = $invoice->loadMissing(['contact', 'items']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $companyName = config('app.name', 'Your Company');
        return new Envelope(
            // Optionally set the 'from' address if needed
            // from: new Address('invoices@example.com', $companyName),
            subject: 'Invoice #' . $this->invoice->invoice_number . ' from ' . $companyName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Pass the invoice object to the markdown view
        return new Content(
            markdown: 'emails.invoices.sent', // The view file created earlier
            with: [
                'invoiceUrl' => route('accounting.invoices.show', $this->invoice->id), // Example: link back to invoice
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
        try {
            // Generate PDF data directly here
            $pdf = Pdf::loadView('accounting.invoices.pdf', ['invoice' => $this->invoice]);
            $filename = 'invoice-' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->invoice->invoice_number) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate PDF attachment for InvoiceMail (Invoice ID: {$this->invoice->id}): " . $e->getMessage());
            // Don't attach if PDF fails, email will still send (or decide to fail whole email)
            return [];
        }
    }
}