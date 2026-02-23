<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\AuditLog;
use App\Models\ClientProfile;
use App\Models\InventoryItem;
use App\Models\InventoryPurchase;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\OperatingExpense;
use App\Models\Project;
use App\Models\User;

use App\Services\CashAccountingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class PagesController extends Controller
{
    public function loginPage(){
        return view('login');
    }
    public function dashboardPage(CashAccountingService $acct){
        
    $totalProducts = InventoryItem::count();
    $totalStock = (int) InventoryItem::sum('quantity');
    $outOfStock = InventoryItem::where('is_out_of_stock', true)->count();
    $totalUsers = User::whereIn('role', ['admin', 'client'])->count();  
    $totalOrders= Apartment::where('status','sold')->count();
    // Pie chart data: group inventory items by type
    $byType = InventoryItem::select('type', DB::raw('COUNT(*) as total'))
        ->groupBy('type')
        ->pluck('total', 'type'); // => ['internal' => 5, 'external' => 2...]
    $totalItems = $byType->sum(); // total count of all types
    // Normalize into arrays for chartjs
    $pieLabels = $byType->keys()->values()->all();
    $pieValues = $byType->map(function ($count) use ($totalItems) {
    return $totalItems > 0 ? round(($count / $totalItems) * 100, 2) : 0;
    })->values()->all();

    // Top clients (fallback)
    $topClients = ClientProfile::orderBy('user_id', 'desc')
        ->limit(10)
        ->get();
    //expenses Chart
$labels = collect(range(5, 0))
    ->map(fn ($i) => Carbon::now()->subMonths($i)->format('M Y')) // 'Sep 2025'
    ->all();
    $start = Carbon::now()->subMonths(5)->startOfMonth();
$end   = Carbon::now()->endOfMonth();
     $summary = $acct->lastMonthsSummary(6);
    return view('dashboard', compact(
        'totalProducts',
        'totalStock',
        'outOfStock',
        'totalUsers',
        'pieLabels',
        'pieValues',
        'topClients',
        'totalOrders'
    ),
    ['labels' => $summary['labels'],
        'revenues' => $summary['revenues'],
        'expenses' => $summary['expenses'],
        'net' => $summary['net'],]);
    }
     public function inventoryPage(){
        $items = InventoryItem::orderBy('created_at', 'desc')->take(4)->get();

    $chartLabels = $items->pluck('name');
    $chartValues = $items->pluck('quantity');
        return view('inventory', [
        'items' => $items,
        'chartLabels' => $chartLabels,
        'chartValues' => $chartValues,
    ]);
    }
    public function stockControlPage(){
        $items = InventoryItem::latest()->paginate(15);
        return view('stockControl', compact('items'));
    }
    public function stockInfoPage(){
        $items = InventoryItem::latest()->paginate(15);
        return view('stockInfo', compact('items'));
    }
    public function addItemPage(){
        return view('addItem');
    }
    public function editItemPage(InventoryItem $inventoryItem){
        return view('editItem', compact('inventoryItem'));
    }
    public function clientPage(){
        $clients_count=ClientProfile::count();
        $clients_volume=ClientProfile::with('contract')->get()->sum(function($client){
            return $client->contract ? $client->contract->final_price : 0;
        });
        $existingClients=ClientProfile::orderBy('created_at','desc')->limit(7)->get();
        return view('clients',compact('clients_count','existingClients','clients_volume'));
    }
    public function addClientPage(){
                $apartments=Apartment::where('status','available')->get();

        return view('addClient',compact('apartments'));
    }
    public function editClientPage(User $user){
           // Load what you need for the form
    $user->load(['clientProfile', 'contracts.apartment.project']); // if you use contracts()

    // If you truly have only one contract per client, get it:
    $contract = $user->contracts()->with(['apartment.project'])->latest('id')->first();

    $apartments = Apartment::with('project')
        ->whereNull('deleted_at')
        ->whereIn('status', ['available', 'reserved']) // adjust to your statuses
        ->orderBy('unit_number')
        ->get();
        return view('editClient', compact('user', 'contract', 'apartments'));
    }
    public function existingClientsPage(){
        $clients=ClientProfile::with([
            'user',
            'contract.apartment.project',
            'contract.invoices'
        ])->get();
        return view('existingClients',compact('clients'));
    }
    public function apartmentsPage(){
         $soldCount = Apartment::
        where('status', 'sold')
        ->count();

    $notSoldCount = Apartment::
        whereIn('status', ['available', 'reserved'])
        ->count();
        $projects = Project::orderBy('created_at', 'desc')
        ->limit(8) // show only latest 8 in the widget
        ->get(['id', 'name']);

        return view('apartments', compact(
        'soldCount',
        'notSoldCount',
        'projects'
    ));
    }
    public function createProjectPage(){
        $inventoryItems = InventoryItem::orderBy('name')->get(['id', 'name', 'unit']);
        return view('createProject', compact('inventoryItems'));
    }
    public function editProjectPage(Project $project){
        $inventoryItems = InventoryItem::orderBy('name')->get(['id', 'name', 'unit']);
         $existingFloors = $project->floors
        ->sortBy('floor_number')
        ->values()
        ->map(function ($floor) {
            return [
                'id' => $floor->id,
                'floor_number' => $floor->floor_number,
                'units' => $floor->apartments
                    ->values()
                    ->map(function ($a) {
                        return [
                            'id' => $a->id,
                            'unit_code' => $a->unit_number,
                            'bedrooms' => $a->bedrooms,
                            'bathrooms' => $a->bathrooms,
                            'area_m2' => $a->area_sqm,
                            'price' => $a->price_total,
                            'status' => $a->status,
                            'note' => $a->notes,
                        ];
                    })
                    ->all(),
            ];
        })
        ->all();
        return view('editProject', compact('inventoryItems','project','existingFloors'));
    }
    public function projectPage(Project $project)
{
    // Load everything needed for the page
    $project->load([
        'manager',
        'floors.apartments',
        'inventoryItems', // pivot has quantity_needed, unit, quantity_used
    ]);

    // Flatten apartments from floors
    $apartments = $project->floors->flatMap(function ($floor) {
        return $floor->apartments;
    });

    $stats = [
        'floors'     => $project->floors->count(),
        'apartments' => $apartments->count(),
        'sold'       => $apartments->where('status', 'sold')->count(),
        'reserved'   => $apartments->where('status', 'reserved')->count(),
        'available'  => $apartments->where('status', 'available')->count(),
    ];

    return view('project', compact('project', 'stats'));
}

    public function existingProjectsPage(){
        $projects = Project::query()
        ->leftJoin('apartments', 'apartments.project_id', '=', 'projects.id')
        ->select([
            'projects.id',
            'projects.code',
            'projects.name',
            'projects.city',
            'projects.area',
            DB::raw('COUNT(DISTINCT apartments.floor_id) as floors_count'),
            DB::raw("SUM(CASE WHEN apartments.status = 'sold' THEN 1 ELSE 0 END) as sold_count"),
            DB::raw("SUM(CASE WHEN apartments.status != 'sold' THEN 1 ELSE 0 END) as not_sold_count"),
        ])
        ->groupBy('projects.id', 'projects.code', 'projects.name', 'projects.city', 'projects.area')
        ->orderByDesc('projects.created_at')
        ->get();

    return view('existingProjects', compact('projects'));
    }
    public function settingsPage(){

    $currentUserRole = auth()->user()->role;
        $employees = User::whereIn('role', ['admin'])->get(['id', 'name', 'phone', 'email', 'role']);
        
        $auditLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->get();
        return view('settings', compact('employees', 'currentUserRole', 'auditLogs'));
    }
  public function invoicesPage(){
         $invoices = Invoice::with(['contract.client', 'contract.project', 'contract.apartment'])
           
            ->orderBy('issue_date','asc') 
            ->get();

        return view('invoicesManage', compact('invoices'));
    }
    public function accountingPage(CashAccountingService $acct){
        $summary = $acct->lastMonthsSummary(6);
        $purchases = InventoryPurchase::with('item')
        ->orderByDesc('purchase_date')
        ->orderByDesc('id')
        ->take(20)
        ->get();

    $opExpenses = OperatingExpense::orderByDesc('expense_date')
        ->orderByDesc('id')
        ->take(20)
        ->get();

        $revenuesRows = LedgerEntry::where('direction', 'in')
    ->where('source_type', 'invoice') // only real revenue cash-in
    ->orderByDesc('posted_at')
    ->take(20)
    ->get();
        return view('accounting.overview', [
            'labels' => $summary['labels'],
            'revenues' => $summary['revenues'],
            'expenses' => $summary['expenses'],
            'net' => $summary['net'],
            'purchases' => $purchases,
        'opExpenses' => $opExpenses,
        'revenuesRows' => $revenuesRows,
        ]);
    }
    public function accountingPurchasesPage()
{
    $items = InventoryItem::orderBy('name')->get();
    return view('accounting.purchases', compact('items'));
}

public function accountingExpensesPage()
{
    // you can also load categories from DB later if you want
    $categories = [
        'rent', 'salaries', 'utilities', 'internet', 'fuel',
        'maintenance', 'marketing', 'supplies', 'legal', 'other'
    ];

    return view('accounting.expenses', compact('categories'));
}
}