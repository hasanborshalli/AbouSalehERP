<?php

namespace App\Jobs;

use App\Models\InKindPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateInKindReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $inKindPaymentId,
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        $payment = InKindPayment::with([
            'contract.client',
            'contract.project',
            'contract.apartment',
            'invoice',
            'items.inventoryItem',
        ])->findOrFail($this->inKindPaymentId);

        $receiptNo = 'INKIND-RCPT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('pdfs.in-kind-receipt', [
            'payment'   => $payment,
            'receiptNo' => $receiptNo,
        ])->setPaper('a4');

        $path = "receipts/inkind-receipt-{$payment->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $payment->update(['receipt_path' => $path]);

        // Also update the invoice's receipt_path if this payment is for an invoice
        if ($payment->invoice) {
            $payment->invoice->update(['receipt_path' => $path]);
        }
    }
}
