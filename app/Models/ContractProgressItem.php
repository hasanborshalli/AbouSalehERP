<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractProgressItem extends Model
{
    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'sort_order',
        'status',
        'weight',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}