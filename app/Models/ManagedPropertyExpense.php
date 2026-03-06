<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagedPropertyExpense extends Model
{
    protected $fillable = [
        'managed_property_id', 'description', 'category',
        'amount', 'expense_date', 'vendor_name', 'notes',
        'voided_at', 'voided_by', 'void_reason', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'voided_at'    => 'datetime',
        'amount'       => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(ManagedProperty::class, 'managed_property_id');
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }
}