<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Item;
use App\Models\PurchaseOrder;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PurchaseOrderTest extends DuskTestCase
{

    /**
     * 1. SUCCESS VECTOR: Test creating a PO with multiple items, discount, and tax calculations.
     */
    public function test_can_create_purchase_order_with_multiple_items_and_calculations(): void
    {
        $admin = User::where('type', 1)->first();

        // Grab two distinct active items from your existing database
        $items = Item::where('status', 1)->take(2)->get();

        $this->assertNotNull($admin, "Dusk Test Failure: No Admin User found in the DB.");
        $this->assertGreaterThanOrEqual(2, $items->count(), "Dusk Test Failure: Need at least 2 active items in DB for array testing.");

        $item1 = $items->get(0);
        $item2 = $items->get(1);

        $this->browse(function (Browser $browser) use ($admin, $item1, $item2) {
            $browser->loginAs($admin)
                ->visitRoute('purchase-orders.create')
                ->waitFor('#po-form')

                // Add FIRST Item (Qty: 2)
                ->select('#item_id', $item1->id)
                ->type('#qty', '2')
                ->type('#batch_no', 'BATCH-M1')
                ->click('#add_to_list')
                ->waitFor('#list tbody tr:nth-child(1)')

                // Add SECOND Item (Qty: 3)
                ->select('#item_id', $item2->id)
                ->type('#qty', '3')
                ->type('#batch_no', 'BATCH-M2')
                ->click('#add_to_list')
                ->waitFor('#list tbody tr:nth-child(2)');

            // Evaluate math matrix up to Sub-Total
            $subTotal = (2 * $item1->cost) + (3 * $item2->cost);
            $expectedSubTotalText = number_format($subTotal, 2);
            $browser->assertSeeIn('.sub-total', $expectedSubTotalText);

            // Apply 10% Discount & 12% Tax parameters
            $browser->type('input[name="discount_perc"]', '10')
                ->type('input[name="tax_perc"]', '12')
                ->type('#remarks', 'Dusk automated multi-item calculation test case scenario.')
                ->pause(500); // Give JS math compilation engine half a second to bind values

            // Run automated financial breakdown confirmations
            $discountAmt = $subTotal * 0.10;
            $afterDiscount = $subTotal - $discountAmt;
            $taxAmt = $afterDiscount * 0.12;
            $grandTotal = $afterDiscount + $taxAmt;

            $browser->assertSeeIn('.discount', number_format($discountAmt, 2))
                ->assertSeeIn('.tax', number_format($taxAmt, 2))
                ->assertSeeIn('.grand-total', number_format($grandTotal, 2))

                // Submit Form
                ->press('Save Purchase Order')

                // Wait for the AJAX notification animation timer loop to process redirection
                ->pause(1500)
                ->waitForRoute('purchase-orders.index')
                ->assertSee('New Purchase Order was Successfully created.');
        });
    }

    /**
     * 2. FAILURE VECTOR: Test submitting an empty form throws errors instead of saving or crashing.
     */
    public function test_form_submitting_empty_item_list_fails_validation(): void
    {
        $admin = User::where('type', 1)->first();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visitRoute('purchase-orders.create')
                ->waitFor('#po-form');

            // Explicitly execute the form submit event listener via JS script injection
            $browser->script("$('#po-form').submit();");

            // Look directly for the custom string text layout written inside your toast warning context
            $browser->waitForText('Please add at least one item.')
                ->assertSee('Please add at least one item.')
                ->assertRouteIs('purchase-orders.create');
        });
    }

    /**
     * 3. BACKEND VALIDATION CRASH VECTOR: Testing raw network parameter failure intercept models.
     */
    public function test_backend_validation_rejection_alerts_user_via_toast(): void
    {
        $admin = User::where('type', 1)->first();
        $item = Item::where('status', 1)->first();

        $this->browse(function (Browser $browser) use ($admin, $item) {
            $browser->loginAs($admin)
                ->visitRoute('purchase-orders.create')
                ->waitFor('#po-form')

                // Inject an element grid row to satisfy client-side JavaScript rules
                ->select('#item_id', $item->id)
                ->type('#qty', '1')
                ->click('#add_to_list')
                ->pause(200)

                // Force supplier_id empty to cause a 422 HTTP validation rejection backend break
                ->script("$('#supplier_id').val('').trigger('change'); $('#po-form').submit();");

            // Look for whatever validation message Laravel returns based on your FormRequest rules
            $browser->waitForText('The supplier id field is required.')
                ->assertSee('The supplier id field is required.');
        });
    }


    /**
     * 4. SUCCESS VECTOR: Test editing an existing Purchase Order record.
     */
    public function test_can_edit_existing_purchase_order(): void
    {
        $admin = User::where('type', 1)->first();
        $po = PurchaseOrder::where('status', 0)->first();

        if (!$po) {
            $this->markTestSkipped('No pending purchase orders available in the database to run edit tests.');
        }

        $this->browse(function (Browser $browser) use ($admin, $po) {
            $browser->loginAs($admin)
                ->visitRoute('purchase-orders.edit', ['purchase_order' => $po->id])
                ->waitFor('#po-form')

                // Append updated contextual markers to remarks string
                ->type('#remarks', 'Modified by Dusk automation tracking scripts.')
                ->press('Save Purchase Order')

                // Safe browser rendering loop pause allocations
                ->pause(1500)
                ->waitForRoute('purchase-orders.index')
                ->assertSee("Purchase Order's Details Successfully updated.");
        });
    }


    /**
     * 5. VIEW VECTOR: Test displaying existing Purchase Order layout parameters and pivot item rows directly.
     */
      /**
     * 5. VIEW VECTOR: Test displaying existing Purchase Order layout parameters and pivot item rows directly.
     */
    public function test_can_view_purchase_order_details_and_calculations(): void
    {
        $admin = User::where('type', 1)->first();

        // Retrieve a purchase order with items to evaluate the layout matrix
        $po = PurchaseOrder::has('items')->with(['supplier', 'items'])->first();

        if (!$po) {
            $this->markTestSkipped('No purchase orders with items available in the database to run view detail tests.');
        }

        $this->browse(function (Browser $browser) use ($admin, $po) {
            // Directly loading the show route
            $browser->loginAs($admin)
                ->visitRoute('purchase-orders.show', ['purchase_order' => $po->id])
                ->waitFor('#print_out')

                // Assert Core Metadata Rendering
                ->assertSee($po->po_code)
                ->assertSee($po->supplier->name ?? 'N/A');

            // Track math calculations across the pivot dataset row matrix
            $computedSubTotal = 0;

            foreach ($po->items as $index => $item) {
                $computedSubTotal += $item->pivot->total;

                // Target row elements dynamically to confirm data formatting matches Blade filters
                $browser->assertSeeIn("#list tbody tr:nth-child(" . ($index + 1) . ")", number_format($item->pivot->quantity, 2))
                    ->assertSeeIn("#list tbody tr:nth-child(" . ($index + 1) . ")", $item->name)
                    ->assertSeeIn("#list tbody tr:nth-child(" . ($index + 1) . ")", number_format($item->pivot->price, 2))
                    ->assertSeeIn("#list tbody tr:nth-child(" . ($index + 1) . ")", number_format($item->pivot->total, 2));
            }

            // Cross-reference footer financial calculations
            $browser->assertSeeIn('.sub-total', number_format($computedSubTotal, 2))
                ->assertSeeIn('.discount', number_format($po->discount ?? 0, 2))
                ->assertSeeIn('.tax', number_format($po->tax ?? 0, 2))
                ->assertSeeIn('.grand-total', number_format($po->amount ?? 0, 2));

            // FIXED: Swapped raw int checks with your model's type-safe helper
            if ($po->isPending()) {
                // Should show edit button if pending
                $browser->assertSeeIn('.card-footer', 'Edit')
                    ->assertSourceHas(route('purchase-orders.edit', $po->id));
            } else {
                // FIXED: Scoped the negative assertion to avoid global template header text collisions
                $browser->assertDontSeeIn('.card-footer', 'Edit');

                $expectedStatusText = $po->isReceived() ? "RECEIVED" : "PARTIALLY RECEIVED";
                $browser->assertSee($expectedStatusText);
            }

            // Assert clean return link configuration exists
            $browser->assertSee('Back To List')
                ->assertSourceHas(route('purchase-orders.index'));
        });
    }

}
