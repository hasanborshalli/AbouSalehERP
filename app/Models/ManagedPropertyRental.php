<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagedPropertyRental extends Model
{
    protected $fillable = [
        'managed_property_id',
        'tenant_name', 'tenant_phone', 'tenant_email',
        'monthly_rent', 'owner_monthly_share', 'company_monthly_commission',
        'deposit_amount', 'deposit_returned_at',
        'start_date', 'end_date', 'actual_end_date',
        'status', 'notes', 'pdf_path', 'created_by',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'actual_end_date'     => 'date',
        'deposit_returned_at' => 'datetime',
        'monthly_rent'        => 'decimal:2',
        'owner_monthly_share' => 'decimal:2',
        'company_monthly_commission' => 'decimal:2',
        'deposit_amount'      => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(ManagedProperty::class, 'managed_property_id');
    }

    public function payments()
    {
        return $this->hasMany(ManagedPropertyRentalPayment::class);
    }

    public function pendingPayments()
    {
        return $this->hasMany(ManagedPropertyRentalPayment::class)
                    ->where('status', 'pending');
    }

    /** Total rent collected from tenant */
    public function totalCollected(): float
    {
        return (float) $this->payments()->whereNotNull('collected_at')->sum('amount_collected');
    }

    /** Total paid out to owner */
    public function totalOwnerPaid(): float
    {
        return (float) $this->payments()->whereNotNull('owner_paid_at')->sum('owner_paid_amount');
    }

    /** Total company commission earned */
    public function totalCommission(): float
    {
        return (float) $this->payments()->where('status', 'owner_paid')->sum('company_commission');
    }
}