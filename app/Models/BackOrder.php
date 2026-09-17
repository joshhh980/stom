<?php

namespace App\Models;

use App\Traits\HasPurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\BackOrder
 *
 * @property int $id
 * @property int $receiving_id
 * @property int $po_id
 * @property string $bo_code
 * @property int $supplier_id
 * @property float|null $amount
 * @property float $discount_perc
 * @property float $discount
 * @property float $tax_perc
 * @property float $tax
 * @property string|null $remarks
 * @property int $status 0 = pending, 1 = partially received, 2 = received
 * @property string $date_created
 * @property string $date_updated
 * 
 * @property-read \App\Models\Receiving $receiving
 * @property-read \App\Models\PurchaseOrder $purchaseOrder
 * @property-read \App\Models\Supplier $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Item[] $items
 */
class BackOrder extends Model
{

    use HasPurchaseOrderStatus;

    protected $table = 'back_order_list';
    public $timestamps = true;

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    protected $fillable = [
        'receiving_id',
        'po_id',
        'bo_code',
        'supplier_id',
        'amount',
        'discount_perc',
        'discount',
        'tax_perc',
        'tax',
        'remarks',
        'status',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($backOrder) {
            if (empty($backOrder->bo_code)) {
                $backOrder->bo_code = self::generateUniqueBoCode();
            }
        });
    }

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class, 'receiving_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'bo_items', 'bo_id', 'item_id')
            ->withPivot(['quantity', 'price', 'unit', 'total', 'expiry_date', 'batch_no']);
    }

    public static function generateUniqueBoCode(): string
    {
        $prefix = "BO";
        $latest = self::where('bo_code', 'LIKE', "{$prefix}-%")->orderBy('bo_code', 'desc')->first();
        $nextNum = $latest ? ((int) substr($latest->bo_code, 3)) + 1 : 1;
        return sprintf("%s-%'.04d", $prefix, $nextNum);
    }
}
