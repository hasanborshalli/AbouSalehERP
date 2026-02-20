<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'posted_at',
        'account_id',
        'amount',
        'direction',
        'description',
        'source_type',
        'source_id',
        'user_id','reverses_entry_id'
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'amount' => 'decimal:2',
        'reverses_entry_id'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function source()
    {
        // polymorphic-ish (manual)
        // you can resolve it yourself if needed
        return null;
    }
}