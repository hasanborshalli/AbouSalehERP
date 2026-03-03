<?php

namespace App\Jobs;

use App\Mail\WorkerPaymentReceiptMail;
use App\Models\WorkerPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWorkerPaymentReceiptMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $paymentId) {}

    public function handle(): void
    {
        $payment = WorkerPayment::with('contract.worker')->findOrFail($this->paymentId);
        $email   = $payment->contract->worker->email ?? null;

        if (!$email) return;

        Mail::to($email)->send(new WorkerPaymentReceiptMail($payment));
    }
}