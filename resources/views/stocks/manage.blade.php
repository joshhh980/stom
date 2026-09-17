@extends('layouts.app')

@section('content')
<style>
    select[readonly].select2-hidden-accessible+.select2-container {
        pointer-events: none;
        touch-action: none;
        background: #eee;
        box-shadow: none;
    }

    select[readonly].select2-hidden-accessible+.select2-container .select2-selection {
        background: #eee;
        box-shadow: none;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">{{ isset($stock) ? 'Update Stock Record' : 'Create New Stock Record' }}</h4>
    </div>
    <div class="card-body">
        <form action="" id="stock-form">
            @csrf
            <input type="hidden" name="id" value="{{ $stock->id ?? '' }}">

            <div class="container-fluid">
                <fieldset>
                    <legend class="text-info">Item Form</legend>
                    <div class="row justify-content-start align-items-end">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="item_id" class="control-label">Item</label>
                                <select name="item_id" id="item_id" class="custom-select select2" {{ isset($stock) ? 'readonly' : '' }}>
                                    <option disabled {{ is_null($stock) ? 'selected' : '' }}></option>
                                    @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ (optional($stock)->item_id == $item->id) ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="qty" class="control-label">Qty</label>
                                <input name="quantity" id="qty" type="number" step="any" class="form-control rounded-0" value="{{ $stock->quantity ?? '' }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="expiry_date" class="control-label">Expiry Date</label>
                                <input type="date" class="form-control rounded-0" id="expiry_date" name="expiry_date" value="{{ $stock->expiry_date ?? '' }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="batch_no" class="control-label">Batch No</label>
                                <input class="form-control rounded-0" id="batch_no" name="batch_no" value="{{ $stock->batch_no ?? '' }}">
                            </div>
                        </div>

                        <div class="col-md-2 text-center mt-3">
                            <div class="form-group">
                                <button type="submit" class="btn btn-flat btn-primary">
                                    {{ isset($stock) ? 'Update Stock' : 'Add to Stock' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>

<script>
    $(function() {
        $('.select2').select2({
            placeholder: "Please select here",
            width: 'resolve',
        });

        $('#stock-form').submit(function(e) {
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();
            start_loader();

            $.ajax({
                url: "{{ route('stocks.store') }}",
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                success: function(resp) {
                    if (resp.status == 'success') {
                        alert_toast("Stock processed successfully.", 'success');
                        setTimeout(function() {
                            location.replace("{{ route('stocks.index') }}");
                        }, 1000);
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        var el = $('<div>').addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        end_loader();
                    } else {
                        alert_toast("An error occurred", 'error');
                        end_loader();
                    }
                    $('html,body').animate({
                        scrollTop: 0
                    }, 'fast');
                },
                error: err => {
                    console.log(err);
                    
                    // 💡 FIXED: Remove the raw alert() popup box to prevent Dusk from crashing
                    if (err.responseJSON && err.responseJSON.errors) {
                        var errMsg = Object.values(err.responseJSON.errors).flat().join('<br>');
                        var el = $('<div>').addClass("alert alert-danger err-msg").html(errMsg);
                        _this.prepend(el);
                    } else {
                        alert_toast("An error occurred", 'error');
                    }
                    
                    end_loader();
                    $('html,body').animate({scrollTop:0}, 'fast');
                },


            });
        });
    });
</script>
@endsection