<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DigitalProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'type'    => 'required|in:pdf,audio',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists'   => 'The selected user does not exist.',

            'book_id.required' => 'The book ID is required.',
            'book_id.exists'   => 'The selected book does not exist.',

            'type.required'    => 'The product type is required.',
            'type.in'          => 'The product type must be either "pdf" or "audio".',
        ];
    }
}
