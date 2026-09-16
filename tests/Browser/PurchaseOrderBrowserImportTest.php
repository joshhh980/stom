<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\DuskTestCase;

class PurchaseOrderBrowserImportTest extends DuskTestCase
{

    /**
     * Test that the browser's file picker upload triggers the import pipeline safely.
     */
    public function test_browser_file_picker_upload_triggers_safely(): void
    {
        // 1. ARRANGE: Set up system data structures
        $user = User::factory()->create(['type' => 1]);
        $supplier = Supplier::factory()->create(['name' => 'Dusk Supplier Corp']);
        $item = Item::factory()->create(['name' => 'Dusk Test Product', 'cost' => 45.00]);

        $stubPath = public_path('sample_po.xlsx');

        // 2. GENERATE: Build an explicit array mapping exactly to what your Action indices scan
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            // Index Row 0: Headers (Skipped by import action)
            ['Amount', 'Discount %', 'Discount Amt', 'Tax %', 'Tax Amt', 'Remarks', 'Date Created', 'Expiry', 'Batch', 'Expected Qty', 'Item Name', 'Actual Qty', 'Price', 'Total', 'Supplier Name', 'Invoice No'],

            // Index Row 1: The row positions MUST align with $row[idx] calls in your parsing loop
            [
                45.00,                 // $row[0]  - Amount
                0,                     // $row[1]  - discount_perc
                0,                     // $row[2]  - discount
                0,                     // $row[3]  - tax_perc
                0,                     // $row[4]  - tax
                'Dusk import test.',   // $row[5]  - remarks
                '2026-09-10 12:00:00', // $row[6]  - date_created
                '2030-01-01',          // $row[7]  - expiry_date
                'B-IMPORT',            // $row[8]  - batch_no
                1,                     // $row[9]  - expected_quantity
                'Dusk Test Product',   // $row[10] - item_name
                1,                     // $row[11] - actual_quantity
                45.00,                 // $row[12] - price / cost
                45.00,                 // $row[13] - total
                'Dusk Supplier Corp',  // $row[14] - supplier_name
                'INV-999'              // $row[15] - invoice_number
            ]
        ]);

        $writer = new Xlsx($spreadsheet);
        $writer->save($stubPath);

        // 3. ACT & ASSERT
        $this->browse(function (Browser $browser) use ($user, $stubPath) {
            // Inside your Dusk success test act:
            $browser->loginAs($user)
                ->visitRoute('purchase-orders.index')
                ->waitFor('#po-table')
                ->attach('.card-tools form:not([action*="receive"]) input[name="excel_file"]', $stubPath)
                ->script("$('.card-tools form:not([action*=\"receive\"])').submit();"); // Triggers AJAX submission

            $browser->waitForText('Data successfully imported and compiled!')
                ->assertSee('Data successfully imported and compiled!');
        });

        // 4. CLEANUP
        if (File::exists($stubPath)) {
            File::delete($stubPath);
        }
    }

    /**
     * FAILURE VECTOR: Test that uploading a corrupted or missing column file rejects safely.
     */
    public function test_uploading_invalid_excel_file_fails_gracefully(): void
    {
        $user = User::factory()->create(['type' => 1]);
        $stubPath = public_path('corrupted_po.xlsx');

        // 1. GENERATE: Create a completely broken spreadsheet missing crucial data (like Supplier)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            ['Amount', 'Discount %', 'Item Name', 'Supplier Name'], // Missing most required headers
            [100.00, 0, '', '']                                    // Leaving names completely blank
        ]);

        $writer = new Xlsx($spreadsheet);
        $writer->save($stubPath);

        // 2. ACT & ASSERT
        $this->browse(function (Browser $browser) use ($user, $stubPath) {
            // Inside your Dusk failure test act:
            $browser->loginAs($user)
                ->visitRoute('purchase-orders.index')
                ->waitFor('#po-table')
                ->attach('excel_file', $stubPath)
                ->script("$('.card-tools form:not([action*=\"receive\"])').submit();");

            $browser->waitForText('Error loading file')
                ->assertSee('Error loading file');
        });

        // 3. CLEANUP
        if (File::exists($stubPath)) {
            File::delete($stubPath);
        }
    }
}
