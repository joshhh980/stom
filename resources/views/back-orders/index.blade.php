@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">

    <!-- 👈 ADD THIS SESSION LISTENER LAYER HERE -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="ajax" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

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
                            <td>{{ \Carbon\Carbon::parse($row->date_created)->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->bo_code }}</td>
                            <td>{{ $row->supplier->name ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($row->items_count) }}</td>
                            <td class="text-center">
                                @if($row->status == 0)
                                <span class="badge badge-primary rounded-pill">Pending</span>
                                @elseif($row->status == 1)
                                <span class="badge badge-warning rounded-pill">Partially received</span>
                                @elseif($row->status == 2)
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
                                    @if($row->status == 0)
                                    <a class="dropdown-item" href="{{ route('admin.receiving.manage', ['bo_id' => $row->id]) }}">
                                        <span class="fa fa-boxes text-dark"></span> Receive
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('admin.back_order.show', $row->id) }}">
                                        <span class="fa fa-eye text-dark"></span> View
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
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Apply padding styles & initialize DataTable cleanly
        $('.table td, .table th').addClass('py-1 px-2 align-middle');
        $('#bo-table').DataTable();

        // Optional AJAX hookups for delete/view can go here
    });
</script>
@endpush