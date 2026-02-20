<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
protected $fillable=[
    'name',
    'code',
    'city',
    'area',
    'address',
    'notes',
    'estimated_completion_date',
    'start_date',
    'manager_user_id'
];
public function manager()
{
    return $this->belongsTo(User::class, 'manager_user_id');
}

public function floors()
{
    return $this->hasMany(ProjectFloor::class);
}

public function apartments()
{
    return $this->hasMany(Apartment::class);
}

public function contracts()
{
    return $this->hasMany(Contract::class);
}

// project inventory usage (pivot as a model)
public function inventoryItems()
{
    return $this->belongsToMany(InventoryItem::class, 'project_inventory_items','project_id',
        'inventory_item_id')
        ->withPivot(['quantity_needed', 'unit'])
        ->withTimestamps();
}

// If you created a pivot MODEL (recommended), also:
public function inventoryUsages()
{
    return $this->hasMany(ProjectInventoryItem::class);
}

}