@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Back Order Details - {{ $backOrder->bo_code }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"><strong>Purchase Order:</strong> {{ $backOrder->purchaseOrder->po_code ?? 'N/A' }}</div>
            <div class="col-md-6"><strong>Supplier:</strong> {{ $backOrder->supplier->name ?? 'N/A' }}</div>
        </div>
        <table class="table table-striped table-bordered mt-4">
            <thead>
                <tr class="text-light bg-navy">
                    <th>Item</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($backOrder->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="text-right">{{ number_format($item->pivot->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format($item->pivot->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->pivot->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer text-center">
        <a class="btn btn-flat btn-dark" href="{{ route('back-orders.index') }}">Back To List</a>
    </div>
</div>
@endsection
