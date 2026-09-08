<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Sales
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
class Sales extends Model
{
    // Points directly to your table schema
    protected $table = 'sales_list';

    public $timestamps = true; 

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sales_code',
        'client',
        'amount',
        'remarks',
        'stock_ids',
    ];
}
