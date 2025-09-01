<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'departure' => 'sometimes|required|string|max:255',
            'destination' => 'sometimes|required|string|max:255',
            'departure_date' => 'sometimes|required|date|after_or_equal:today',
            'departure_time' => 'sometimes|required|date_format:H:i',
            'return_date' => 'nullable|date|after_or_equal:departure_date',
            'return_time' => 'nullable|date_format:H:i',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'available_seats' => 'sometimes|required|integer|min:1|max:50',
            'status' => 'sometimes|required|in:active,cancelled,completed',
        ];
    }

    public function messages(): array
    {
        return [
            'departure_date.after_or_equal' => 'The departure date must be today or later.',
            'return_date.after_or_equal' => 'The return date must be the same as or after the departure date.',
        ];
    }
}
