<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'stock_transfers';

    public function items()
    {
        return $this->belongsToMany(Item::class, 'stock_transfer_item_details', 'stock_transfer_id', 'item_id')
            ->withPivot('amount')
            ->withTimestamps();
    }
}
