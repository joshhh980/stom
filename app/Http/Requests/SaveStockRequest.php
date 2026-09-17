<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'          => 'nullable|integer|exists:stock_list,id',
            'item_id'     => 'required|integer|exists:item_list,id',
            'quantity' => 'required|numeric|min:0.01',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'batch_no'    => 'nullable|string|max:100',
        ];
    }
}
