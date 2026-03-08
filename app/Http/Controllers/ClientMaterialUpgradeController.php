<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Apartment;
use App\Models\ApartmentMaterial;
use App\Models\AuditLog;
use App\Models\ClientMaterialUpgrade;
use App\Models\Invoice;
use App\Models\InventoryItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientMaterialUpgradeController extends Controller
{
    /**
     * Client upgrades a material on their apartment.
     *
     * Rules:
     * - The old material record is removed from the apartment (stock restored).
     * - The new material is recorded as a replacement.
     * - The client is charged the FULL cost of the new material via a standalone invoice.
     * - The old material is flagged as "client-replaced" so it is excluded from apartment costing.
     */
    public function store(Request $request, Apartment $apartment)
    {
        $data = $request->validate([
            'old_material_id'    => ['required', 'integer', 'exists:apartment_materials,id'],
            'new_inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'new_quantity'       => ['required', 'numeric', 'min:0.01'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        // Verify old material belongs to this apartment
        $oldMaterial = ApartmentMaterial::findOrFail($data['old_material_id']);
        abort_unless($oldMaterial->apartment_id === $apartment->id, 403, 'Material does not belong to this apartment.');

        // Must have a client contract to bill
        $contract = $apartment->contract;
        abort_unless($contract, 422, 'This apartment has no active client contract. Cannot create upgrade invoice.');

        $newItem = InventoryItem::findOrFail($data['new_inventory_item_id']);

        if ($newItem->quantity < $data['new_quantity']) {
            return back()->with('error', "Not enough stock for {$newItem->name}. Available: {$newItem->quantity} {$newItem->unit}.");
        }

        $totalAmount = round((float)$newItem->price * (float)$data['new_quantity'], 2);

        return DB::transaction(function () use ($apartment, $contract, $oldMaterial, $newItem, $data, $totalAmount) {

            // 1. Restore stock for old material
            $oldItem = InventoryItem::find($oldMaterial->inventory_item_id);
            if ($oldItem) {
                $oldItem->increment('quantity', $oldMaterial->quantity_needed);
                $oldItem->is_out_of_stock = false;
                $oldItem->save();
            }

            // 2. Remove old material record
            $oldItemId  = $oldMaterial->inventory_item_id;
            $oldQty     = $oldMaterial->quantity_needed;
            $oldMaterial->delete();

            // 3. Deduct stock for new material
            $newItem->decrement('quantity', $data['new_quantity']);
            $newItem->is_out_of_stock = $newItem->quantity <= 0;
            $newItem->save();

            // 4. Add new material to apartment
            $newMaterial = $apartment->materials()->create([
                'inventory_item_id' => $newItem->id,
                'quantity_needed'   => $data['new_quantity'],
                'unit'              => $newItem->unit,
            ]);

            // 5. Create a standalone upgrade invoice on the client's contract
            $invoiceNumber = sprintf(
                'UPG-%06d-%s',
                $contract->id,
                now()->format('YmdHis')
            );

            $invoice = Invoice::create([
                'contract_id'    => $contract->id,
                'invoice_number' => $invoiceNumber,
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->addDays(14)->toDateString(),
                'amount'         => $totalAmount,
                'status'         => 'pending',
            ]);

            // 6. Record the upgrade for history/reporting
            ClientMaterialUpgrade::create([
                'apartment_id'          => $apartment->id,
                'contract_id'           => $contract->id,
                'invoice_id'            => $invoice->id,
                'old_inventory_item_id' => $oldItemId,
                'old_quantity'          => $oldQty,
                'new_inventory_item_id' => $newItem->id,
                'new_quantity'          => $data['new_quantity'],
                'unit_price_snapshot'   => $newItem->price,
                'total_amount'          => $totalAmount,
                'notes'                 => $data['notes'] ?? null,
                'created_by'            => auth()->id(),
            ]);

            // 7. Generate PDF for the invoice
            GenerateInvoicePdfJob::dispatchSync($invoice->id);

            // Audit
            $audit = new AuditLog();
            $audit->user_id     = auth()->id();
            $audit->event       = 'Create';
            $audit->entity_type = 'Client Material Upgrade';
            $audit->details     = "Client upgraded material on unit {$apartment->unit_number}: replaced item #{$oldItemId} with {$newItem->name} (x{$data['new_quantity']}). Invoice {$invoiceNumber} created for \${$totalAmount}.";
            $audit->save();
            $audit->record = 'UPG-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
            $audit->save();

            return back()->with('success', "Upgrade recorded. Invoice {$invoiceNumber} ($ {$totalAmount}) created for the client.");
        });
    }
}