<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\OperatingExpense;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        $projects = Project::with('floors.apartments.floor')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'code', 'city']);

        // Pre-build the apartment routes map keyed by project_id
        $aptRoutesMap = [];
        foreach ($projects as $proj) {
            $apts = [];
            foreach ($proj->floors as $floor) {
                foreach ($floor->apartments as $apt) {
                    $apts[] = [
                        'id'     => $apt->id,
                        'label'  => 'Unit ' . $apt->unit_number . ' (Floor ' . $floor->floor_number . ')',
                        'status' => $apt->status,
                        'url'    => route('reports.apartment', $apt->id),
                    ];
                }
            }
            $aptRoutesMap[$proj->id] = $apts;
        }
        $aptRoutesJson = json_encode($aptRoutesMap);

        return view('reports.index', compact('projects', 'aptRoutesJson'));
    }

    public function byProject(Project $project)
    {
        $project->load([
            'floors.apartments.contract.invoices',
            'floors.apartments.materials.inventoryItem',
            'floors.apartments.additionalCosts',
            'inventoryUsages.inventoryItem',
            'additionalCosts',
        ]);

        $apartments = $project->floors->flatMap(fn($f) => $f->apartments);

        // ── Project-level materials cost ──
        $projectMaterialsCost = $project->inventoryUsages->sum(function ($usage) {
            $price = (float) ($usage->inventoryItem->price ?? 0);
            return (float) $usage->quantity_needed * $price;
        });

        // ── Apartment-level materials cost (all apartments) ──
        $apartmentMaterialsCost = $apartments->sum(function ($apt) {
            return $apt->materials->sum(function ($m) {
                $price = (float) ($m->inventoryItem->price ?? 0);
                return (float) $m->quantity_needed * $price;
            });
        });

        // ── Project additional costs ──
        $projCosts = $project->additionalCosts;
        $projCostsExpected = $projCosts->sum('expected_amount');
        // Only count actual when settled; unsettled costs are still pending — not yet a real expense
        $projCostsActual = $projCosts->sum(fn($c) => $c->isSettled() ? (float) $c->actual_amount : 0.0);

        // ── Apartment additional costs ──
        $aptCostsExpected = $apartments->sum(fn($apt) => $apt->additionalCosts->sum('expected_amount'));
        $aptCostsActual   = $apartments->sum(fn($apt) => $apt->additionalCosts->sum(
            fn($c) => $c->isSettled() ? (float) $c->actual_amount : 0.0
        ));

        // ── Revenues (paid invoices + down payments) ──
        $contracts = $apartments->map(fn($a) => $a->contract)->filter();
        $paidInvoicesTotal = $contracts->sum(fn($c) => $c->invoices->where('status', 'paid')->sum('amount'));
        $downPaymentsTotal = $contracts->sum('down_payment');
        $totalRevenue      = $paidInvoicesTotal + $downPaymentsTotal;

        // ── Total selling price potential ──
        $totalSellingPrice = $apartments->sum('price_total');

        // ── Total cost (actual where available, else expected) ──
        $totalCost = $projectMaterialsCost + $apartmentMaterialsCost + $projCostsActual + $aptCostsActual;

        // ── Profit ──
        $profit = $totalRevenue - $totalCost;

        // ── Stats ──
        $stats = [
            'total_apartments' => $apartments->count(),
            'sold'             => $apartments->where('status', 'sold')->count(),
            'reserved'         => $apartments->where('status', 'reserved')->count(),
            'available'        => $apartments->where('status', 'available')->count(),
        ];

        $inventoryItems = InventoryItem::orderBy('name')->get(['id', 'name', 'unit', 'quantity']);

        return view('reports.project', compact(
            'project', 'apartments', 'contracts',
            'projectMaterialsCost', 'apartmentMaterialsCost',
            'projCosts', 'projCostsExpected', 'projCostsActual',
            'aptCostsExpected', 'aptCostsActual',
            'paidInvoicesTotal', 'downPaymentsTotal', 'totalRevenue',
            'totalSellingPrice', 'totalCost', 'profit', 'stats',
            'inventoryItems'
        ));
    }

    public function byApartment(Apartment $apartment)
    {
        $apartment->load([
            'project',
            'floor',
            'contract.invoices',
            'contract.client',
            'materials.inventoryItem',
            'additionalCosts',
        ]);

        $contract = $apartment->contract;
        $invoices = $contract?->invoices ?? collect();

        // ── Materials cost ──
        $materialsCost = $apartment->materials->sum(function ($m) {
            $price = (float) ($m->inventoryItem->price ?? 0);
            return (float) $m->quantity_needed * $price;
        });

        // ── Additional costs ──
        $costsExpected = $apartment->additionalCosts->sum('expected_amount');
        $costsActual   = $apartment->additionalCosts->sum(fn($c) => $c->isSettled() ? (float) $c->actual_amount : 0.0);

        // ── Revenue ──
        $paidAmount  = (float) $invoices->where('status', 'paid')->sum('amount');
        $downPayment = (float) ($contract?->down_payment ?? 0);
        $totalRevenue = $paidAmount + $downPayment;

        // ── Cost & profit ──
        $totalCost = $materialsCost + $costsActual;
        $profit    = $totalRevenue - $totalCost;

        // ── Invoice breakdown ──
        $paidInvoices    = $invoices->where('status', 'paid');
        $pendingInvoices = $invoices->whereIn('status', ['pending', 'overdue']);

        return view('reports.apartment', compact(
            'apartment', 'contract', 'invoices',
            'paidInvoices', 'pendingInvoices',
            'materialsCost', 'costsExpected', 'costsActual',
            'paidAmount', 'downPayment', 'totalRevenue',
            'totalCost', 'profit'
        ));
    }

    // ── Profit & Loss ─────────────────────────────────────────────────────
    public function profitLoss(Request $request)
    {
        $dateFrom   = $request->input('date_from', now()->startOfYear()->toDateString());
        $dateTo     = $request->input('date_to',   now()->toDateString());
        $groupBy    = $request->input('group_by', 'month'); // month | source

        $baseQ = LedgerEntry::whereBetween(DB::raw('DATE(posted_at)'), [$dateFrom, $dateTo])
            ->whereNull('reverses_entry_id')
            ->where('amount', '>', 0);

        // exclude void entries
        $revenueQ  = (clone $baseQ)->where('direction','in');
        $expenseQ  = (clone $baseQ)->where('direction','out');

        $totalRevenue  = (float) $revenueQ->sum('amount');
        $totalExpenses = (float) $expenseQ->sum('amount');
        $netProfit     = $totalRevenue - $totalExpenses;

        // Revenue rows grouped
        $revenueRows = (clone $revenueQ)
            ->select('source_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as entries'))
            ->groupBy('source_type')->orderByDesc('total')->get();

        // Expense rows grouped
        $expenseRows = (clone $expenseQ)
            ->select('source_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as entries'))
            ->groupBy('source_type')->orderByDesc('total')->get();

        // Monthly trend
        $monthlyData = LedgerEntry::whereBetween(DB::raw('DATE(posted_at)'), [$dateFrom, $dateTo])
            ->whereNull('reverses_entry_id')->where('amount', '>', 0)
            ->select(
                DB::raw("DATE_FORMAT(posted_at, '%Y-%m') as month"),
                'direction',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month','direction')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        return view('reports.pl', compact(
            'dateFrom','dateTo','groupBy',
            'totalRevenue','totalExpenses','netProfit',
            'revenueRows','expenseRows','monthlyData'
        ));
    }

    // ── Sales Pipeline ────────────────────────────────────────────────────
    public function salesPipeline(Request $request)
    {
        $projectId = $request->input('project_id');
        $status    = $request->input('status');

        $query = Apartment::with(['project','floor','contract.invoices','contract.client'])
            ->orderBy('project_id')->orderBy('id');

        if ($projectId) $query->where('project_id', $projectId);
        if ($status)    $query->where('status', $status);

        $apartments = $query->get();

        $projects = Project::orderBy('name')->get(['id','name','code']);

        $totalUnits     = $apartments->count();
        $totalSold      = $apartments->where('status','sold')->count();
        $totalReserved  = $apartments->where('status','reserved')->count();
        $totalAvailable = $apartments->where('status','available')->count();
        $totalValue     = (float) $apartments->sum('price_total');

        $totalCollected = $apartments->sum(function ($apt) {
            $c = $apt->contract;
            if (!$c) return 0;
            return (float)$c->down_payment + (float)$c->invoices->where('status','paid')->sum('amount');
        });

        $totalOutstanding = $apartments->sum(function ($apt) {
            $c = $apt->contract;
            if (!$c) return 0;
            return (float)$c->invoices->whereIn('status',['pending','overdue'])->sum('amount');
        });

        return view('reports.sales-pipeline', compact(
            'apartments','projects','projectId','status',
            'totalUnits','totalSold','totalReserved','totalAvailable',
            'totalValue','totalCollected','totalOutstanding'
        ));
    }

    // ── Outstanding Invoices ──────────────────────────────────────────────
    public function outstandingInvoices(Request $request)
    {
        $projectId = $request->input('project_id');
        $overdue   = $request->input('overdue'); // '' | '1'
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = Invoice::with(['contract.apartment.project','contract.client'])
            ->whereIn('status',['pending','overdue'])
            ->whereNull('deleted_at')
            ->orderBy('due_date');

        if ($projectId) {
            $query->whereHas('contract.apartment', fn($q) => $q->where('project_id',$projectId));
        }
        if ($overdue === '1') {
            $query->where('status','overdue');
        }
        if ($dateFrom) $query->whereDate('due_date','>=',$dateFrom);
        if ($dateTo)   $query->whereDate('due_date','<=',$dateTo);

        $invoices  = $query->get();
        $projects  = Project::orderBy('name')->get(['id','name']);
        $today     = now()->toDateString();

        $totalCount    = $invoices->count();
        $totalAmount   = (float) $invoices->sum('amount');
        $overdueCount  = $invoices->where('status','overdue')->count();
        $overdueAmount = (float) $invoices->where('status','overdue')->sum('amount');

        return view('reports.outstanding-invoices', compact(
            'invoices','projects','projectId','overdue','dateFrom','dateTo','today',
            'totalCount','totalAmount','overdueCount','overdueAmount'
        ));
    }

    // ── Worker Payments ───────────────────────────────────────────────────
    public function workerPayments(Request $request)
    {
        $status    = $request->input('status');   // '' | 'paid' | 'pending'
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');
        $workerId  = $request->input('worker_id');

        $query = WorkerPayment::with(['contract.worker','contract.project'])
            ->whereNull('deleted_at')
            ->orderBy('due_date');

        if ($status)   $query->where('status', $status);
        if ($dateFrom) $query->whereDate('due_date','>=',$dateFrom);
        if ($dateTo)   $query->whereDate('due_date','<=',$dateTo);
        if ($workerId) $query->whereHas('contract', fn($q) => $q->where('worker_user_id',$workerId));

        $payments = $query->get();

        $workers = User::where('role','worker')->orderBy('name')->get(['id','name']);

        $totalPaid    = (float) $payments->where('status','paid')->sum('amount');
        $totalPending = (float) $payments->where('status','pending')->sum('amount');
        $totalAll     = $totalPaid + $totalPending;
        $countPending = $payments->where('status','pending')->count();
        $countOverdue = $payments->where('status','pending')->filter(fn($p)=>$p->due_date < now())->count();

        return view('reports.worker-payments', compact(
            'payments','workers','status','dateFrom','dateTo','workerId',
            'totalPaid','totalPending','totalAll','countPending','countOverdue'
        ));
    }

    // ── Operating Expenses ────────────────────────────────────────────────
    public function operatingExpenses(Request $request)
    {
        $category  = $request->input('category');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = OperatingExpense::whereNull('voided_at')->orderByDesc('expense_date');

        if ($category) $query->where('category', $category);
        if ($dateFrom) $query->whereDate('expense_date','>=',$dateFrom);
        if ($dateTo)   $query->whereDate('expense_date','<=',$dateTo);

        $expenses   = $query->get();
        $categories = OperatingExpense::whereNull('voided_at')->distinct()->orderBy('category')->pluck('category');

        $totalAmount = (float) $expenses->sum('amount');
        $byCategory  = $expenses->groupBy('category')->map(fn($g)=>(float)$g->sum('amount'))->sortDesc();
        $countRows   = $expenses->count();

        return view('reports.operating-expenses', compact(
            'expenses','categories','category','dateFrom','dateTo',
            'totalAmount','byCategory','countRows'
        ));
    }

}