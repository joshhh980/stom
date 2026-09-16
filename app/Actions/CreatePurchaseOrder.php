<?php 

namespace App\Actions;

use App\Enums\PurchaseOrderStatus;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder
{

    public function execute(PurchaseOrderRequest $request): PurchaseOrder
    {
        return DB::transaction(function () use ($request) {
            $po = new PurchaseOrder();
            
            $po->status = PurchaseOrderStatus::PENDING; 

            $po->updateDetailsAndItems($request);

            return $po;
        });
    }
}
