<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\Invoice;
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

    public function __construct(public int $invoiceId, public ?int $markedByUserId = null) {}


    public function handle(): void
    {
        $invoice = Invoice::with('contract.client')->findOrFail($this->invoiceId);
        $contract = Contract::with(['client', 'project', 'apartment'])->findOrFail($invoice->contract_id);

        $paidAt = $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at) : now();
        $date = $paidAt->timezone('Asia/Beirut')->format('Y-m-d');

        // Receipt number (best-effort without new DB column)
        $receiptNo = 'RCPT-' . $invoice->invoice_number;

        $clientName = $contract->client->name ?? 'Client';
        $amount = (float) ($invoice->amount ?? 0);
        $lateFee = (float) ($invoice->late_fee_amount ?? 0);
        $total = $amount + $lateFee;

        $paymentMethod = 'cash'; // default for now (can be upgraded later)

        $forWhat = "Payment for invoice {$invoice->invoice_number}";
        $amountNumbers = '$' . number_format($total, 2);

        // Keep Sum Of simple (you can upgrade to words later)
        $sumOf = MoneyToWords::en($total, 'USD');

        $receiverName = auth()->user()->name ?? 'Receiver';

        $pdf = Pdf::loadView('pdfs.receipt', [
            'receiptNo'      => $receiptNo,
            'date'           => $date,
            'receivedFrom'   => $clientName,
            'sumOf'          => $sumOf,
            'amountNumbers'  => $amountNumbers,
            'forWhat'        => $forWhat,
            'paymentMethod'  => $paymentMethod,
            'receiverName'   => $receiverName,
        ])->setPaper('a4');
        

        $path = "receipts/receipt-{$invoice->invoice_number}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $invoice->update(['receipt_path' => $path]);
    }
}