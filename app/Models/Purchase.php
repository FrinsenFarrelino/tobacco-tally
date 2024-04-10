<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'purchases';

    public function items()
    {
        return $this->belongsToMany(Item::class, 'purchase_item_details', 'purchase_id', 'item_id')
            ->withPivot('amount', 'subtotal')
            ->withTimestamps();
    }
}
