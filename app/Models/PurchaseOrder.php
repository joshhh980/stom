<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\PurchaseOrder
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
 * @property int $status 0 = pending, 1 = partially received, 2 = received
 * @property string $date_created
 * @property string $date_updated
 * 
 * @property-read \App\Models\Supplier $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Item[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BackOrder[] $backOrders
 */
class PurchaseOrder extends Model
{
    // Points directly to your table schema
    protected $table = 'purchase_order_list';

    public $timestamps = true; 
    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

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

    /**
     * Get the supplier that this purchase order belongs to.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the items linked to this purchase order through the po_items pivot table.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'po_items', 'po_id', 'item_id')
                    ->withPivot(['quantity', 'price', 'unit', 'total', 'expiry_date', 'batch_no']);
    }

    /**
     * Get downstream back orders triggered by this purchase order.
     */
    public function backOrders(): HasMany
    {
        return $this->hasMany(BackOrder::class, 'po_id');
    }
}
