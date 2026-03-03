<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentMaterial extends Model
{
    protected $fillable = [
        'apartment_id',
        'inventory_item_id',
        'quantity_needed',
        'unit',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class)->withTrashed();
    }
}