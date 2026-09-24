<div class="container-fluid">
    <form action="{{ route('items.store') }}" id="item-form">
        @csrf
        <input type="hidden" name="id" value="{{ $item->id ?? '' }}">

        <div class="form-group">
            <label for="name">Name</label>
            <input name="name" id="name" class="form-control rounded-0" value="{{ old('name', $item->name) }}" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="2" class="form-control">{{ old('description', $item->description) }}</textarea>
        </div>
        <div class="form-group">
            <label for="cost">Cost</label>
            <input type="number" name="cost" id="cost" step="any" min="0" class="form-control text-right" value="{{ old('cost', $item->cost) }}" required>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="custom-select">
                <option value="1" @selected(old('status', $item->status?->value ?? 1) == 1)>Active</option>
                <option value="0" @selected(old('status', $item->status?->value ?? 1) == 0)>Inactive</option>
            </select>
        </div>
        @if(!$item->exists)
        <div class="form-group">
            <label for="quantity">Initial Quantity</label>
            <input type="number" name="quantity" id="quantity" min="0" class="form-control" value="{{ old('quantity') }}">
        </div>
        <div class="form-group">
            <label for="expiry_date">Expiry Date</label>
            <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
        </div>
        <div class="form-group">
            <label for="batch_no">Batch No</label>
            <input name="batch_no" id="batch_no" class="form-control" value="{{ old('batch_no') }}">
        </div>
        @endif
    </form>
</div>

<script>
$(function () {
    $('#item-form').on('submit', function (event) {
        event.preventDefault();
        const form = this;
        $('.err-msg').remove();
        start_loader();

        $.ajax({
            url: form.action,
            method: 'POST',
            data: new FormData(form),
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') location.reload();
            },
            error: function (xhr) {
                end_loader();
                const errors = xhr.responseJSON?.errors || {};
                Object.values(errors).flat().forEach(function (message) {
                    $('<div class="alert alert-danger err-msg"></div>').text(message).prependTo(form);
                });
                if (!Object.keys(errors).length) alert_toast('An error occurred.', 'error');
            }
        });
    });
});
</script>