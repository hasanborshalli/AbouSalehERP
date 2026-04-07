<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerInKindPaymentItem extends Model
{
    protected $fillable = [
        'worker_in_kind_payment_id',
        'inventory_item_id',
        'quantity',
        'unit_price_snapshot',
        'total_value',
        'notes',
    ];

    public function payment()       { return $this->belongsTo(WorkerInKindPayment::class, 'worker_in_kind_payment_id'); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
}