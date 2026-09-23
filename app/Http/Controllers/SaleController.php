<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Stock;
use App\Enums\Status;
use App\Enums\StockType;
use App\Http\Requests\SaveSaleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::orderBy('date_created', 'desc')->get();

        return view('sales.index', compact('sales'));
    }

    public function destroy(int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);

        if (!empty(trim($sale->stock_ids))) {
            Stock::whereIn('id', explode(',', $sale->stock_ids))->delete();
        }

        $sale->delete();

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle form creation and record editing interfaces.
     */
    public function createOrEdit(int $id = null)
    {
        $sale = $id ? Sale::findOrFail($id) : null;

        /*
         * Pull active items with their stock movement totals.
         *
         * The collection is keyed by item ID because the frontend
         * accesses items using:
         *
         *     items[itemId]
         */
        $items = Item::where('status', Status::ACTIVE->value)
            ->withSum(['stocks as total_in' => function ($q) {
                $q->where('type', StockType::IN->value);
            }], 'quantity')
            ->withSum(['stocks as total_out' => function ($q) {
                $q->where('type', StockType::OUT->value);
            }], 'quantity')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($item) {
                $item->available_stock =
                    ($item->total_in ?? 0) -
                    ($item->total_out ?? 0);

                return $item;
            })
            ->filter(fn($item) => $item->available_stock > 0)
            ->keyBy('id');

        $currentItems = collect();

        if ($sale && !empty(trim($sale->stock_ids))) {
            $currentItems = Stock::with('item')
                ->whereIn('id', explode(',', $sale->stock_ids))
                ->get();
        }

        return view(
            'sales.manage',
            compact('sale', 'items', 'currentItems')
        );
    }


    public function store(SaveSaleRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {

                $validated = $request->validated();

                $id = $validated['id'] ?? null;
                $isNew = empty($id);

                $salesCode = $validated['sales_code'] ?? null;

                /*
                 * Generate a sales code for new sales.
                 */
                if ($isNew) {
                    $prefix = 'SALE-';

                    $latestSale = Sale::where(
                        'sales_code',
                        'LIKE',
                        "{$prefix}%"
                    )
                        ->orderBy('sales_code', 'desc')
                        ->first();

                    if ($latestSale) {
                        $lastNumber = (int) str_replace(
                            $prefix,
                            '',
                            $latestSale->sales_code
                        );

                        $nextNumber = $lastNumber + 1;

                        $salesCode =
                            $prefix .
                            sprintf('%04d', $nextNumber);
                    } else {
                        $salesCode =
                            $prefix .
                            sprintf('%04d', 1);
                    }
                }


                $dateCreated = !empty($validated['date_created'])
                    ? Carbon::parse($validated['date_created'])
                    : Carbon::now();

                $sale = Sale::updateOrCreate(
                    ['id' => $id],
                    [
                        'sales_code' => $salesCode,
                        'client' => $validated['client']
                            ?? 'Guest Customer',
                        'remarks' => $validated['remarks']
                            ?? null,
                        'date_created' =>
                        $dateCreated->toDateTimeString(),
                    ]
                );

                /*
                 * When editing an existing sale, remove the
                 * previous outgoing stock records first.
                 */
                if (
                    !$isNew &&
                    !empty(trim($sale->stock_ids))
                ) {
                    Stock::whereIn(
                        'id',
                        explode(',', $sale->stock_ids)
                    )->delete();
                }

                /*
                 * Create outgoing stock records.
                 */
                $stockIds = [];

                foreach ($validated['item_id'] as $k => $itemId) {
                    $stock = Stock::create([
                        'item_id' => $itemId,
                        'quantity' => $validated['qty'][$k],
                        'price' => $validated['price'][$k],
                        'total' => $validated['total'][$k],
                        'type' => StockType::OUT->value,
                    ]);

                    $stockIds[] = $stock->id;
                }

                /*
                 * Calculate the sale amount from the submitted
                 * line totals.
                 */
                $sale->update([
                    'stock_ids' => implode(',', $stockIds),
                    'amount' => array_sum($validated['total']),
                ]);

                return response()->json([
                    'status' => 'success',
                    'id' => $sale->id,
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'msg' => 'Failed to save sale: ' . $e->getMessage(),
            ], 422);
        }
    }
}
