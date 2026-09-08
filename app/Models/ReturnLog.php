<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReturnLog
 *
 * @property int $id
 * @property string $return_code
 * @property int $supplier_id
 * @property float $amount
 * @property string|null $remarks
 * @property string $stock_ids
 * @property string $date_created
 * @property string $date_updated
 * 
 * @property-read \App\Models\Supplier $supplier
 */
class ReturnLog extends Model
{
    protected $table = 'return_list';
    
    
    public $timestamps = true; 

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    protected $fillable = [
        'return_code',
        'supplier_id',
        'amount',
        'remarks',
        'stock_ids',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
