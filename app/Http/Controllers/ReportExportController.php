<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\InventoryPurchase;
use App\Models\ApartmentMaterial;
use App\Models\ProjectInventoryItem;
use App\Models\LedgerEntry;
use App\Models\OperatingExpense;
use App\Models\Project;
use App\Models\User;
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
            'inventory'            => $this->inventoryExcel($request),
            'project'              => $this->projectExcel($request),
            'apartment'            => $this->apartmentExcel($request),
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
            'inventory'            => $this->inventoryPdf($request),
            'project'              => $this->projectPdf($request),
            'apartment'            => $this->apartmentPdf($request),
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


    // ── Inventory ─────────────────────────────────────────────────────────

    private function inventoryData(Request $request): array
    {
        $itemId = $request->input('item_id');
        $item   = $itemId ? InventoryItem::withTrashed()->find($itemId) : null;

        $pQ = InventoryPurchase::where('inventory_item_id', $itemId)->whereNull('voided_at');
        if ($request->date_from) $pQ->whereDate('purchase_date', '>=', $request->date_from);
        if ($request->date_to)   $pQ->whereDate('purchase_date', '<=', $request->date_to);
        $purchases = $item ? $pQ->orderByDesc('purchase_date')->get() : collect();

        $totalPurchaseCost = (float) $purchases->sum('total_cost');
        $totalPurchased    = (int)   $purchases->sum('qty');

        $projectUsages   = $item ? ProjectInventoryItem::with('project')->where('inventory_item_id',$itemId)->get() : collect();
        $apartmentUsages = $item ? ApartmentMaterial::with('apartment.project','apartment.floor')->where('inventory_item_id',$itemId)->get() : collect();
        $avgCost = $totalPurchased > 0 ? $totalPurchaseCost / $totalPurchased : (float)($item?->price ?? 0);

        return compact('item','purchases','projectUsages','apartmentUsages','totalPurchaseCost','totalPurchased','avgCost');
    }

    private function inventoryExcel(Request $request)
    {
        $d = $this->inventoryData($request);
        if (!$d['item']) return response('No item selected', 400);
        $rows = [];
        $rows[] = ['--- PURCHASES ---'];
        $rows[] = ['Date','Vendor','Receipt Ref','Qty','Unit Cost','Total Cost'];
        foreach ($d['purchases'] as $p) {
            $rows[] = [$p->purchase_date->format('Y-m-d'),$p->vendor_name??'—',$p->receipt_ref??'—',$p->qty,number_format($p->unit_cost,2,'.',''),number_format($p->total_cost,2,'.','')];
        }
        $rows[] = [];
        $rows[] = ['--- PROJECT USAGE ---'];
        $rows[] = ['Project','Qty Assigned','Est. Cost'];
        foreach ($d['projectUsages'] as $pu) {
            $rows[] = [$pu->project->name??'—',number_format($pu->quantity_needed,1),number_format($pu->quantity_needed*$d['avgCost'],2,'.','')];
        }
        $rows[] = [];
        $rows[] = ['--- APARTMENT USAGE ---'];
        $rows[] = ['Project','Unit','Floor','Qty Needed','Est. Cost'];
        foreach ($d['apartmentUsages'] as $au) {
            $rows[] = [$au->apartment?->project?->name??'—','Unit '.($au->apartment?->unit_number??'?'),'Floor '.($au->apartment?->floor?->floor_number??'?'),number_format($au->quantity_needed,1),number_format($au->quantity_needed*$d['avgCost'],2,'.','')];
        }
        $totals = [['Total Cost Paid','','','','',$d['totalPurchaseCost']]];
        return $this->csvResponse('inventory-'.$d['item']->name.'.csv',['Type/Date','Vendor/Project','Ref/Unit','Qty','Unit Cost','Total'],array_merge([['Item: '.$d['item']->name]],$rows),$totals);
    }

    private function inventoryPdf(Request $request)
    {
        $d = $this->inventoryData($request);
        if (!$d['item']) abort(400,'No item selected');
        return $this->pdfView('pdfs.reports.inventory', $d)->download('inventory-'.$d['item']->name.'.pdf');
    }

    // ── Project Report ────────────────────────────────────────────────────

    private function projectData(Request $request): ?array
    {
        $projectId = $request->input('project_id');
        if (!$projectId) return null;
        $project = Project::with([
            'floors.apartments.contract.invoices',
            'floors.apartments.materials.inventoryItem',
            'floors.apartments.additionalCosts',
            'inventoryUsages.inventoryItem',
            'additionalCosts',
        ])->find($projectId);
        if (!$project) return null;

        $apartments         = $project->floors->flatMap(fn($f) => $f->apartments);
        $projectMaterialsCost = $project->inventoryUsages->sum(fn($u) =>
            (float)$u->quantity_needed * (float)($u->inventoryItem->price ?? 0));
        $apartmentMaterialsCost = $apartments->sum(fn($apt) =>
            $apt->materials->sum(fn($m) =>
                (float)$m->quantity_needed * (float)($m->inventoryItem->price ?? 0)));

        $projCosts          = $project->additionalCosts;
        $projCostsExpected  = (float)$projCosts->sum('expected_amount');
        $projCostsActual    = (float)$projCosts->sum(fn($c) => $c->isSettled() ? (float)$c->actual_amount : 0.0);
        $aptCostsExpected   = (float)$apartments->sum(fn($a) => $a->additionalCosts->sum('expected_amount'));
        $aptCostsActual     = (float)$apartments->sum(fn($a) => $a->additionalCosts->sum(fn($c) => $c->isSettled() ? (float)$c->actual_amount : 0.0));

        $contracts          = $apartments->map(fn($a) => $a->contract)->filter();
        $paidInvoicesTotal  = (float)$contracts->sum(fn($c) => $c->invoices->where('status','paid')->sum('amount'));
        $downPaymentsTotal  = (float)$contracts->sum('down_payment');
        $totalRevenue       = $paidInvoicesTotal + $downPaymentsTotal;
        $totalCost          = $projectMaterialsCost + $apartmentMaterialsCost + $projCostsActual + $aptCostsActual;
        $profit             = $totalRevenue - $totalCost;

        return compact('project','apartments','projCosts',
            'projectMaterialsCost','apartmentMaterialsCost',
            'projCostsExpected','projCostsActual','aptCostsExpected','aptCostsActual',
            'paidInvoicesTotal','downPaymentsTotal','totalRevenue','totalCost','profit');
    }

    private function projectExcel(Request $request)
    {
        $d = $this->projectData($request);
        if (!$d) abort(400, 'project_id required');
        $rows = [['--- PROJECT MATERIALS ---'],['Item','Qty','Unit Price','Line Cost']];
        foreach ($d['project']->inventoryUsages as $u) {
            $rows[] = [$u->inventoryItem->name??'—', $u->quantity_needed,
                number_format($u->inventoryItem->price??0,2,'.',''),
                number_format((float)$u->quantity_needed*(float)($u->inventoryItem->price??0),2,'.','')];
        }
        $rows[] = []; $rows[] = ['--- PROJECT ADDITIONAL COSTS ---'];
        $rows[] = ['Description','Category','Expected','Actual','Status'];
        foreach ($d['projCosts'] as $c) {
            $rows[] = [$c->description, $c->category??'—',
                number_format($c->expected_amount,2,'.',''),
                $c->isSettled() ? number_format($c->actual_amount,2,'.',''): '—',
                $c->isSettled() ? 'Settled' : 'Pending'];
        }
        $rows[] = []; $rows[] = ['--- APARTMENTS ---'];
        $rows[] = ['Unit','Floor','Status','Total Cost','Collected','Profit'];
        foreach ($d['apartments'] as $apt) {
            $mat  = $apt->materials->sum(fn($m) => (float)$m->quantity_needed*(float)($m->inventoryItem->price??0));
            $cost = $mat + $apt->additionalCosts->sum(fn($c) => $c->isSettled() ? (float)$c->actual_amount : 0.0);
            $coll = (float)($apt->contract?->invoices->where('status','paid')->sum('amount')??0)
                  + (float)($apt->contract?->down_payment??0);
            $rows[] = ['Unit '.$apt->unit_number, 'Floor '.$apt->floor->floor_number,
                ucfirst($apt->status),
                number_format($cost,2,'.',''), number_format($coll,2,'.',''), number_format($coll-$cost,2,'.','')];
        }
        $totals = [
            ['Total Revenue','','','','',number_format($d['totalRevenue'],2,'.','')],
            ['Total Cost',   '','','','',number_format($d['totalCost'],2,'.','')],
            ['Net Profit/Loss','','','','',number_format($d['profit'],2,'.','')],
        ];
        return $this->csvResponse('project-'.$d['project']->name.'.csv',
            ['Item / Description','Category / Floor','Expected / Unit Price','Actual / Line Cost','Status','Total'],
            $rows, $totals);
    }

    private function projectPdf(Request $request)
    {
        $d = $this->projectData($request);
        if (!$d) abort(400,'project_id required');
        return $this->pdfView('pdfs.reports.project', $d)
            ->download('project-'.$d['project']->name.'.pdf');
    }

    // ── Apartment Report ──────────────────────────────────────────────────

    private function apartmentData(Request $request): ?array
    {
        $apartmentId = $request->input('apartment_id');
        if (!$apartmentId) return null;
        $apartment = Apartment::with([
            'project','floor','contract.invoices','contract.client',
            'materials.inventoryItem','additionalCosts',
        ])->find($apartmentId);
        if (!$apartment) return null;

        $contract     = $apartment->contract;
        $invoices     = $contract?->invoices ?? collect();
        $paidInvoices = $invoices->where('status','paid');
        $materialsCost = $apartment->materials->sum(fn($m) =>
            (float)$m->quantity_needed * (float)($m->inventoryItem->price ?? 0));
        $costsExpected = (float)$apartment->additionalCosts->sum('expected_amount');
        $costsActual   = (float)$apartment->additionalCosts->sum(fn($c) => $c->isSettled() ? (float)$c->actual_amount : 0.0);
        $paidAmount    = (float)$paidInvoices->sum('amount');
        $downPayment   = (float)($contract?->down_payment ?? 0);
        $totalRevenue  = $paidAmount + $downPayment;
        $totalCost     = $materialsCost + $costsActual;
        $profit        = $totalRevenue - $totalCost;

        return compact('apartment','contract','invoices','paidInvoices',
            'materialsCost','costsExpected','costsActual',
            'paidAmount','downPayment','totalRevenue','totalCost','profit');
    }

    private function apartmentExcel(Request $request)
    {
        $d = $this->apartmentData($request);
        if (!$d) abort(400,'apartment_id required');
        $apt  = $d['apartment'];
        $rows = [['--- INVOICES ---'],['Invoice #','Issue Date','Due Date','Amount','Status','Paid At']];
        foreach ($d['invoices']->sortBy('issue_date') as $inv) {
            $rows[] = [$inv->invoice_number??$inv->id, $inv->issue_date, $inv->due_date,
                number_format($inv->amount,2,'.',''), ucfirst($inv->status),
                $inv->paid_at ? \Carbon\Carbon::parse($inv->paid_at)->format('Y-m-d') : '—'];
        }
        $rows[] = []; $rows[] = ['--- MATERIALS ---'];
        $rows[] = ['Item','Qty','Unit Price','Line Cost'];
        foreach ($apt->materials as $m) {
            $rows[] = [$m->inventoryItem->name??'—', $m->quantity_needed,
                number_format($m->inventoryItem->price??0,2,'.',''),
                number_format((float)$m->quantity_needed*(float)($m->inventoryItem->price??0),2,'.','')];
        }
        $rows[] = []; $rows[] = ['--- ADDITIONAL COSTS ---'];
        $rows[] = ['Description','Category','Expected','Actual','Status'];
        foreach ($apt->additionalCosts as $c) {
            $rows[] = [$c->description, $c->category??'—',
                number_format($c->expected_amount,2,'.',''),
                $c->isSettled() ? number_format($c->actual_amount,2,'.',''): '—',
                $c->isSettled() ? 'Settled' : 'Pending'];
        }
        $totals = [
            ['Total Revenue','','',number_format($d['totalRevenue'],2,'.','')],
            ['Total Cost',   '','',number_format($d['totalCost'],2,'.','')],
            ['Net Profit/Loss','','',number_format($d['profit'],2,'.','')],
        ];
        return $this->csvResponse('unit-'.$apt->unit_number.'-'.$apt->project->name.'.csv',
            ['Description / Item','Category / Floor','Expected / Unit Price','Amount'],
            $rows, $totals);
    }

    private function apartmentPdf(Request $request)
    {
        $d = $this->apartmentData($request);
        if (!$d) abort(400,'apartment_id required');
        $apt = $d['apartment'];
        return $this->pdfView('pdfs.reports.apartment', $d)
            ->download('unit-'.$apt->unit_number.'-'.$apt->project->name.'.pdf');
    }

}