<?php

namespace App\Http\Requests;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->filled('id');

        return [
            'id'          => 'nullable|integer|exists:item_list,id',
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_list', 'name')->ignore($this->input('id')),
            ],
            'description' => 'nullable|string',
            'cost'        => 'required|numeric|min:0',
            'status'      => ['required', new Enum(Status::class)],
            'quantity'    => [$isUpdate ? 'nullable' : 'nullable', 'integer', 'min:0'],
            'expiry_date' => 'nullable|date',
            'batch_no'    => 'nullable|string|max:255',
        ];
    }
}