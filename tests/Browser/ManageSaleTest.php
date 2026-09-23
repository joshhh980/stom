<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Item;
use App\Models\Stock;
use App\Enums\UserType;
use App\Enums\Status;
use App\Enums\StockType;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class ManageSaleTest extends DuskTestCase
{
    public function test_admin_can_create_sale_with_date_created_visible(): void
    {
        $adminUser = User::where([
            'type' => UserType::ADMIN->value
        ])->first();

        $this->assertNotNull($adminUser);

        $item = Item::where(
            'status',
            Status::ACTIVE->value
        )->first();

        $this->assertNotNull($item);

        $expectedTotal = round(
            round(($item->cost ?? 500.00) * 1.20, 2) * 2,
            2
        );

        $clientName = 'Walk-in Retail Client';

        $this->browse(function (Browser $browser) use (
            $adminUser,
            $item,
            $clientName,
            $expectedTotal
        ) {
            $rowSelector = "table#list tbody tr[data-id='{$item->id}']";
            $itemSelector = ".item-card[data-id='{$item->id}']";

            $browser->loginAs($adminUser)
                ->visit(route('sales.manage'))
                ->waitFor('#sale-form')
                ->assertPresent('#date_created')
                ->type('client', $clientName);

            $browser->click($itemSelector)
                ->waitFor($rowSelector)
                ->assertSeeIn(
                    "$rowSelector .qty-cell .cell-display",
                    '1'
                );

            $browser->click($itemSelector)
                ->pause(300)
                ->assertSeeIn(
                    "$rowSelector .qty-cell .cell-display",
                    '2'
                );

            $browser->type(
                '#remarks',
                'Automated corporate wholesale checkout Dusk test log runs.'
            );

            $browser->press('Save')
                ->waitForLocation(route('sales.index'), 10)
                ->assertSeeIn('#sales_table tbody', $clientName)
                ->assertSeeIn(
                    '#sales_table tbody',
                    number_format($expectedTotal, 2)
                );
        });
    }


    public function test_standard_user_cannot_see_or_modify_date_created_field(): void
    {
        $standardUser = User::factory()->create(['type' => UserType::STANDARD->value]);

        $item = Item::factory()->create([
            'name'   => 'Heavy Duty Cable Reels ' . uniqid(),
            'cost'   => 100.00,
            'status' => Status::ACTIVE->value
        ]);

        Stock::create([
            'item_id'  => $item->id,
            'quantity' => 5,
            'price'    => 100.00,
            'total'    => 500.00,
            'type'     => 1
        ]);

        $this->browse(function (Browser $browser) use ($standardUser, $item) {
            $browser->loginAs($standardUser)
                ->visit(route('sales.manage'))
                ->waitFor('#sale-form')

                ->assertMissing('#date_created')
                ->type('client', 'Walk-in Retail Client');

            $browser->click(".item-card[data-id='{$item->id}']")
                ->pause(200)
                ->press('Save')
                ->waitForLocation(route('sales.index'), 5)
                ->assertPathIs(route('sales.index', [], false))
                ->assertSeeIn('#sales_table tbody', 'Walk-in Retail Client')
                ->assertSeeIn('#sales_table tbody', '120.00');
        });
    }

    public function test_sale_form_shows_error_toast_when_saved_without_items(): void
    {
        $adminUser = User::where([
            'type' => UserType::ADMIN->value
        ])->first();

        $this->assertNotNull($adminUser);

        $clientName = 'Frontend Validation Test ' . uniqid();

        $this->browse(function (Browser $browser) use (
            $adminUser,
            $clientName
        ) {
            $browser->loginAs($adminUser)
                ->visit(route('sales.manage'))
                ->waitFor('#sale-form')
                ->type('client', $clientName)

                /*
             * No items have been added.
             */
                ->assertMissing('table#list tbody tr[data-id]')

                /*
             * Attempt to save.
             */
                ->press('Save')

                ->waitFor('.swal2-toast', 3)
                ->assertSeeIn(
                    '.swal2-toast',
                    'Click an item above to add it before saving.'
                );
        });
    }
}
