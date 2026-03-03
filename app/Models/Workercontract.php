<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerContract extends Model
{
    protected $fillable = [
        'worker_user_id',
        'project_id',
        'project_ids',
        'apartment_id',
        'apartment_ids',
        'scope_of_work',
        'category',
        'contract_date',
        'start_date',
        'expected_end_date',
        'total_amount',
        'payment_months',
        'monthly_amount',
        'first_payment_date',
        'notes',
        'pdf_path',
        'created_by',
    ];

    protected $casts = [
        'contract_date'      => 'date',
        'start_date'         => 'date',
        'expected_end_date'  => 'date',
        'first_payment_date' => 'date',
        'project_ids'        => 'array',
        'apartment_ids'      => 'array',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /** Resolve all linked projects (from project_ids JSON array) */
    public function linkedProjects()
    {
        $ids = $this->project_ids ?? [];
        if ($this->project_id && !in_array($this->project_id, $ids)) {
            $ids[] = $this->project_id;
        }
        return Project::whereIn('id', $ids)->get();
    }

    /** Resolve all linked apartments (from apartment_ids JSON array) */
    public function linkedApartments()
    {
        $ids = $this->apartment_ids ?? [];
        if ($this->apartment_id && !in_array($this->apartment_id, $ids)) {
            $ids[] = $this->apartment_id;
        }
        return \App\Models\Apartment::whereIn('id', $ids)->get();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(WorkerPayment::class)->orderBy('installment_index');
    }

    public function paidPayments()
    {
        return $this->hasMany(WorkerPayment::class)->where('status', 'paid');
    }

    public function pendingPayments()
    {
        return $this->hasMany(WorkerPayment::class)->where('status', 'pending')->orderBy('due_date');
    }

    public function totalPaid(): float
    {
        return (float) $this->paidPayments()->sum('amount');
    }

    public function totalPending(): float
    {
        return (float) $this->pendingPayments()->sum('amount');
    }
}