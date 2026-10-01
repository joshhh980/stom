<?php

namespace Tests\Unit;

use App\Enums\ReceivingOrigin;
use App\Models\Receiving;
use Tests\TestCase;

class ReceivingModelTest extends TestCase
{
    public function test_it_detects_purchase_order_origin(): void
    {
        $receiving = new Receiving([
            'from_order' => ReceivingOrigin::PURCHASE_ORDER,
        ]);

        $this->assertTrue($receiving->isFromPurchaseOrder());
        $this->assertFalse($receiving->isFromBackOrder());
    }

    public function test_it_detects_back_order_origin(): void
    {
        $receiving = new Receiving([
            'from_order' => ReceivingOrigin::BACK_ORDER,
        ]);

        $this->assertTrue($receiving->isFromBackOrder());
        $this->assertFalse($receiving->isFromPurchaseOrder());
    }
}
