<?php

namespace App\Traits;

use App\Enums\PurchaseOrderStatus;

trait HasPurchaseOrderStatus
{


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'status'       => PurchaseOrderStatus::class,
            'date_created' => 'datetime',
        ];
    }

    /**
     * Check if the order status is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PurchaseOrderStatus::PENDING;
    }

    /**
     * Check if the order status is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        return $this->status === PurchaseOrderStatus::PARTIALLY_RECEIVED;
    }

    /**
     * Check if the order status is fully received.
     */
    public function isReceived(): bool
    {
        return $this->status === PurchaseOrderStatus::RECEIVED;
    }
}
