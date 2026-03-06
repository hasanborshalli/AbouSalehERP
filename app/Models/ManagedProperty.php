<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagedProperty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_name', 'owner_phone', 'owner_email',
        'address', 'city', 'area',
        'bedrooms', 'bathrooms', 'area_sqm', 'description',
        'type', 'status',
        'owner_asking_price', 'estimated_renovation_cost',
        'agreed_listing_price', 'agreed_rent_price', 'company_commission_pct',
        'agreement_date', 'notes', 'pdf_path', 'created_by',
    ];

    protected $casts = [
        'agreement_date'           => 'date',
        'owner_asking_price'       => 'decimal:2',
        'estimated_renovation_cost'=> 'decimal:2',
        'agreed_listing_price'     => 'decimal:2',
        'agreed_rent_price'        => 'decimal:2',
        'company_commission_pct'   => 'decimal:3',
    ];

    // ── Relations ────────────────────────────────────────────

    public function expenses()
    {
        return $this->hasMany(ManagedPropertyExpense::class);
    }

    public function activeExpenses()
    {
        return $this->hasMany(ManagedPropertyExpense::class)
                    ->whereNull('voided_at');
    }

    public function sale()
    {
        return $this->hasOne(ManagedPropertySale::class);
    }

    public function rentals()
    {
        return $this->hasMany(ManagedPropertyRental::class);
    }

    public function activeRental()
    {
        return $this->hasOne(ManagedPropertyRental::class)->where('status', 'active');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Computed helpers ─────────────────────────────────────

    /** Total renovation/expense costs actually paid (not voided) */
    public function totalExpenses(): float
    {
        return (float) $this->activeExpenses()->sum('amount');
    }

    /** For flip: net company profit = sale_price - owner_payout - expenses */
    public function flipProfit(): ?float
    {
        if ($this->type !== 'flip' || !$this->sale) return null;

        return (float)$this->sale->sale_price
             - (float)$this->sale->owner_payout_amount
             - $this->totalExpenses();
    }

    /** For rental: total commissions collected so far */
    public function totalRentalCommission(): float
    {
        return (float) $this->rentals()
            ->with('payments')
            ->get()
            ->flatMap->payments
            ->where('status', 'owner_paid')
            ->sum('company_commission');
    }

    public function isFlip(): bool   { return $this->type === 'flip'; }
    public function isRental(): bool { return $this->type === 'rental'; }

    public function statusBadge(): array
    {
        return match($this->status) {
            'pending'    => ['label' => 'Pending',    'color' => '#d97706'],
            'active'     => ['label' => 'Active',     'color' => '#2563eb'],
            'sold'       => ['label' => 'Sold',       'color' => '#059669'],
            'rented'     => ['label' => 'Rented',     'color' => '#7c3aed'],
            'terminated' => ['label' => 'Terminated', 'color' => '#dc2626'],
            default      => ['label' => ucfirst($this->status), 'color' => '#6b7280'],
        };
    }
}
