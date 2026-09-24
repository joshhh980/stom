<?php

namespace App\Http\Controllers;

use App\Enums\StockType;
use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('name')->get();

        return view('items.index', compact('items'));
    }

    public function create()
    {
        $item = new Item(['status' => 1]);
        $suppliers = Supplier::orderBy('name')->get();

        return view('items.manage', compact('item', 'suppliers'));
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $id = $validated['id'] ?? null;
        $quantity = $validated['quantity'] ?? 0;

        DB::transaction(function () use ($validated, $id, $quantity) {
            $item = Item::updateOrCreate(
                ['id' => $id],
                collect($validated)->only([
                    'name', 'description', 'supplier_id', 'cost', 'status',
                ])->all()
            );

            if (!$id && $quantity > 0) {
                Stock::create([
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $item->cost,
                    'total' => $quantity * $item->cost,
                    'type' => StockType::IN,
                    'expiry_date' => $validated['expiry_date'] ?? null,
                    'batch_no' => $validated['batch_no'] ?? null,
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => $id
                ? 'Item successfully updated.'
                : 'New Item successfully saved.',
        ]);
    }

    public function show(Item $item)
    {
        $item->load('supplier');

        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('items.manage', compact('item', 'suppliers'));
    }

    public function destroy(Item $item): JsonResponse
    {
        try {
            $item->delete();
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unable to delete item: ' . $exception->getMessage(),
            ], 422);
        }

        return response()->json(['status' => 'success']);
    }
}