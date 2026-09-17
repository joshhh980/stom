<?php

namespace App\Actions;

use App\Models\Receiving;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SaveReceivingRequest;

class ProcessReceivingStock
{
    public function __construct(
        protected PersistReceivingRecord $persistReceiving,
        protected SyncStockAllocations $syncStocks,
        protected HandleBackOrderLifecycle $handleBackOrder
    ) {}

    public function execute(array $data): Receiving
    {
        return DB::transaction(function () use ($data) {
            $isNew = empty($data['id'] ?? null);

            // Phase 1: Create or update the main receipt record
            $receiving = $this->persistReceiving->execute($data);

            // Phase 2: Process inventory line items & determine if items are missing
            $stockResult = $this->syncStocks->execute($receiving, $data, $isNew);

            // Phase 3: Track order status states, create backorders, and finalise totals
            $this->handleBackOrder->execute($receiving, $data, $stockResult['backorder_item_indices']);

            return $receiving;
        });
    }
}
