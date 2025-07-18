<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisitReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'visit_date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|in:1,2,3',
            'start_time' => 'required|date_format:H:i',
            'guest_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,checked_in,done,cancelled',

        ];
    }
}
