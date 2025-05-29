<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOrder extends Model
{
    protected $fillable = ['user_id', 'book_title', 'book_author', 'note','status','owner_note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
