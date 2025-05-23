<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'brand',
        'model',
        'quantity',
        'equipmentStatus',
        'photo',
        'property_number',
        'serial_number',
    ];
}
