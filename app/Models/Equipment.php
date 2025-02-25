<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_type',
        'brand',
        'model',
        'condition',
        'availability',
        'status',
        'description',
        'registered_date',
    ];

    public function toArray()
    {
        return [
            'id' => $this->id,
            'equipment_type' => $this->equipment_type,
            'brand' => $this->brand,
            'model' => $this->model,
            'condition' => $this->condition,
            'availability' => $this->availability,
            'status' => $this->status,
            'description' => $this->description,
            'registered_date' => $this->registered_date,
        ];
    }
}
