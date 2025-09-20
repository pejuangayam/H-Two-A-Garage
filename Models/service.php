<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class service extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_id', 'service_date','items', 'quantity', 'per_price', 'total','labour_total', 'grand_total', 'alltotal'];

    protected $casts = [
        'service_date' => 'date',
    ];
    
    public function vehicle(){
        return $this->belongsTo(Vehicle::class);
    }

}


