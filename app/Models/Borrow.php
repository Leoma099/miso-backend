<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'account_id',
            'equipment_id',
            'full_name',
            'office_name',
            'office_address',
            'type',
            'position',
            'mobile_number',
            'purpose',
            'status',
            'date_borrow',
            'date_return'
        ];

    public function Account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }
};