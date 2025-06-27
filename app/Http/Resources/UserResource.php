<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            'username' => $this->username,
            'fullname' => $this->fullname,
            'image' => $this->image,
            'address' => $this->address,
            'phonenumber' => $this->phonenumber,
            'email' => $this->email,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
