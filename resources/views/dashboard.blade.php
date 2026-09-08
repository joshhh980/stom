<h1>Welcome to {{ config('app.name') }}</h1>
<hr>
<div class="row">
    <!-- PO Records -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-th-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">PO Records</span>
                <span class="info-box-number text-right">
                    {{ $counts['po_records'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Receiving Records -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Receiving Records</span>
                <span class="info-box-number text-right">
                    {{ $counts['receiving_records'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Sales Records -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sales Records</span>
                <span class="info-box-number text-right">
                    {{ $counts['sales_records'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Suppliers -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-navy elevation-1"><i class="fas fa-truck-loading"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Suppliers</span>
                <span class="info-box-number text-right">
                    {{ $counts['suppliers'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-lightblue elevation-1"><i class="fas fa-th-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Items</span>
                <span class="info-box-number text-right">
                    {{ $counts['items'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Admin-Only Users Count -->
    @if(auth()->user() && auth()->user()->type == 1)
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-teal elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Users</span>
                <span class="info-box-number text-right">
                    {{ $counts['users'] }}
                </span>
            </div>
        </div>
    </div>
    @endif
</div>
