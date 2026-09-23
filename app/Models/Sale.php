<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Sale
 *
 * @property int $id
 * @property string $sales_code
 * @property string|null $client
 * @property float $amount
 * @property string|null $remarks
 * @property string|null $stock_ids
 * @property string $date_created
 * @property string $date_updated
 */
class Sale extends Model
{
    protected $table = 'sales_list';

    public $timestamps = true;

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    protected $fillable = [
        'sales_code',
        'client',
        'amount',
        'remarks',
        'stock_ids',
        'date_created',
    ];

    /**
     * Generate the next sales code automatically
     * whenever a new Sale is created.
     */
    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (!empty($sale->sales_code)) {
                return;
            }

            $prefix = 'SALE-';

            $latestSale = static::where(
                'sales_code',
                'LIKE',
                "{$prefix}%"
            )
                ->orderBy('sales_code', 'desc')
                ->first();

            if ($latestSale) {
                $lastNumber = (int) str_replace(
                    $prefix,
                    '',
                    $latestSale->sales_code
                );

                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $sale->sales_code =
                $prefix . sprintf('%04d', $nextNumber);
        });
    }
}
