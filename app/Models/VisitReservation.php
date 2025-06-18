<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class VisitReservation extends Model
{
    protected $fillable = [
        'user_id', 'guest_name', 'visit_date',
        'start_time', 'end_time', 'status', 'code',
    ];

    protected static function booted()
    {
        static::creating(function ($reservation) {
            $reservation->code = Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
