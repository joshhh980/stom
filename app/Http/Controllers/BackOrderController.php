<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BackOrder;

class BackOrderController extends Controller
{
    public function index()
    {
        $backOrders = BackOrder::with('supplier')
            ->withCount('items') 
            ->orderBy('date_created', 'desc')
            ->get();

        return view('back-orders.index', compact('backOrders'));
    }
}
