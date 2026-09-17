<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Item;
use App\Enums\Status;
use App\Models\Stock;
use Carbon\Carbon;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class ManageStockTest extends DuskTestCase
{
    public function test_can_interactively_create_new_stock_record(): void
    {
        $user = User::first();
        $item = Item::factory()->create([
            'name'   => 'Industrial Copper Connectors ' . uniqid(),
            'cost'   => 45.50,
            'status' => Status::ACTIVE->value
        ]);


        $inputExpiryFormat = Carbon::today()->addYear()->format('mdY');

        $this->browse(function (Browser $browser) use ($user, $item, $inputExpiryFormat) {
            $browser->loginAs($user)
                ->visit(route('stocks.manage'))
                ->waitFor('#stock-form')
                ->assertSee('Create New Stock Record');

            $browser->script([
                "$('#item_id').val('" . $item->id . "').trigger('change');"
            ]);

            $browser->pause(200);

            $browser->type('quantity', '150')
                ->type('expiry_date', $inputExpiryFormat)
                ->type('batch_no', 'BAT-2026-X99');

            $browser->press('Add to Stock')
                ->waitForLocation(route('stocks.index'), 5)
                ->assertPathIs(route('stocks.index', [], false));


            $this->assertDatabaseHas('stock_list', [
                'item_id'  => $item->id,
                'quantity' => 150,
                'type'     => 1,
                'batch_no' => 'BAT-2026-X99',
            ]);

            $savedStock = Stock::where('item_id', $item->id)
                ->where('batch_no', 'BAT-2026-X99')
                ->first();

            $this->assertNotNull($savedStock, "The stock record was not written to the database.");

            $this->assertEquals(45.50, $savedStock->price);
            $this->assertEquals(150 * $savedStock->price, $savedStock->total);
        });
    }


    public function test_validation_blocks_empty_stock_submissions(): void
    {
        $user = User::first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(route('stocks.manage'))
                ->waitFor('#stock-form');

            $browser->press('Add to Stock')
                ->pause(500);

            $browser->assertPresent('.err-msg')
                ->assertSeeIn('.err-msg', 'The item id field is required.')
                ->assertSeeIn('.err-msg', 'The quantity field is required.');

            $browser->assertPathIs(route('stocks.manage', [], false));
        });
    }
}
