<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
protected $fillable=[
    'apartment_id',
    'contract_date',
    'payment_start_date',
    'discount',
    'down_payment',
    'installment_months' ,
    'installment_amount',
    'late_fee',
    'notes',
    'client_user_id',
    'project_id',
    'final_price',
    'total_price',
    'pdf_path',
'processing_status',
'processing_progress',
'processing_error',
'processing_started_at',
'processing_finished_at',
'created_by',

];
protected $casts = [
    'contract_date' => 'date',
    'payment_start_date' => 'date',
    'processing_started_at' => 'datetime',
    'processing_finished_at' => 'datetime',
];

public function client()
{
    return $this->belongsTo(User::class, 'client_user_id');
}

public function project()
{
    return $this->belongsTo(Project::class);
}

public function apartment()
{
    return $this->belongsTo(Apartment::class);
}

public function createdBy()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function installments()
{
    return $this->hasMany(Installment::class);
}

public function invoices()
{
    return $this->hasMany(Invoice::class);
}

public function nextPendingInvoice()
{
    return $this->hasOne(Invoice::class)
        ->where('status', 'pending')
        ->orderBy('due_date','desc');
}
}