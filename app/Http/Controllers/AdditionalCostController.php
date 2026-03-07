<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentAdditionalCost;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectAdditionalCost;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;

class AdditionalCostController extends Controller
{
    // ─────────────────────────────────────────────
    // PROJECT-LEVEL ADDITIONAL COSTS
    // ─────────────────────────────────────────────

    public function storeProjectCost(Request $request, Project $project)
    {
        $data = $request->validate([
            'description'     => ['required', 'string', 'max:255'],
            'category'        => ['nullable', 'string', 'max:80'],
            'expected_amount' => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $project->additionalCosts()->create($data);

        // NOTE: No cash-out posted here. Cash flows only when the cost is settled (actual_amount entered).

        $this->auditLog('Create', 'Project Additional Cost',
            "Added cost '{$data['description']}' (\${$data['expected_amount']}) to project {$project->name}.");

        return back()->with('success', 'Additional cost added.');
    }

    public function settleProjectCost(Request $request, Project $project, ProjectAdditionalCost $cost, CashAccountingService $cash)
    {
        abort_unless($cost->project_id === $project->id, 404);
        abort_if($cost->isSettled(), 422, 'This cost is already settled.');

        $data = $request->validate([
            'actual_amount' => ['required', 'numeric', 'min:0'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $actual   = (float) $data['actual_amount'];
        $expected = (float) $cost->expected_amount;
        $variance = $actual - $expected;

        $cost->update([
            'actual_amount'     => $actual,
            'actual_entered_at' => now(),
            'actual_entered_by' => auth()->id(),
            'notes'             => $data['notes'] ?? $cost->notes,
        ]);

        // Worker-contract costs are accounted for when the worker payment is marked paid.
        // Posting cash-out here would double-count. For all other cost types, post now.
        if ($cost->category !== 'worker_contract') {
            $cash->createOperatingExpense([
                'expense_date' => now()->toDateString(),
                'category'     => 'project_additional_cost',
                'amount'       => $actual,
                'description'  => "{$cost->description} (Project: {$project->name})",
            ], auth()->id());

            if ($variance < -0.001) {
                $cash->postCostSaving(
                    abs($variance),
                    "Under-budget saving: {$cost->description} (Project: {$project->name})",
                    'project_cost_saving',
                    $cost->id,
                    auth()->id()
                );
            }
        }

        $this->auditLog('Update', 'Project Additional Cost',
            "Settled project cost #{$cost->id} for project {$project->name}: expected \${$expected}, actual \${$actual}");

        return back()->with('success', 'Actual cost saved' . ($variance != 0 ? ' and ledger updated.' : '.'));
    }

    public function destroyProjectCost(Project $project, ProjectAdditionalCost $cost)
    {
        abort_unless($cost->project_id === $project->id, 404);
        $this->auditLog('Delete', 'Project Additional Cost',
            "Deleted cost '{$cost->description}' (\${$cost->expected_amount}) from project {$project->name}.");
        $cost->delete();
        return back()->with('success', 'Cost entry removed.');
    }

    // ─────────────────────────────────────────────
    // APARTMENT-LEVEL ADDITIONAL COSTS
    // ─────────────────────────────────────────────

    public function storeApartmentCost(Request $request, Apartment $apartment)
    {
        $data = $request->validate([
            'description'     => ['required', 'string', 'max:255'],
            'category'        => ['nullable', 'string', 'max:80'],
            'expected_amount' => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $apartment->additionalCosts()->create($data);

        // NOTE: No cash-out posted here. Cash flows only when the cost is settled (actual_amount entered).

        $this->auditLog('Create', 'Apartment Additional Cost',
            "Added cost '{$data['description']}' (\${$data['expected_amount']}) to unit {$apartment->unit_number}.");

        return back()->with('success', 'Additional cost added to apartment.');
    }

    public function settleApartmentCost(Request $request, Apartment $apartment, ApartmentAdditionalCost $cost, CashAccountingService $cash)
    {
        abort_unless($cost->apartment_id === $apartment->id, 404);
        abort_if($cost->isSettled(), 422, 'This cost is already settled.');

        $data = $request->validate([
            'actual_amount' => ['required', 'numeric', 'min:0'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $actual   = (float) $data['actual_amount'];
        $expected = (float) $cost->expected_amount;
        $variance = $actual - $expected;

        $cost->update([
            'actual_amount'     => $actual,
            'actual_entered_at' => now(),
            'actual_entered_by' => auth()->id(),
            'notes'             => $data['notes'] ?? $cost->notes,
        ]);

        // Worker-contract costs are accounted for when the worker payment is marked paid.
        // Posting cash-out here would double-count. For all other cost types, post now.
        if ($cost->category !== 'worker_contract') {
            $cash->createOperatingExpense([
                'expense_date' => now()->toDateString(),
                'category'     => 'apartment_additional_cost',
                'amount'       => $actual,
                'description'  => "{$cost->description} (Unit: {$apartment->unit_number})",
            ], auth()->id());

            if ($variance < -0.001) {
                $cash->postCostSaving(
                    abs($variance),
                    "Under-budget saving: {$cost->description} (Unit: {$apartment->unit_number})",
                    'apartment_cost_saving',
                    $cost->id,
                    auth()->id()
                );
            }
        }

        $this->auditLog('Update', 'Apartment Additional Cost',
            "Settled apt cost #{$cost->id} for unit {$apartment->unit_number}: expected \${$expected}, actual \${$actual}");

        return back()->with('success', 'Actual cost saved' . ($variance != 0 ? ' and ledger updated.' : '.'));
    }

    public function destroyApartmentCost(Apartment $apartment, ApartmentAdditionalCost $cost)
    {
        abort_unless($cost->apartment_id === $apartment->id, 404);
        $this->auditLog('Delete', 'Apartment Additional Cost',
            "Deleted cost '{$cost->description}' (\${$cost->expected_amount}) from unit {$apartment->unit_number}.");
        $cost->delete();
        return back()->with('success', 'Cost entry removed.');
    }

    // ─────────────────────────────────────────────
    // APARTMENT MATERIALS (add/remove after creation)
    // ─────────────────────────────────────────────

    public function storeApartmentMaterial(Request $request, Apartment $apartment)
    {
        $data = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'quantity_needed'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $item = \App\Models\InventoryItem::lockForUpdate()->findOrFail($data['inventory_item_id']);

        if ($item->quantity < $data['quantity_needed']) {
            return back()->with('error', "Not enough stock for {$item->name}. Available: {$item->quantity} {$item->unit}.");
        }

        $item->decrement('quantity', $data['quantity_needed']);
        $item->is_out_of_stock = $item->quantity <= 0;
        $item->save();

        $apartment->materials()->create([
            'inventory_item_id' => $item->id,
            'quantity_needed'   => $data['quantity_needed'],
            'unit'              => $item->unit,
        ]);

        $this->auditLog('Create', 'Apartment Material',
            "Added {$data['quantity_needed']} {$item->unit} of {$item->name} to unit {$apartment->unit_number}.");

        return back()->with('success', "Material added: {$item->name}.");
    }

    public function destroyApartmentMaterial(Apartment $apartment, \App\Models\ApartmentMaterial $material)
    {
        abort_unless($material->apartment_id === $apartment->id, 404);

        // Restore stock
        $item = \App\Models\InventoryItem::find($material->inventory_item_id);
        if ($item) {
            $item->increment('quantity', $material->quantity_needed);
            $item->is_out_of_stock = false;
            $item->save();
        }

        $itemName = $item?->name ?? "Item #{$material->inventory_item_id}";
        $material->delete();
        $this->auditLog('Delete', 'Apartment Material',
            "Removed {$material->quantity_needed} {$material->unit} of {$itemName} from unit {$apartment->unit_number}. Stock restored.");
        return back()->with('success', 'Material removed and stock restored.');
    }

    // ─────────────────────────────────────────────
    // PROJECT-LEVEL MATERIALS (add/remove after creation)
    // ─────────────────────────────────────────────

    public function storeProjectMaterial(Request $request, Project $project)
    {
        $data = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'quantity_needed'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $item = \App\Models\InventoryItem::lockForUpdate()->findOrFail($data['inventory_item_id']);

        if ($item->quantity < $data['quantity_needed']) {
            return back()->with('error', "Not enough stock for {$item->name}. Available: {$item->quantity} {$item->unit}.");
        }

        // If this item is already assigned to the project, add to its quantity instead of inserting a duplicate
        $existing = \App\Models\ProjectInventoryItem::where('project_id', $project->id)
            ->where('inventory_item_id', $item->id)
            ->first();

        $item->decrement('quantity', $data['quantity_needed']);
        $item->is_out_of_stock = $item->quantity <= 0;
        $item->save();

        if ($existing) {
            $existing->increment('quantity_needed', $data['quantity_needed']);
            $this->auditLog('Update', 'Project Material', "Added {$data['quantity_needed']} {$item->unit} of {$item->name} to project {$project->name} (total: {$existing->quantity_needed}).");
            return back()->with('success', "Updated quantity for {$item->name}.");
        }

        \App\Models\ProjectInventoryItem::create([
            'project_id'        => $project->id,
            'inventory_item_id' => $item->id,
            'quantity_needed'   => $data['quantity_needed'],
            'unit'              => $item->unit,
        ]);

        $this->auditLog('Create', 'Project Material', "Added {$data['quantity_needed']} {$item->unit} of {$item->name} to project {$project->name}.");

        return back()->with('success', "Material added: {$item->name}.");
    }

    public function destroyProjectMaterial(Project $project, \App\Models\ProjectInventoryItem $material)
    {
        abort_unless($material->project_id === $project->id, 404);

        $item = \App\Models\InventoryItem::find($material->inventory_item_id);
        if ($item) {
            $item->increment('quantity', $material->quantity_needed);
            $item->is_out_of_stock = false;
            $item->save();
        }

        $material->delete();
        return back()->with('success', 'Material removed and stock restored.');
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function auditLog(string $event, string $entityType, string $details): void
    {
        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = $event;
        $audit->entity_type = $entityType;
        $audit->details     = $details;
        $audit->save();
        $audit->record = 'CST-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();
    }
}