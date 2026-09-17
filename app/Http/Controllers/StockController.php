<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Item;
use App\Models\Supplier;
use Carbon\Carbon;

class StockController extends Controller
{

public function index()
{
    $today = Carbon::today();

    $items = Item::with('supplier')
        // Type 1 = Inwards movement (Exclude records that have already expired)
        ->withSum(['stocks as total_in' => function ($query) use ($today) {
            $query->where('type', 1)
                  ->where(function ($q) use ($today) {
                      $q->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>=', $today);
                  });
        }], 'quantity')
        // Type 2 = Outwards movement (Deductions from inventory)
        ->withSum(['stocks as total_out' => function ($query) {
            $query->where('type', 2);
        }], 'quantity')
        ->get()
        ->map(function ($item) {
            // Compute structural running balance calculations inline
            $item->available_stock = ($item->total_in ?? 0) - ($item->total_out ?? 0);
            return $item;
        });

    return view('stocks.index', compact('items'));
}

}
