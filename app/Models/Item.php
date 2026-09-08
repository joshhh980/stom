<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Item
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int|null $supplier_id
 * @property float $cost
 * @property float|null $cost_price
 * @property int $status 0 = inactive, 1 = active
 * @property string $date_created
 * @property string $date_updated
 * 
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Stock[] $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseOrder[] $purchaseOrders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BackOrder[] $backOrders
 */
class Item extends Model
{
    // Points directly to your table schema
    protected $table = 'item_list';

    public $timestamps = true; 

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'supplier_id',
        'cost',
        'cost_price',
        'status',
    ];

    /**
     * Get the supplier who provides this item.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the stock ledger movements linked to this item.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'item_id');
    }

    /**
     * Get the purchase orders this item belongs to.
     */
    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'po_items', 'item_id', 'po_id')
                    ->withPivot(['quantity', 'price', 'unit', 'total', 'expiry_date', 'batch_no']);
    }

    /**
     * Get the back orders this item belongs to.
     */
    public function backOrders(): BelongsToMany
    {
        return $this->belongsToMany(BackOrder::class, 'bo_items', 'item_id', 'bo_id')
                    ->withPivot(['quantity', 'price', 'unit', 'total', 'expiry_date', 'batch_no']);
    }
}
