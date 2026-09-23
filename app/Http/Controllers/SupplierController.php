<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();

        return view('suppliers.index', compact('suppliers'));
    }


    public function create()
    {
        $supplier = new Supplier();
        return view('suppliers.manage', compact('supplier'));
    }

    public function edit(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.manage', compact('supplier'));
    }

    public function store(StoreSupplierRequest $request)
    {
        $id = $request->input('id');

        try {
            Supplier::updateOrCreate(
                ['id' => $id],
                $request->validated()
            );

            $msg = empty($id) ? "New Supplier successfully saved." : "Supplier successfully updated.";

            session()->flash('success', $msg);

            return response()->json([
                'status' => 'success',
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'msg' => 'An error occurred while saving: ' . $e->getMessage()
            ], 500);
        }
    }
}
