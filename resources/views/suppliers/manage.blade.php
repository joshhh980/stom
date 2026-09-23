<div class="container-fluid">
    <form action="{{ route('suppliers.store') }}" id="supplier-form">
        @csrf
        <!-- Hidden ID for updateOrCreate tracking -->
        <input type="hidden" name="id" value="{{ $supplier->id ?? '' }}">

        <div class="form-group mb-3">
            <label for="name" class="control-label">Name</label>
            <input name="name" id="name" class="form-control rounded-0" value="{{ old('name', $supplier->name) }}" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="address" class="control-label">Address</label>
            <textarea name="address" id="address" cols="30" rows="2" class="form-control no-resize">{{ old('address', $supplier->address) }}</textarea>
        </div>
        
        <div class="form-group mb-3">
            <label for="cperson" class="control-label">Contact Person</label>
            <input name="cperson" id="cperson" class="form-control rounded-0" value="{{ old('cperson', $supplier->cperson) }}" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="contact" class="control-label">Contact #</label>
            <input name="contact" id="contact" class="form-control rounded-0" value="{{ old('contact', $supplier->contact) }}">
        </div>
        
        <div class="form-group mb-3">
            <label for="status" class="control-label">Status</label>
            <select name="status" id="status" class="custom-select rounded-0">
                <!-- Clean direct comparison to Enum classes -->
                <option value="{{ \App\Enums\Status::ACTIVE->value }}" 
                    {{ old('status', $supplier->status) === \App\Enums\Status::ACTIVE ? 'selected' : '' }}>
                    Active
                </option>
                <option value="{{ \App\Enums\Status::INACTIVE->value }}" 
                    {{ old('status', $supplier->status) === \App\Enums\Status::INACTIVE ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#supplier-form').submit(function(e) {
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();
            start_loader();

            $.ajax({
                url: _this.attr('action'),
                method: 'POST',
                // FormData is clean and supports file/image fields seamlessly if added later
                // Change this line inside resources/views/suppliers/manage.blade.php
                data: new FormData($(this)[0]), // <-- Add the [0] index array accessor back here
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                error: function(xhr) {
                    console.log(xhr);
                    end_loader();

                    // Check if Laravel returned standard Form Request validation error messages
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;

                        // Loop through fields and prepend validation error alerts above your form layout
                        $.each(errors, function(key, messages) {
                            $.each(messages, function(index, msg) {
                                var el = $('<div>').addClass("alert alert-danger err-msg").text(msg);
                                _this.prepend(el);
                            });
                        });
                        $('.err-msg').show('slow');
                    } else {
                        alert_toast("An error occurred.", 'error');
                    }
                },

                success: function(resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        location.reload();
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        var el = $('<div>').addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        end_loader();
                    } else {
                        alert_toast("An error occurred", 'error');
                        end_loader();
                    }
                }
            });
        });
    });
</script>