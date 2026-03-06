<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InKindPayment extends Model
{
    protected $fillable = [
        'contract_id',
        'invoice_id',
        'payment_date',
        'total_estimated_value',
        'notes',
        'receipt_path',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function contract()  { return $this->belongsTo(Contract::class); }
    public function invoice()   { return $this->belongsTo(Invoice::class); }
    public function items()     { return $this->hasMany(InKindPaymentItem::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
