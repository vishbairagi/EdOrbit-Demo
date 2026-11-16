<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'registration_number' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'enrollment_prefix' => 'nullable|string|max:255',
            'enrollment_base_number' => 'nullable|integer|min:0',
            'enrollment_base_padding' => 'nullable|integer|min:1',
            'admission_prefix' => 'nullable|string|max:255',
            'admission_base_number' => 'nullable|integer|min:0',
            'admission_base_padding' => 'nullable|integer|min:1',
        ];
    }
}
