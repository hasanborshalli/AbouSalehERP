<?php

namespace App\Http\Controllers;

use App\Mail\ClientCredentialsMail;
use App\Models\Apartment;
use App\Models\AuditLog;
use App\Models\ClientProfile;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use App\Jobs\GenerateContractPdfJob;
use App\Jobs\GenerateInvoicePdfJob;

class ClientsController extends Controller
{
    public function createClient(Request $request){
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Create';
        $audit->entity_type='Client';
        $audit->details='Creating client failed';
        $audit->save();
        $audit->record='CL-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        //fields used to create user
        $userFields=$request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            
        ]);
        $userFields['role']='client';
        $userFields['is_active']=true;
        $rawPassword = Str::password(6);
        $userFields['password'] = Hash::make($rawPassword);
        $userFields['created_by']=auth()->id();
        
        //fields used to create contract
        $contractFields = $request->validate([
            'apartment_id' => [
                'required',
                'integer',
                Rule::exists('apartments', 'id')
                    ->whereNull('deleted_at'), // if apartments soft delete
            ],

            'contract_date' => ['required', 'date'],
            'payment_start_date' => ['required', 'date', 'after_or_equal:contract_date'],

            'discount' => ['nullable', 'numeric', 'min:0'],
            'down_payment' => ['required', 'numeric', 'min:0'],

            'installment_months' => ['required', 'integer', 'min:1', 'max:600'],
            'installment_amount' => ['required', 'numeric', 'min:0'],

            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $apartment = Apartment::with(['floor.project'])->findOrFail($contractFields['apartment_id']);
        $contractFields['project_id']=$apartment->project_id;
        $contractFields['created_by']=auth()->id();
        $totalPrice = (float) $apartment->price_total; // or price_total if that’s your column
        $contractFields['total_price']=$totalPrice;
        $discount   = (float) ($contractFields['discount'] ?? 0);

        if ($discount > $totalPrice) {
            return back()
                ->withInput()
                ->with(['error' => 'Discount cannot be greater than total price.']);
        }

        $contractFields['final_price'] = $totalPrice - $discount;
        if ($apartment->status === 'sold') {
        return back()
        ->withInput()
        ->with(['error' => 'This apartment is already sold.']);
        }
        $down = (float) $contractFields['down_payment'];
        $months = (int) $contractFields['installment_months'];
        $inst = (float) $contractFields['installment_amount'];
        $monthly = (float) $request->installment_amount;
        $totalPlanned = $down + ($months * $inst);

        if ($totalPlanned > $contractFields['final_price'] + 0.00001) {
            return back()->withInput()->with([
                'error' => 'Total payments exceed the final price.',
            ]);
        }
        
        $user = null;
$contract = null;
$invoiceIds = [];

// IMPORTANT: define $clientFields (see section 1)
$clientFields = []; // <-- replace with your real validate(...) if needed

DB::transaction(function () use (
    &$user,
    &$contract,
    &$invoiceIds,
    $userFields,
    $clientFields,
    $contractFields,
    $apartment,
    $months,
    $rawPassword,
    $monthly
) {
    // 1) Create user + profile
    $user = User::create($userFields);

    $clientFields['user_id'] = $user->id;
    ClientProfile::create($clientFields);

    // 2) Create contract
    $contractFields['client_user_id'] = $user->id;
    $contract = Contract::create($contractFields);

    // 3) Reserve apartment
    $apartment->update(['status' => 'reserved']);

    // 4) Create invoices in DB ONLY (NO PDF generation here)
    $start = Carbon::parse($contract->payment_start_date)->startOfDay();

    for ($i = 0; $i < $months; $i++) {
        $issueDate = $start->copy()->addMonths($i);
        $dueDate = $issueDate->copy()->addDays(7);

        $invoiceNumber = sprintf(
  "INV-%06d-%s-%03d",
  $contract->id,
  $issueDate->format('Ym'),
  $i + 1
);


        $inv = Invoice::create([
            'contract_id' => $contract->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => $issueDate->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'amount' => $monthly,
            'status' => 'pending',
           
        ]);

        $invoiceIds[] = $inv->id;
    }

    // 5) Optional: mark contract processing state if you added the columns
    if (Schema::hasColumn('contracts', 'processing_status')) {
        $contract->update([
            'processing_status' => 'queued',
            'processing_progress' => 0,
            'processing_error' => null,
        ]);
    }

    // 6) Dispatch jobs AFTER COMMIT
    DB::afterCommit(function () use ($contract, $user, $invoiceIds, $rawPassword) {

    // ✅ Choice A: send credentials immediately (no attachment)
    Mail::to($user->email)->queue(
        new ClientCredentialsMail($user, $rawPassword, null)
    );

    $jobs = [];
    $jobs[] = new GenerateContractPdfJob($contract->id);

    foreach ($invoiceIds as $invoiceId) {
        $jobs[] = new GenerateInvoicePdfJob($invoiceId);
    }

    Bus::batch($jobs)
        ->name("Contract {$contract->id} docs")
        ->then(function () use ($contract) {
            // optional: update status when done
            $contract->refresh();

            if (Schema::hasColumn('contracts', 'processing_status')) {
                $contract->update([
                    'processing_status' => 'done',
                    'processing_progress' => 100,
                    'processing_finished_at' => now(),
                ]);
            }
        })
        ->catch(function (\Throwable $e) use ($contract) {
            if (Schema::hasColumn('contracts', 'processing_status')) {
                $contract->update([
                    'processing_status' => 'failed',
                    'processing_error' => $e->getMessage(),
                    'processing_finished_at' => now(),
                ]);
            }
        })
        ->dispatch();

    if (Schema::hasColumn('contracts', 'processing_status')) {
        $contract->update([
            'processing_status' => 'processing',
            'processing_started_at' => now(),
        ]);
    }
});

});

// return FAST
$audit->details='Creating client ('.$user->name.') succeeded';
$audit->save();
return redirect('/clients')->with('success', 'Client created. Documents are processing in background…');

}
public function contractProcessingStatus(Contract $contract)
{
    return response()->json([
        'status' => $contract->processing_status,
        'progress' => $contract->processing_progress,
        'error' => $contract->processing_error,
        'pdf_path' => $contract->pdf_path,
    ]);
}
public function destroy(User $user): JsonResponse
{
            $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Delete';
        $audit->entity_type='Client';
        $audit->details='Deleting client ('.$user->name.') failed';
        $audit->save();
        $audit->record='CL-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
    DB::transaction(function () use ($user) {

        // Load contract + invoices + apartment (assumes 1 active contract)
        $contract = $user->contracts()
            ->with(['invoices', 'apartment'])
            ->latest('id')
            ->first();

        if ($contract) {
            // Free apartment (adjust status if needed)
            if ($contract->apartment) {
                $contract->apartment->update(['status' => 'available']);
            }

            // Delete invoice PDF files
            foreach ($contract->invoices as $inv) {
                if ($inv->pdf_path) {
                    Storage::disk('public')->delete($inv->pdf_path);
                }
            }

            // Delete contract PDF file
            if ($contract->pdf_path) {
                Storage::disk('public')->delete($contract->pdf_path);
            }

            // ✅ Delete invoices rows (query builder, not collection)
            $contract->invoices()->delete();

            // Delete contract row
            $contract->delete();
        }

        // ✅ Delete client profile row (query builder)
        $user->clientProfile()->delete();

        // Delete user (soft delete because User uses SoftDeletes)
        $user->delete();
    });
    $audit->details='Deleting client ('.$user->name.') succeeded';
        $audit->save();
    return response()->json(['message' => 'Client deleted']);
}
public function update(Request $request, User $user)
{
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Update';
        $audit->entity_type='Client';
        $audit->details='Updating client '.$user->name.' failed';
        $audit->save();
        $audit->record='CL-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
    $userFields = $request->validate([
        'name'  => ['required','string','max:255'],
        'email' => ['required','email','max:255'],
        'phone' => ['required','string','max:30'],
    ]);

    $contractFields = $request->validate([
        'apartment_id' => ['required','integer', Rule::exists('apartments','id')->whereNull('deleted_at')],
        'contract_date' => ['required','date'],
        'payment_start_date' => ['required','date','after_or_equal:contract_date'],
        'discount' => ['nullable','numeric','min:0'],
        'down_payment' => ['required','numeric','min:0'],
        'installment_months' => ['required','integer','min:1','max:600'],
        'installment_amount' => ['required','numeric','min:0'],
        'late_fee' => ['nullable','numeric','min:0'],
        'notes' => ['nullable','string','max:2000'],
    ]);

    $contract = $user->contracts()->with('invoices')->latest()->firstOrFail();
    $oldApartmentId = $contract->apartment_id;

    // Guard: cannot edit if any invoice is already paid
    if ($contract->invoices->where('status', 'paid')->count() > 0) {
        return back()->with('error', 'This client cannot be edited because they have paid invoices.');
    }

    $months  = (int)$contractFields['installment_months'];
    $monthly = (float)$contractFields['installment_amount'];

    // recompute total/final based on apartment price
    $apartment = Apartment::findOrFail($contractFields['apartment_id']);
    $totalPrice = (float) $apartment->price_total;
    $discount = (float) ($contractFields['discount'] ?? 0);

    if ($discount > $totalPrice) {
        return back()->withInput()->with('error','Discount cannot be greater than total price.');
    }

    $contractFields['total_price'] = $totalPrice;
    $contractFields['final_price'] = $totalPrice - $discount;
    $contractFields['project_id']=$apartment->project_id;
    // optional: validate plan
    $down = (float)$contractFields['down_payment'];
    $totalPlanned = $down + ($months * $monthly);
    if ($totalPlanned > $contractFields['final_price'] + 0.00001) {
        return back()->withInput()->with('error','Total payments exceed the final price.');
    }

    $invoiceIds = [];

    DB::transaction(function () use (
        $user, $userFields,
        $contract, $contractFields,
        $apartment, $oldApartmentId,
        $months, $monthly,
        &$invoiceIds
    ) {
        // 1) update user
        $user->update($userFields);

        // 2) free old apartment if changed
        if ($oldApartmentId && $oldApartmentId != $contractFields['apartment_id']) {
            Apartment::where('id',$oldApartmentId)->update(['status' => 'available']);
        }

        // 3) reserve new apartment
        $apartment->update(['status' => 'reserved']);

        // 4) bump contract revision
        $contract->revision = ($contract->revision ?? 1) + 1;

        // 5) update contract fields
        $contract->fill($contractFields);
        $contract->save();

        // 6) delete old PDFs (contract + invoices) and null pdf_path
        if ($contract->pdf_path) {
            Storage::disk('public')->delete($contract->pdf_path);
            $contract->pdf_path = null;
            $contract->save();
        }

        foreach ($contract->invoices as $inv) {
            if ($inv->pdf_path) Storage::disk('public')->delete($inv->pdf_path);
            $inv->update(['pdf_path' => null]);
        }

        // 7) soft-delete old invoices (full rebuild)
        $contract->invoices()->delete();

        // 8) create NEW invoices based on NEW schedule
        $start = Carbon::parse($contract->payment_start_date)->startOfDay();
        $rev = $contract->revision;

        for ($i=0; $i<$months; $i++) {
            $issueDate = $start->copy()->addMonths($i);
            $dueDate = $issueDate->copy()->addDays(7);

            $invoiceNumber = sprintf(
                "INV-%06d-%s-%03d-R%d",
                $contract->id,
                $issueDate->format('Ym'),
                $i + 1,
                $rev
            );

            $inv = Invoice::create([
                'contract_id' => $contract->id,
                'invoice_number' => $invoiceNumber,
                'issue_date' => $issueDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'amount' => $monthly,
                'status' => 'pending',
            ]);

            $invoiceIds[] = $inv->id;
        }

        // 9) mark processing flags
        $contract->update([
            'processing_status' => 'queued',
            'processing_progress' => 0,
            'processing_error' => null,
        ]);

        // 10) dispatch after commit
        DB::afterCommit(function () use ($contract, $invoiceIds) {
            $jobs = [];
            $jobs[] = new \App\Jobs\GenerateContractPdfJob($contract->id);
            foreach ($invoiceIds as $id) $jobs[] = new \App\Jobs\GenerateInvoicePdfJob($id);

            Bus::batch($jobs)
                ->name("Rebuild contract {$contract->id} PDFs")
                ->then(function () use ($contract) {
                    $contract->refresh();
                    $contract->update([
                        'processing_status' => 'done',
                        'processing_progress' => 100,
                        'processing_finished_at' => now(),
                    ]);
                })
                ->catch(function (\Throwable $e) use ($contract) {
                    $contract->update([
                        'processing_status' => 'failed',
                        'processing_error' => $e->getMessage(),
                        'processing_finished_at' => now(),
                    ]);
                })
                ->dispatch();

            $contract->update([
                'processing_status' => 'processing',
                'processing_started_at' => now(),
            ]);
        });
    });
    $audit->details='Updating client '.$user->name.' succeeded';
    $audit->save();
    return redirect('/clients')->with('success', 'Client updated. New invoices & PDFs are processing…');
}

}