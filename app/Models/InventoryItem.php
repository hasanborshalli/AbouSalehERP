<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
protected $fillable=[
    'name',
    'price',
    'type',
    'quantity',
    'is_out_of_stock',
    'type',
    'unit',
    'image_path',
];

public function projects()
{
    
    return $this->belongsToMany(Project::class, 'project_inventory_items')
        ->withPivot(['quantity_needed', 'unit', 'quantity_used'])
        ->withTimestamps();
}

public function projectUsages()
{
    return $this->hasMany(ProjectInventoryItem::class);
}

}