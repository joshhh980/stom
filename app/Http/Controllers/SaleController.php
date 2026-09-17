<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\JsonResponse;

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
            \App\Models\Stock::whereIn('id', explode(',', $sale->stock_ids))->delete();
        }

        $sale->delete();

        return response()->json(['status' => 'success']);
    }
}
