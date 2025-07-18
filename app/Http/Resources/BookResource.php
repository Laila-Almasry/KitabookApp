<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'preview'      => $this->preview,
            'price'        => $this->price,
            'language'     => $this->language,
            'publisher'    => $this->publisher,
            'author'       => $this->author,
            'category'     => $this->category,
            'cover_image_url' => $this->cover_image ? env('APP_URL').':8000/storage/' . $this->cover_image : null,
            'file_path_url'   => $this->file_path ?  env('APP_URL').':8000/storage/' . $this->file_path : null,
            'sound_path_url'  => $this->sound_path ?env('APP_URL').':8000/storage/' . $this->sound_path : null,
            'ratings' => $this->ratings->map(function ($rating) {
                return [
                    'user_id'    => $rating->user_id,
                    'user_name'  => $rating->user->name ?? 'Unknown',
                    'rating'     => $rating->rating,
                    'comment'    => $rating->comment,
                    'created_at' => $rating->created_at->toDateTimeString(),
                ];
            }),
            'avg_rating'   => $this->rating,
            'raters_count' => $this->raterscount,
        ];
    }
}
