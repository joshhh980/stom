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
        <h4 class="card-title">
            {{ is_null($receiving) ? "Receive Order from " . ($boCode ?? $poCode) : 'Update Received Order' }}
        </h4>
    </div>
    <div class="card-body">
        <form action="" id="receive-form">
            @csrf
            {{-- Form context tracking tokens mapping --}}
            <input type="hidden" name="id" value="{{ $receiving->id ?? '' }}">
            <input type="hidden" name="from_order" value="{{ $boId ? \App\Enums\ReceivingOrigin::BACK_ORDER->value : \App\Enums\ReceivingOrigin::PURCHASE_ORDER->value }}"> <input type="hidden" name="form_id" value="{{ $boId ?? $poId }}">
            <input type="hidden" name="po_id" value="{{ $poId ?? '' }}">

            @if(auth()->user() && auth()->user()->type == 1)
            <input name="date_created" type="hidden" value="{{ $receiving->date_created ?? '' }}">
            @endif

            <div class="container-fluid">
                <div class="row">
                    @if(!$boCode)
                    <div class="col-md-6">
                        <label class="control-label text-info">P.O. Code</label>
                        <input type="text" class="form-control form-control-sm rounded-0" value="{{ $poCode }}" readonly>
                    </div>
                    @else
                    <div class="col-md-6">
                        <label class="control-label text-info">B.O. Code</label>
                        <input type="text" class="form-control form-control-sm rounded-0" value="{{ $boCode }}" readonly>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_id" class="control-label text-info">Supplier</label>
                            <select id="supplier_id" name="supplier_id" class="custom-select select2">
                                <option disabled {{ is_null($supplierId) ? 'selected' : '' }}></option>
                                @foreach($suppliers as $row)
                                <option value="{{ $row->id }}" {{ $supplierId == $row->id ? 'selected' : '' }}>
                                    {{ $row->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <hr>

                <table class="table w-100 table-striped table-bordered" id="list">
                    <colgroup>
                        <col width="5%">
                        <col width="25%">
                        <col width="15%">
                        <col width="15%">
                        <col width="20%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr class="text-light bg-navy">
                            <th class="text-center py-1 px-2">Qty</th>
                            <th class="text-center py-1 px-2">Item</th>
                            <th class="text-center py-1 px-2">Expiry Date</th>
                            <th class="text-center py-1 px-2">Batch No</th>
                            <th class="text-center py-1 px-2">Cost</th>
                            <th class="text-center py-1 px-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $row)
                        @php
                        // Updated: Extract order values directly from Eloquent's pivot data layer
                        $orderQty = $row->pivot->quantity ?? 0;
                        $itemPrice = $row->pivot->price ?? 0;
                        $expiryDate = $row->pivot->expiry_date ?? '';
                        $batchNo = $row->pivot->batch_no ?? '';

                        $currentQty = $orderQty;

                        // Updated: Replaced raw inline DB::table lookup with clean collection manipulation if editing
                        if (isset($receiving) && !empty($receiving->stock_ids)) {
                        $stockIdArray = explode(',', $receiving->stock_ids);
                        $stockLog = \App\Models\Stock::whereIn('id', $stockIdArray)
                        ->where('item_id', $row->id)
                        ->first();

                        if ($stockLog) {
                        $currentQty = $stockLog->quantity;
                        }
                        }
                        $lineTotal = $currentQty * $itemPrice;
                        @endphp
                        <!--  REFACTORED TABLE BODY ROW STRUCTURE -->
                        <tr>
                            <td class="py-1 px-2 text-center qty">
                                <!-- Keep ONLY the visible input field here so JS can modify it freely -->
                                <input type="number" name="qty[]" class="form-control form-control-sm text-center qty-input" value="{{ $currentQty }}" max="{{ $orderQty }}" min="0">
                            </td>
                            <td class="py-1 px-2 item">
                                <!-- 💡 SAFELY MOVED: Hidden tracking arrays are nested here instead -->
                                <input type="hidden" name="item_id[]" value="{{ $row->id }}">
                                <input type="hidden" name="oqty[]" value="{{ $orderQty }}">
                                <input type="hidden" name="expiry_date[]" value="{{ $expiryDate }}">
                                <input type="hidden" name="batch_no[]" value="{{ $batchNo }}">
                                <input type="hidden" name="price[]" value="{{ $itemPrice }}">
                                <input type="hidden" name="total[]" value="{{ $lineTotal }}">

                                <strong>{{ $row->name }}</strong> <br>
                                {{ $row->description }}
                            </td>
                            <td class="py-1 px-2 expiry_date">
                                {{ $expiryDate }}
                            </td>
                            <td class="py-1 px-2 batch_no">
                                {{ $batchNo }}
                            </td>
                            <td class="py-1 px-2 text-right cost">
                                {{ number_format($itemPrice) }}
                            </td>
                            <td class="py-1 px-2 text-right total">
                                {{ number_format($lineTotal) }}
                            </td>
                        </tr>

                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="5">Sub Total</th>
                            <th class="text-right py-1 px-2 sub-total">0</th>
                        </tr>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="5">
                                Discount <input style="width:40px !important" name="discount_perc" type="number" min="0" max="100" value="{{ $receiving->discount_perc ?? 0 }}">%
                                <input type="hidden" name="discount" value="{{ $receiving->discount ?? 0 }}">
                            </th>
                            <th class="text-right py-1 px-2 discount">{{ number_format($receiving->discount ?? 0) }}</th>
                        </tr>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="5">
                                Tax <input style="width:40px !important" name="tax_perc" type="number" min="0" max="100" value="{{ $receiving->tax_perc ?? 0 }}">%
                                <input type="hidden" name="tax" value="{{ $receiving->tax ?? 0 }}">
                            </th>
                            <th class="text-right py-1 px-2 tax">{{ number_format($receiving->tax ?? 0) }}</th>
                        </tr>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="5">
                                Total
                                <input type="hidden" name="amount" value="{{ $receiving->amount ?? 0 }}">
                            </th>
                            <th class="text-right py-1 px-2 grand-total">0</th>
                        </tr>
                    </tfoot>
                </table>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remarks" class="text-info control-label">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="3" class="form-control rounded-0">{{ $receiving->remarks ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-primary" type="submit" form="receive-form">Save</button>
        <a class="btn btn-flat btn-dark" href="{{ route('purchase-orders.index') }}">Cancel</a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        $('.select2').select2({
            placeholder: "Please select here",
            width: 'resolve',
        });

        $('#receive-form').submit(function(e) {
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();
            start_loader();

            var formData = new FormData($(this)[0]);

            $.ajax({
                url: "{{ route('receivings.store') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err);
                    var msg = (err.responseJSON && err.responseJSON.message) ?
                        err.responseJSON.message :
                        "An error occurred";
                    var el = $('<div>').addClass("alert alert-danger err-msg").text(msg);
                    _this.prepend(el);
                    el.show('slow');
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function(resp) {
                    if (resp.status == 'success') {
                        // 💡 FIXED: End the loading spinner instantly on success
                        if (typeof end_loader === 'function') end_loader();
                        
                        alert_toast("Operation completed successfully.", 'success');
                        
                        setTimeout(function() {
                            location.replace("{{ route('receivings.index') }}");
                        }, 1000);
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        var el = $('<div>').addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        if (typeof end_loader === 'function') end_loader();
                    } else {
                        alert_toast("An error occurred", 'error');
                        if (typeof end_loader === 'function') end_loader();
                        console.log(resp);
                    }
                    $('html,body').animate({
                        scrollTop: 0
                    }, 'fast');
                },


                error: err => {
                    console.log(err);
                    var el = $('<div>').addClass("alert alert-danger err-msg").text("An error occurred while processing your request.");
                    _this.prepend(el);
                    el.show('slow');
                    end_loader();
                },
            });
        });

        if ('{{ isset($receiving) || $poId > 0 }}' == 1) {
            calc();
            $('#supplier_id').attr('readonly', 'readonly');

            $('[name="qty[]"], [name="discount_perc"], [name="tax_perc"]').on('input', function() {
                calc();
            });
        }
    });

    function calc() {
        var sub_total = 0;

        $('table#list tbody tr').each(function() {
            var qtyInput = $(this).find('[name="qty[]"]');
            var qty = parseFloat(qtyInput.val()) || 0;
            var maxQty = parseFloat(qtyInput.attr('max')) || 0;

            // Enforce the ceiling cap boundary constraint
            if (qty > maxQty) {
                qty = maxQty;
                qtyInput.val(maxQty);
            }

            var price = parseFloat($(this).find('[name="price[]"]').val()) || 0;
            var total = price * qty;

            $(this).find('[name="total[]"]').val(total);
            $(this).find('.total').text(fmt(total));

            sub_total += total;
        });

        $('table#list tfoot .sub-total').text(fmt(sub_total));

        var discount_perc = parseFloat($('[name="discount_perc"]').val()) || 0;
        var discount = sub_total * (discount_perc / 100);
        $('.discount').text(fmt(discount));
        $('[name="discount"]').val(discount.toFixed(2));

        var after_discount = sub_total - discount;

        var tax_perc = parseFloat($('[name="tax_perc"]').val()) || 0;
        var tax = after_discount * (tax_perc / 100);
        $('.tax').text(fmt(tax));
        $('[name="tax"]').val(tax.toFixed(2));

        var grand_total = after_discount + tax;
        $('table#list tfoot .grand-total').text(fmt(grand_total));
        $('[name="amount"]').val(grand_total.toFixed(2));
    }

    // Always renders two decimals, so Dusk's assertSee('100.00') matches.
    function fmt(n) {
        return parseFloat(n).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
</script>

@endpush