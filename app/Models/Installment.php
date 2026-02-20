<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{

public function contract()
{
    return $this->belongsTo(Contract::class);
}

public function recordedBy()
{
    return $this->belongsTo(User::class, 'recorded_by');
}

}