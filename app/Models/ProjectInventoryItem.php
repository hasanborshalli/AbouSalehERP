<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectInventoryItem extends Model
{
protected $fillable = [
        'project_id',
        'inventory_item_id',
        'quantity_needed',
        'unit',
        'quantity_used',
    ];
public function project()
{
    return $this->belongsTo(Project::class);
}

public function inventoryItem()
{
    return $this->belongsTo(InventoryItem::class);
}

}