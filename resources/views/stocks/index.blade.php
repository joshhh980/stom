@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Stocks</h3>
        
        @if(auth()->user() && auth()->user()->type == \App\Enums\UserType::ADMIN)
            <div class="card-tools">
                <button type="button" class="btn btn-flat btn-success add_item">
                    <span class="fas fa-plus"></span> Add Item
                </button>
                <a href="{{ route('stocks.manage') }}" class="btn btn-flat btn-primary">
                    <span class="fas fa-plus"></span> Add to Stock
                </a>
            </div>
            <div class="position-relative d-inline-block ml-2">
                <form action="{{ route('stocks.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="excel_file" accept=".xlsx, .xls, .csv" required>
                    <button class="btn btn-flat btn-primary position-relative" type="submit">Upload Stock</button>
                </form>
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped" id="stocks_table">
                <colgroup>
                    <col width="5%">
                    <col width="20%">
                    <col width="20%">
                    <col width="40%">
                    <col width="15%">
                </colgroup>
                <thead>
                    <tr class="bg-navy text-light">
                        <th class="py-1 px-2 text-center">#</th>
                        <th class="py-1 px-2">Item Name</th>
                        <th class="py-1 px-2">Supplier</th>
                        <th class="py-1 px-2">Description</th>
                        <th class="py-1 px-2 text-center">Available Stocks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $row)
                        <tr>
                            <td class="text-center align-middle py-1 px-2">{{ $loop->iteration }}</td>
                            <td class="align-middle py-1 px-2">{{ $row->name }}</td>
                            <td class="align-middle py-1 px-2">{{ $row->supplier->name ?? 'N/A' }}</td>
                            <td class="align-middle py-1 px-2">{{ $row->description }}</td>
                            <td class="text-right align-middle py-1 px-2 font-weight-bold">
                                {{ number_format($row->available_stock) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on('click', '.add_item', function(){
            uni_modal("Add Item", "{{ route('items.create') }}", 'mid-large');
        });

        $('#stocks_table').DataTable({
            language: {
                zeroRecords: '<div class="text-center py-3">' +
                    'No items match your search.<br>' +
                    '<button type="button" class="btn btn-sm btn-success mt-2 add_item">' +
                    '<span class="fas fa-plus"></span> Add New Item</button>' +
                    '</div>',
                emptyTable: '<div class="text-center py-3">' +
                    'No items in stock yet.<br>' +
                    '<button type="button" class="btn btn-sm btn-success mt-2 add_item">' +
                    '<span class="fas fa-plus"></span> Add New Item</button>' +
                    '</div>'
            }
        });
    });
</script>
@endsection
