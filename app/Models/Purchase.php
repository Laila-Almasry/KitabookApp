<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'book_copy_id', 'price', 'purchased_at'
    ];

    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
    }
}

