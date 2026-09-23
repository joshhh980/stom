<?php

namespace App\Http\Requests;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('supplier_list', 'name')->ignore($this->input('id'))
            ],
            'address' => 'nullable|string',
            'cperson' => 'required|string|max:255',
            'contact' => 'nullable|string|max:50',
            // ⚡ Ensures only valid Enum values can pass through validation
            'status'  => ['required', new Enum(Status::class)], 
        ];
    }
}
