@extends('layouts.app')
@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Supplier</h3>
        <div class="card-tools">
            <a href="javascript:void(0)" id="create_new" class="btn btn-flat btn-primary">
                <span class="fas fa-plus"></span> Create New
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped" id="supplier-table">
                <colgroup>
                    <col width="5%">
                    <col width="15%">
                    <col width="25%">
                    <col width="25%">
                    <col width="15%">
                    <col width="15%">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Created</th>
                        <th>Supplier</th>
                        <th>Contact Person</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr>
                            <!-- $loop->iteration naturally manages sequential numbering -->
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $supplier->date_created->format('Y-m-d H:i') }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->cperson }}</td>
                            <td class="text-center">
                                @if($supplier->status == 1)
                                    <span class="badge badge-success rounded-pill">Active</span>
                                @else
                                    <span class="badge badge-danger rounded-pill">Inactive</span>
                                @endif
                            </td>
                            <td align="center">
                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    Action <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a class="dropdown-item view_data" href="javascript:void(0)" data-id="{{ $supplier->id }}">
                                        <span class="fa fa-eye text-dark"></span> View
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item edit_data" href="javascript:void(0)" data-id="{{ $supplier->id }}">
                                        <span class="fa fa-edit text-primary"></span> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="{{ $supplier->id }}">
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
    // Setup global CSRF header for safe Laravel AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.delete_data').click(function(){
        _conf("Are you sure to delete this Supplier permanently?", "delete_category", [$(this).attr('data-id')])
    });

    $('#create_new').click(function(){
        // Point paths to your target Laravel routes instead of static .php files
        uni_modal("<i class='fa fa-plus'></i> Add New Supplier", "{{ route('suppliers.create') }}", "mid-large")
    });

    $('.edit_data').click(function(){
        var id = $(this).attr('data-id');
        uni_modal("<i class='fa fa-edit'></i> Edit Supplier Details", "/suppliers/" + id + "/edit", "mid-large")
    });

    $('.view_data').click(function(){
        var id = $(this).attr('data-id');
        uni_modal("<i class='fa fa-truck-loading'></i> Supplier Details", "/suppliers/" + id, "")
    });

    $('.table td, .table th').addClass('py-1 px-2 align-middle');
    $('#supplier-table').dataTable();
});

function delete_category($id){
    start_loader();
    $.ajax({
        url: "/suppliers/" + $id,
        method: "DELETE", // standard RESTful method for deletions
        dataType: "json",
        error: err => {
            console.log(err)
            alert_toast("An error occurred.", 'error');
            end_loader();
        },
        success: function(resp){
            if(typeof resp == 'object' && resp.status == 'success'){
                location.reload();
            } else {
                alert_toast("An error occurred.", 'error');
                end_loader();
            }
        }
    });
}
</script>
@endpush