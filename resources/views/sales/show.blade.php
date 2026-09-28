@extends('layouts.app')

@section('content')
<div class="d-none" id="thermal-receipt">
    <div class="receipt-container">
        <h1 class="store-title">{{ config('settings.name', 'Store') }}</h1>
        <div class="receipt-label">Sales Receipt</div>
        <div class="divider"></div>
        <div class="meta-section">
            <div><strong>Sales Code:</strong> {{ $sale->sales_code }}</div>
            <div><strong>Client:</strong> {{ $sale->client ?: 'Guest' }}</div>
            <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->date_created)->format('Y-m-d H:i') }}</div>
        </div>
        <h2 class="receipt-heading">Items</h2>
        <table class="receipt-table">
            <thead>
                <tr>
                    <th class="item-column">Item</th>
                    <th class="qty-column">Qty</th>
                    <th class="money-column">Price</th>
                    <th class="money-column">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stocks as $stock)
                <tr>
                    <td>{{ $stock->item->name ?? 'Unknown Item' }}</td>
                    <td class="numeric">{{ number_format($stock->quantity, 2) }}</td>
                    <td class="numeric">{{ number_format($stock->price, 2) }}</td>
                    <td class="numeric">{{ number_format($stock->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th class="numeric">{{ config('settings.currency', 'GHS') }} {{ number_format($sale->amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
        @if(trim((string) $sale->remarks) !== '')
        <div class="remarks"><strong>Remarks:</strong> {{ $sale->remarks }}</div>
        @endif
        <div class="divider"></div>
        <div class="receipt-footer">Thank you for your business!</div>
    </div>
</div>

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
        <button class="btn btn-flat btn-success" type="button" id="print-pos-receipt">
            <i class="fas fa-print"></i> Print POS Receipt
        </button>
        <a class="btn btn-flat btn-primary" href="{{ route('sales.edit', $sale) }}"><i class="fa fa-edit"></i> Edit</a>
        <a class="btn btn-flat btn-dark" href="{{ route('sales.index') }}">Back To List</a>
    </div>
</div>

<script>
    $(function () {
        $('#print-pos-receipt').on('click', function () {
            const printWindow = window.open('', '', 'width=420,height=800');

            if (!printWindow) {
                window.alert('Allow pop-ups to print the receipt.');
                return;
            }

            printWindow.document.open();
            printWindow.document.write(`<!doctype html>
                <html>
                    <head>
                        <meta charset="utf-8">
                        <title>Sales Receipt</title>
                        <style>
                            @page { size: 80mm auto; margin: 0; }
                            html, body { width: 80mm; margin: 0; padding: 0; color: #000; background: #fff; }
                            body { font-family: "Courier New", Courier, monospace; }
                            .receipt-container { width: 80mm; padding: 4mm; box-sizing: border-box; }
                            .store-title { margin: 2px 0; font-size: 18px; font-weight: 900; text-align: center; text-transform: uppercase; overflow-wrap: anywhere; }
                            .receipt-label { margin-bottom: 4px; font-size: 14px; font-weight: bold; text-align: center; text-transform: uppercase; }
                            .divider { width: 100%; margin: 5px 0; border-top: 1px dashed #000; }
                            .meta-section { font-size: 12px; line-height: 1.4; overflow-wrap: anywhere; }
                            .receipt-heading { margin: 8px 0 4px; padding-bottom: 2px; border-bottom: 1px dashed #000; font-size: 15px; text-transform: uppercase; }
                            .receipt-table { width: 100%; margin-top: 5px; border-collapse: collapse; table-layout: fixed; }
                            .receipt-table th, .receipt-table td { padding: 4px 2px; font-size: 10px; line-height: 1.3; overflow-wrap: anywhere; }
                            .receipt-table tbody tr { border-bottom: 1px dashed #000; }
                            .item-column { width: 38%; text-align: left; }
                            .qty-column { width: 14%; text-align: right; }
                            .money-column { width: 24%; text-align: right; }
                            .numeric { text-align: right; }
                            .receipt-table tfoot th { padding-top: 6px; border-top: 1px solid #000; font-size: 12px; }
                            .remarks { margin-top: 8px; font-size: 11px; overflow-wrap: anywhere; }
                            .receipt-footer { margin-top: 12px; font-size: 12px; font-weight: bold; text-align: center; }
                        </style>
                    </head>
                    <body></body>
                </html>`);
            printWindow.document.close();

            const receipt = document.querySelector('#thermal-receipt .receipt-container');
            printWindow.document.body.appendChild(printWindow.document.importNode(receipt, true));
            printWindow.onafterprint = function () {
                printWindow.close();
            };
            printWindow.focus();
            printWindow.requestAnimationFrame(function () {
                printWindow.print();
            });
        });
    });
</script>
@endsection
