<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerInKindPayment extends Model
{
    protected $fillable = [
        'worker_payment_id',
        'worker_contract_id',
        'payment_date',
        'total_estimated_value',
        'notes',
        'receipt_path',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function workerPayment()  { return $this->belongsTo(WorkerPayment::class); }
    public function workerContract() { return $this->belongsTo(WorkerContract::class); }
    public function items()          { return $this->hasMany(WorkerInKindPaymentItem::class); }
    public function createdBy()      { return $this->belongsTo(User::class, 'created_by'); }
}