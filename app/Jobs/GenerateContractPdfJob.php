<?php

namespace App\Jobs;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Batchable;

class GenerateContractPdfJob implements ShouldQueue
{
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $contractId)
    {
        //
    }

    /**
     * Execute the job.
     */
       public function handle(): void
{
    $contract = Contract::with(['client','project','apartment'])->findOrFail($this->contractId);
        $generatedAt = now();

    $pdf = Pdf::loadView('pdfs.contract', ['contract' => $contract,'generatedAt' => $generatedAt,])
        ->setPaper('a4')
        ->setOption('isRemoteEnabled', true); // ok temporarily

    $bytes = $pdf->output();
    // ✅ Check if output really is a PDF
    if (substr($bytes, 0, 5) !== '%PDF-') {
        \Log::error('Contract PDF output is NOT PDF', [
            'contract_id' => $contract->id,
            'head' => substr($bytes, 0, 200),
            'len' => strlen($bytes),
        ]);
        throw new \RuntimeException('Contract output is not a valid PDF');
    }

    $path = "contracts/contract-{$contract->id}.pdf";
    Storage::disk('public')->put($path, $bytes);

    $contract->update(['pdf_path' => $path]);
}

}