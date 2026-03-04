<?php

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LedgerExportController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = LedgerEntry::with('account')->orderByDesc('posted_at')->orderByDesc('id');

        $direction  = $request->input('direction');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        $sourceType = $request->input('source_type');
        $search     = $request->input('search');

        if ($direction && in_array($direction, ['in', 'out'])) {
            $query->where('direction', $direction);
        }
        if ($dateFrom)   $query->whereDate('posted_at', '>=', $dateFrom);
        if ($dateTo)     $query->whereDate('posted_at', '<=', $dateTo);
        if ($sourceType) $query->where('source_type', $sourceType);
        if ($search)     $query->where('description', 'like', '%' . $search . '%');

        return $query;
    }

    public function exportExcel(Request $request)
    {
        $entries = $this->buildQuery($request)->get();

        $filename = 'ledger-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($entries) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Date', 'Type', 'Flow', 'Source', 'Description', 'Amount (USD)']);

            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->posted_at->format('Y-m-d'),
                    $e->account->name ?? $e->account->code ?? '—',
                    $e->direction === 'in' ? 'Credit' : 'Debit',
                    $e->source_type ?? '—',
                    $e->description ?? '—',
                    number_format((float) $e->amount, 2, '.', ''),
                ]);
            }

            // Totals row
            $totalCredit = $entries->where('direction', 'in')->sum('amount');
            $totalDebit  = $entries->where('direction', 'out')->sum('amount');
            fputcsv($handle, []);
            fputcsv($handle, ['', '', '', '', 'Total Credit (دين)', number_format($totalCredit, 2, '.', '')]);
            fputcsv($handle, ['', '', '', '', 'Total Debit (مدين)',  number_format($totalDebit,  2, '.', '')]);
            fputcsv($handle, ['', '', '', '', 'Net',          number_format($totalCredit - $totalDebit, 2, '.', '')]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $entries = $this->buildQuery($request)->get();

        $totalCredit = (float) $entries->where('direction', 'in')->sum('amount');
        $totalDebit  = (float) $entries->where('direction', 'out')->sum('amount');
        $net         = $totalCredit - $totalDebit;

        $filters = [
            'direction'   => $request->input('direction'),
            'date_from'   => $request->input('date_from'),
            'date_to'     => $request->input('date_to'),
            'source_type' => $request->input('source_type'),
            'search'      => $request->input('search'),
        ];

        $pdf = Pdf::loadView('pdfs.ledger', compact('entries', 'totalCredit', 'totalDebit', 'net', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('ledger-' . now()->format('Y-m-d') . '.pdf');
    }
}