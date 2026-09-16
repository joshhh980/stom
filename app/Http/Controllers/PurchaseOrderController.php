<?php

namespace App\Http\Controllers;

use App\Actions\CreatePurchaseOrder;
use App\Actions\ImportPurchaseOrder;
use App\Actions\UpdatePurchaseOrder;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{

    private function getFormData(): array
    {
        return [
            'suppliers' => Supplier::active()->get(),
            'items'     => Item::active()->get(),
        ];
    }

    public function index(): View
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->withCount('items')
            ->orderBy('date_created', 'desc')
            ->get();

        return view('purchase_orders.index', compact('purchaseOrders'));
    }


    public function create(): View
    {
        return view('purchase_orders.create', $this->getFormData());
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('items');

        return view('purchase_orders.create', array_merge(
            ['po' => $purchaseOrder],
            $this->getFormData()
        ));
    }

    public function store(PurchaseOrderRequest $request, CreatePurchaseOrder $createAction): JsonResponse
    {
        $po = $createAction->execute($request);

        session()->flash('success', 'New Purchase Order was Successfully created.');
        return response()->json(['status' => 'success', 'id' => $po->id]);
    }


    public function update(
        PurchaseOrderRequest $request,
        PurchaseOrder $purchaseOrder,
        UpdatePurchaseOrder $updateAction
    ): JsonResponse {
        $po = $updateAction->execute($purchaseOrder, $request);

        session()->flash('success', "Purchase Order's Details Successfully updated.");
        return response()->json(['status' => 'success', 'id' => $po->id]);
    }


    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->isPending()) {
            $purchaseOrder->delete();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Only pending orders can be deleted.'], 400);
    }


    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function import(Request $request, ImportPurchaseOrder $importAction): JsonResponse
    {
        try {
            $importAction->execute($request);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data successfully imported and compiled!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error loading file: ' . $e->getMessage()
            ], 400);
        }
    }
}
