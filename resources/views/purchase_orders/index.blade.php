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
        <!-- Your List of Purchase Orders Table markup remains here unchanged ... -->

        <div class="card-header">
            <h3 class="card-title">List of Purchase Orders</h3>
            <div class="card-tools d-flex justify-content-between">
                <div>
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-flat btn-primary">
                        <span class="fas fa-plus"></span> Create New
                    </a>
                </div>

                @if(auth()->user() && auth()->user()->type == \App\Enums\UserType::ADMIN)
                <!-- Excel Import and Receive -->
                <div class="position-relative d-inline-block ml-2">
                    <form action="{{ route('purchase-orders.import', ['receive' => 'true']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="excel_file" accept=".xlsx, .xls, .csv" required>
                        <button class="btn btn-flat btn-primary position-relative" type="submit">Upload and Receive</button>
                    </form>
                </div>

                <!-- Standard Excel Import -->
                <div class="position-relative d-inline-block ml-2">
                    <form action="{{ route('purchase-orders.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="excel_file" accept=".xlsx, .xls, .csv" required>
                        <button class="btn btn-flat btn-primary position-relative" type="submit">Upload</button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div class="card-body">
            <div class="container-fluid">
                <table class="table table-bordered table-striped" id="po-table">
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
                            <th>PO Code</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrders as $index => $po)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($po->date_created)->format('Y-m-d H:i') }}</td>
                            <td>{{ $po->po_code }}</td>
                            <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($po->items_count) }}</td>
                            <td class="text-center">{!! $po->status->badgeHtml() !!}</td>

                            <td align="center">
                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    Action <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <!-- Check if Pending for Receiving -->
                                    @if($po->status === \App\Enums\PurchaseOrderStatus::PENDING)
                                    <a class="dropdown-item" href="{{ route('receivings.manage', ['po_id' => $po->id]) }}"><span class="fa fa-boxes text-dark"></span> Receive</a>
                                    <div class="dropdown-divider"></div>
                                    @endif

                                    <a class="dropdown-item" href="{{ route('purchase-orders.show', ['purchase_order' => $po->id]) }}"><span class="fa fa-eye text-dark"></span> View</a>
                                    <div class="dropdown-divider"></div>

                                    <!-- Check if Pending for Editing -->
                                    @if($po->status === \App\Enums\PurchaseOrderStatus::PENDING)
                                    <a class="dropdown-item" href="{{ route('purchase-orders.edit', ['purchase_order' => $po->id]) }}"> <span class="fa fa-edit text-primary"></span> Edit</a>
                                    <div class="dropdown-divider"></div>
                                    @endif

                                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="{{ $po->id }}"><span class="fa fa-trash text-danger"></span> Delete</a>
                                    <div class="dropdown-menu" role="menu">
                                        @if($po->isPending())
                                        <a class="dropdown-item" href="#"><span class="fa fa-boxes text-dark"></span> Receive</a>
                                        <div class="dropdown-divider"></div>
                                        @endif

                                        <a class="dropdown-item" href="{{ route('purchase-orders.show', ['purchase_order' => $po->id]) }}"><span class="fa fa-eye text-dark"></span> View</a>
                                        <div class="dropdown-divider"></div>

                                        @if($po->isPending())
                                        <a class="dropdown-item" href="{{ route('purchase-orders.edit', ['purchase_order' => $po->id]) }}"> <span class="fa fa-edit text-primary"></span> Edit</a>
                                        <div class="dropdown-divider"></div>
                                        @endif

                                        <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="{{ $po->id }}"><span class="fa fa-trash text-danger"></span> Delete</a>
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
        $('.table td, .table th').addClass('py-1 px-2 align-middle');
        $('#po-table').dataTable();

        $('.delete_data').click(function() {
            var id = $(this).attr('data-id');
            // Using Laravel SweetAlert or classic confirmation wrapper
            if (confirm("Are you sure to delete this Purchase Order permanently?")) {
                delete_po(id);
            }
        });



        // Intercept both standard import and upload/receive forms natively
        $('.card-tools form').submit(function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(this); // Required to stream file binaries correctly

            if (typeof start_loader === "function") start_loader();

            $.ajax({
                url: form.attr('action'),
                method: "POST",
                data: formData,
                contentType: false, // Prevents jQuery from setting incorrect content headers
                processData: false, // Prevents jQuery from converting files to raw strings
                dataType: "json",
                error: err => {
                    console.log(err);
                    let msg = "An unexpected error occurred while uploading.";
                    if (err.responseJSON && err.responseJSON.message) {
                        msg = err.responseJSON.message;
                    }

                    if (typeof alert_toast === "function") alert_toast(msg, 'error');
                    if (typeof end_loader === "function") end_loader();
                },
                success: function(resp) {
                    if (resp && resp.status === 'success') {
                        if (typeof alert_toast === "function") {
                            alert_toast(resp.message, 'success');
                        }
                        if (typeof end_loader === "function") end_loader();

                        // Refresh after the toast has been visible so the imported order appears in the list.
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        if (typeof alert_toast === "function") {
                            alert_toast(resp.message || 'An error occurred.', 'error');
                        }
                        if (typeof end_loader === "function") end_loader();
                    }
                }
            });
        });


    });

    function delete_po($id) {
        // Ensure your master layout script loader functions exist (start_loader/end_loader)
        if (typeof start_loader === "function") start_loader();

        $.ajax({
            url: "{{ url('/purchase-orders') }}/" + $id,
            method: "POST",
            data: {
                _method: 'DELETE', // Spoofs DELETE request block over standard HTTP rules
                _token: '{{ csrf_token() }}' // Secures operation against injection attacks
            },
            dataType: "json",
            error: err => {
                console.log(err);
                if (typeof alert_toast === "function") alert_toast("An error occurred.", 'error');
                if (typeof end_loader === "function") end_loader();
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    if (typeof alert_toast === "function") alert_toast("An error occurred.", 'error');
                    if (typeof end_loader === "function") end_loader();
                }
            }
        });
    }
</script>
@endpush