@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Received Orders</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped" id="receiving-table">
                <colgroup>
                    <col width="5%">
                    <col width="25%">
                    <col width="25%">
                    <col width="25%">
                    <col width="20%">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Created</th>
                        <th>From</th>
                        <th>Items</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receivingOrders as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $row->date_created->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->source_code }}</td>
                            <td class="text-right">{{ number_format($row->items_count) }}</td>
                            <td align="center">
                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    Action <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a class="dropdown-item" href="{{ route('receivings.show', $row->id) }}">
                                        <span class="fa fa-eye text-dark"></span> View
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="{{ $row->id }}">
                                        <span class="fa fa-trash text-danger"></span> Delete
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
@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        // Style parameters injection
        $('.table td, .table th').addClass('py-1 px-2 align-middle');
        $('#receiving-table').DataTable();

        // Bind CSRF verification values
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Delete hook action listener
        $(document).on('click', '.delete_data', function(){
            let id = $(this).attr('data-id');
            _conf("Are you sure to delete this Received Orders permanently?", "delete_receiving", [id]);
        });
    });

    function delete_receiving(id){
        start_loader();
        
        let deleteUrl = "{{ route('receivings.destroy', ':id') }}".replace(':id', id);

        $.ajax({
            url: deleteUrl,
            method: "POST", 
            data: {
                _method: 'DELETE'
            },
            dataType: "json",
            error: err => {
                console.log(err);
                alert_toast("An error occurred.", 'error');
                end_loader();
            },
            success: function(resp){
                if(typeof resp == 'object' && resp.status == 'success'){
                    location.reload();
                } else {
                    alert_toast(resp.message || "An error occurred.", 'error');
                    end_loader();
                }
            }
        });
    }
</script>
@endpush