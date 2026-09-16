<?php

namespace App\Actions;

use App\Http\Requests\PurchaseOrderRequest;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class UpdatePurchaseOrder
{
    /**
     * Execute the purchase order details update action.
     * 
     * @param \App\Models\PurchaseOrder $purchaseOrder
     * @param \App\Http\Requests\PurchaseOrderRequest $request
     * @return \App\Models\PurchaseOrder
     */
    public function execute(PurchaseOrder $purchaseOrder, PurchaseOrderRequest $request): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $request) {
            if (!$purchaseOrder->isPending()) {
                abort(400, "Only pending purchase orders can be modified.");
            }

            $purchaseOrder->updateDetailsAndItems($request);

            return $purchaseOrder;
        });
    }
}
