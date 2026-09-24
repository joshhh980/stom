<?php

namespace App\Actions;

use App\Enums\PurchaseOrderStatus;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Class ImportPurchaseOrder
 * 
 * Coordinates parsing spreadsheet payloads to create purchase orders and optional stock receipts.
 * 
 * @package App\Actions
 */
class ImportPurchaseOrder
{
    public function __construct(
        protected ReceiveImportedPurchaseOrder $receiveAction
    ) {}

    /**
     * Parse and execute the Excel file import sequence.
     * 
     * @throws \Exception When row data is corrupted or data mappings fail.
     */
    public function execute(Request $request): PurchaseOrder
    {
        if (!$request->hasFile('excel_file')) {
            throw new Exception("No valid upload file payload encountered.");
        }

        $fileTmpPath = $request->file('excel_file')->getRealPath();
        
        // Load the Excel file structure utilizing standard PhpSpreadsheet wrappers
        $spreadsheet = IOFactory::load($fileTmpPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        return DB::transaction(function () use ($rows, $request) {
            $supplierId = null;
            $itemsPayload = [];

            // Aggregate metrics initialized from file structures
            $amount = 0;
            $discountPerc = 0;
            $discount = 0;
            $taxPerc = 0;
            $tax = 0;
            $remarks = '';
            $dateCreated = now();

            // Loop rows (Skip index 0 to cleanly jump table header allocations)
            foreach ($rows as $index => $row) {
                if ($index === 0 || empty($row[10])) {
                    continue; 
                }

                // 1. Resolve or Auto-Build the Target Supplier Group
                $supplierName = trim($row[14]);
                if (!empty($supplierName)) {
                    $supplier = Supplier::firstOrCreate(
                        ['name' => $supplierName]
                    );
                    $supplierId = $supplier->id;
                }

                // 2. Resolve or Auto-Create the Target Item configuration
                $itemName = trim($row[10]);
                $itemCostPrice = $this->parseNumeric($row[12]);

                $item = Item::firstOrCreate(
                    ['name' => $itemName],
                    [
                        'cost'        => $itemCostPrice,
                        'supplier_id' => $supplierId,
                        'description' => 'N/A'
                    ]
                );

                // Collect row structural metadata targets
                $amount       = $this->parseNumeric($row[0]);
                $discountPerc = $this->parseNumeric($row[1]);
                $discount     = $this->parseNumeric($row[2]);
                $taxPerc      = $this->parseNumeric($row[3]);
                $tax          = $this->parseNumeric($row[4]);
                $remarks      = $row[5] ?? '';
                $dateCreated  = $this->parseDateTime($row[6]);

                // Track items mapping matrix for direct sync executions
                $itemsPayload[$item->id] = [
                    'quantity'       => $this->parseNumeric($row[9]),    // Expected Qty
                    'actual_quantity'=> $this->parseNumeric($row[11]),   // Actual Received Qty
                    'expiry_date'    => $this->parseDateTime($row[7]),
                    'batch_no'       => $row[8] ?? null,
                    'price'          => $itemCostPrice,
                    'total'          => $this->parseNumeric($row[13]),
                ];
            }

            if (!$supplierId) {
                throw new Exception("Unable to extract a valid parent Supplier reference within file rows.");
            }

            // Determine workflow context based on view request parameter flags
            $shouldReceiveImmediately = $request->query('receive') === 'true';

            // 3. Hydrate and instantiate the core Purchase Order
            $purchaseOrder = new PurchaseOrder();
            // Automatically invokes model static booted tracking code routines
            $purchaseOrder->supplier_id   = $supplierId;
            $purchaseOrder->amount        = $amount;
            $purchaseOrder->discount_perc = $discountPerc;
            $purchaseOrder->discount      = $discount;
            $purchaseOrder->tax_perc      = $taxPerc;
            $purchaseOrder->tax           = $tax;
            $purchaseOrder->remarks       = $remarks;
            $purchaseOrder->date_created  = $dateCreated;
            
            // Assign type-safe status mapping paths cleanly based on context options
            $purchaseOrder->status = $shouldReceiveImmediately 
                ? PurchaseOrderStatus::RECEIVED 
                : PurchaseOrderStatus::PENDING;

            $purchaseOrder->save();

            // 4. Map and Sync relationship collection entries into the database
            $syncData = [];
            foreach ($itemsPayload as $itemId => $data) {
                $syncData[$itemId] = [
                    'quantity'    => $data['quantity'],
                    'price'       => $data['price'],
                    'total'       => $data['total'],
                    'expiry_date' => $data['expiry_date'],
                    'batch_no'    => $data['batch_no'],
                ];
            }
            $purchaseOrder->items()->sync($syncData);

            // 5. Action Group: Execute Immediate Receiving Pipeline (If requested)
            if ($shouldReceiveImmediately) {
                $this->receiveAction->execute($purchaseOrder, $itemsPayload);
            }

            return $purchaseOrder;
        });
    }

    /**
     * Clean and safely cast string characters into structural numeric configurations.
     */
    private function parseNumeric(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $cleaned = preg_replace('/[^0-9.]/', '', (string) $value);
        return empty($cleaned) ? 0.0 : (float) $cleaned;
    }

    /**
     * Parse raw mixed inputs safely into reliable Carbon datetime objects or null.
     */
    private function parseDateTime(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (Exception $e) {
            return null;
        }
    }
}
