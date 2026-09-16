<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Receiving;
use Illuminate\Http\Request;

class ReceivingController extends Controller
{
    public function index()
    {
        // Fetches list and eager-loads relationships dynamically
        $receivingOrders = Receiving::with('orderSource')
            ->orderBy('date_created', 'desc')
            ->get();

        return view('receivings.index', compact('receivingOrders'));
    }
}
