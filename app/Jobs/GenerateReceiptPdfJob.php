<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use App\Support\MoneyToWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReceiptPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int  $invoiceId,
        public ?int $markedByUserId = null,
    ) {}

    public function handle(): void
    {
        $invoice  = Invoice::with('contract.client')->findOrFail($this->invoiceId);
        $contract = Contract::with(['client', 'project', 'apartment'])
                        ->findOrFail($invoice->contract_id);

        $paidAt     = $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at) : now();
        $date       = $paidAt->timezone('Asia/Beirut')->format('Y-m-d');
        $clientName = $contract->client->name ?? 'Client';

        // Face value of this invoice (amount + any late fee)
        $faceValue  = (float)($invoice->amount ?? 0) + (float)($invoice->late_fee_amount ?? 0);

        // Use the actual amount paid when the client paid more than the face value.
        // This correctly handles overpayments on any invoice (including the last one).
        // amount_paid is set by the controller at the time of marking paid.
        $amountPaid = $invoice->amount_paid !== null ? (float)$invoice->amount_paid : null;
        $total      = ($amountPaid !== null && $amountPaid > $faceValue + 0.009)
                        ? $amountPaid
                        : $faceValue;

        $receiptNo     = 'RCPT-' . $invoice->invoice_number;
        $amountNumbers = '$' . number_format($total, 2);
        $sumOf         = MoneyToWords::en($total, 'USD');
        $forWhat       = 'Payment for invoice ' . $invoice->invoice_number
                            . ($contract->apartment ? ' – Apt ' . ($contract->apartment->unit_number ?? '') : '')
                            . ($contract->project   ? ' – ' . $contract->project->name : '');
        $receiverName  = User::find($this->markedByUserId)?->name ?? 'Receiver';

        $projectNameAr = $contract->project ? ($contract->project->name_ar ?? $contract->project->name) : null;
        $aptUnit       = $contract->apartment->unit_number ?? null;

        $pdf = Pdf::loadView('pdfs.receipt', [
            'receiptNo'     => $receiptNo,
            'date'          => $date,
            'receivedFrom'  => $clientName,
            'sumOf'         => $sumOf,
            'amountNumbers' => $amountNumbers,
            'forWhat'       => $forWhat,
            'forWhatAr1'    => 'فاتورة رقم ' . $invoice->invoice_number,
            'forWhatAr2'    => $aptUnit ? 'شقة ' . $aptUnit : null,
            'forWhatAr3'    => $projectNameAr ? 'مشروع: ' . $projectNameAr : null,
            'paymentMethod' => 'cash',
            'receiverName'  => $receiverName,
        ])->setPaper('a4');

        $path = "receipts/receipt-{$invoice->invoice_number}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $invoice->update(['receipt_path' => $path]);
    }
}