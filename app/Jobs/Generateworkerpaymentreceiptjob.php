<?php

namespace App\Jobs;

use App\Models\WorkerPayment;
use App\Support\MoneyToWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateWorkerPaymentReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $paymentId) {}

    public function handle(): void
    {
        $payment  = WorkerPayment::with('contract.worker', 'contract.project')
                        ->findOrFail($this->paymentId);
        $contract = $payment->contract;
        $worker   = $contract->worker;

        $faceValue  = (float)$payment->amount;
        $paidAmount = $payment->amount_paid !== null ? (float)$payment->amount_paid : null;

        // Use amount_paid when the worker was paid more than the scheduled amount.
        $amount = ($paidAmount !== null && $paidAmount > $faceValue + 0.009)
                    ? $paidAmount
                    : $faceValue;

        $sumOf = MoneyToWords::en($amount, 'USD');
        $date  = $payment->paid_at
                    ? $payment->paid_at->format('Y-m-d')
                    : now()->format('Y-m-d');

        $pdf = Pdf::loadView('pdfs.worker-receipt', [
            'receiptNo'     => 'WP-' . $payment->payment_number,
            'date'          => $date,
            'payeeName'     => $worker->name,
            'sumOf'         => $sumOf,
            'amountNumbers' => '$' . number_format($amount, 2),
            'forWhat'       => "Payment #{$payment->installment_index} – {$contract->scope_of_work}" .
                               ($contract->project ? " (Project: {$contract->project->name})" : ''),
            'paymentMethod' => 'cash',
        ])->setPaper('a4');

        $path = "worker-receipts/receipt-{$payment->payment_number}.pdf";
        Storage::disk('public')->put($path, $pdf->output());
        $payment->update(['receipt_path' => $path]);
    }
}