<?php

namespace App\Actions;

use App\Models\Receiving;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Validation\ValidationException;

class PersistReceivingRecord
{
    public function execute(array $data): Receiving
    {
        $id = $data['id'] ?? null;
        $isNew = empty($id);

        $stockIds = [];
        if (!$isNew) {
            $existingReceiving = Receiving::findOrFail($id);
            $stockIds = array_filter(explode(',', (string) $existingReceiving->stock_ids));

            if ($stockIds && Sale::where(function ($query) use ($stockIds) {
                foreach ($stockIds as $stockId) {
                    $query->orWhereRaw('FIND_IN_SET(?, stock_ids)', [$stockId]);
                }
            })->exists()) {
                throw ValidationException::withMessages([
                    'id' => 'This receiving record cannot be edited because its stock has been used in a sale.',
                ]);
            }
        }

        $receiving = Receiving::updateOrCreate(
            ['id' => $id],
            array_intersect_key($data, array_flip(['form_id', 'from_order', 'discount_perc', 'tax_perc', 'remarks']))
        );

        // Wipe old items if editing an existing record
        if ($stockIds) {
            Stock::whereIn('id', $stockIds)->delete();
        }

        return $receiving;
    }
}
