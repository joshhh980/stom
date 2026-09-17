<?php

namespace App\Actions;

use App\Models\Receiving;
use App\Models\Stock;

class PersistReceivingRecord
{
    public function execute(array $data): Receiving
    {
        $id = $data['id'] ?? null;
        $isNew = empty($id);

        $receiving = Receiving::updateOrCreate(
            ['id' => $id],
            array_intersect_key($data, array_flip(['form_id', 'from_order', 'discount_perc', 'tax_perc', 'remarks']))
        );

        // Wipe old items if editing an existing record
        if (!$isNew && !empty(trim($receiving->stock_ids ?? ''))) {
            Stock::whereIn('id', explode(',', $receiving->stock_ids))->delete();
        }

        return $receiving;
    }
}
