<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferItemDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'stock_transfer_item_details';
}
