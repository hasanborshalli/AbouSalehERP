<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagedPropertySale extends Model
{
    protected $fillable = [
        'managed_property_id',
        'buyer_name', 'buyer_phone', 'buyer_email',
        'sale_price', 'sale_date',
        'owner_payout_amount', 'owner_paid_at',
        'notes', 'created_by',
    ];

    protected $casts = [
        'sale_date'     => 'date',
        'owner_paid_at' => 'datetime',
        'sale_price'    => 'decimal:2',
        'owner_payout_amount' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(ManagedProperty::class, 'managed_property_id');
    }

    public function ownerIsPaid(): bool
    {
        return $this->owner_paid_at !== null;
    }
}