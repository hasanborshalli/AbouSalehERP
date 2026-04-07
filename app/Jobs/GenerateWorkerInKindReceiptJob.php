<?php

namespace App\Jobs;

use App\Models\WorkerInKindPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateWorkerInKindReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $paymentId,
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        $payment = WorkerInKindPayment::with([
            'workerPayment',
            'workerContract.worker',
            'workerContract.project',
            'items.inventoryItem',
        ])->findOrFail($this->paymentId);

        $receiptNo = 'WRK-INKIND-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('pdfs.worker-in-kind-receipt', [
            'payment'   => $payment,
            'receiptNo' => $receiptNo,
        ])->setPaper('a4');

        $path = "receipts/worker-inkind-receipt-{$payment->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $payment->update(['receipt_path' => $path]);

        // Also update the worker payment's receipt_path
        if ($payment->workerPayment) {
            $payment->workerPayment->update(['receipt_path' => $path]);
        }
    }
}