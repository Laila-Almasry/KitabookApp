<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MyReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'status' => 'required|in:continue_reading,done,read_later',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'The book field is required.',
            'book_id.exists'   => 'The selected book does not exist.',
            'status.required'  => 'The status field is required.',
            'status.in'        => 'Status must be one of: continue_reading, done, read_later.',
        ];
    }
}
