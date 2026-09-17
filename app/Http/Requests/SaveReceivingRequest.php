<?php

namespace App\Http\Requests;

use App\Enums\ReceivingOrigin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveReceivingRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'id'            => 'nullable',
            // Enforces that incoming values strictly match the valid integer values of your enum definitions (1 or 2)
            'from_order'    => ['required', Rule::enum(ReceivingOrigin::class)],
            'form_id'       => 'required|integer',
            'po_id'         => 'required|integer',
            'supplier_id'   => 'required|integer|exists:supplier_list,id',
            'discount_perc' => 'nullable|numeric|min:0|max:100',
            'tax_perc'      => 'nullable|numeric|min:0|max:100',
            'remarks'       => 'nullable|string',
            'item_id'       => 'required|array',
            'item_id.*'     => 'required|integer',
            'qty'           => 'required|array',
            'qty.*'         => 'required|numeric|min:0',
            'oqty'          => 'required|array',
            'oqty.*'        => 'required|numeric|min:0',
            'price'         => 'required|array',
            'price.*'       => 'required|numeric|min:0',
            'total'         => 'required|array',
            'total.*'       => 'required|numeric|min:0',
            'expiry_date'   => 'nullable|array',
            'batch_no'      => 'nullable|array',
            'discount'      => 'nullable|numeric',
            'tax'           => 'nullable|numeric',
            'amount'        => 'nullable|numeric',
        ];
    }
}
