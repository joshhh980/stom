<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Inside App\Http\Requests\SaveSaleRequest.php

    public function rules(): array
    {
        $rules = [
            'id'        => 'nullable|integer|exists:sales_list,id',
            'client'    => 'required|string|max:255',
            'remarks'   => 'nullable|string|max:1000',
            'item_id'   => 'required|array|min:1',
            'item_id.*' => 'required|integer|exists:item_list,id',
            'qty'       => 'required|array|min:1',
            'qty.*'     => 'required|numeric|min:0',
            'price'     => 'required|array|min:1',
            'price.*'   => 'required|numeric|min:0',
        ];

        // Enforce backend validation rules based strictly on your UserType Enum
        if (auth()->user() && auth()->user()->type === \App\Enums\UserType::ADMIN->value) {
            $rules['date_created'] = 'required|date|before_or_equal:today';
        } else {
            // Fallback: If not an admin, default the data parameter to today's date automatically
            $this->merge(['date_created' => now()->toDateString()]);
            $rules['date_created'] = 'nullable|date';
        }

        return $rules;
    }
}
