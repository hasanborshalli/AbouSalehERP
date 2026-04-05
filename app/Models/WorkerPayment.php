<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkerPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'worker_contract_id',
        'payment_number',
        'installment_index',
        'due_date',
        'paid_at',
        'amount',
        'amount_paid',
        'status',
        'receipt_path',
        'marked_paid_by',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'paid_at'     => 'date',
        'amount'      => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(WorkerContract::class, 'worker_contract_id');
    }

    public function markedPaidBy()
    {
        return $this->belongsTo(User::class, 'marked_paid_by');
    }
}