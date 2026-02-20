<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class InventoryController extends Controller
{

    public function store(Request $request, CashAccountingService $cash)
    {
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Create';
        $audit->entity_type='Inventory Item';
        $audit->details='Creating inventory item failed';
        $audit->save();
        $audit->record='ST-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        $data = $request->validate([
    'item_name' => ['required', 'string', 'max:255'],
    'item_price' => ['required', 'numeric', 'min:0'],
    'item_type' => ['required', Rule::in(['internal', 'external', 'sale'])],
    'item_quantity' => ['required', 'integer', 'min:0'],
    'item_image' => ['nullable', 'image', 'max:4096'],
    'item_unit' => ['nullable','string','max:50'],

    'purchase_date' => ['nullable', 'date'],
    'purchase_unit_cost' => ['nullable', 'numeric', 'min:0'],
    'vendor_name' => ['nullable', 'string', 'max:255'],
    'purchase_notes' => ['nullable', 'string', 'max:500'],
    'payment_method' => ['required', Rule::in(['cash','bank','other'])],
]);
if ((int)$data['item_quantity'] > 0 && blank($request->input('purchase_unit_cost'))) {
    return back()->withErrors([
        'purchase_unit_cost' => 'Unit cost is required when quantity is greater than 0.'
    ])->withInput();
}
        $imagePath = null;
        if ($request->hasFile('item_image')) {
            $imagePath = $request->file('item_image')->store('inventory', 'public');
        }

       $item = InventoryItem::create([
            'name' => $data['item_name'],
            'price' => $data['item_price'],
            'type' => $data['item_type'],
            'unit' => $data['item_unit'],
            'quantity' => 0,
            'is_out_of_stock' => true,
            'image_path' => $imagePath,
        ]);
        if ((int)$data['item_quantity'] > 0) {
$cash->createInventoryPurchase([
    'inventory_item_id' => $item->id,
    'purchase_date' => $request->input('purchase_date') ?? now()->toDateString(),
    'qty' => (int) $data['item_quantity'],
    'unit_cost' => (float) $request->input('purchase_unit_cost'),
    'vendor_name' => $request->input('vendor_name'),
    'payment_method' => $request->input('payment_method', 'cash'),
    'notes' => $request->input('purchase_notes') ?: 'Auto purchase from inventory create',
], auth()->id());

    // Important: if you do this, set item.quantity initially to 0 above,
    // because the purchase service will increase stock.
}
        $audit->details='Creating inventory item succeeded. Item name: '.$data['item_name'];
        $audit->save();
        return redirect()->route('inventory.stock-control')->with('success', 'Item added.');
    }


    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Update';
        $audit->entity_type='Inventory Item';
        $audit->details='Updating inventory item ('.$inventoryItem->name.') failed';
        $audit->save();
        $audit->record='ST-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'item_price' => ['required', 'numeric', 'min:0'],
            'item_type' => ['required', Rule::in(['internal', 'external', 'sale'])],
            'item_image' => ['nullable', 'image', 'max:4096'],
            'is_out_of_stock' => ['nullable'],
            'item_unit'=>['nullable','string']
        ]);

        // image replace (optional)
        if ($request->hasFile('item_image')) {
            if ($inventoryItem->image_path) {
                Storage::disk('public')->delete($inventoryItem->image_path);
            }
            $inventoryItem->image_path = $request->file('item_image')->store('inventory', 'public');
        }

        $inventoryItem->name = $data['item_name'];
        $inventoryItem->price = $data['item_price'];
        $inventoryItem->type = $data['item_type'];
        $inventoryItem->unit = $data['item_unit'];
$inventoryItem->is_out_of_stock = $request->boolean('is_out_of_stock') || ((int)$inventoryItem->quantity <= 0);
        $inventoryItem->save();
        $audit->details='Updating inventory item ('.$inventoryItem->name.') succeeded';
        $audit->save();
        return redirect()->route('inventory.stock-control')->with('success', 'Item updated.');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Delete';
        $audit->entity_type='Inventory Item';
        $audit->details='Deleting inventory item ('.$inventoryItem->name.') failed';
        $audit->save();
        $audit->record='ST-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        if ($inventoryItem->image_path) {
            Storage::disk('public')->delete($inventoryItem->image_path);
        }

        $inventoryItem->delete();
        $audit->details='Deleting inventory item ('.$inventoryItem->name.') succeeded';
        $audit->save();
        return redirect()->route('inventory.stock-control')->with('success', 'Item deleted.');
    }
}