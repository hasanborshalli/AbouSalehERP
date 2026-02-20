<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apartment extends Model
{
    use SoftDeletes;
protected $fillable=[
    'project_id',
    'floor_id' ,
    'unit_number' ,
    'bedrooms' ,
    'bathrooms',
    'area_sqm' ,
    'price_total',
    'status' ,
    'notes' ,
];
public function project()
{
    return $this->belongsTo(Project::class);
}

public function floor()
{
    return $this->belongsTo(ProjectFloor::class, 'floor_id');
}

public function contract()
{
    return $this->hasOne(Contract::class);
}

}