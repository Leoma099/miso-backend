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
        'condition',
        'availability',
        'registered_date',
        'photo',
        'property_number',
        'serial_number',
    ];
}
