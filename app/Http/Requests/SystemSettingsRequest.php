<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'img' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'cover' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:10240'],
        ];
    }
}
