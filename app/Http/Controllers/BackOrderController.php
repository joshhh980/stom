<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BackOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

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

    public function create()
    {
        return Redirect::route('back-orders.index');
    }

    public function show(BackOrder $backOrder)
    {
        $backOrder->load(['supplier', 'purchaseOrder', 'items']);

        return view('back-orders.show', compact('backOrder'));
    }

    public function destroy(BackOrder $backOrder): JsonResponse
    {
        if (!$backOrder->isPending() || $backOrder->receiving()->exists()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only unreceived back orders can be deleted.',
            ], 422);
        }

        DB::transaction(function () use ($backOrder) {
            $backOrder->items()->detach();
            $backOrder->delete();
        });

        AuditLog::record('Delete Back Order', "Deleted back order Code: {$backOrder->bo_code} (ID: {$backOrder->id})");

        return response()->json(['status' => 'success']);
    }
}
