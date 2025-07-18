<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DigitalProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'user_id'  => $this->user_id,
            'book_id'  => $this->book_id,
            'type'     => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
