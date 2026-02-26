<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryPurchase extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'purchase_date',
        'qty',
        'unit_cost',
        'total_cost',
        'vendor_name',
        'notes',
        'created_by',
        'voided_at','voided_by','void_reason'
    ];

    protected $casts = [
  'purchase_date' => 'date',
  'qty' => 'integer',
  'unit_cost' => 'decimal:2',
  'total_cost' => 'decimal:2',
  'voided_at' => 'datetime',
];
public function isVoided(): bool
{
    return !is_null($this->voided_at);
}
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id')->withTrashed();
    }
}