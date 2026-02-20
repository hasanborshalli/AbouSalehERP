<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contract;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ExportController extends Controller
{
    public function exportZip(Request $request)
    {
        // If you want owner only and you don't have middleware:
        // abort_unless(auth()->user()?->role === 'owner', 403);

        $disk = Storage::disk('public');

        // Create temp zip in storage/app (NOT public)
        $zipName = 'backup-' . now()->format('Y-m-d_H-i-s') . '.zip';
        $tmpZipPath = storage_path('app/' . $zipName);

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create zip file.');
        }

        // -------------------------
        // 1) Add PDFs from storage
        // -------------------------
        $this->addFolderToZip($zip, $disk, 'contracts', 'contracts');
        $this->addFolderToZip($zip, $disk, 'invoices', 'invoices');

        // -------------------------
        // 2) Add CSV exports from DB
        // -------------------------
        $zip->addFromString('data/clients.csv', $this->clientsCsv());
        $zip->addFromString('data/contracts.csv', $this->contractsCsv());
        $zip->addFromString('data/invoices.csv', $this->invoicesCsv());
        $zip->addFromString('data/employees.csv', $this->employeesCsv());

        // Add metadata
        $zip->addFromString('data/README.txt', "Export generated at: " . now()->toDateTimeString() . PHP_EOL);

        $zip->close();

        // Download and delete afterwards
        return response()->download($tmpZipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function addFolderToZip(ZipArchive $zip, $disk, string $folder, string $zipFolder)
    {
        // If folder doesn't exist, just skip
        if (!$disk->exists($folder)) return;

        $files = $disk->allFiles($folder);

        foreach ($files as $path) {
            // Only PDFs (optional guard)
            if (!Str::endsWith(Str::lower($path), '.pdf')) continue;

            // Read file and add
            $contents = $disk->get($path);

            // Put inside zip with same relative path
            $relative = ltrim(Str::after($path, $folder), '/\\');
            $zipPath = $zipFolder . '/' . $relative;

            $zip->addFromString($zipPath, $contents);
        }
    }

    private function clientsCsv(): string
    {
        // Adjust these relations/columns to your schema
        // Example assumes "clients are users with role client"
        $rows = User::query()
            ->where('role', 'client')
            ->select(['id','name','email','phone','created_at'])
            ->orderBy('id')
            ->get();

        $header = ['id','name','email','phone','created_at'];
        return $this->toCsv($header, $rows->map(fn ($u) => [
            $u->id,
            $u->name,
            $u->email,
            $u->phone,
            optional($u->created_at)->toDateTimeString(),
        ])->all());
    }

    private function contractsCsv(): string
    {
        $rows = Contract::query()
            ->with(['client','apartment','project'])
            ->orderBy('id')
            ->get();

        $header = [
            'id','client_id','client_name',
            'project','apartment',
            'total_price','discount','final_price',
            'down_payment','installment_months','installment_amount',
            'contract_date','payment_start_date','late_fee','pdf_path'
        ];

        return $this->toCsv($header, $rows->map(fn ($c) => [
            $c->id,
            $c->client_id,
            optional($c->client)->name,
            optional($c->project)->name,
            optional($c->apartment)->unit_number ?? optional($c->apartment)->unit_code,
            $c->total_price,
            $c->discount,
            $c->final_price,
            $c->down_payment,
            $c->installment_months,
            $c->installment_amount,
            optional($c->contract_date)->format('Y-m-d'),
            optional($c->payment_start_date)->format('Y-m-d'),
            $c->late_fee,
            $c->pdf_path,
        ])->all());
    }

    private function invoicesCsv(): string
    {
        $rows = Invoice::query()
            ->with(['contract','contract.client'])
            ->orderBy('id')
            ->get();

        $header = [
            'id','contract_id','client_name',
            'amount','status','issue_date','due_date','paid_at','pdf_path'
        ];

        return $this->toCsv($header, $rows->map(fn ($inv) => [
            $inv->id,
            $inv->contract_id,
            optional(optional($inv->contract)->client)->name,
            $inv->amount,
            $inv->status,
            optional($inv->issue_date)->format('Y-m-d'),
            optional($inv->due_date)->format('Y-m-d'),
            optional($inv->paid_at)->toDateTimeString(),
            $inv->pdf_path,
        ])->all());
    }
private function employeesCsv(): string
{
    // Adjust roles if you use different names.
    // If "employees" are users where role != client:
    $rows = \App\Models\User::query()
        ->whereIn('role', ['owner', 'admin', 'employee']) // change to your roles
        ->select(['id','name','email','phone','role','created_at'])
        ->orderBy('id')
        ->get();

    $header = ['id','name','email','phone','role','created_at'];

    return $this->toCsv($header, $rows->map(fn ($u) => [
        $u->id,
        $u->name,
        $u->email,
        $u->phone,
        $u->role,
        optional($u->created_at)->toDateTimeString(),
    ])->all());
}

    private function toCsv(array $header, array $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, $header);

        foreach ($rows as $row) {
            // Clean newlines etc
            $row = array_map(function ($v) {
                $v = $v ?? '';
                $v = is_string($v) ? preg_replace("/\R/u", " ", $v) : $v;
                return $v;
            }, $row);

            fputcsv($fh, $row);
        }

        rewind($fh);
        return stream_get_contents($fh);
    }
}