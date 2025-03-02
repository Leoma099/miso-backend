<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable // Change from Model to Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable =
        [
            'username',
            'password',
            'role',
        ];

    protected $hidden = ['password'];

    public function account()
    {
        return $this->hasOne(Account::class, 'user_id');
    }
}
