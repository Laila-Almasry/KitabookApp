<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'book' => [
                'title' => $this->book->title,
                'cover' => $this->book->cover_image ?? null,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
