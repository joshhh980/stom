<?php

namespace App\Http\Controllers;


use App\Models\Item;
use Carbon\Carbon;
use App\Models\Stock;
use App\Enums\Status;
use App\Enums\StockType;
use App\Http\Requests\SaveStockRequest;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{

    public function index()
    {
        $today = Carbon::today();

        $items = Item::with('supplier')
            ->withSum(['stocks as total_in' => function ($query) use ($today) {
                $query->where('type', StockType::IN)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', $today);
                    });
            }], 'quantity')
            ->withSum(['stocks as total_out' => function ($query) {
                $query->where('type', StockType::OUT);
            }], 'quantity')
            ->get()
            ->map(function ($item) {
                $item->available_stock = ($item->total_in ?? 0) - ($item->total_out ?? 0);
                return $item;
            });

        return view('stocks.index', compact('items'));
    }


    public function createOrEdit(int $id = null)
    {
        $stock = $id ? Stock::findOrFail($id) : null;

        $items = Item::where('status', Status::ACTIVE->value)
            ->orderBy('name', 'asc')
            ->get();

        return view('stocks.manage', compact('stock', 'items'));
    }


    public function store(SaveStockRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $item = Item::findOrFail($validated['item_id']);
        $price = $item->cost ?? 0;

        $stockData = [
            'item_id'     => $validated['item_id'],
            'quantity'    => $validated['quantity'],
            'price'       => $price,
            'total'       => $validated['quantity'] * $price,
            'type'        => StockType::IN,
            'expiry_date' => $validated['expiry_date'],
            'batch_no'    => $validated['batch_no'],
        ];

        Stock::updateOrCreate(['id' => $validated['id'] ?? null], $stockData);

        return response()->json(['status' => 'success']);
    }
}
