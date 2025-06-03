<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MyReadingRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'status' => 'required|string|in:reading,completed,on-hold,dropped',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'The user field is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'book_id.required' => 'The book field is required.',
            'book_id.exists' => 'The selected book does not exist.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of the following: reading, completed, on-hold, dropped.',
        ];
    }
}
