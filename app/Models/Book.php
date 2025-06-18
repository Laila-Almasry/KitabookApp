<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'barcode', 'title', 'preview', 'cover_image', 'author_id',
        'price', 'is_physical', 'sound_path', 'file_path',
        'category_id', 'language', 'rating', 'raterscount','copies','publisher',
    ];


public function author()
{
    return $this->belongsTo(Author::class);
}

public function category()
{
    return $this->belongsTo(Category::class);
}

public function ratings()
{
    return $this->hasMany(BookRating::class);
}
public function book_copies()
{
    return $this->hasMany(BookCopy::class);
}


}
