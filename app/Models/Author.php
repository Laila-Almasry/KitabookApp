<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable=['fullname','about','image'];

    public function books(){
        return $this->hasMany(Book::class);
    }
}
