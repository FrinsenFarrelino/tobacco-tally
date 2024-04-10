<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'sales';

    public function items()
    {
        return $this->belongsToMany(Item::class, 'sale_item_details', 'sale_id', 'item_id')
            ->withPivot('amount', 'subtotal')
            ->withTimestamps();
    }
}
