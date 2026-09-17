<?php

namespace App\Actions;

use App\Enums\StockType;
use App\Models\Receiving;
use App\Models\Stock;

class SyncStockAllocations
{
    public function execute(Receiving $receiving, array $data, bool $isNew): array
    {
        $stockIds = [];
        $backorderItemIndices = [];

        foreach ($data['item_id'] ?? [] as $k => $itemId) {
            $qty = $data['qty'][$k] ?? 0;
            $oqty = $data['oqty'][$k] ?? 0;
            $price = $data['price'][$k] ?? 0;
            $total = $data['total'][$k] ?? ($qty * $price);

            $stock = Stock::create([
                'item_id'     => $itemId,
                'quantity'    => $qty,
                'price'       => $price,
                'total'       => $total,
                'type'        => StockType::IN,
                'expiry_date' => $data['expiry_date'][$k] ?? null,
                'batch_no'    => $data['batch_no'][$k] ?? null,
            ]);

            $stockIds[] = $stock->id;

            // Flag missing partial items for backorder generation
            if ($qty < $oqty) {
                $backorderItemIndices[] = $k;
            }
        }

        // Link allocated stocks to receipt record
        $receiving->update(['stock_ids' => implode(',', $stockIds)]);

        return [
            'stock_ids' => $stockIds,
            'backorder_item_indices' => $backorderItemIndices,
        ];
    }
}
