<div class="container-fluid" id="print_out">
    <dl>
        <dt class="text-info">Item Name:</dt>
        <dd class="pl-3">{{ $item->name }}</dd>
        <dt class="text-info">Description:</dt>
        <dd class="pl-3">{{ $item->description }}</dd>
        <dt class="text-info">Cost:</dt>
        <dd class="pl-3">{{ number_format($item->cost, 2) }}</dd>
        <dt class="text-info">Supplier:</dt>
        <dd class="pl-3">{{ $item->supplier->name ?? 'N/A' }}</dd>
        <dt class="text-info">Status:</dt>
        <dd class="pl-3">
            <span class="badge badge-{{ $item->status->value ? 'success' : 'danger' }} rounded-pill">
                {{ $item->status->value ? 'Active' : 'Inactive' }}
            </span>
        </dd>
    </dl>
</div>