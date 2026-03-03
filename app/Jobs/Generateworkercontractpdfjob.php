<?php

namespace App\Jobs;

use App\Models\WorkerContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateWorkerContractPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $contractId) {}

    public function handle(): void
    {
        $contract = WorkerContract::with(['worker', 'project'])->findOrFail($this->contractId);

        $pdf = Pdf::loadView('pdfs.worker-contract', ['contract' => $contract])
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        $bytes = $pdf->output();

        if (substr($bytes, 0, 5) !== '%PDF-') {
            throw new \RuntimeException('Worker contract PDF output is not valid.');
        }

        $path = "worker-contracts/contract-{$contract->id}.pdf";
        Storage::disk('public')->put($path, $bytes);
        $contract->update(['pdf_path' => $path]);
    }
}