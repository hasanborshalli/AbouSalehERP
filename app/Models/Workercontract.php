<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerContract extends Model
{
    protected $fillable = [
        'worker_user_id',
        'project_id',
        'project_ids',
        'project_costs',
        'apartment_id',
        'apartment_ids',
        'apartment_costs',
        'managed_property_ids',
        'managed_property_costs',
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
        'contract_date'           => 'date',
        'start_date'              => 'date',
        'expected_end_date'       => 'date',
        'first_payment_date'      => 'date',
        'project_ids'             => 'array',
        'project_costs'           => 'array',
        'apartment_ids'           => 'array',
        'apartment_costs'         => 'array',
        'managed_property_ids'    => 'array',
        'managed_property_costs'  => 'array',
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

    public function managedProperties()
    {
        return $this->belongsToMany(\App\Models\ManagedProperty::class, 'worker_contract_managed_properties', 'worker_contract_id', 'managed_property_id');
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

    /** All linked managed property IDs */
    public function allManagedPropertyIds(): array
    {
        return array_values(array_unique(array_filter($this->managed_property_ids ?? [])));
    }

    /** All linked project IDs merged */
    public function allProjectIds(): array
    {
        $ids = $this->project_ids ?? [];
        if ($this->project_id && !in_array($this->project_id, $ids)) {
            $ids[] = $this->project_id;
        }
        return array_values(array_unique(array_filter($ids)));
    }

    /** All linked apartment IDs merged */
    public function allApartmentIds(): array
    {
        $ids = $this->apartment_ids ?? [];
        if ($this->apartment_id && !in_array($this->apartment_id, $ids)) {
            $ids[] = $this->apartment_id;
        }
        return array_values(array_unique(array_filter($ids)));
    }
}