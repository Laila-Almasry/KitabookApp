<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider'     => 'nullable|string|max:255',
            'provider_id'  => 'nullable|string|max:255',
            'username'     => 'nullable|string|max:255',
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
            'provider.string'      => 'The provider must be a string.',
            'provider_id.string'   => 'The provider ID must be a string.',
            'username.string'      => 'The username must be a string.',
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
