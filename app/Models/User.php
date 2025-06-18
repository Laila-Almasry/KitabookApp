<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'provider', 'provider_id','username', 'fullname', 'image', 'address',
        'phonenumber', 'email','wallet_id'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];



    public function borrows()
{
    return $this->hasMany(Borrow::class);
}
public function borrow_reservations(){
  return $this->hasMany(BorrowReservation::class);
}

public function wallet()
{
    return $this->hasOne(Wallet::class);
}

public function ratings(){
    return $this->hasMany(BookRating::class);
}

//automatically create a wallet for the user once the user is created
protected static function booted()
{
    static::created(function ($user) {
        $user->wallet()->create([
            'freezed_money' => 0,
            'credits' => 0,
        ]);
    });
}


}
