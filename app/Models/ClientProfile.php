<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    protected $fillable=[
    'user_id'
    ];
    public function user()
{
    return $this->belongsTo(User::class);
}
public function contract(){
    return $this->hasOne(Contract::class, 'client_user_id', 'user_id');
}
}