<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagedPropertyRentalPayment extends Model
{
    protected $fillable = [
        'managed_property_rental_id',
        'due_date', 'amount_due', 'owner_share', 'company_commission',
        'amount_collected', 'collected_at',
        'owner_paid_amount', 'owner_paid_at',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'due_date'      => 'date',
        'collected_at'  => 'datetime',
        'owner_paid_at' => 'datetime',
        'amount_due'    => 'decimal:2',
        'owner_share'   => 'decimal:2',
        'company_commission' => 'decimal:2',
        'amount_collected'   => 'decimal:2',
        'owner_paid_amount'  => 'decimal:2',
    ];

    public function rental()
    {
        return $this->belongsTo(ManagedPropertyRental::class, 'managed_property_rental_id');
    }
}