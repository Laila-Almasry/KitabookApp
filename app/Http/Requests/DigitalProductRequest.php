<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DigitalProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'file_path' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'category' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ];
    }
} 