<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ClientPortalController extends Controller
{
    // ----------------------------
    // Helpers
    // ----------------------------
    private function clientId(): int
    {
        return (int) auth()->id();
    }

    private function clientContractsQuery()
    {
        return Contract::with(['project.manager', 'apartment'])
            ->where('client_user_id', $this->clientId())
            ->orderByDesc('id');
    }

    private function clientInvoicesQuery()
    {
        return Invoice::with(['contract.project', 'contract.apartment'])
            ->whereHas('contract', function ($q) {
                $q->where('client_user_id', $this->clientId());
            })
            ->orderBy('issue_date','asc');
    }

    private function contractStatus(Contract $contract): string
    {
        $invoices = $contract->relationLoaded('invoices') ? $contract->invoices : $contract->invoices()->get();

        if ($invoices->count() === 0) {
            return 'Signed';
        }
        if ($invoices->where('status', 'pending')->count() > 0) {
            return 'In Progress';
        }
        if ($invoices->where('status', 'paid')->count() === $invoices->count()) {
            return 'Completed';
        }
        return 'In Progress';
    }

    private function contractProgressPercent(Contract $contract): float
    {
        $paidInvoices = (float) $contract->invoices()->where('status', 'paid')->sum('amount');
        $down = (float) ($contract->down_payment ?? 0);
        $final = (float) ($contract->final_price ?? 0);

        if ($final <= 0) return 0;

        $pct = (($paidInvoices + $down) / $final) * 100;
        return round(min(100, max(0, $pct)), 2);
    }

    private function assertOwnContract(Contract $contract): void
    {
        abort_unless($contract->client_user_id === $this->clientId(), 403);
    }

    private function assertOwnInvoice(Invoice $invoice): void
    {
        $invoice->loadMissing('contract');
        abort_unless((int) $invoice->contract?->client_user_id === $this->clientId(), 403);
    }

    // ----------------------------
    // Contracts
    // ----------------------------
    public function contractsHome()
    {
        return view('client.contracts');
    }


public function contractsOverview()
{
    $contracts = $this->clientContractsQuery()->get();

    $rows = $contracts->map(function (Contract $c) {
        return [
            'contract' => $c,
            'project_name' => $c->project?->name,
            'start_date' => $c->project?->start_date,
            'estimated_completion_date' => $c->project?->estimated_completion_date,
            'status' => $this->contractStatus($c),
        ];
    });

    // ✅ pick ONE contract for progress tracker (latest one)
    $contract = $this->clientContractsQuery()
        ->with('progressItems')
        ->first(); // latest because query has orderByDesc('id')

    $items = $contract?->progressItems ?? collect();

    $overall = 0;
    if ($items->isNotEmpty()) {
        $totalWeight = max(1, (int) $items->sum('weight'));
        $doneWeight  = (int) $items->where('status', 'done')->sum('weight');
        $overall     = (int) round(($doneWeight / $totalWeight) * 100);
    }

    return view('client.contracts-overview', [
        'rows' => $rows,
        'progressItems' => $items,
        'overallProgress' => $overall,
        'progressContract' => $contract, // optional (if you want show which contract)
    ]);
}

    public function contractsManager()
    {
        $contracts = $this->clientContractsQuery()->get();
        return view('client.contracts-manager', compact('contracts'));
    }

    public function contractsDocuments()
    {
        $contracts = $this->clientContractsQuery()->get();
        return view('client.contracts-documents', compact('contracts'));
    }

    public function contractsProgress()
    {
        $contracts = $this->clientContractsQuery()->with('invoices')->get();
        $rows = $contracts->map(function (Contract $c) {
            return [
                'contract' => $c,
                'status' => $this->contractStatus($c),
                'progress' => $this->contractProgressPercent($c),
                'paid_amount' => (float) $c->invoices->where('status', 'paid')->sum('amount') + (float) ($c->down_payment ?? 0),
                'final_price' => (float) ($c->final_price ?? 0),
            ];
        });
        return view('client.contracts-progress', compact('rows'));
    }

    public function viewContractPdf(Contract $contract)
    {
        $this->assertOwnContract($contract);
        abort_unless($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path), 404);
        return response()->file(Storage::disk('public')->path($contract->pdf_path));
    }

    public function downloadContractPdf(Contract $contract)
    {
        $this->assertOwnContract($contract);
        abort_unless($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path), 404);
        $filename = 'contract-' . $contract->id . '.pdf';
        return response()->download(Storage::disk('public')->path($contract->pdf_path), $filename);
    }

    // ----------------------------
    // Invoices
    // ----------------------------
    public function invoicesHome()
    {
        return view('client.invoices');
    }

    public function invoicesList()
    {
        $invoices = $this->clientInvoicesQuery()->get();
        return view('client.invoices-list', compact('invoices'));
    }

    public function invoicesReceipts()
    {
        $invoices = $this->clientInvoicesQuery()->where('status', 'paid')->get();
        return view('client.invoices-receipts', compact('invoices'));
    }

    public function invoicesDownloadCenter()
    {
        $unpaid = $this->clientInvoicesQuery()->where('status', 'pending')->get();
        return view('client.invoices-download-center', compact('unpaid'));
    }

    public function downloadUnpaidInvoicesZip()
    {
        $invoices = $this->clientInvoicesQuery()->where('status', 'pending')->get();
        if ($invoices->isEmpty()) {
            return back()->with('error', 'No unpaid invoices found.');
        }

        $zipName = 'unpaid-invoices-' . str_pad($this->clientId(), 5, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd_His') . '.zip';
        $tmpPath = storage_path('app/tmp/' . $zipName);
        if (!is_dir(dirname($tmpPath))) {
            @mkdir(dirname($tmpPath), 0775, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Failed to create ZIP.');
        }

        $added = 0;
        foreach ($invoices as $inv) {
            if (!$inv->pdf_path) continue;
            if (!Storage::disk('public')->exists($inv->pdf_path)) continue;
            $absolute = Storage::disk('public')->path($inv->pdf_path);
            $zip->addFile($absolute, 'invoice-' . $inv->invoice_number . '.pdf');
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($tmpPath);
            return back()->with('error', 'No PDF files found for unpaid invoices.');
        }

        return response()->download($tmpPath, $zipName)->deleteFileAfterSend(true);
    }


    public function viewInvoicePdf(Invoice $invoice)
    {
        $this->assertOwnInvoice($invoice);
        abort_unless($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path), 404);
        return response()->file(Storage::disk('public')->path($invoice->pdf_path));
    }

    public function downloadInvoicePdf(Invoice $invoice)
    {
        $this->assertOwnInvoice($invoice);
        abort_unless($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path), 404);
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';
        return response()->download(Storage::disk('public')->path($invoice->pdf_path), $filename);
    }

    public function downloadInvoiceReceipt(Invoice $invoice)
    {
        $this->assertOwnInvoice($invoice);

        // If you later generate a separate receipt_path, use it. For now fallback to invoice PDF.
        $path = $invoice->receipt_path ?: $invoice->pdf_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $filename = ($invoice->receipt_path ? 'receipt-' : 'invoice-') . $invoice->invoice_number . '.pdf';
        return response()->download(Storage::disk('public')->path($path), $filename);
    }

    // ----------------------------
    // Settings
    // ----------------------------
    public function settingsHome()
    {
        /** @var User $user */
        $user = auth()->user();
        return view('client.settings', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        // keep validation simple here; you can add unique rules if you want.
        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->password = $data['password'];
        $user->save();

        return back()->with('success', 'Password updated.');
    }

    public function updateAvatar(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        // delete old avatar if it was stored locally
        if ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
            $old = ltrim(str_replace('/storage/', '', $user->avatar), '/');
            if ($old) Storage::disk('public')->delete($old);
        }

        $path = $data['avatar']->store('avatars', 'public');
        $user->avatar = '/storage/' . $path;
        $user->save();

        return back()->with('success', 'Avatar updated.');
    }
}