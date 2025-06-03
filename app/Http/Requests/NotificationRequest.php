<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:info,warning,success,error',
            'user_id' => 'required|exists:users,id',
            'is_read' => 'boolean',
            'data' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'message.required' => 'The message field is required.',
            'message.string' => 'The message must be a string.',
            'type.required' => 'The type field is required.',
            'type.string' => 'The type must be a string.',
            'type.in' => 'The type must be one of: info, warning, success, error.',
            'user_id.required' => 'The user field is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'is_read.boolean' => 'The is read field must be true or false.',
            'data.array' => 'The data must be an array.',
        ];
    }
} 