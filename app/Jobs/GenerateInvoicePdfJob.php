<?php
namespace App\Jobs;

use App\Models\Contract;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Batchable;
class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public int $invoiceId) {}

    public function handle(): void
    {
        $invoice = Invoice::findOrFail($this->invoiceId);
        $contract = Contract::with(['client', 'project', 'apartment'])->findOrFail($invoice->contract_id);
            $generatedAt = now();

        $pdf = Pdf::loadView('pdfs.invoice', [
            'invoice'  => $invoice,
            'contract' => $contract,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4');

        $path = "invoices/invoice-{$invoice->invoice_number}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);
    }
}