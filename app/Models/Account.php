<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'email', 'mobile_number', 'address', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
