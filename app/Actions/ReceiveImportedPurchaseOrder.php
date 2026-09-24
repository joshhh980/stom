<?php

namespace App\Actions;

use App\Enums\ReceivingOrigin;
use App\Models\PurchaseOrder;
use App\Models\Receiving;

class ReceiveImportedPurchaseOrder
{
    public function __construct(
        protected ProcessReceivingStock $processReceiving
    ) {}

    public function execute(PurchaseOrder $purchaseOrder, array $items): Receiving
    {
        $data = [
            'form_id' => $purchaseOrder->id,
            'from_order' => ReceivingOrigin::PURCHASE_ORDER->value,
            'po_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'discount_perc' => $purchaseOrder->discount_perc,
            'tax_perc' => $purchaseOrder->tax_perc,
            'remarks' => $purchaseOrder->remarks,
            'item_id' => [],
            'qty' => [],
            'oqty' => [],
            'price' => [],
            'total' => [],
            'expiry_date' => [],
            'batch_no' => [],
        ];

        foreach ($items as $itemId => $item) {
            $data['item_id'][] = $itemId;
            $data['qty'][] = $item['actual_quantity'];
            $data['oqty'][] = $item['quantity'];
            $data['price'][] = $item['price'];
            $data['total'][] = $item['actual_quantity'] * $item['price'];
            $data['expiry_date'][] = $item['expiry_date'];
            $data['batch_no'][] = $item['batch_no'];
        }

        return $this->processReceiving->execute($data);
    }
}
