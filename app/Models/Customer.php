<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'customers';

    public function banks()
    {
        return $this->belongsToMany(Bank::class, 'customer_bank_account_details', 'customer_id', 'bank_id')
            ->withPivot('bank_account_number', 'bank_account_name')
            ->withTimestamps();
    }
}
