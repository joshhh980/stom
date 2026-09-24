@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Sale Details - {{ $sale->sales_code }}</h4>
    </div>
    <div class="card-body" id="print_out">
        <div class="row">
            <div class="col-md-6"><strong>Client:</strong> {{ $sale->client }}</div>
            <div class="col-md-6"><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->date_created)->format('Y-m-d H:i') }}</div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6"><strong>Remarks:</strong> {{ $sale->remarks ?: 'N/A' }}</div>
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
                @foreach($stocks as $stock)
                    <tr>
                        <td>{{ $stock->item->name ?? 'Unknown Item' }}</td>
                        <td class="text-right">{{ number_format($stock->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format($stock->price, 2) }}</td>
                        <td class="text-right">{{ number_format($stock->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right">Total</th>
                    <th class="text-right">{{ number_format($sale->amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer text-center">
        <a class="btn btn-flat btn-primary" href="{{ route('sales.edit', $sale) }}"><i class="fa fa-edit"></i> Edit</a>
        <a class="btn btn-flat btn-dark" href="{{ route('sales.index') }}">Back To List</a>
    </div>
</div>
@endsection
