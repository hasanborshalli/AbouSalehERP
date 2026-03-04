<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\OperatingExpense;
use App\Models\WorkerPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportExportController extends Controller
{
    // ── Router ────────────────────────────────────────────────────────────

    public function excel(Request $request, string $type)
    {
        return match($type) {
            'pl'                   => $this->plExcel($request),
            'sales-pipeline'       => $this->pipelineExcel($request),
            'outstanding-invoices' => $this->invoicesExcel($request),
            'worker-payments'      => $this->workerExcel($request),
            'operating-expenses'   => $this->opexExcel($request),
            default                => abort(404),
        };
    }

    public function pdf(Request $request, string $type)
    {
        return match($type) {
            'pl'                   => $this->plPdf($request),
            'sales-pipeline'       => $this->pipelinePdf($request),
            'outstanding-invoices' => $this->invoicesPdf($request),
            'worker-payments'      => $this->workerPdf($request),
            'operating-expenses'   => $this->opexPdf($request),
            default                => abort(404),
        };
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function csvResponse(string $filename, array $headers, array $rows, array $totals = [])
    {
        $httpHeaders = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        $callback = function () use ($headers, $rows, $totals) {
            $h = fopen('php://output', 'w');
            fputs($h, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($h, $headers);
            foreach ($rows as $row) fputcsv($h, $row);
            if ($totals) {
                fputcsv($h, []);
                foreach ($totals as $row) fputcsv($h, $row);
            }
            fclose($h);
        };
        return response()->stream($callback, 200, $httpHeaders);
    }

    private function pdfView(string $view, array $data)
    {
        return Pdf::loadView($view, $data)->setPaper('a4', 'landscape');
    }

    // ── P&L ───────────────────────────────────────────────────────────────

    private function plData(Request $request): array
    {
        $dateFrom = $request->input('date_from', now()->startOfYear()->toDateString());
        $dateTo   = $request->input('date_to',   now()->toDateString());

        $baseQ = LedgerEntry::whereBetween(DB::raw('DATE(posted_at)'), [$dateFrom, $dateTo])
            ->whereNull('reverses_entry_id')->where('amount', '>', 0);

        $revenueRows = (clone $baseQ)->where('direction','in')
            ->select('source_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as entries'))
            ->groupBy('source_type')->orderByDesc('total')->get();

        $expenseRows = (clone $baseQ)->where('direction','out')
            ->select('source_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as entries'))
            ->groupBy('source_type')->orderByDesc('total')->get();

        $totalRevenue  = (float) $revenueRows->sum('total');
        $totalExpenses = (float) $expenseRows->sum('total');

        return compact('dateFrom','dateTo','revenueRows','expenseRows','totalRevenue','totalExpenses');
    }

    private function plExcel(Request $request)
    {
        $d = $this->plData($request);
        $rows = [];
        $rows[] = ['--- REVENUE (Credit) ---'];
        $rows[] = ['Source', 'Entries', 'Amount'];
        foreach ($d['revenueRows'] as $r) {
            $rows[] = [str_replace('_',' ',$r->source_type ?? '—'), $r->entries, number_format($r->total,2,'.','')];
        }
        $rows[] = [];
        $rows[] = ['--- EXPENSES (Debit) ---'];
        $rows[] = ['Source', 'Entries', 'Amount'];
        foreach ($d['expenseRows'] as $r) {
            $rows[] = [str_replace('_',' ',$r->source_type ?? '—'), $r->entries, number_format($r->total,2,'.','')];
        }
        $totals = [
            ['Total Revenue',  '', number_format($d['totalRevenue'],2,'.','')],
            ['Total Expenses', '', number_format($d['totalExpenses'],2,'.','')],
            ['Net Profit/Loss','', number_format($d['totalRevenue']-$d['totalExpenses'],2,'.','')],
        ];
        return $this->csvResponse('pl-'.$d['dateFrom'].'-'.$d['dateTo'].'.csv', ['Type','Entries','Amount (USD)'], $rows, $totals);
    }

    private function plPdf(Request $request)
    {
        $d = $this->plData($request);
        $netProfit = $d['totalRevenue'] - $d['totalExpenses'];
        return $this->pdfView('pdfs.reports.pl', array_merge($d, compact('netProfit')))->download('pl-report.pdf');
    }

    // ── Sales Pipeline ────────────────────────────────────────────────────

    private function pipelineData(Request $request)
    {
        $projectId = $request->input('project_id');
        $status    = $request->input('status');

        $q = Apartment::with(['project','floor','contract.invoices','contract.client'])
            ->orderBy('project_id')->orderBy('id');
        if ($projectId) $q->where('project_id', $projectId);
        if ($status)    $q->where('status', $status);

        return ['apartments' => $q->get(), 'projectId' => $projectId, 'status' => $status];
    }

    private function pipelineExcel(Request $request)
    {
        $d = $this->pipelineData($request);
        $rows = [];
        foreach ($d['apartments'] as $apt) {
            $c = $apt->contract;
            $collected   = $c ? (float)$c->down_payment + (float)$c->invoices->where('status','paid')->sum('amount') : 0;
            $outstanding = $c ? (float)$c->invoices->whereIn('status',['pending','overdue'])->sum('amount') : 0;
            $rows[] = [
                $apt->project->name ?? '—',
                'Unit '.($apt->unit_number ?? '#'.$apt->id),
                $apt->bedrooms ?? '—',
                $apt->area_sqm ?? '—',
                $apt->price_total ? number_format($apt->price_total,2,'.','') : '—',
                ucfirst($apt->status),
                $c?->client?->name ?? '—',
                $c?->contract_date?->format('Y-m-d') ?? '—',
                $collected > 0 ? number_format($collected,2,'.','') : '0.00',
                $outstanding > 0 ? number_format($outstanding,2,'.','') : '0.00',
            ];
        }
        return $this->csvResponse('sales-pipeline.csv',
            ['Project','Unit','Beds','Area (m²)','List Price','Status','Client','Contract Date','Collected','Outstanding'],
            $rows
        );
    }

    private function pipelinePdf(Request $request)
    {
        $d = $this->pipelineData($request);
        return $this->pdfView('pdfs.reports.sales-pipeline', $d)->download('sales-pipeline.pdf');
    }

    // ── Outstanding Invoices ──────────────────────────────────────────────

    private function invoicesData(Request $request)
    {
        $q = Invoice::with(['contract.apartment.project','contract.client'])
            ->whereIn('status',['pending','overdue'])->whereNull('deleted_at')->orderBy('due_date');
        if ($request->project_id) $q->whereHas('contract.apartment', fn($x)=>$x->where('project_id',$request->project_id));
        if ($request->overdue==='1') $q->where('status','overdue');
        if ($request->date_from) $q->whereDate('due_date','>=',$request->date_from);
        if ($request->date_to)   $q->whereDate('due_date','<=',$request->date_to);
        $invoices = $q->get();
        return ['invoices'=>$invoices,'totalAmount'=>(float)$invoices->sum('amount'),'overdueAmount'=>(float)$invoices->where('status','overdue')->sum('amount')];
    }

    private function invoicesExcel(Request $request)
    {
        $d = $this->invoicesData($request);
        $rows = [];
        foreach ($d['invoices'] as $inv) {
            $apt = $inv->contract?->apartment;
            $daysOverdue = $inv->status==='overdue' ? now()->diffInDays($inv->due_date,false)*-1 : '—';
            $rows[] = [
                $inv->invoice_number,
                $inv->contract?->client?->name ?? '—',
                $apt?->project?->name ?? '—',
                $apt ? 'Unit '.($apt->unit_number ?? '#'.$apt->id) : '—',
                $inv->issue_date?->format('Y-m-d') ?? '—',
                $inv->due_date?->format('Y-m-d') ?? '—',
                ucfirst($inv->status),
                number_format($inv->amount,2,'.',''),
                $daysOverdue,
            ];
        }
        return $this->csvResponse('outstanding-invoices.csv',
            ['Invoice #','Client','Project','Unit','Issue Date','Due Date','Status','Amount','Days Overdue'],
            $rows,
            [['','','','','','','Total Outstanding', number_format($d['totalAmount'],2,'.',''), '']]
        );
    }

    private function invoicesPdf(Request $request)
    {
        $d = $this->invoicesData($request);
        return $this->pdfView('pdfs.reports.outstanding-invoices', $d)->download('outstanding-invoices.pdf');
    }

    // ── Worker Payments ───────────────────────────────────────────────────

    private function workerData(Request $request)
    {
        $q = WorkerPayment::with(['contract.worker','contract.project'])->whereNull('deleted_at')->orderBy('due_date');
        if ($request->status)    $q->where('status',$request->status);
        if ($request->date_from) $q->whereDate('due_date','>=',$request->date_from);
        if ($request->date_to)   $q->whereDate('due_date','<=',$request->date_to);
        if ($request->worker_id) $q->whereHas('contract',fn($x)=>$x->where('worker_user_id',$request->worker_id));
        $payments = $q->get();
        return [
            'payments'     => $payments,
            'totalPaid'    => (float)$payments->where('status','paid')->sum('amount'),
            'totalPending' => (float)$payments->where('status','pending')->sum('amount'),
        ];
    }

    private function workerExcel(Request $request)
    {
        $d = $this->workerData($request);
        $rows = [];
        foreach ($d['payments'] as $pmt) {
            $isLate = $pmt->status==='pending' && $pmt->due_date < now();
            $rows[] = [
                $pmt->contract?->worker?->name ?? '—',
                $pmt->contract?->category ?? '—',
                $pmt->contract?->project?->name ?? '—',
                $pmt->payment_number,
                $pmt->due_date?->format('Y-m-d') ?? '—',
                $pmt->paid_at?->format('Y-m-d') ?? '—',
                $isLate ? 'Overdue' : ucfirst($pmt->status),
                number_format($pmt->amount,2,'.',''),
            ];
        }
        return $this->csvResponse('worker-payments.csv',
            ['Worker','Category','Project','Payment #','Due Date','Paid On','Status','Amount'],
            $rows,
            [['','','','','','','Total Paid',number_format($d['totalPaid'],2,'.','')],['','','','','','','Total Pending',number_format($d['totalPending'],2,'.','')]]
        );
    }

    private function workerPdf(Request $request)
    {
        $d = $this->workerData($request);
        return $this->pdfView('pdfs.reports.worker-payments', $d)->download('worker-payments.pdf');
    }

    // ── Operating Expenses ────────────────────────────────────────────────

    private function opexData(Request $request)
    {
        $q = OperatingExpense::whereNull('voided_at')->orderByDesc('expense_date');
        if ($request->category)  $q->where('category',$request->category);
        if ($request->date_from) $q->whereDate('expense_date','>=',$request->date_from);
        if ($request->date_to)   $q->whereDate('expense_date','<=',$request->date_to);
        $expenses   = $q->get();
        $byCategory = $expenses->groupBy('category')->map(fn($g)=>(float)$g->sum('amount'))->sortDesc();
        return ['expenses'=>$expenses,'totalAmount'=>(float)$expenses->sum('amount'),'byCategory'=>$byCategory];
    }

    private function opexExcel(Request $request)
    {
        $d = $this->opexData($request);
        $rows = [];
        foreach ($d['expenses'] as $exp) {
            $rows[] = [
                $exp->expense_date->format('Y-m-d'),
                $exp->category,
                $exp->description ?? '—',
                number_format($exp->amount,2,'.',''),
            ];
        }
        $totals = [['','','Total', number_format($d['totalAmount'],2,'.','')]];
        foreach ($d['byCategory'] as $cat => $amt) {
            $totals[] = ['','', ucfirst($cat), number_format($amt,2,'.','')];
        }
        return $this->csvResponse('operating-expenses.csv', ['Date','Category','Description','Amount (USD)'], $rows, $totals);
    }

    private function opexPdf(Request $request)
    {
        $d = $this->opexData($request);
        return $this->pdfView('pdfs.reports.operating-expenses', $d)->download('operating-expenses.pdf');
    }
}