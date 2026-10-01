<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\BackOrder;
use App\Models\Receiving;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ReceivingOrigin;
use App\Actions\ProcessReceivingStock;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class ManageReceivingTest extends DuskTestCase
{
    /**
     * Verify that users can interactively ingest partial stock lines and compute calculations.
     */
    /**
     * Verify that users can interactively ingest partial stock lines and compute calculations.
     */
    public function test_can_interactively_receive_and_calculate_purchase_order_stock(): void
    {
        // Query the first existing user and supplier record directly from the database
        $user = User::first();
        $supplier = Supplier::factory()->create();

        $purchaseOrder = PurchaseOrder::create([
            'po_code'       => 'PO-2026-0001',
            'supplier_id'   => $supplier->id,
            'discount_perc' => 10,
            'tax_perc'      => 5,
            'status'        => 0,
            'amount'        => 450.00
        ]);

        $itemA = Item::first() ?? Item::factory()->create(['name' => 'Premium Widgets']);
        $itemB = Item::skip(1)->first() ?? Item::factory()->create(['name' => 'Standard Bolts']);

        // Bind standard item rows directly to pivot tables
        $purchaseOrder->items()->attach([
            $itemA->id => ['quantity' => 10, 'price' => 20.00, 'total' => 200.00],
            $itemB->id => ['quantity' => 30, 'price' => 10.00, 'total' => 300.00],
        ]);

        $this->browse(function (Browser $browser) use ($user, $purchaseOrder) {
            // Target the location passing parameters as pure key-value query fragments
            $browser->loginAs($user)
                ->visit(route('receivings.manage') . '?po_id=' . $purchaseOrder->id)
                ->waitFor('#receive-form')
                ->assertSee('Receive Order from PO-2026-0001');

            // Update incoming quantity arrays interactively to trigger client side real-time calculation hooks
            $browser->clear('table#list tbody tr:nth-child(1) input[name="qty[]"]')
                ->type('table#list tbody tr:nth-child(1) input[name="qty[]"]', '5')
                ->clear('table#list tbody tr:nth-child(2) input[name="qty[]"]')
                ->type('table#list tbody tr:nth-child(2) input[name="qty[]"]', '30');

            $browser->click('.card-title')
                ->pause(200); // Give the DOM a tiny split-second to update calculations

            // Update core percentages fields values configuration parameters 
            $browser->clear('input[name="discount_perc"]')
                ->type('input[name="discount_perc"]', '10')
                ->clear('input[name="tax_perc"]')
                ->type('input[name="tax_perc"]', '5')
                ->type('#remarks', 'Partial shipment verification run automated dusk sequence logs.');

            // Inspect computed calculated UI text outputs strings to verify jQuery mathematical operations 
            $browser->assertSee('100')
                ->assertSee('300')
                ->assertSee('400')
                ->assertSee('40')
                ->assertSee('18')
                ->assertSee('378');

            // Submit the dataset via the dynamic AJAX handler pipeline hooks
            $browser->press('Save')
                ->waitFor('.swal2-toast', 3)
                ->assertSeeIn('.swal2-toast', 'Operation completed successfully.')
                ->waitForLocation(route('receivings.index', [], false), 5)
                ->assertPathIs(route('receivings.index', [], false))
                ->assertPresent('#receiving-table tbody tr')
                ->assertSeeIn('#receiving-table tbody', '2');
        });

        $receiving = Receiving::where('form_id', $purchaseOrder->id)
            ->where('from_order', ReceivingOrigin::PURCHASE_ORDER)
            ->latest('id')
            ->firstOrFail();
        $backOrder = BackOrder::where('receiving_id', $receiving->id)->firstOrFail();

        $this->assertSame(PurchaseOrderStatus::PENDING, $backOrder->status);
        $this->assertSame($itemA->id, $backOrder->items()->first()->id);
        $this->assertSame(5.0, (float) $backOrder->items()->first()->pivot->quantity);
        $this->assertSame(100.0, (float) $backOrder->items()->first()->pivot->total);
    }

        public function test_can_receive_a_back_order_and_mark_it_received(): void
        {
            $user = User::first();
            $supplier = Supplier::factory()->create();
            $purchaseOrder = PurchaseOrder::create([
                'po_code' => 'PO-BO-RECEIVE-' . uniqid(),
                'supplier_id' => $supplier->id,
                'status' => PurchaseOrderStatus::PARTIALLY_RECEIVED,
                'amount' => 100.00,
            ]);
            $item = Item::first();
            $sourceReceiving = Receiving::create([
                'form_id' => $purchaseOrder->id,
                'from_order' => ReceivingOrigin::PURCHASE_ORDER,
                'amount' => 0,
            ]);
            $backOrder = BackOrder::create([
                'receiving_id' => $sourceReceiving->id,
                'po_id' => $purchaseOrder->id,
                'supplier_id' => $supplier->id,
                'amount' => 100.00,
                'status' => PurchaseOrderStatus::PENDING,
            ]);
            $backOrder->items()->attach($item->id, [
                'quantity' => 5,
                'price' => 20.00,
                'total' => 100.00,
            ]);

            $this->browse(function (Browser $browser) use ($user, $backOrder) {
                $browser->loginAs($user)
                    ->visit(route('receivings.manage') . '?bo_id=' . $backOrder->id)
                    ->waitFor('#receive-form')
                    ->assertSee('Receive Order from ' . $backOrder->bo_code)
                    ->press('Save')
                    ->waitFor('.swal2-toast', 3)
                    ->assertSeeIn('.swal2-toast', 'Operation completed successfully.')
                    ->waitForLocation(route('receivings.index', [], false), 5);
            });

            $backOrder->refresh();
            $this->assertSame(PurchaseOrderStatus::RECEIVED, $backOrder->status);
            $this->assertDatabaseHas('receiving_list', [
                'form_id' => $backOrder->id,
                'from_order' => ReceivingOrigin::BACK_ORDER->value,
            ]);
    }

    public function test_partial_back_order_receipt_keeps_original_purchase_order_reference(): void
    {
        $supplier = Supplier::factory()->create();
        $item = Item::first();
        $purchaseOrder = PurchaseOrder::create([
            'po_code' => 'PO-BO-PARTIAL-' . uniqid(),
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::PARTIALLY_RECEIVED,
            'amount' => 100.00,
        ]);
        $sourceReceiving = Receiving::create([
            'form_id' => $purchaseOrder->id,
            'from_order' => ReceivingOrigin::PURCHASE_ORDER,
            'amount' => 0,
        ]);
        $sourceBackOrder = BackOrder::create([
            'receiving_id' => $sourceReceiving->id,
            'po_id' => $purchaseOrder->id,
            'supplier_id' => $supplier->id,
            'amount' => 100.00,
            'status' => PurchaseOrderStatus::PENDING,
        ]);
        $sourceBackOrder->items()->attach($item->id, [
            'quantity' => 5,
            'price' => 20.00,
            'total' => 100.00,
        ]);

        $receiving = app(ProcessReceivingStock::class)->execute([
            'from_order' => ReceivingOrigin::BACK_ORDER->value,
            'form_id' => $sourceBackOrder->id,
            'po_id' => $purchaseOrder->id,
            'supplier_id' => $supplier->id,
            'item_id' => [$item->id],
            'qty' => [2],
            'oqty' => [5],
            'price' => [20.00],
            'total' => [40.00],
            'expiry_date' => [null],
            'batch_no' => [null],
            'discount_perc' => 0,
            'discount' => 0,
            'tax_perc' => 0,
            'tax' => 0,
            'amount' => 40.00,
        ]);

        $sourceBackOrder->refresh();
        $newBackOrder = BackOrder::where('receiving_id', $receiving->id)->firstOrFail();

        $this->assertSame(PurchaseOrderStatus::PARTIALLY_RECEIVED, $sourceBackOrder->status);
        $this->assertSame($purchaseOrder->id, $newBackOrder->po_id);
        $this->assertSame($purchaseOrder->id, $newBackOrder->purchaseOrder->id);
        $this->assertSame(3.0, (float) $newBackOrder->items()->first()->pivot->quantity);
    }

    public function test_rejects_deleting_received_or_already_received_back_orders(): void
        {
            $user = User::first();
            $supplier = Supplier::factory()->create();
            $purchaseOrder = PurchaseOrder::create([
                'po_code' => 'PO-BO-DELETE-' . uniqid(),
                'supplier_id' => $supplier->id,
                'status' => PurchaseOrderStatus::RECEIVED,
                'amount' => 0,
            ]);
            $sourceReceiving = Receiving::create([
                'form_id' => $purchaseOrder->id,
                'from_order' => ReceivingOrigin::PURCHASE_ORDER,
                'amount' => 0,
            ]);
            $receivedBackOrder = BackOrder::create([
                'receiving_id' => $sourceReceiving->id,
                'po_id' => $purchaseOrder->id,
                'supplier_id' => $supplier->id,
                'status' => PurchaseOrderStatus::RECEIVED,
            ]);
            $pendingBackOrder = BackOrder::create([
                'receiving_id' => $sourceReceiving->id,
                'po_id' => $purchaseOrder->id,
                'supplier_id' => $supplier->id,
                'status' => PurchaseOrderStatus::PENDING,
            ]);

            $this->withSession(['_token' => 'back-order-test-token'])
                ->withHeader('X-CSRF-TOKEN', 'back-order-test-token')
                ->actingAs($user)
                ->deleteJson(route('back-orders.destroy', $receivedBackOrder))
                ->assertStatus(422)
                ->assertJsonPath('message', 'Only unreceived back orders can be deleted.');

            $this->withSession(['_token' => 'back-order-test-token'])
                ->withHeader('X-CSRF-TOKEN', 'back-order-test-token')
                ->actingAs($user)
                ->deleteJson(route('back-orders.destroy', $pendingBackOrder))
                ->assertStatus(422)
                ->assertJsonPath('message', 'Only unreceived back orders can be deleted.');

            $this->assertDatabaseHas('back_order_list', ['id' => $receivedBackOrder->id]);
            $this->assertDatabaseHas('back_order_list', ['id' => $pendingBackOrder->id]);
    }

    /**
     * Verify frontend cap boundary validation rules block quantities exceeding original limits.
     */
    /**
     * Verify frontend cap boundary validation rules block quantities exceeding original limits.
     */
    public function test_frontend_caps_quantity_input_to_maximum_ordered_boundary(): void
    {
        $user = User::first();
        $supplier = Supplier::first();

        $purchaseOrder = PurchaseOrder::create([
            'po_code'     => 'PO-2026-0002',
            'supplier_id' => $supplier->id,
            'status'      => 0,
            'amount'      => 100.00
        ]);

        $item = Item::first() ?? Item::factory()->create(['name' => 'Limited Shielding']);
        $purchaseOrder->items()->attach([
            $item->id => ['quantity' => 5, 'price' => 20.00, 'total' => 100.00],
        ]);

        $this->browse(function (Browser $browser) use ($user, $purchaseOrder) {
            $browser->loginAs($user)
                ->visit(route('receivings.manage') . '?po_id=' . $purchaseOrder->id)
                ->waitFor('#receive-form');

            // 1. Simulate an user trying to input an overflowing quantity line item
            $browser->clear('table#list tbody tr:nth-child(1) input[name="qty[]"]')
                ->type('table#list tbody tr:nth-child(1) input[name="qty[]"]', '12');

            // 2. Click outside the input to trigger the change / input blur calculation event cascade
            $browser->click('#remarks')
                ->pause(300); // 💡 FIXED: Gives your jQuery calc() script a brief window to force the value down to 5

            // 3. Confirm that the boundary engine clamped the input back down to its maximum constraint attribute
            $browser->assertValue('table#list tbody tr:nth-child(1) input[name="qty[]"]', '5');
            $browser->assertSeeIn('table#list tbody tr:nth-child(1) .total', '100');
        });
    }


    public function test_returns_404_for_nonexistent_purchase_order(): void
    {
        $user = User::first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(route('receivings.manage') . '?po_id=99999');
                
            $browser->assertSee('404');
        });
    }


    public function test_renders_error_alert_banner_when_backend_processing_fails(): void
    {
        $user = User::first();
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::create([
            'po_code' => 'PO-2026-0003',
            'supplier_id' => $supplier->id,
            'status' => 0,
            'amount' => 0,
        ]);
        $item = Item::first() ?? Item::factory()->create();
        $purchaseOrder->items()->attach([
            $item->id => ['quantity' => 5, 'price' => 20.00, 'total' => 100.00],
        ]);

        $this->browse(function (Browser $browser) use ($user, $purchaseOrder) {
            $browser->loginAs($user)
                ->visit(route('receivings.manage') . '?po_id=' . $purchaseOrder->id)
                ->waitFor('#receive-form')
                // force a real processing failure — e.g. clear a required field
                ->script("document.querySelector('[name=\"supplier_id\"]').value = ''");

            $browser->press('Save')
                ->waitFor('.err-msg')
                ->assertSeeIn('.err-msg', 'error');
        });
    }
}
