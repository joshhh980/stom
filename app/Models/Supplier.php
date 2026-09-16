<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Supplier
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $cperson
 * @property string $contact
 * @property int $status 0 = inactive, 1 = active
 * @property string $date_created
 * @property string $date_updated
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Item[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseOrder[] $purchaseOrders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BackOrder[] $backOrders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ReturnLog[] $returns
 */
class Supplier extends Model
{

    use HasActiveStatus, HasFactory;

    protected $table = 'supplier_list';

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
        'address',
        'cperson',
        'contact',
        'status',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'supplier_id');
    }


    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }


    public function backOrders(): HasMany
    {
        return $this->hasMany(BackOrder::class, 'supplier_id');
    }


    public function returns(): HasMany
    {
        return $this->hasMany(ReturnLog::class, 'supplier_id');
    }
}
