<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAdditionalCost extends Model
{
    protected $fillable = [
        'project_id',
        'description',
        'category',
        'expected_amount',
        'actual_amount',
        'actual_entered_at',
        'actual_entered_by',
        'notes',
    ];

    protected $casts = [
        'expected_amount'   => 'decimal:2',
        'actual_amount'     => 'decimal:2',
        'actual_entered_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'actual_entered_by');
    }

    /** Whether actual cost has been recorded */
    public function isSettled(): bool
    {
        return !is_null($this->actual_amount);
    }

    /** Variance: positive = over budget, negative = under budget */
    public function variance(): ?float
    {
        if (!$this->isSettled()) return null;
        return (float) $this->actual_amount - (float) $this->expected_amount;
    }
}