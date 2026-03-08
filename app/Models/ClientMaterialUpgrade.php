<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMaterialUpgrade extends Model
{
    protected $fillable = [
        'apartment_id',
        'contract_id',
        'invoice_id',
        'old_inventory_item_id',
        'old_quantity',
        'new_inventory_item_id',
        'new_quantity',
        'unit_price_snapshot',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'old_quantity'         => 'decimal:3',
        'new_quantity'         => 'decimal:3',
        'unit_price_snapshot'  => 'decimal:2',
        'total_amount'         => 'decimal:2',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function oldInventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'old_inventory_item_id');
    }

    public function newInventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'new_inventory_item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}