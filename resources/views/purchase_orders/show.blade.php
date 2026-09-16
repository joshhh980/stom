@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Purchase Order Details - {{ $purchaseOrder->po_code }}</h4>
    </div>
    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label text-info">P.O. Code</label>
                    <div>{{ $purchaseOrder->po_code }}</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="supplier_id" class="control-label text-info">Supplier</label>
                        <div>{{ $purchaseOrder->supplier->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            
            <h4 class="text-info mt-4">Orders</h4>
            <table class="table table-striped table-bordered" id="list">
                <colgroup>
                    <col width="10%">
                    <col width="30%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                </colgroup>
                <thead>
                    <tr class="text-light bg-navy">
                        <th class="text-center py-1 px-2">Qty</th>
                        <th class="text-center py-1 px-2">Item</th>
                        <th class="text-center py-1 px-2">Expiry Date</th>
                        <th class="text-center py-1 px-2">Batch No</th>
                        <th class="text-center py-1 px-2">Cost</th>
                        <th class="text-center py-1 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subTotal = 0; @endphp
                    @foreach($purchaseOrder->items as $item)
                        @php $subTotal += $item->pivot->total; @endphp
                        <tr>
                            <td class="py-1 px-2 text-center">{{ number_format($item->pivot->quantity, 2) }}</td>
                            <td class="py-1 px-2">
                                <strong>{{ $item->name }}</strong><br>
                                <small class="text-muted">{{ $item->description }}</small>
                            </td>
                            <td class="py-1 px-2 text-center">{{ $item->pivot->expiry_date ?? 'N/A' }}</td>
                            <td class="py-1 px-2 text-center">{{ $item->pivot->batch_no ?? 'N/A' }}</td>
                            <td class="py-1 px-2 text-right">{{ number_format($item->pivot->price, 2) }}</td>
                            <td class="py-1 px-2 text-right">{{ number_format($item->pivot->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="5">Sub Total</th>
                        <th class="text-right py-1 px-2 sub-total">{{ number_format($subTotal, 2) }}</th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="5">Discount {{ $purchaseOrder->discount_perc ?? 0 }}%</th>
                        <th class="text-right py-1 px-2 discount">{{ number_format($purchaseOrder->discount ?? 0, 2) }}</th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="5">Tax {{ $purchaseOrder->tax_perc ?? 0 }}%</th>
                        <th class="text-right py-1 px-2 tax">{{ number_format($purchaseOrder->tax ?? 0, 2) }}</th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="5">Total</th>
                        <th class="text-right py-1 px-2 grand-total">{{ number_format($purchaseOrder->amount ?? 0, 2) }}</th>
                    </tr>
                </tfoot>
            </table>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="remarks" class="text-info control-label">Remarks</label>
                        <p>{{ $purchaseOrder->remarks ?? 'No remarks provided.' }}</p>
                    </div>
                </div>
                
                @if(!$purchaseOrder->isPending())
                    <div class="col-md-6 d-flex align-items-center">
                        @if($purchaseOrder->isReceived())
                            <span class="badge badge-success p-2">RECEIVED</span>
                        @elseif($purchaseOrder->isPartiallyReceived())
                            <span class="badge badge-warning p-2">PARTIALLY RECEIVED</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="card-footer py-2 text-center">
        @if($purchaseOrder->isPending())
            <a class="btn btn-flat btn-primary" href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}">Edit</a>
        @endif
        <a class="btn btn-flat btn-dark" href="{{ route('purchase-orders.index') }}">Back To List</a>
    </div>
</div>
@endsection
