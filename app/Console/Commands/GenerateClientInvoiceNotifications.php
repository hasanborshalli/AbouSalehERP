<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateClientInvoiceNotifications extends Command
{
    protected $signature = 'client:invoice-notifications';
    protected $description = 'Generate client invoice reminders (issue/due/overdue/late fee)';

    public function handle(NotificationService $notify)
    {
        $today = Carbon::today();

        // Pull invoices that matter around today.
        // You can widen this window if needed.
        $invoices = Invoice::query()
            ->with('contract:id,client_user_id')
            ->whereHas('contract', fn ($q) => $q->whereNotNull('client_user_id'))
            ->where(function ($q) use ($today) {
                $q->whereDate('issue_date', '<=', $today->copy()->addDays(2))
                  ->whereDate('issue_date', '>=', $today->copy()->subDays(2));
            })
            ->orWhere(function ($q) use ($today) {
                $q->whereDate('due_date', '<=', $today->copy()->addDays(2))
                  ->whereDate('due_date', '>=', $today->copy()->subDays(10));
            })
            ->get();

        foreach ($invoices as $inv) {
            $clientUserId = (int) ($inv->contract?->client_user_id);
            if (!$clientUserId) continue;

            $invNo = $inv->invoice_number ?? ('INV-'.$inv->id);

            // 1) 2 days before issue_date
            if ($inv->issue_date) {
                $issue = Carbon::parse($inv->issue_date)->startOfDay();
                if ($today->equalTo($issue->copy()->subDays(2))) {
                    $notify->createOnce([
                        'user_id' => $clientUserId,
                        'key' => "invoice:{$inv->id}:issue_minus_2",
                        'type' => 'invoice',
                        'title' => "Invoice {$invNo} will be issued soon",
                        'message' => "Your invoice {$invNo} will be issued in 2 days.",
                        'url' => '/client/invoices/list',
                        'entity_type' => 'invoice',
                        'entity_id' => $inv->id,
                    ]);
                }

                // 2) on issue_date
                if ($today->equalTo($issue)) {
                    $notify->createOnce([
                        'user_id' => $clientUserId,
                        'key' => "invoice:{$inv->id}:issue_today",
                        'type' => 'invoice',
                        'title' => "Invoice {$invNo} issued",
                        'message' => "Invoice {$invNo} has been issued today.",
                        'url' => '/client/invoices/list',
                        'entity_type' => 'invoice',
                        'entity_id' => $inv->id,
                    ]);
                }
            }

            // 3) 2 days before due_date
            if ($inv->due_date) {
                $due = Carbon::parse($inv->due_date)->startOfDay();

                if ($today->equalTo($due->copy()->subDays(2)) && $inv->status !== 'paid') {
                    $notify->createOnce([
                        'user_id' => $clientUserId,
                        'key' => "invoice:{$inv->id}:due_minus_2",
                        'type' => 'invoice',
                        'title' => "Invoice {$invNo} due soon",
                        'message' => "Invoice {$invNo} is due in 2 days.",
                        'url' => '/client/invoices/list',
                        'entity_type' => 'invoice',
                        'entity_id' => $inv->id,
                    ]);
                }

                // 4) on due_date
                if ($today->equalTo($due) && $inv->status !== 'paid') {
                    $notify->createOnce([
                        'user_id' => $clientUserId,
                        'key' => "invoice:{$inv->id}:due_today",
                        'type' => 'invoice',
                        'title' => "Invoice {$invNo} is due today",
                        'message' => "Invoice {$invNo} is due today. Please pay on time to avoid late fees.",
                        'url' => '/client/invoices/list',
                        'entity_type' => 'invoice',
                        'entity_id' => $inv->id,
                    ]);
                }

                // 5) overdue + late fee applied
                // This assumes your overdue logic sets status='late' OR sets late_fee_applied/late_fee_amount.
                // Adapt these checks to your real columns.
                $isOverdue = $today->gt($due) && $inv->status !== 'paid';
                $lateFeeApplied = ($inv->status === 'overdue') || ((float)($inv->late_fee_amount ?? 0) > 0);

                if ($isOverdue && $lateFeeApplied) {
                    $notify->createOnce([
                        'user_id' => $clientUserId,
                        'key' => "invoice:{$inv->id}:overdue_late_fee",
                        'type' => 'invoice',
                        'title' => "Invoice {$invNo} overdue (Late fee applied)",
                        'message' => "Invoice {$invNo} is overdue and a late fee was applied.",
                        'url' => '/client/invoices/list',
                        'entity_type' => 'invoice',
                        'entity_id' => $inv->id,
                    ]);
                }
            }
        }

        $this->info('Client invoice notifications generated.');
        return 0;
    }
}