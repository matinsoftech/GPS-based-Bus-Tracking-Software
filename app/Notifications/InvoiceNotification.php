<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceNotification extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $invoice = $this->invoice;

        return [
            'type' => 'invoice_issued',
            'title' => 'New Invoice Issued',
            'message' => 'Invoice '.$invoice->invoice_number.' of '.$invoice->currency.' '
                .number_format((float) $invoice->amount, 2).' has been issued.',
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'school_id' => $invoice->school_id,
            'amount' => $invoice->amount,
            'status' => $invoice->status,
        ];
    }
}
