<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInkindItem extends Model
{
    protected $table = 'purchase_inkind_items';

    protected $fillable = [
        'receipt_ref',
        'inventory_item_id',
        'quantity',
        'unit_price_snapshot',
        'total_value',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity'            => 'decimal:3',
        'unit_price_snapshot' => 'decimal:2',
        'total_value'         => 'decimal:2',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class)->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
