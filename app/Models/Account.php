<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
     protected $fillable = ['code','name','type','is_system'];

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}