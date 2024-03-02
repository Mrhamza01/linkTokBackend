<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Str;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class User extends Model implements AuthenticatableContract
{
    use HasApiTokens, HasFactory, Authenticatable;

    // Disable auto-incrementing and set the key type to string
    public $incrementing = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'username',
        'email',
        'password',
        'profilepicture',
        'userbio',
        'isactive',
    ];

    // The attributes that should be hidden for arrays
    protected $hidden = [
        'password',
        'usertype',
    ];


}
