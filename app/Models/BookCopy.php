<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    protected $fillable = [
        'book_id',
        'order_item_id',
        'barcode',
        'status',
    ];


    public function borrows()
{
    return $this->hasMany(Borrow::class);
}

public function currentBorrow()
{
    return $this->hasOne(Borrow::class)->where('status', 'active');
}

public function book(){
    return $this->belongsTo(Book::class);
}
}
