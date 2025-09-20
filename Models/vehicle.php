<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_id', 'model', 'name', 'kilometer', 'sumTotal', 'created_at', 'updated_at', 'noPhone'];

    public function service(){
        return $this ->hasMany(Service::class);
    }
}
