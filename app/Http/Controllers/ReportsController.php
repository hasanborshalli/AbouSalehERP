<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\InventoryItem;
use App\Models\Project;

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
}