<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\InKindPayment;
use App\Models\InKindPaymentItem;
use App\Models\InventoryItem;
use App\Models\InventoryPurchase;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\OperatingExpense;
use App\Models\Project;
use App\Models\Apartment;
use App\Models\User;
use App\Models\WorkerContract;
use App\Models\WorkerPayment;
use App\Models\ManagedProperty;
use App\Models\ManagedPropertyExpense;
use App\Models\ManagedPropertyRental;
use App\Models\ManagedPropertyRentalPayment;
use App\Models\ManagedPropertySale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ExportController extends Controller
{
    public function exportZip(Request $request)
    {
        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = 'Export';
        $audit->entity_type = 'Zip Archive';
        $audit->details     = 'Exporting zip archive failed';
        $audit->save();
        $audit->record = 'EXP-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        $disk = Storage::disk('public');

        $zipName    = 'backup-' . now()->format('Y-m-d_H-i-s') . '.zip';
        $tmpZipPath = storage_path('app/' . $zipName);

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create zip file.');
        }

        // PDFs
        $this->addFolderToZip($zip, $disk, 'contracts',        'pdfs/contracts');
        $this->addFolderToZip($zip, $disk, 'invoices',         'pdfs/invoices');
        $this->addFolderToZip($zip, $disk, 'receipts',         'pdfs/receipts');
        $this->addFolderToZip($zip, $disk, 'worker-contracts', 'pdfs/worker-contracts');
        $this->addFolderToZip($zip, $disk, 'worker-receipts',      'pdfs/worker-receipts');
        $this->addFolderToZip($zip, $disk, 'managed-properties',    'pdfs/managed-properties');

        // CSV data sheets
        $zip->addFromString('data/clients.csv',               $this->clientsCsv());
        $zip->addFromString('data/contracts.csv',             $this->contractsCsv());
        $zip->addFromString('data/invoices.csv',              $this->invoicesCsv());
        $zip->addFromString('data/in_kind_payments.csv',      $this->inKindPaymentsCsv());
        $zip->addFromString('data/in_kind_payment_items.csv', $this->inKindPaymentItemsCsv());
        $zip->addFromString('data/inventory_items.csv',       $this->inventoryItemsCsv());
        $zip->addFromString('data/inventory_purchases.csv',   $this->inventoryPurchasesCsv());
        $zip->addFromString('data/operating_expenses.csv',    $this->operatingExpensesCsv());
        $zip->addFromString('data/ledger_entries.csv',        $this->ledgerEntriesCsv());
        $zip->addFromString('data/projects.csv',              $this->projectsCsv());
        $zip->addFromString('data/apartments.csv',            $this->apartmentsCsv());
        $zip->addFromString('data/workers.csv',               $this->workersCsv());
        $zip->addFromString('data/worker_contracts.csv',      $this->workerContractsCsv());
        $zip->addFromString('data/worker_payments.csv',       $this->workerPaymentsCsv());
        $zip->addFromString('data/employees.csv',             $this->employeesCsv());
        $zip->addFromString('data/managed_properties.csv',        $this->managedPropertiesCsv());
        $zip->addFromString('data/managed_property_expenses.csv', $this->managedPropertyExpensesCsv());
        $zip->addFromString('data/managed_property_rentals.csv',  $this->managedPropertyRentalsCsv());
        $zip->addFromString('data/managed_property_rental_payments.csv', $this->managedPropertyRentalPaymentsCsv());
        $zip->addFromString('data/managed_property_sales.csv',    $this->managedPropertySalesCsv());
        $zip->addFromString('data/README.txt',                $this->readme());

        $zip->close();

        $audit->details = 'Exporting zip archive succeeded';
        $audit->save();

        return response()->download($tmpZipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function addFolderToZip(ZipArchive $zip, $disk, string $folder, string $zipFolder): void
    {
        if (!$disk->exists($folder)) return;
        foreach ($disk->allFiles($folder) as $path) {
            if (!Str::endsWith(Str::lower($path), '.pdf')) continue;
            $relative = ltrim(Str::after($path, $folder), '/\\');
            $zip->addFromString($zipFolder . '/' . $relative, $disk->get($path));
        }
    }

    private function clientsCsv(): string
    {
        $rows = User::where('role', 'client')
            ->select(['id','name','email','phone','is_active','created_at'])
            ->orderBy('id')->get();

        return $this->toCsv(
            ['id','name','email','phone','is_active','created_at'],
            $rows->map(fn($u) => [
                $u->id, $u->name, $u->email, $u->phone,
                $u->is_active ? 'yes' : 'no',
                optional($u->created_at)->toDateTimeString(),
            ])->all()
        );
    }

    private function contractsCsv(): string
    {
        $rows = Contract::with(['client','apartment','project'])->orderBy('id')->get();

        return $this->toCsv(
            ['id','client_name','project','apartment','contract_date',
             'total_price','discount','final_price',
             'payment_type','down_payment','installment_months','installment_amount',
             'payment_start_date','late_fee','in_kind_notes','status','notes'],
            $rows->map(fn($c) => [
                $c->id,
                optional($c->client)->name,
                optional($c->project)->name,
                optional($c->apartment)->unit_number,
                optional($c->contract_date)->format('Y-m-d'),
                $c->total_price, $c->discount, $c->final_price,
                $c->payment_type,
                $c->down_payment, $c->installment_months, $c->installment_amount,
                optional($c->payment_start_date)->format('Y-m-d'),
                $c->late_fee, $c->in_kind_notes, $c->status, $c->notes,
            ])->all()
        );
    }

    private function invoicesCsv(): string
    {
        $rows = Invoice::with(['contract.client'])->orderBy('id')->get();

        return $this->toCsv(
            ['id','invoice_number','contract_id','client_name',
             'amount','late_fee_amount','status','issue_date','due_date','paid_at'],
            $rows->map(fn($inv) => [
                $inv->id, $inv->invoice_number, $inv->contract_id,
                optional(optional($inv->contract)->client)->name,
                $inv->amount, $inv->late_fee_amount, $inv->status,
                optional($inv->issue_date)->format('Y-m-d'),
                optional($inv->due_date)->format('Y-m-d'),
                optional($inv->paid_at)->toDateTimeString(),
            ])->all()
        );
    }

    private function inKindPaymentsCsv(): string
    {
        $rows = InKindPayment::with(['contract.client','contract.apartment','invoice'])
            ->orderBy('id')->get();

        return $this->toCsv(
            ['id','contract_id','client_name','apartment','invoice_number',
             'payment_date','total_estimated_value','notes','receipt_path'],
            $rows->map(fn($p) => [
                $p->id, $p->contract_id,
                optional(optional($p->contract)->client)->name,
                optional(optional($p->contract)->apartment)->unit_number,
                optional($p->invoice)->invoice_number,
                optional($p->payment_date)->format('Y-m-d'),
                $p->total_estimated_value, $p->notes, $p->receipt_path,
            ])->all()
        );
    }

    private function inKindPaymentItemsCsv(): string
    {
        $rows = InKindPaymentItem::with(['payment.contract.client','inventoryItem'])
            ->orderBy('id')->get();

        return $this->toCsv(
            ['id','in_kind_payment_id','client_name','inventory_item',
             'quantity','unit_price_snapshot','total_value','notes'],
            $rows->map(fn($item) => [
                $item->id, $item->in_kind_payment_id,
                optional(optional(optional($item->payment)->contract)->client)->name,
                optional($item->inventoryItem)->name,
                $item->quantity, $item->unit_price_snapshot, $item->total_value, $item->notes,
            ])->all()
        );
    }

    private function inventoryItemsCsv(): string
    {
        $rows = InventoryItem::withTrashed()->orderBy('id')->get();

        return $this->toCsv(
            ['id','name','type','unit','price','quantity','is_out_of_stock','deleted_at'],
            $rows->map(fn($i) => [
                $i->id, $i->name, $i->type, $i->unit, $i->price, $i->quantity,
                $i->is_out_of_stock ? 'yes' : 'no',
                optional($i->deleted_at)->toDateTimeString(),
            ])->all()
        );
    }

    private function inventoryPurchasesCsv(): string
    {
        $rows = InventoryPurchase::with('item')->orderBy('id')->get();

        return $this->toCsv(
            ['id','receipt_ref','inventory_item','purchase_date','qty',
             'unit_cost','total_cost','vendor_name','payment_method','notes','voided','void_reason'],
            $rows->map(fn($p) => [
                $p->id, $p->receipt_ref,
                optional($p->item)->name,
                optional($p->purchase_date)->format('Y-m-d'),
                $p->qty, $p->unit_cost, $p->total_cost,
                $p->vendor_name, $p->payment_method, $p->notes,
                $p->voided_at ? 'yes' : 'no',
                $p->void_reason,
            ])->all()
        );
    }

    private function operatingExpensesCsv(): string
    {
        $rows = OperatingExpense::orderBy('id')->get();

        return $this->toCsv(
            ['id','expense_date','category','amount','description','voided','void_reason'],
            $rows->map(fn($e) => [
                $e->id,
                optional($e->expense_date)->format('Y-m-d'),
                $e->category, $e->amount, $e->description,
                $e->voided_at ? 'yes' : 'no',
                $e->void_reason,
            ])->all()
        );
    }

    private function ledgerEntriesCsv(): string
    {
        $rows = LedgerEntry::with('account')->orderBy('id')->get();

        return $this->toCsv(
            ['id','posted_at','account_code','account_name',
             'amount','direction','description','source_type','source_id'],
            $rows->map(fn($e) => [
                $e->id,
                optional($e->posted_at)->toDateTimeString(),
                optional($e->account)->code,
                optional($e->account)->name,
                $e->amount, $e->direction, $e->description,
                $e->source_type, $e->source_id,
            ])->all()
        );
    }

    private function projectsCsv(): string
    {
        $rows = Project::orderBy('id')->get();

        return $this->toCsv(
            ['id','name','code','city','area','address','status',
             'start_date','estimated_completion_date','notes'],
            $rows->map(fn($p) => [
                $p->id, $p->name, $p->code, $p->city, $p->area, $p->address, $p->status,
                optional($p->start_date)->format('Y-m-d'),
                optional($p->estimated_completion_date)->format('Y-m-d'),
                $p->notes,
            ])->all()
        );
    }

    private function apartmentsCsv(): string
    {
        $rows = Apartment::with(['project','floor','contract.client'])
            ->withTrashed()->orderBy('id')->get();

        return $this->toCsv(
            ['id','project','floor','unit_number','area_sqm',
             'bedrooms','bathrooms','price_total','status','client_name','notes'],
            $rows->map(fn($a) => [
                $a->id,
                optional($a->project)->name,
                optional($a->floor)->floor_number,
                $a->unit_number, $a->area_sqm, $a->bedrooms, $a->bathrooms,
                $a->price_total, $a->status,
                optional(optional($a->contract)->client)->name,
                $a->notes,
            ])->all()
        );
    }

    private function workersCsv(): string
    {
        $rows = User::where('role', 'worker')
            ->select(['id','name','email','phone','is_active','created_at'])
            ->orderBy('id')->get();

        return $this->toCsv(
            ['id','name','email','phone','is_active','created_at'],
            $rows->map(fn($u) => [
                $u->id, $u->name, $u->email, $u->phone,
                $u->is_active ? 'yes' : 'no',
                optional($u->created_at)->toDateTimeString(),
            ])->all()
        );
    }

    private function workerContractsCsv(): string
    {
        $rows = WorkerContract::with(['worker','project'])->orderBy('id')->get();

        return $this->toCsv(
            ['id','worker_name','project','scope_of_work','category',
             'contract_date','start_date','expected_end_date',
             'total_amount','payment_months','monthly_amount','first_payment_date','notes'],
            $rows->map(fn($wc) => [
                $wc->id,
                optional($wc->worker)->name,
                optional($wc->project)->name,
                $wc->scope_of_work, $wc->category,
                optional($wc->contract_date)->format('Y-m-d'),
                optional($wc->start_date)->format('Y-m-d'),
                optional($wc->expected_end_date)->format('Y-m-d'),
                $wc->total_amount, $wc->payment_months,
                $wc->monthly_amount,
                optional($wc->first_payment_date)->format('Y-m-d'),
                $wc->notes,
            ])->all()
        );
    }

    private function workerPaymentsCsv(): string
    {
        $rows = WorkerPayment::with(['contract.worker'])->orderBy('id')->get();

        return $this->toCsv(
            ['id','payment_number','worker_name','installment_index',
             'due_date','paid_at','amount','status'],
            $rows->map(fn($wp) => [
                $wp->id, $wp->payment_number,
                optional(optional($wp->contract)->worker)->name,
                $wp->installment_index,
                optional($wp->due_date)->format('Y-m-d'),
                optional($wp->paid_at)->toDateTimeString(),
                $wp->amount, $wp->status,
            ])->all()
        );
    }

    private function employeesCsv(): string
    {
        $rows = User::whereIn('role', ['owner','admin'])
            ->select(['id','name','email','phone','role','is_active','created_at'])
            ->orderBy('id')->get();

        return $this->toCsv(
            ['id','name','email','phone','role','is_active','created_at'],
            $rows->map(fn($u) => [
                $u->id, $u->name, $u->email, $u->phone, $u->role,
                $u->is_active ? 'yes' : 'no',
                optional($u->created_at)->toDateTimeString(),
            ])->all()
        );
    }

    private function managedPropertiesCsv(): string
    {
        $rows = ManagedProperty::withTrashed()->orderBy('id')->get();

        return $this->toCsv(
            ['id','type','status','owner_name','owner_phone','owner_email',
             'address','city','area','area_sqm','bedrooms','bathrooms',
             'owner_asking_price','estimated_renovation_cost',
             'agreed_listing_price','agreed_rent_price','company_commission_pct',
             'agreement_date','notes'],
            $rows->map(fn($p) => [
                $p->id, $p->type, $p->status,
                $p->owner_name, $p->owner_phone, $p->owner_email,
                $p->address, $p->city, $p->area,
                $p->area_sqm, $p->bedrooms, $p->bathrooms,
                $p->owner_asking_price, $p->estimated_renovation_cost,
                $p->agreed_listing_price, $p->agreed_rent_price, $p->company_commission_pct,
                optional($p->agreement_date)->format('Y-m-d'),
                $p->notes,
            ])->all()
        );
    }

    private function managedPropertyExpensesCsv(): string
    {
        $rows = ManagedPropertyExpense::with('property')->orderBy('id')->get();

        return $this->toCsv(
            ['id','managed_property_id','property_address','description','category',
             'amount','expense_date','vendor_name','notes','voided','void_reason'],
            $rows->map(fn($e) => [
                $e->id, $e->managed_property_id,
                optional($e->property)->address,
                $e->description, $e->category, $e->amount,
                optional($e->expense_date)->format('Y-m-d'),
                $e->vendor_name, $e->notes,
                $e->voided_at ? 'yes' : 'no',
                $e->void_reason,
            ])->all()
        );
    }

    private function managedPropertyRentalsCsv(): string
    {
        $rows = ManagedPropertyRental::with('property')->orderBy('id')->get();

        return $this->toCsv(
            ['id','managed_property_id','property_address',
             'tenant_name','tenant_phone','tenant_email',
             'monthly_rent','owner_monthly_share','company_monthly_commission',
             'deposit_amount','deposit_returned_at',
             'start_date','end_date','actual_end_date','status','notes'],
            $rows->map(fn($r) => [
                $r->id, $r->managed_property_id,
                optional($r->property)->address,
                $r->tenant_name, $r->tenant_phone, $r->tenant_email,
                $r->monthly_rent, $r->owner_monthly_share, $r->company_monthly_commission,
                $r->deposit_amount,
                optional($r->deposit_returned_at)->toDateTimeString(),
                optional($r->start_date)->format('Y-m-d'),
                optional($r->end_date)->format('Y-m-d'),
                optional($r->actual_end_date)->format('Y-m-d'),
                $r->status, $r->notes,
            ])->all()
        );
    }

    private function managedPropertyRentalPaymentsCsv(): string
    {
        $rows = ManagedPropertyRentalPayment::with(['rental.property'])->orderBy('id')->get();

        return $this->toCsv(
            ['id','rental_id','property_address','tenant_name',
             'due_date','amount_due','owner_share','company_commission',
             'amount_collected','collected_at',
             'owner_paid_amount','owner_paid_at','status','notes'],
            $rows->map(fn($p) => [
                $p->id, $p->managed_property_rental_id,
                optional(optional($p->rental)->property)->address,
                optional($p->rental)->tenant_name,
                optional($p->due_date)->format('Y-m-d'),
                $p->amount_due, $p->owner_share, $p->company_commission,
                $p->amount_collected,
                optional($p->collected_at)->toDateTimeString(),
                $p->owner_paid_amount,
                optional($p->owner_paid_at)->toDateTimeString(),
                $p->status, $p->notes,
            ])->all()
        );
    }

    private function managedPropertySalesCsv(): string
    {
        $rows = ManagedPropertySale::with('property')->orderBy('id')->get();

        return $this->toCsv(
            ['id','managed_property_id','property_address',
             'buyer_name','buyer_phone','buyer_email',
             'sale_price','sale_date',
             'owner_payout_amount','owner_paid_at','notes'],
            $rows->map(fn($s) => [
                $s->id, $s->managed_property_id,
                optional($s->property)->address,
                $s->buyer_name, $s->buyer_phone, $s->buyer_email,
                $s->sale_price,
                optional($s->sale_date)->format('Y-m-d'),
                $s->owner_payout_amount,
                optional($s->owner_paid_at)->toDateTimeString(),
                $s->notes,
            ])->all()
        );
    }

    private function readme(): string
    {
        $now  = now()->toDateTimeString();
        $user = optional(auth()->user())->name ?? 'Unknown';
        return <<<TXT
Mohasabe ERP — Data Export
Generated : {$now}
Generated by: {$user}

FILES IN THIS ARCHIVE
─────────────────────────────────────────────────────────────

pdfs/contracts/             Contract PDFs
pdfs/invoices/              Invoice PDFs
pdfs/receipts/              Payment receipts (cash & in-kind)
pdfs/worker-contracts/      Worker contract PDFs
pdfs/worker-receipts/       Worker payment receipts
pdfs/managed-properties/    Managed property agreement PDFs

data/clients.csv                        Client accounts
data/contracts.csv                      Apartment sales contracts (cash & in-kind)
data/invoices.csv                       Invoice records
data/in_kind_payments.csv               In-kind payment headers
data/in_kind_payment_items.csv          Items delivered per in-kind payment
data/inventory_items.csv                Inventory catalogue
data/inventory_purchases.csv            Inventory purchase history
data/operating_expenses.csv             Operating expenses
data/ledger_entries.csv                 Full accounting ledger
data/projects.csv                       Projects
data/apartments.csv                     Apartment units
data/workers.csv                        Worker accounts
data/worker_contracts.csv               Worker contracts
data/worker_payments.csv                Worker payment schedules
data/employees.csv                      Admin / owner accounts
data/managed_properties.csv             Managed properties (flip & rental)
data/managed_property_expenses.csv      Renovation / maintenance expenses per property
data/managed_property_rentals.csv       Rental agreements
data/managed_property_rental_payments.csv  Monthly rental payment records
data/managed_property_sales.csv         Property sale records

TXT;
    }

    private function toCsv(array $header, array $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, $header);
        foreach ($rows as $row) {
            $row = array_map(fn($v) => is_string($v ?? '') ? preg_replace("/\R/u", ' ', $v ?? '') : ($v ?? ''), $row);
            fputcsv($fh, $row);
        }
        rewind($fh);
        return stream_get_contents($fh);
    }
}