<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateWorkerContractPdfJob;
use App\Jobs\GenerateWorkerInKindReceiptJob;
use App\Jobs\GenerateWorkerPaymentReceiptJob;
use App\Jobs\SendWorkerPaymentReceiptMailJob;
use App\Mail\WorkerCredentialsMail;
use App\Models\AuditLog;
use App\Models\ApartmentAdditionalCost;
use App\Models\InventoryItem;
use App\Models\Project;
use App\Models\ProjectAdditionalCost;
use App\Models\User;
use App\Models\WorkerContract;
use App\Models\WorkerInKindPayment;
use App\Models\WorkerInKindPaymentItem;
use App\Models\WorkerPayment;
use App\Services\CashAccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WorkersController extends Controller
{
    // ─────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────

    public function index()
    {
        $workers = User::where('role', 'worker')
            ->withCount('workerContracts')
            ->orderByDesc('created_at')
            ->get();

        return view('workers.index', compact('workers'));
    }

    // ─────────────────────────────────────────────
    // CREATE WORKER + CONTRACT
    // ─────────────────────────────────────────────

    public function createPage()
    {
        $projects         = Project::with('apartments')->orderByDesc('created_at')->get();
        $managedProperties = \App\Models\ManagedProperty::orderBy('address')->get();
        return view('workers.create', compact('projects', 'managedProperties'));
    }

    public function store(Request $request)
    {
        $workerData = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $contractData = $request->validate([
            'project_costs'      => ['nullable', 'array'],
            'project_costs.*'    => ['nullable', 'numeric', 'min:0'],
            'apartment_costs'    => ['nullable', 'array'],
            'apartment_costs.*'  => ['nullable', 'numeric', 'min:0'],
            'scope_of_work'      => ['required', 'string', 'max:500'],
            'scope_of_work_ar'   => ['nullable', 'string', 'max:500'],
            'category'           => ['nullable', 'string', 'max:80'],
            'contract_date'      => ['required', 'date'],
            'start_date'         => ['nullable', 'date'],
            'expected_end_date'  => ['nullable', 'date'],
            'total_amount'       => ['nullable', 'numeric', 'min:0.01'],
            'payment_months'     => ['required', 'integer', 'min:1', 'max:120'],
            'first_payment_date' => ['required', 'date'],
            'notes'              => ['nullable', 'string', 'max:2000'],
        ]);

        // ── Parse per-item costs ──────────────────────────────────────────────
        // project_ids[]     = all selected project IDs (reliable — hidden field enabled on check)
        // project_costs[id] = cost per project (only when a cost was entered)
        $projectIds          = array_map('intval', array_filter($request->input('project_ids', [])));
        $apartmentIds        = array_map('intval', array_filter($request->input('apartment_ids', [])));
        $managedPropertyIds  = array_map('intval', array_filter($request->input('managed_property_ids', [])));

        $rawProjectCosts          = $request->input('project_costs', []);
        $rawApartmentCosts        = $request->input('apartment_costs', []);
        $rawManagedPropertyCosts  = $request->input('managed_property_costs', []);

        // Only non-zero cost entries → used for accounting + total
        $projectCosts          = array_filter($rawProjectCosts,         fn($v) => is_numeric($v) && (float)$v > 0);
        $apartmentCosts        = array_filter($rawApartmentCosts,       fn($v) => is_numeric($v) && (float)$v > 0);
        $managedPropertyCosts  = array_filter($rawManagedPropertyCosts, fn($v) => is_numeric($v) && (float)$v > 0);
        $projectCosts          = empty($projectCosts)         ? [] : array_combine(array_map('intval', array_keys($projectCosts)),         array_map('floatval', array_values($projectCosts)));
        $apartmentCosts        = empty($apartmentCosts)       ? [] : array_combine(array_map('intval', array_keys($apartmentCosts)),       array_map('floatval', array_values($apartmentCosts)));
        $managedPropertyCosts  = empty($managedPropertyCosts) ? [] : array_combine(array_map('intval', array_keys($managedPropertyCosts)), array_map('floatval', array_values($managedPropertyCosts)));

        // Total = sum of entered costs, or fall back to manual total_amount
        $totalAmount = array_sum($projectCosts) + array_sum($apartmentCosts) + array_sum($managedPropertyCosts);
        if ($totalAmount <= 0) {
            $totalAmount = (float) ($contractData['total_amount'] ?? 0);
        }
        if ($totalAmount <= 0) {
            return back()->withErrors(['total_amount' => 'Please assign a cost to at least one project, apartment, or managed property, or enter a total amount.'])->withInput();
        }
       

        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = 'Create';
        $audit->entity_type = 'Worker';
        $audit->details     = 'Creating worker failed';
        $audit->save();
        $audit->record = 'WRK-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        return DB::transaction(function () use ($workerData,$managedPropertyIds,$managedPropertyCosts, $contractData, $audit, $request, $totalAmount, $projectIds, $apartmentIds, $projectCosts, $apartmentCosts) {
            $rawPassword = Str::password(8);

            $worker = User::create([
                'name'       => $workerData['name'],
                'email'      => $workerData['email'],
                'phone'      => $workerData['phone'],
                'role'       => 'worker',
                'is_active'  => true,
                'password'   => Hash::make($rawPassword),
                'created_by' => auth()->id(),
                'avatar'     => '/img/default-avatar.png',
            ]);

            $months = (int) $contractData['payment_months'];
            // If the user used reverse-calc mode (set monthly → get months), the form
            // submits the intended monthly amount via the hidden monthly_amount_input field.
            // Use that directly so 10 × $20,000 + last $5,000 stays correct.
            // Without it, total/months recalculation produces the wrong per-payment amount.
            $submittedMonthly = (float) ($request->input('monthly_amount_input', 0));
            if ($submittedMonthly > 0) {
                $monthlyAmount = round($submittedMonthly, 2);
            } else {
                $monthlyAmount = $months > 0 ? round($totalAmount / $months, 2) : $totalAmount;
            }
            // Last payment absorbs the remainder (handles fractional months exactly)
            $lastAmount = round($totalAmount - ($monthlyAmount * ($months - 1)), 2);

            $contract = WorkerContract::create([
                'worker_user_id'         => $worker->id,
                'project_id'             => count($projectIds) ? $projectIds[0] : null,
                'project_ids'            => $projectIds ?: null,
                'project_costs'          => $projectCosts ?: null,
                'apartment_id'           => count($apartmentIds) ? $apartmentIds[0] : null,
                'apartment_ids'          => $apartmentIds ?: null,
                'apartment_costs'        => $apartmentCosts ?: null,
                'managed_property_ids'   => $managedPropertyIds ?: null,
                'managed_property_costs' => $managedPropertyCosts ?: null,
                'scope_of_work'          => $contractData['scope_of_work'],
                'scope_of_work_ar'       => $contractData['scope_of_work_ar'] ?? null,
                'category'               => $contractData['category'] ?? null,
                'contract_date'          => $contractData['contract_date'],
                'start_date'             => $contractData['start_date'] ?? null,
                'expected_end_date'      => $contractData['expected_end_date'] ?? null,
                'total_amount'           => $totalAmount,
                'payment_months'         => $months,
                'monthly_amount'         => $monthlyAmount,
                'first_payment_date'     => $contractData['first_payment_date'],
                'notes'                  => $contractData['notes'] ?? null,
                'created_by'             => auth()->id(),
            ]);

            // ── Create ProjectAdditionalCost records ──
            foreach ($projectCosts as $pid => $cost) {
                ProjectAdditionalCost::create([
                    'project_id'      => $pid,
                    'description'     => "Worker contract: {$contractData['scope_of_work']}",
                    'category'        => 'worker_contract',
                    'expected_amount' => $cost,
                    'notes'           => "Auto-created from worker contract #{$contract->id} ({$worker->name})",
                ]);
            }

            // ── Create ApartmentAdditionalCost records ──
            foreach ($apartmentCosts as $aid => $cost) {
                ApartmentAdditionalCost::create([
                    'apartment_id'    => $aid,
                    'description'     => "Worker contract: {$contractData['scope_of_work']}",
                    'category'        => 'worker_contract',
                    'expected_amount' => $cost,
                    'notes'           => "Auto-created from worker contract #{$contract->id} ({$worker->name})",
                ]);
            }

            // ── Generate payment schedule ──────────────────────────────────────
            $firstDate = Carbon::parse($contractData['first_payment_date']);
            for ($i = 0; $i < $months; $i++) {
                $dueDate = $firstDate->copy()->addMonths($i);
                $amount  = ($i === $months - 1)
                    ? round($totalAmount - ($monthlyAmount * ($months - 1)), 2)
                    : $monthlyAmount;

                WorkerPayment::create([
                    'worker_contract_id' => $contract->id,
                    'payment_number'     => $worker->id . '-' . $contract->id . '-' . ($i + 1),
                    'installment_index'  => $i + 1,
                    'due_date'           => $dueDate,
                    'amount'             => $amount,
                    'status'             => 'pending',
                ]);
            }

            // Generate contract PDF
            GenerateWorkerContractPdfJob::dispatchSync($contract->id);
            $contract->refresh();

            // Send credentials email with contract PDF
            Mail::to($worker->email)->queue(
                new WorkerCredentialsMail($worker, $rawPassword, $contract->pdf_path)
            );

            $audit->details = "Worker {$worker->name} (ID {$worker->id}) created with contract #{$contract->id}.";
            $audit->save();

            return redirect()->route('workers.show', $worker)
                ->with('success', "Worker created and credentials emailed to {$worker->email}.");
        });
    }

    // ─────────────────────────────────────────────
    // SHOW WORKER DETAIL
    // ─────────────────────────────────────────────

    public function show(User $worker)
    {
        abort_unless($worker->role === 'worker', 404);

        $worker->load(['workerContracts.payments.inKindPayment', 'workerContracts.project']);
        $projects         = Project::with('apartments')->orderByDesc('created_at')->get();
        $managedProperties = \App\Models\ManagedProperty::orderBy('address')->get();

        return view('workers.show', compact('worker', 'projects', 'managedProperties'));
    }

    // ─────────────────────────────────────────────
    // CONTRACT DETAIL
    // ─────────────────────────────────────────────

    public function showContract(WorkerContract $contract)
    {
        $contract->load(['worker', 'project', 'payments']);
        return view('workers.contract', compact('contract'));
    }

    // ─────────────────────────────────────────────
    // MARK PAYMENT AS PAID
    // ─────────────────────────────────────────────

    public function markPaid(Request $request, WorkerPayment $payment, CashAccountingService $cash)
    {
        abort_if($payment->status === 'paid', 422, 'Already paid.');

        // Sequential enforcement: must pay in order
        $hasEarlierPending = WorkerPayment::where('worker_contract_id', $payment->worker_contract_id)
            ->where('status', 'pending')
            ->where('due_date', '<', $payment->due_date)
            ->exists();

        if ($hasEarlierPending) {
            return back()->with('error', 'Please pay earlier installments first. Payments must be in order.');
        }

        $paymentType = $request->input('payment_type', 'cash');

        // ── IN-KIND PAYMENT ───────────────────────────────────────────────
        if ($paymentType === 'in_kind') {
            $rawItems = collect($request->input('items', []))
                ->filter(fn($row) => !empty($row['inventory_item_id']) && !empty($row['quantity']))
                ->values()
                ->toArray();
            $request->merge(['items' => $rawItems]);

            $data = $request->validate([
                'items'                     => ['required', 'array', 'min:1'],
                'items.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
                'items.*.quantity'          => ['required', 'numeric', 'min:0.001'],
                'items.*.notes'             => ['nullable', 'string', 'max:255'],
                'payment_notes'             => ['nullable', 'string', 'max:1000'],
            ]);

            $inKindPayment  = null;
            $autoPaidIds    = [];
            $totalValue     = 0;
            $userId         = auth()->id();

            DB::transaction(function () use ($data, $payment, $cash, $userId, &$inKindPayment, &$autoPaidIds, &$totalValue) {
                $now = now();

                $totalValue = 0;
                $itemRows   = [];
                foreach ($data['items'] as $row) {
                    $item      = InventoryItem::findOrFail($row['inventory_item_id']);
                    $qty       = (float)$row['quantity'];
                    $unitPrice = (float)$item->price;
                    $rowVal    = round($unitPrice * $qty, 2);
                    $totalValue += $rowVal;
                    $itemRows[] = [
                        'item'      => $item,
                        'qty'       => $qty,
                        'unitPrice' => $unitPrice,
                        'rowVal'    => $rowVal,
                        'notes'     => $row['notes'] ?? null,
                    ];
                }

                // Mark payment as paid
                $payment->update([
                    'status'         => 'paid',
                    'amount_paid'    => $totalValue,
                    'paid_at'        => $now->toDateString(),
                    'marked_paid_by' => $userId,
                ]);

                // Create in-kind payment record
                $inKindPayment = WorkerInKindPayment::create([
                    'worker_payment_id'     => $payment->id,
                    'worker_contract_id'    => $payment->worker_contract_id,
                    'payment_date'          => $now->toDateString(),
                    'notes'                 => $data['payment_notes'] ?? null,
                    'created_by'            => $userId,
                    'total_estimated_value' => $totalValue,
                ]);

                foreach ($itemRows as $row) {
                    WorkerInKindPaymentItem::create([
                        'worker_in_kind_payment_id' => $inKindPayment->id,
                        'inventory_item_id'          => $row['item']->id,
                        'quantity'                   => $row['qty'],
                        'unit_price_snapshot'        => $row['unitPrice'],
                        'total_value'                => $row['rowVal'],
                        'notes'                      => $row['notes'],
                    ]);

                    // Decrement stock (company gives items to worker)
                    $item           = $row['item'];
                    $item->quantity = max(0, (float)$item->quantity - $row['qty']);
                    $item->is_out_of_stock = ($item->quantity <= 0);
                    $item->save();
                }

                // Post accounting: operating expense (in-kind value as cash-out equivalent)
                $contract = $payment->contract()->with('worker', 'project')->first();
                $cash->createOperatingExpense([
                    'expense_date' => $now->toDateString(),
                    'category'     => 'worker_payment',
                    'amount'       => $totalValue,
                    'description'  => "Worker in-kind payment #{$payment->payment_number} – {$contract->worker->name}" .
                                      ($contract->project ? " (Project: {$contract->project->name})" : ''),
                ], $userId);

                // ── Credit/deficit cascade ────────────────────────────────
                $amountDue = round((float)$payment->amount, 2);
                $credit    = round($totalValue - $amountDue, 2);

                if ($credit >= 0.01) {
                    $upcoming = WorkerPayment::where('worker_contract_id', $payment->worker_contract_id)
                        ->where('status', 'pending')
                        ->where('due_date', '>', $payment->due_date)
                        ->orderBy('due_date')
                        ->get();

                    foreach ($upcoming as $next) {
                        if ($credit < 0.01) break;
                        $nextDue = round((float)$next->amount, 2);

                        if ($credit >= $nextDue) {
                            $credit -= $nextDue;
                            $credit  = round($credit, 2);
                            $next->update([
                                'status'         => 'paid',
                                'amount_paid'    => $nextDue,
                                'paid_at'        => $now->toDateString(),
                                'marked_paid_by' => $userId,
                            ]);
                            $autoPaidIds[] = $next->id;
                        } else {
                            $next->update(['amount' => round($nextDue - $credit, 2)]);
                            $credit = 0;
                        }
                    }
                } elseif ($credit <= -0.01) {
                    $next = WorkerPayment::where('worker_contract_id', $payment->worker_contract_id)
                        ->where('status', 'pending')
                        ->where('due_date', '>', $payment->due_date)
                        ->orderBy('due_date')
                        ->first();
                    if ($next) {
                        $next->update(['amount' => round((float)$next->amount + abs($credit), 2)]);
                    }
                }
            });

            DB::afterCommit(function () use ($inKindPayment, $autoPaidIds) {
                if ($inKindPayment) {
                    \App\Jobs\GenerateWorkerInKindReceiptJob::dispatch($inKindPayment->id);
                }
                foreach ($autoPaidIds as $autoPaidId) {
                    GenerateWorkerPaymentReceiptJob::dispatch($autoPaidId);
                }
            });

            // Notify worker
            $notifService = app(\App\Services\NotificationService::class);
            $notifService->createOnce([
                'user_id'     => $payment->contract->worker_user_id,
                'key'         => 'worker_payment_received_' . $payment->id,
                'type'        => 'payment_received',
                'title'       => 'Payment Received (In-Kind)',
                'message'     => 'In-kind payment #' . $payment->installment_index . ' of $' . number_format($totalValue, 2) . ' has been processed.',
                'url'         => '/worker/payments',
                'entity_type' => 'worker_payment',
                'entity_id'   => $payment->id,
            ]);

            return back()->with('success', 'In-kind payment recorded. Stock updated.');
        }

        $data = $request->validate([
            'paid_at'     => ['nullable', 'date'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = 'Update';
        $audit->entity_type = 'Worker Payment';
        $audit->details     = "Marking worker payment {$payment->payment_number} as paid failed";
        $audit->save();
        $audit->record = 'WRK-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        $paidAt     = ($data['paid_at'] ?? null) ? Carbon::parse($data['paid_at']) : now();
        $amountDue  = round((float) $payment->amount, 2);
        $amountPaid = isset($data['amount_paid']) && $data['amount_paid'] !== null
            ? round((float) $data['amount_paid'], 2)
            : $amountDue; // blank = exact payment

        $payment->update([
            'status'         => 'paid',
            'amount_paid'    => $amountPaid,
            'paid_at'        => $paidAt->toDateString(),
            'marked_paid_by' => auth()->id(),
        ]);

        // Post as cash-out for the amount actually paid
        $contract = $payment->contract()->with('worker', 'project')->first();
        $cash->createOperatingExpense([
            'expense_date' => $paidAt->toDateString(),
            'category'     => 'worker_payment',
            'amount'       => $amountPaid,
            'description'  => "Worker payment {$payment->payment_number} – {$contract->worker->name}" .
                              ($contract->project ? " (Project: {$contract->project->name})" : ''),
        ], auth()->id());

        // ── Apply over/underpayment to next pending payments ─────────────
        // Credit cascades forward; deficit hits the next payment only.
        $credit          = round($amountPaid - $amountDue, 2);
        $autoPaidIds     = []; // IDs of payments auto-covered by the credit

        if ($credit >= 0.01) {
            // Overpayment: mark subsequent pending payments as paid until credit runs out.
            // IMPORTANT: do NOT post additional expenses here — the $amountPaid already
            // represents the full cash-out. Auto-paid payments are just allocation of that
            // credit, not a new outflow.
            $upcoming = WorkerPayment::where('worker_contract_id', $payment->worker_contract_id)
                ->where('status', 'pending')
                ->where('due_date', '>', $payment->due_date)
                ->orderBy('due_date')
                ->get();

            foreach ($upcoming as $next) {
                if ($credit < 0.01) break;
                $nextDue = round((float) $next->amount, 2);

                if ($credit >= $nextDue) {
                    $credit -= $nextDue;
                    $credit  = round($credit, 2);
                    $next->update([
                        'status'         => 'paid',
                        'amount_paid'    => $nextDue,
                        'paid_at'        => $paidAt->toDateString(),
                        'marked_paid_by' => auth()->id(),
                    ]);
                    $autoPaidIds[] = $next->id;
                } else {
                    // Partial credit — reduce the next payment's amount, no status change
                    $next->update(['amount' => round($nextDue - $credit, 2)]);
                    $credit = 0;
                }
            }

        } elseif ($credit <= -0.01) {
            // Underpayment: add deficit to the very next pending payment only
            $next = WorkerPayment::where('worker_contract_id', $payment->worker_contract_id)
                ->where('status', 'pending')
                ->where('due_date', '>', $payment->due_date)
                ->orderBy('due_date')
                ->first();
            if ($next) {
                $next->update(['amount' => round((float) $next->amount + abs($credit), 2)]);
            }
        }

        // Receipt PDF + email for the manually paid payment, then receipts for auto-paid ones
        // The job reads amount_paid from the DB and uses it when it exceeds the face value.
        DB::afterCommit(function () use ($payment, $autoPaidIds) {
            Bus::chain([
                new GenerateWorkerPaymentReceiptJob($payment->id),
                new SendWorkerPaymentReceiptMailJob($payment->id),
            ])->dispatch();

            // Generate receipts for auto-paid payments
            foreach ($autoPaidIds as $autoPaidId) {
                GenerateWorkerPaymentReceiptJob::dispatch($autoPaidId);
            }
        });

        // Notify the worker
        $notifService = app(\App\Services\NotificationService::class);
        $notifService->createOnce([
            'user_id'     => $payment->contract->worker_user_id,
            'key'         => 'worker_payment_received_' . $payment->id,
            'type'        => 'payment_received',
            'title'       => 'Payment Received',
            'message'     => 'Payment #' . $payment->installment_index . ' of $' . number_format($amountPaid, 2) . ' has been processed for: ' . $payment->contract->scope_of_work,
            'url'         => '/worker/payments',
            'entity_type' => 'worker_payment',
            'entity_id'   => $payment->id,
        ]);

        $audit->details = "Worker payment {$payment->payment_number} marked as paid (\${$amountPaid}).";
        $audit->save();

        return back()->with('success', 'Payment marked as paid and receipt emailed to worker.');
    }

    // ─────────────────────────────────────────────
    // ADD CONTRACT TO EXISTING WORKER
    // ─────────────────────────────────────────────

    public function addContract(Request $request, User $worker)
    {
        abort_unless($worker->role === 'worker', 404);

        $contractData = $request->validate([
            'project_costs'      => ['nullable', 'array'],
            'project_costs.*'    => ['nullable', 'numeric', 'min:0'],
            'apartment_costs'    => ['nullable', 'array'],
            'apartment_costs.*'  => ['nullable', 'numeric', 'min:0'],
            'scope_of_work'      => ['required', 'string', 'max:500'],
            'scope_of_work_ar'   => ['nullable', 'string', 'max:500'],
            'category'           => ['nullable', 'string', 'max:80'],
            'contract_date'      => ['required', 'date'],
            'start_date'         => ['nullable', 'date'],
            'expected_end_date'  => ['nullable', 'date'],
            'total_amount'       => ['nullable', 'numeric', 'min:0.01'],
            'payment_months'     => ['required', 'integer', 'min:1', 'max:120'],
            'first_payment_date' => ['required', 'date'],
            'notes'              => ['nullable', 'string', 'max:2000'],
        ]);

        $projectIds          = array_map('intval', array_filter($request->input('project_ids', [])));
        $apartmentIds        = array_map('intval', array_filter($request->input('apartment_ids', [])));
        $managedPropertyIds  = array_map('intval', array_filter($request->input('managed_property_ids', [])));

        $rawProjectCosts          = $request->input('project_costs', []);
        $rawApartmentCosts        = $request->input('apartment_costs', []);
        $rawManagedPropertyCosts  = $request->input('managed_property_costs', []);

        $projectCosts          = array_filter($rawProjectCosts,         fn($v) => is_numeric($v) && (float)$v > 0);
        $apartmentCosts        = array_filter($rawApartmentCosts,       fn($v) => is_numeric($v) && (float)$v > 0);
        $managedPropertyCosts  = array_filter($rawManagedPropertyCosts, fn($v) => is_numeric($v) && (float)$v > 0);
        $projectCosts          = empty($projectCosts)         ? [] : array_combine(array_map('intval', array_keys($projectCosts)),         array_map('floatval', array_values($projectCosts)));
        $apartmentCosts        = empty($apartmentCosts)       ? [] : array_combine(array_map('intval', array_keys($apartmentCosts)),       array_map('floatval', array_values($apartmentCosts)));
        $managedPropertyCosts  = empty($managedPropertyCosts) ? [] : array_combine(array_map('intval', array_keys($managedPropertyCosts)), array_map('floatval', array_values($managedPropertyCosts)));

        $totalAmount = array_sum($projectCosts) + array_sum($apartmentCosts) + array_sum($managedPropertyCosts);
        if ($totalAmount <= 0) {
            $totalAmount = (float) ($contractData['total_amount'] ?? 0);
        }
        if ($totalAmount <= 0) {
            return back()->withErrors(['total_amount' => 'Please assign a cost to at least one project, apartment, or managed property, or enter a total amount.'])->withInput();
        }

        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = 'Create';
        $audit->entity_type = 'Worker Contract';
        $audit->details     = "Adding contract to worker {$worker->name} failed";
        $audit->save();
        $audit->record = 'WRK-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        DB::transaction(function () use ($request,$worker,$managedPropertyIds,$managedPropertyCosts, $contractData, $audit, $totalAmount, $projectIds, $apartmentIds, $projectCosts, $apartmentCosts) {
            $months = (int) $contractData['payment_months'];
            // If the user used reverse-calc mode (set monthly → get months), the form
            // submits the intended monthly amount via the hidden monthly_amount_input field.
            // Use that directly so 10 × $20,000 + last $5,000 stays correct.
            // Without it, total/months recalculation produces the wrong per-payment amount.
            $submittedMonthly = (float) ($request->input('monthly_amount_input', 0));
            if ($submittedMonthly > 0) {
                $monthlyAmount = round($submittedMonthly, 2);
            } else {
                $monthlyAmount = $months > 0 ? round($totalAmount / $months, 2) : $totalAmount;
            }
            // Last payment absorbs the remainder (handles fractional months exactly)
            $lastAmount = round($totalAmount - ($monthlyAmount * ($months - 1)), 2);

            $contract = WorkerContract::create([
                'worker_user_id'         => $worker->id,
                'project_id'             => count($projectIds) ? $projectIds[0] : null,
                'project_ids'            => $projectIds ?: null,
                'project_costs'          => $projectCosts ?: null,
                'apartment_id'           => count($apartmentIds) ? $apartmentIds[0] : null,
                'apartment_ids'          => $apartmentIds ?: null,
                'apartment_costs'        => $apartmentCosts ?: null,
                'managed_property_ids'   => $managedPropertyIds ?: null,
                'managed_property_costs' => $managedPropertyCosts ?: null,
                'scope_of_work'          => $contractData['scope_of_work'],
                'scope_of_work_ar'       => $contractData['scope_of_work_ar'] ?? null,
                'category'               => $contractData['category'] ?? null,
                'contract_date'          => $contractData['contract_date'],
                'start_date'             => $contractData['start_date'] ?? null,
                'expected_end_date'      => $contractData['expected_end_date'] ?? null,
                'total_amount'           => $totalAmount,
                'payment_months'         => $months,
                'monthly_amount'         => $monthlyAmount,
                'first_payment_date'     => $contractData['first_payment_date'],
                'notes'                  => $contractData['notes'] ?? null,
                'created_by'             => auth()->id(),
            ]);

            foreach ($projectCosts as $pid => $cost) {
                ProjectAdditionalCost::create([
                    'project_id'      => $pid,
                    'description'     => "Worker contract: {$contractData['scope_of_work']}",
                    'category'        => 'worker_contract',
                    'expected_amount' => $cost,
                    'notes'           => "Auto-created from worker contract #{$contract->id} ({$worker->name})",
                ]);
            }

            foreach ($apartmentCosts as $aid => $cost) {
                ApartmentAdditionalCost::create([
                    'apartment_id'    => $aid,
                    'description'     => "Worker contract: {$contractData['scope_of_work']}",
                    'category'        => 'worker_contract',
                    'expected_amount' => $cost,
                    'notes'           => "Auto-created from worker contract #{$contract->id} ({$worker->name})",
                ]);
            }

            $firstDate = Carbon::parse($contractData['first_payment_date']);
            for ($i = 0; $i < $months; $i++) {
                $amount = ($i === $months - 1)
                    ? round($totalAmount - ($monthlyAmount * ($months - 1)), 2)
                    : $monthlyAmount;
                WorkerPayment::create([
                    'worker_contract_id' => $contract->id,
                    'payment_number'     => $worker->id . '-' . $contract->id . '-' . ($i + 1),
                    'installment_index'  => $i + 1,
                    'due_date'           => $firstDate->copy()->addMonths($i),
                    'amount'             => $amount,
                    'status'             => 'pending',
                ]);
            }

            GenerateWorkerContractPdfJob::dispatchSync($contract->id);

            $audit->details = "New contract #{$contract->id} added to worker {$worker->name}.";
            $audit->save();
        });

        return redirect()->route('workers.show', $worker)
            ->with('success', 'Contract added successfully.');
    }

    // ─────────────────────────────────────────────
    // CONTRACT PDF
    // ─────────────────────────────────────────────

    public function contractPdf(WorkerContract $contract)
    {
        abort_unless($contract->pdf_path && \Storage::disk('public')->exists($contract->pdf_path), 404);
        return response()->file(\Storage::disk('public')->path($contract->pdf_path));
    }

    public function contractPdfDownload(WorkerContract $contract)
    {
        abort_unless($contract->pdf_path && \Storage::disk('public')->exists($contract->pdf_path), 404);
        return response()->download(\Storage::disk('public')->path($contract->pdf_path), "WorkerContract-{$contract->id}.pdf");
    }

    // ─────────────────────────────────────────────
    // PAYMENT RECEIPT
    // ─────────────────────────────────────────────

    public function paymentReceiptDownload(WorkerPayment $payment)
    {
        abort_unless($payment->receipt_path && \Storage::disk('public')->exists($payment->receipt_path), 404);
        return response()->download(\Storage::disk('public')->path($payment->receipt_path), "Receipt-{$payment->payment_number}.pdf");
    }

    public function inKindReceiptDownload(WorkerPayment $payment)
    {
        $inKind = $payment->inKindPayment;
        abort_unless($inKind && $inKind->receipt_path && \Storage::disk('public')->exists($inKind->receipt_path), 404);
        return response()->download(\Storage::disk('public')->path($inKind->receipt_path), "InKind-Receipt-{$payment->payment_number}.pdf");
    }
}