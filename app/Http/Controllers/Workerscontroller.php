<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateWorkerContractPdfJob;
use App\Jobs\GenerateWorkerPaymentReceiptJob;
use App\Jobs\SendWorkerPaymentReceiptMailJob;
use App\Mail\WorkerCredentialsMail;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkerContract;
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
        $projects   = Project::orderByDesc('created_at')->get(['id', 'name', 'code']);
        $apartments = \App\Models\Apartment::with('project')->orderBy('id')->get();
        return view('workers.create', compact('projects', 'apartments'));
    }

    public function store(Request $request)
    {
        $workerData = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $contractData = $request->validate([
            'project_ids'        => ['nullable', 'array'],
            'project_ids.*'      => ['exists:projects,id'],
            'apartment_ids'      => ['nullable', 'array'],
            'apartment_ids.*'    => ['exists:apartments,id'],
            'scope_of_work'      => ['required', 'string', 'max:500'],
            'category'           => ['nullable', 'string', 'max:80'],
            'contract_date'      => ['required', 'date'],
            'start_date'         => ['nullable', 'date'],
            'expected_end_date'  => ['nullable', 'date'],
            'total_amount'       => ['required', 'numeric', 'min:0.01'],
            'payment_months'     => ['required', 'integer', 'min:1', 'max:120'],
            'first_payment_date' => ['required', 'date'],
            'notes'              => ['nullable', 'string', 'max:2000'],
        ]);

        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = 'Create';
        $audit->entity_type = 'Worker';
        $audit->details     = 'Creating worker failed';
        $audit->save();
        $audit->record = 'WRK-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        return DB::transaction(function () use ($workerData, $contractData, $audit, $request) {
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

            $totalAmount   = (float) $contractData['total_amount'];
            $months        = (int)   $contractData['payment_months'];
            $monthlyAmount = round($totalAmount / $months, 2);

            $projectIds   = $contractData['project_ids']   ?? [];
            $apartmentIds = $contractData['apartment_ids'] ?? [];
            $contract = WorkerContract::create([
                'worker_user_id'    => $worker->id,
                'project_id'        => count($projectIds) ? $projectIds[0] : null,
                'project_ids'       => $projectIds ?: null,
                'apartment_id'      => count($apartmentIds) ? $apartmentIds[0] : null,
                'apartment_ids'     => $apartmentIds ?: null,
                'scope_of_work'     => $contractData['scope_of_work'],
                'category'          => $contractData['category'] ?? null,
                'contract_date'     => $contractData['contract_date'],
                'start_date'        => $contractData['start_date'] ?? null,
                'expected_end_date' => $contractData['expected_end_date'] ?? null,
                'total_amount'      => $totalAmount,
                'payment_months'    => $months,
                'monthly_amount'    => $monthlyAmount,
                'first_payment_date'=> $contractData['first_payment_date'],
                'notes'             => $contractData['notes'] ?? null,
                'created_by'        => auth()->id(),
            ]);

            // Generate payment schedule
            $firstDate = Carbon::parse($contractData['first_payment_date']);
            for ($i = 0; $i < $months; $i++) {
                $dueDate = $firstDate->copy()->addMonths($i);
                // last installment absorbs rounding difference
                $amount = ($i === $months - 1)
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

        $worker->load(['workerContracts.payments', 'workerContracts.project']);
        $projects   = Project::orderByDesc('created_at')->get(['id', 'name', 'code']);
        $apartments = \App\Models\Apartment::with('project')->orderBy('id')->get();

        return view('workers.show', compact('worker', 'projects', 'apartments'));
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

        $data = $request->validate([
            'paid_at' => ['nullable', 'date'],
        ]);

        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = 'Update';
        $audit->entity_type = 'Worker Payment';
        $audit->details     = "Marking worker payment {$payment->payment_number} as paid failed";
        $audit->save();
        $audit->record = 'WRK-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        $paidAt = $data['paid_at'] ? Carbon::parse($data['paid_at']) : now();

        $payment->update([
            'status'         => 'paid',
            'paid_at'        => $paidAt->toDateString(),
            'marked_paid_by' => auth()->id(),
        ]);

        // Post as cash-out (we pay the worker)
        $contract = $payment->contract()->with('worker', 'project')->first();
        $cash->createOperatingExpense([
            'expense_date' => $paidAt->toDateString(),
            'category'     => 'worker_payment',
            'amount'       => (float) $payment->amount,
            'description'  => "Worker payment {$payment->payment_number} – {$contract->worker->name}" .
                              ($contract->project ? " (Project: {$contract->project->name})" : ''),
        ], auth()->id());

        // Generate receipt PDF then send email
        DB::afterCommit(function () use ($payment) {
            Bus::chain([
                new GenerateWorkerPaymentReceiptJob($payment->id),
                new SendWorkerPaymentReceiptMailJob($payment->id),
            ])->dispatch();
        });

        // Notify the worker
        $notifService = app(\App\Services\NotificationService::class);
        $notifService->createOnce([
            'user_id'     => $payment->contract->worker_user_id,
            'key'         => 'worker_payment_received_' . $payment->id,
            'type'        => 'payment_received',
            'title'       => 'Payment Received',
            'message'     => 'Payment #' . $payment->installment_index . ' of $' . number_format($payment->amount, 2) . ' has been processed for: ' . $payment->contract->scope_of_work,
            'url'         => '/worker/payments',
            'entity_type' => 'worker_payment',
            'entity_id'   => $payment->id,
        ]);

        $audit->details = "Worker payment {$payment->payment_number} marked as paid (\${$payment->amount}).";
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
            'project_ids'        => ['nullable', 'array'],
            'project_ids.*'      => ['exists:projects,id'],
            'apartment_ids'      => ['nullable', 'array'],
            'apartment_ids.*'    => ['exists:apartments,id'],
            'scope_of_work'      => ['required', 'string', 'max:500'],
            'category'           => ['nullable', 'string', 'max:80'],
            'contract_date'      => ['required', 'date'],
            'start_date'         => ['nullable', 'date'],
            'expected_end_date'  => ['nullable', 'date'],
            'total_amount'       => ['required', 'numeric', 'min:0.01'],
            'payment_months'     => ['required', 'integer', 'min:1', 'max:120'],
            'first_payment_date' => ['required', 'date'],
            'notes'              => ['nullable', 'string', 'max:2000'],
        ]);

        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = 'Create';
        $audit->entity_type = 'Worker Contract';
        $audit->details     = "Adding contract to worker {$worker->name} failed";
        $audit->save();
        $audit->record = 'WRK-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        DB::transaction(function () use ($worker, $contractData, $audit) {
            $totalAmount   = (float) $contractData['total_amount'];
            $months        = (int)   $contractData['payment_months'];
            $monthlyAmount = round($totalAmount / $months, 2);

            $projectIds   = $contractData['project_ids']   ?? [];
            $apartmentIds = $contractData['apartment_ids'] ?? [];
            $contract = WorkerContract::create([
                'worker_user_id'    => $worker->id,
                'project_id'        => count($projectIds) ? $projectIds[0] : null,
                'project_ids'       => $projectIds ?: null,
                'apartment_id'      => count($apartmentIds) ? $apartmentIds[0] : null,
                'apartment_ids'     => $apartmentIds ?: null,
                'scope_of_work'     => $contractData['scope_of_work'],
                'category'          => $contractData['category'] ?? null,
                'contract_date'     => $contractData['contract_date'],
                'start_date'        => $contractData['start_date'] ?? null,
                'expected_end_date' => $contractData['expected_end_date'] ?? null,
                'total_amount'      => $totalAmount,
                'payment_months'    => $months,
                'monthly_amount'    => $monthlyAmount,
                'first_payment_date'=> $contractData['first_payment_date'],
                'notes'             => $contractData['notes'] ?? null,
                'created_by'        => auth()->id(),
            ]);

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
}