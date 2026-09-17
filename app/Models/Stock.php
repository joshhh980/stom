<?php

namespace App\Models;

use App\Enums\StockType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Stock
 *
 * @property int $id
 * @property int $item_id
 * @property int $quantity
 * @property string|null $unit
 * @property float|null $price
 * @property float|null $total
 * @property int $type 1 = IN, 2 = OUT
 * @property string $date_created
 * @property string|null $expiry_date
 * @property string|null $batch_no
 * 
 * @property-read \App\Models\Item $item
 */
class Stock extends Model
{
    protected $table = 'stock_list'; 

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'quantity',
        'unit',
        'price',
        'total',
        'type',
        'expiry_date',
        'batch_no',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockType::class,
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
