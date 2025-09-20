<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sell extends Model
{
    use HasFactory;
    protected $table = "sell_tables";
    protected $fillable = ['items', 'quantity', 'real_price', 'sales_price', 'revenue', 'total', 'created_at', 'updated_at'];

    protected $casts = [
        'created_at',
        'updated_at'
        // any other date fields
    ];
}
