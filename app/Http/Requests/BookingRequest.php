<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:120',
            'email' => 'required|email|max:160',
            'phone' => 'nullable|string|max:40',
            'country' => 'nullable|string|max:80',
            'adults' => 'required|integer|min:1|max:40',
            'children' => 'nullable|integer|min:0|max:40',
            'start_date' => 'required|date|after:today',
            'room_preference' => 'nullable|in:Twin sharing,Double,Single rooms,Family rooms',
            'special_requests' => 'nullable|string|max:3000',
        ];
    }
}
