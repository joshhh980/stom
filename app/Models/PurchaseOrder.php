<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Http\Requests\PurchaseOrderRequest;
use App\Traits\HasPurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\PurchaseOrder
 * 
 * Handles procurement tracking and inventory ingestion workflows.
 *
 * @property int $id
 * @property string $po_code
 * @property string|null $invoice_number
 * @property int $supplier_id
 * @property float $amount
 * @property float $discount_perc
 * @property float $discount
 * @property float $tax_perc
 * @property float $tax
 * @property string $remarks
 * @property \App\Enums\PurchaseOrderStatus $status
 * @property string $date_created
 * @property string $date_updated
 * 
 * @property-read \App\Models\Supplier $supplier
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Item[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BackOrder[] $backOrders
 */
class PurchaseOrder extends Model
{

    use HasPurchaseOrderStatus;

    protected $table = 'purchase_order_list';

    public $timestamps = true;
    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $purchaseOrder) {
            // Only generate a code if one hasn't been manually set yet
            if (blank($purchaseOrder->po_code)) {
                $purchaseOrder->po_code = self::generateUniquePoCode();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'po_code',
        'invoice_number',
        'supplier_id',
        'amount',
        'discount_perc',
        'discount',
        'tax_perc',
        'tax',
        'remarks',
        'status',
    ];


    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }


    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'po_items', 'po_id', 'item_id')
            ->withPivot(['quantity', 'price', 'unit', 'total', 'expiry_date', 'batch_no']);
    }


    public function backOrders(): HasMany
    {
        return $this->hasMany(BackOrder::class, 'po_id');
    }

    private static function generateUniquePoCode(): string
    {
        $prefix = "PO";

        $latestPo = static::where('po_code', 'LIKE', "{$prefix}-%")
            ->orderBy('po_code', 'desc')
            ->first();

        $nextNumber = 1;

        if ($latestPo) {
            $latestNumber = (int) substr($latestPo->po_code, strlen($prefix) + 1);
            $nextNumber = $latestNumber + 1;
        }

        return sprintf("%s-%'.04d", $prefix, $nextNumber);
    }


    public function updateDetailsAndItems(PurchaseOrderRequest $request): void
    {
        $inputData = $request->safe()->only([
            'supplier_id',
            'amount',
            'discount_perc',
            'discount',
            'tax_perc',
            'tax'
        ]);
        $inputData['remarks'] = $request->input('remarks') ?? '';

        $this->fill($inputData)->save();

        $pivotData = [];
        foreach ($request->item_id as $index => $itemId) {
            $pivotData[$itemId] = [
                'quantity'    => $request->qty[$index] ?? 0,
                'price'       => $request->price[$index] ?? 0,
                'unit'        => $request->unit[$index] ?? null,
                'total'       => $request->total[$index] ?? 0,
                'expiry_date' => !empty($request->expiry_date[$index]) ? $request->expiry_date[$index] : null,
                'batch_no'    => !empty($request->batch_no[$index]) ? $request->batch_no[$index] : null,
            ];
        }

        $this->items()->sync($pivotData);
    }

}
