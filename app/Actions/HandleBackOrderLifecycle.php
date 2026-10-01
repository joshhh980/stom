<?php

namespace App\Actions;

use App\Models\Receiving;
use App\Models\PurchaseOrder;
use App\Models\BackOrder;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ReceivingOrigin;

class HandleBackOrderLifecycle
{
    public function execute(Receiving $receiving, array $data, array $boItemIds): void
    {
        $hasMissingItems = count($boItemIds) > 0;

        if ($hasMissingItems) {
            $this->processPartialReceipt($receiving, $data, $boItemIds);
        } else {
            $this->processFullReceipt($data);
        }

        $this->computeReceivingTotals($receiving, $data);
    }

    protected function processPartialReceipt(Receiving $receiving, array $data, array $boItemIds): void
    {
        PurchaseOrder::where('id', $data['po_id'])->update(['status' => PurchaseOrderStatus::PARTIALLY_RECEIVED]);

        if ($data['from_order'] == ReceivingOrigin::BACK_ORDER->value) {
            BackOrder::where('id', $data['form_id'])->update(['status' => PurchaseOrderStatus::PARTIALLY_RECEIVED]);
        }

        // Inside HandleBackOrderLifecycle Action Class (or your model method)

        $existingBo = BackOrder::where('receiving_id', $receiving->id)->first();

        $boMasterData = [
            'receiving_id'  => $receiving->id,
            'po_id'         => $data['po_id'],
            'supplier_id'   => $data['supplier_id'],
            'discount_perc' => $data['discount_perc'] ?? 0,
            'tax_perc'      => $data['tax_perc'] ?? 0,
            'status'        => PurchaseOrderStatus::PENDING,
        ];

        if ($existingBo) {
            // 💡 If the existing record is missing a code, generate one before updating
            if (empty($existingBo->bo_code)) {
                $boMasterData['bo_code'] = BackOrder::generateUniqueBoCode();
            }
            $backOrderInstance = tap($existingBo)->update($boMasterData);
        } else {
            // This triggers the standard static::creating() boot hook perfectly
            $backOrderInstance = BackOrder::create($boMasterData);
        }

        // Map short items onto intermediate relationship pivot lists
        $subTotalBO = 0;
        $pivotData = [];

        foreach ($data['item_id'] as $k => $itemId) {
            if (!in_array($k, $boItemIds)) {
                continue;
            }

            $remQty = ($data['oqty'][$k] ?? 0) - ($data['qty'][$k] ?? 0);
            $price = $data['price'][$k] ?? 0;
            $lineTotalBO = $remQty * $price;
            $subTotalBO += $lineTotalBO;

            $pivotData[$itemId] = [
                'quantity'    => $remQty,
                'price'       => $price,
                'total'       => $lineTotalBO,
                'expiry_date' => $data['expiry_date'][$k] ?? null,
                'batch_no'    => $data['batch_no'][$k] ?? null,
            ];
        }

        $backOrderInstance->items()->sync($pivotData);

        // Update financial variables on the child record
        $discountBO = $subTotalBO * (($data['discount_perc'] ?? 0) / 100);
        $subTotalBO -= $discountBO;
        $taxBO = $subTotalBO * (($data['tax_perc'] ?? 0) / 100);

        $backOrderInstance->update([
            'amount'   => $subTotalBO + $taxBO,
            'discount' => $discountBO,
            'tax'      => $taxBO
        ]);
    }

    protected function processFullReceipt(array $data): void
    {
        PurchaseOrder::where('id', $data['po_id'])->update(['status' => PurchaseOrderStatus::RECEIVED]);

        if ($data['from_order'] == ReceivingOrigin::BACK_ORDER->value) {
            BackOrder::where('id', $data['form_id'])->update(['status' => PurchaseOrderStatus::RECEIVED]);
        }
    }

    protected function computeReceivingTotals(Receiving $receiving, array $data): void
    {
        $subtotalSum = array_sum($data['total'] ?? []);
        $rcvDiscount = $subtotalSum * (($data['discount_perc'] ?? 0) / 100);
        $subtotalSum -= $rcvDiscount;
        $rcvTax = $subtotalSum * (($data['tax_perc'] ?? 0) / 100);

        $receiving->update([
            'amount'   => $subtotalSum + $rcvTax,
            'discount' => $rcvDiscount,
            'tax'      => $rcvTax
        ]);
    }
}
