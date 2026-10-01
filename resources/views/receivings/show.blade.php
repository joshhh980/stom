@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">
            Received Order Details - {{ $receiving->orderSource->po_code ?? '' }}
        </h4>
    </div>
    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label text-info">FROM P.O. Code</label>
                    <div>{{ $receiving->orderSource->po_code ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="supplier_id" class="control-label text-info">Supplier</label>
                        <div>{{ $receiving->orderSource->supplier->name ?? '' }}</div>
                    </div>
                </div>

                @if($receiving->isFromBackOrder() && isset($receiving->orderSource->bo_code))
                <div class="col-md-6">
                    <label class="control-label text-info">FROM B.O. Code</label>
                    <div>{{ $receiving->orderSource->bo_code }}</div>
                </div>
                @endif
            </div>

            <h4 class="text-info mt-4">Orders</h4>
            <table class="table table-striped table-bordered" id="list">
                <thead>
                    <tr class="text-light bg-navy">
                        <th class="text-center py-1 px-2">Qty</th>
                        <th class="text-center py-1 px-2">Item</th>
                        <th class="text-center py-1 px-2">Cost</th>
                        <th class="text-center py-1 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocks as $stock)
                    <tr>
                        <td class="py-1 px-2 text-center">
                            {{ number_format($stock->quantity, 2) }}
                        </td>
                        <td class="py-1 px-2">
                            <strong>{{ $stock->item->name ?? 'Unknown Item' }}</strong> <br>
                            {{ $stock->item->description ?? '' }}
                        </td>
                        <td class="py-1 px-2 text-right">
                            {{ number_format($stock->price, 2) }}
                        </td>
                        <td class="py-1 px-2 text-right">
                            {{ number_format($stock->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="3">Sub Total</th>
                        <th class="text-right py-1 px-2 sub-total">
                            {{ number_format($computedSubTotal, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="3">
                            Discount {{ number_format($receiving->discount_perc, 0) }}%
                        </th>
                        <th class="text-right py-1 px-2 discount">
                            {{ number_format($receiving->discount, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="3">
                            Tax {{ number_format($receiving->tax_perc, 0) }}%
                        </th>
                        <th class="text-right py-1 px-2 tax">
                            {{ number_format($receiving->tax, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="3">Total</th>
                        <th class="text-right py-1 px-2 grand-total">
                            {{ number_format($receiving->amount, 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="remarks" class="text-info control-label">Remarks</label>
                        <p>{{ $receiving->remarks ?? 'No remarks provided.' }}</p>
                    </div>
                </div>

                @if($receiving->orderSource && $receiving->orderSource->status !== \App\Enums\PurchaseOrderStatus::PENDING->value)
                <div class="col-md-6 text-right align-self-center">
                    <div><strong>Status:</strong></div>
                    {!! $receiving->orderSource->status->badgeHtml() !!}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card-footer py-2 text-center">
        {{--
        <a class="btn btn-flat btn-primary" href="{{ route('receivings.manage', ['id' => $receiving->id]) }}">
            <i class="fa fa-edit"></i> Edit
        </a>
        --}}
        <a class="btn btn-flat btn-dark" href="{{ route('receivings.index') }}">
            Back To List
        </a>
    </div>
</div>
@endsection