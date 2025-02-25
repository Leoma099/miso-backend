<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'equipment_id', 'condition', 'status', 'date_borrowed', 'date_returned'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function getBorrowerNameAttribute()
    {
        return $this->user ? $this->user->name : 'Unknown';
    }

    public function getEquipmentTypeAttribute()
    {
        return $this->equipment ? $this->equipment->type : 'Unknown';
    }
};