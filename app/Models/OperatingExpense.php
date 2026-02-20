<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingExpense extends Model
{
    protected $fillable = [
  'expense_date','category','amount','description','created_by',
  'voided_at','voided_by','void_reason'
];

    protected $casts = [
  'expense_date' => 'date',
  'amount' => 'decimal:2',
  'voided_at' => 'datetime',
];

public function isVoided(): bool
{
    return !is_null($this->voided_at);
}
}