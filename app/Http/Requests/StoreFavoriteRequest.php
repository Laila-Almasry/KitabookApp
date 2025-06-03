<?php 

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFavoriteRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists'   => 'The selected user does not exist.',
            'book_id.required' => 'The book ID is required.',
            'book_id.exists'   => 'The selected book does not exist.',
        ];
    }
}

