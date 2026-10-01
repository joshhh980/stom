<?php

namespace App\Http\Controllers;

use App\Actions\ProcessReceivingStock;
use App\Http\Controllers\Controller;
use App\Models\Receiving;
use App\Models\AuditLog;
use App\Models\Sale;
use Illuminate\Http\Request;
use App\Http\Requests\SaveReceivingRequest;
use App\Models\ReceivingOrder;
use App\Models\PurchaseOrder;
use App\Models\BackOrder;
use App\Models\Stock;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ReceivingOrigin;
use App\Enums\Status;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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


    public function store(SaveReceivingRequest $request, ProcessReceivingStock $action): JsonResponse
    {
        // Executes entire process pipeline natively through isolated Action container
        $receiving = $action->execute($request->validated());

        $isNew = empty($request->input('id'));
        AuditLog::record(
            $isNew ? 'Receive Stock' : 'Update Received Stock',
            ($isNew ? 'Processed new inventory receiving entry' : 'Updated details for inventory receiving entry') . " (ID: {$receiving->id})"
        );
        session()->flash(
            'success',
            $isNew
                ? "New Stock was Successfully received."
                : "Received Stock's Details Successfully updated."
        );

        Log::info(($isNew ? "Receive Stock" : "Update Received Stock") . ": Processed record ID: {$receiving->id}");

        return response()->json(['status' => 'success', 'id' => $receiving->id]);
    }


    /**
     * Interface orchestrator assembling transactional documents and relationship graphs.
     */
    public function manage(Request $request)
    {
        $id = $request->get('id');
        $poId = $request->get('po_id');
        $boId = $request->get('bo_id');

        $receiving = null;
        $orderSource = null; // Poly-morphic context target: Can be a PurchaseOrder or a BackOrder
        $supplierId = null;
        $poCode = null;
        $boCode = null;
        $items = collect();

        // Scenario A: Editing an existing transaction entry
        if ($id) {
            $receiving = Receiving::findOrFail($id);
            $supplierId = $receiving->supplier_id;

            if ($receiving->isFromPurchaseOrder()) {
                $orderSource = PurchaseOrder::with(['supplier', 'items'])->find($receiving->form_id);
                $poId = $orderSource->id;
                $poCode = $orderSource->po_code;
                $items = $orderSource->items;
            } else {
                $orderSource = BackOrder::with(['supplier', 'purchaseOrder', 'items'])->find($receiving->form_id);
                $boId = $orderSource->id;
                $boCode = $orderSource->bo_code;
                $poId = $orderSource->purchaseOrder->id;
                $poCode = $orderSource->purchaseOrder->po_code;
                $items = $orderSource->items;
            }
        }
        // Scenario B: Processing intake straight from an active Purchase Order
        elseif ($poId) {
            $orderSource = PurchaseOrder::with(['supplier', 'items'])->findOrFail($poId);

            if (!$orderSource) {
                return view('receivings.manage', [
                    'receiving'   => null,
                    'orderSource' => null,
                    'suppliers'   => Supplier::where('status', Status::ACTIVE->value)->orderBy('name')->get(),
                    'supplierId'  => null,
                    'poId'        => $poId,
                    'boId'        => null,
                    'poCode'      => null,
                    'boCode'      => null,
                    'items'       => collect(),
                    'loadError'   => 'The requested purchase order could not be found.',
                ]);
            }

            $poCode = $orderSource->po_code;
            $supplierId = $orderSource->supplier_id;
            $items = $orderSource->items;
        }
        // Scenario C: Processing intake straight from an active Back Order allocation
        elseif ($boId) {
            $orderSource = BackOrder::with(['supplier', 'purchaseOrder', 'items'])->findOrFail($boId);
            $boCode = $orderSource->bo_code;
            $poId = $orderSource->purchaseOrder->id;
            $poCode = $orderSource->purchaseOrder->po_code;
            $supplierId = $orderSource->supplier_id;
            $items = $orderSource->items;
        }

        // Fetch active suppliers list using the global Status enum schema mapping cleanly
        $suppliers = Supplier::where('status', Status::ACTIVE->value)->orderBy('name', 'asc')->get();

        return view('receivings.manage', compact(
            'receiving',
            'orderSource',
            'suppliers',
            'supplierId',
            'poId',
            'boId',
            'poCode',
            'boCode',
            'items'
        ));
    }


    public function show(int $id)
    {
        // 💡 Load the supplier nested inside the orderSource graph instead
        $receiving = Receiving::with(['orderSource.supplier'])->findOrFail($id);

        $stockIds = array_filter(explode(',', $receiving->stock_ids));

        $stocks = \App\Models\Stock::with('item')
            ->whereIn('id', $stockIds)
            ->get();

        $computedSubTotal = $stocks->sum('total');

        return view('receivings.show', compact('receiving', 'stocks', 'computedSubTotal'));
    }

    public function destroy(Receiving $receiving): JsonResponse
    {
        $stockIds = array_filter(explode(',', (string) $receiving->stock_ids));

        if ($receiving->backOrdersCreated()->exists()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'This receiving record cannot be deleted because it has generated a back order.',
            ], 422);
        }

        if ($stockIds && Sale::where(function ($query) use ($stockIds) {
            foreach ($stockIds as $stockId) {
                $query->orWhereRaw('FIND_IN_SET(?, stock_ids)', [$stockId]);
            }
        })->exists()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'This receiving record cannot be deleted because its stock has been used in a sale.',
            ], 422);
        }

        DB::transaction(function () use ($receiving, $stockIds) {
            Stock::whereIn('id', $stockIds)->where('type', 1)->delete();
            $receiving->delete();
        });

        AuditLog::record('Delete Received Stock', "Deleted receiving record ID: {$receiving->id} and linked stock records.");

        return response()->json(['status' => 'success']);
    }
}
