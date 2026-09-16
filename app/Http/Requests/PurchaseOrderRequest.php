<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'supplier_id'   => 'required|integer|exists:supplier_list,id',
            'amount'        => 'required|numeric|min:0',
            'discount_perc' => 'nullable|numeric|min:0|max:100',
            'discount'      => 'nullable|numeric|min:0',
            'tax_perc'      => 'nullable|numeric|min:0|max:100',
            'tax'           => 'nullable|numeric|min:0',
            'remarks'       => 'nullable|string',
            
            'item_id'       => 'required|array|min:1',
            'item_id.*'     => 'required|integer|exists:item_list,id',
            'qty'           => 'required|array',
            'qty.*'         => 'required|numeric|min:0.01',
            'price'         => 'required|array',
            'price.*'       => 'required|numeric|min:0',
            'total'         => 'required|array',
            'total.*'       => 'required|numeric|min:0',
            
            'expiry_date'   => 'nullable|array',
            'batch_no'      => 'nullable|array',
            'unit'          => 'nullable|array',
        ];
    }
}
