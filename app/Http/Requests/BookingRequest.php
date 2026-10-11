<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:120',
            'email' => 'required|email|max:160',
            'phone' => 'nullable|string|max:40',
            'country' => ['nullable', 'string', Rule::in(array_values(config('countries')))],
            'adults' => 'required|integer|min:1|max:40',
            'children' => 'nullable|integer|min:0|max:40',
            'start_date' => 'required|date|after:today',
            'room_type_id' => 'nullable|integer',
            'extras' => 'nullable|array',
            'extras.*' => 'string|max:10',
            'special_requests' => 'nullable|string|max:3000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['country.in' => 'Please choose your country from the list.'];
    }
}
