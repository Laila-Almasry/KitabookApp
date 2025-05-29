<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
protected $fillable=[
    'freezed_money',
    'credits'
];

    public function user()
{
    return $this->hasOne(User::class);
}

}
