<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
              'fullname'     => 'nullable|string|max:255',
            'image'        => 'nullable|string|max:255',
            'address'      => 'nullable|string',
            'phonenumber'  => 'nullable|string|max:20',
            'email'        => 'nullable|email|unique:users,email,' . $this->user,
        ];
    }
    public function messages(): array
    {
        return [
            'fullname.string'      => 'The fullname must be a string.',
            'image.string'         => 'The image path must be a string.',
            'address.string'       => 'The address must be a string.',
            'phonenumber.string'   => 'The phone number must be a string.',
            'phonenumber.max'      => 'The phone number may not be greater than 20 characters.',
            'email.email'          => 'Please enter a valid email address.',
            'email.unique'         => 'This email is already taken.',
        ];
    }
}
