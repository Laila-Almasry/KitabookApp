<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Change to authorization logic if needed
    }

    public function rules(): array
    {
        return [
            'title'         => 'nullable|string|max:255',
            'preview'       => 'nullable|string',
            'price'         => 'nullable|numeric|min:0',
            'publisher'     => 'nullable|string|max:255',
            'language'      => 'nullable|string|in:english,arabic,french',
            'author_id'     => 'nullable|exists:authors,id',
            'category_id'   => 'nullable|exists:categories,id',
            'cover_image'   => 'nullable|image|max:2048',
            'file_path'     => 'nullable|mimes:pdf|max:10240',
            'sound_path'    => 'nullable|file|mimes:mpeg,mp3|max:51200',
        ];
    }
}
