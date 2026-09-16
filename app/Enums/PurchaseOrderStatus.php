<?php

namespace App\Enums;

/**
 * Class PurchaseOrderStatus
 * 
 * Represents the lifecycle stages of a Purchase Order document.
 * 
 * @package App\Enums
 */
enum PurchaseOrderStatus: int
{
    /** @var int Order has been created but no items have arrived yet */
    case PENDING = 0;

    /** @var int Some items have arrived, but shipment is incomplete */
    case PARTIALLY_RECEIVED = 1;

    /** @var int All items have been safely received and processed */
    case RECEIVED = 2;


    public function badgeHtml(): string
    {
        return match ($this) {
            self::PENDING => '<span class="badge badge-primary rounded-pill">Pending</span>',
            self::PARTIALLY_RECEIVED => '<span class="badge badge-warning rounded-pill">Partially received</span>',
            self::RECEIVED => '<span class="badge badge-success rounded-pill">Received</span>',
        };
    }
}
