@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Sales</h3>
        <div class="card-tools">
            <a href="{{ route('sales.manage') }}" class="btn btn-flat btn-primary">
                <span class="fas fa-plus"></span> Create New
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped" id="sales_table">
                <colgroup>
                    <col width="5%">
                    <col width="15%">
                    <col width="20%">
                    <col width="20%">
                    <col width="10%">
                    <col width="10%">
                    <col width="10%">
                </colgroup>
                <thead>
                    <tr class="bg-navy text-light">
                        <th class="py-1 px-2 text-center">#</th>
                        <th class="py-1 px-2">Date Created</th>
                        <th class="py-1 px-2">Sale Code</th>
                        <th class="py-1 px-2">Client</th>
                        <th class="py-1 px-2 text-center">Items</th>
                        <th class="py-1 px-2 text-right">Amount</th>
                        <th class="py-1 px-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $row)
                        @php 
                            // 💡 Calculate item units dynamically from comma-separated stock_ids safely
                            $itemCount = !empty(trim($row->stock_ids)) ? count(explode(',', $row->stock_ids)) : 0;
                        @endphp
                        <tr>
                            <td class="text-center align-middle py-1 px-2">{{ $loop->iteration }}</td>
                            <td class="align-middle py-1 px-2">
                                {{ \Carbon\Carbon::parse($row->date_created)->format('Y-m-d H:i') }}
                            </td>
                            <td class="align-middle py-1 px-2">{{ $row->sales_code }}</td>
                            <td class="align-middle py-1 px-2">{{ $row->client }}</td>
                            <td class="text-right align-middle py-1 px-2">
                                {{ number_format($itemCount) }}
                            </td>
                            <td class="text-right align-middle py-1 px-2 font-weight-bold">
                                {{ number_format($row->amount, 2) }}
                            </td>
                            <td class="text-center align-middle py-1 px-2">
                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    Action <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a class="dropdown-item" href="{{ route('sales.show', $row->id) }}">
                                        <span class="fa fa-eye text-dark mr-2"></span> View
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('sales.edit', $row->id) }}">
                                        <span class="fa fa-edit text-primary mr-2"></span> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="{{ $row->id }}">
                                        <span class="fa fa-trash text-danger mr-2"></span> Delete
                                    </a>
                                </div>
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
        // Initialize DataTables
        $('#sales_table').DataTable();

        // Bind delete click event handler 
        $(document).on('click', '.delete_data', function(){
            var id = $(this).attr('data-id');
            // Leverages your master layout's structural confirmation modal helper wrapper
            _conf("Are you sure to delete this Sales Record permanently?", "delete_sale", [id]);
        });
    });

    function delete_sale($id){
        start_loader();
        $.ajax({
            url: "{{ url('sales') }}/" + $id,
            method: "POST",
            data: {
                _method: "DELETE", // Map native Laravel HTTP Method Spoofing
                _token: "{{ csrf_token() }}" // Append structural security tokens payload
            },
            dataType: "json",
            error: err => {
                console.log(err);
                alert_toast("An error occurred.", 'error');
                end_loader();
            },
            success: function(resp){
                if(typeof resp == 'object' && resp.status == 'success'){
                    alert_toast("Sales record deleted successfully.", 'success');
                    location.reload();
                } else {
                    alert_toast("An error occurred.", 'error');
                    end_loader();
                }
            }
        });
    }
</script>
@endsection
