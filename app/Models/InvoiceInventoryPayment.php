<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceInventoryPayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'inventory_item_id',
        'quantity_used',
        'unit_price',
        'total_value',
        'notes',
        'created_by',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
