<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
 use SoftDeletes;

    protected $fillable = [
        'contract_id','invoice_number','issue_date','due_date',
        'amount','late_fee_amount','late_marked_at','status','pdf_path','receipt_path'
    ];
public function contract()
{
    return $this->belongsTo(Contract::class);
}

}