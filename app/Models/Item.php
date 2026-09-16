<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasActiveStatus, HasFactory;
    
    protected $table = 'item_list';

    public $timestamps = true; 

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';


    protected $fillable = [
        'name',
        'description',
        'supplier_id',
        'cost',
        'cost_price',
        'status',
    ];


    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'item_id');
    }

    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'po_items', 'item_id', 'po_id')
                    ->withPivot(['quantity', 'price', 'unit', 'total', 'expiry_date', 'batch_no']);
    }

    public function backOrders(): BelongsToMany
    {
        return $this->belongsToMany(BackOrder::class, 'bo_items', 'item_id', 'bo_id')
                    ->withPivot(['quantity', 'price', 'unit', 'total', 'expiry_date', 'batch_no']);
    }
}
