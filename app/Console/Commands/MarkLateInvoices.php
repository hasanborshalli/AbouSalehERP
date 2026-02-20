<?php

namespace App\Console\Commands;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkLateInvoices extends Command
{
    protected $signature = 'invoices:mark-late {--sync : Regenerate PDFs synchronously}';
    protected $description = 'Mark unpaid invoices past due date as late, apply contract late fee, regenerate PDF.';

    public function handle(): int
    {
        $today = now()->toDateString();

        $invoices = Invoice::query()
            ->whereIn('status', ['pending'])              // only unpaid
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)          // past due
            ->with('contract:id,late_fee')                // get late fee
            ->get();

        $updated = 0;

        foreach ($invoices as $invoice) {
            // Idempotency: if already late and fee applied, skip
            if ($invoice->status === 'overdue') {
                continue;
            }

            $lateFee = (float) ($invoice->contract?->late_fee ?? 0);

            $invoice->status = 'overdue';
            $invoice->late_fee_amount = $lateFee;         // applied ONCE and stored
            $invoice->late_marked_at = now();

            // optional: bump revision if you’re using revision history
                $invoice->revision = ((int) $invoice->revision) + 1;

            $invoice->save();

            // Regenerate PDF
            if ($this->option('sync')) {
                GenerateInvoicePdfJob::dispatchSync($invoice->id);
            } else {
                GenerateInvoicePdfJob::dispatch($invoice->id);
            }

            $updated++;
        }

        $this->info("Marked {$updated} invoice(s) as LATE and regenerated PDFs.");
        return self::SUCCESS;
    }
}