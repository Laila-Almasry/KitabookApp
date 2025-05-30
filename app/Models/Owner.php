<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Owner  extends Authenticatable
{
    use HasApiTokens, Notifiable,HasFactory;
    protected $guard='owner';
    protected $fillable=['email','password'];
}
