<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFloor extends Model
{
protected $fillable=[
    'project_id',
    'floor_number'
];
public function project()
{
    return $this->belongsTo(Project::class);
}

public function apartments()
{
    return $this->hasMany(Apartment::class, 'floor_id');
}

}