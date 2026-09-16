@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Back Orders</h3>
        <div class="card-tools">
            <a href="{{ route('back-orders.create') }}" class="btn btn-flat btn-primary">
                <span class="fas fa-plus"></span> Create New
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped" id="bo-table">
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
                    <tr>
                        <th>#</th>
                        <th>Date Created</th>
                        <th>BO Code</th>
                        <th>Supplier</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backOrders as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $row->date_created->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->bo_code }}</td>
                            <td>{{ $row->supplier->name ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($row->items_count) }}</td>
                            <td class="text-center">
                                @if($row->isPending())
                                    <span class="badge badge-primary rounded-pill">Pending</span>
                                @elseif($row->isPartiallyReceived())
                                    <span class="badge badge-warning rounded-pill">Partially received</span>
                                @elseif($row->isReceived())
                                    <span class="badge badge-success rounded-pill">Received</span>
                                @else
                                    <span class="badge badge-danger rounded-pill">N/A</span>
                                @endif
                            </td>
                            <td align="center">
                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    Action <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    @if($row->isPending())
                                        <a class="dropdown-item" href="{{ route('receivings.index', ['bo_id' => $row->id]) }}">
                                            <span class="fa fa-boxes text-dark"></span> Receive
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('back-orders.show', $row->id) }}">
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
        // Apply table styling metrics and initialize DataTables
        $('.table td, .table th').addClass('py-1 px-2 align-middle');
        $('#bo-table').DataTable();

        // Enforce Laravel CSRF compliance on all outbound AJAX request transactions
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Click wrapper target hook for executing deletions
        $(document).on('click', '.delete_data', function(){
            let id = $(this).attr('data-id');
            _conf("Are you sure to delete this Back Order permanently?", "delete_bo", [id]);
        });
    });

    function delete_bo(id){
        start_loader();
        
        let deleteUrl = "{{ route('back-orders.destroy', ':id') }}".replace(':id', id);

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