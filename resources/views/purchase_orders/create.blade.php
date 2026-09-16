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

<div class="container-fluid p-4">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h4 class="card-title">{{ isset($po) ? "Purchase Order Details - " . $po->po_code : 'Create New Purchase Order' }}</h4>
        </div>
        <div class="card-body">
            <form id="po-form">
                @csrf
                @if(isset($po)) @method('PUT') @endif

                <input type="hidden" name="id" value="{{ $po->id ?? '' }}">

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="control-label text-info">P.O. Code</label>
                            <input type="text" class="form-control form-control-sm rounded-0" value="{{ $po->po_code ?? 'Auto Generated' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplier_id" class="control-label text-info">Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="custom-select select2" {{ isset($po) ? 'readonly' : '' }}>
                                    <option value="" disabled {{ !isset($po) ? 'selected' : '' }}></option>
                                    @foreach($suppliers as $row)
                                    <option value="{{ $row->id }}" {{ (isset($po) && $po->supplier_id == $row->id) ? 'selected' : '' }}>{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <fieldset>
                        <legend class="text-info">Item Form</legend>
                        <div class="row justify-content-start align-items-end">
                            <div class="col-md-3">
                                <div class="form-group"><label for="item_id">Item</label><select id="item_id" class="custom-select select2">
                                        <option disabled selected></option>
                                    </select></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group"><label for="qty">Qty</label><input type="number" step="any" class="form-control rounded-0" id="qty"></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group"><label for="expiry_date">Expiry Date</label><input type="date" class="form-control rounded-0" id="expiry_date"></div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group"><label for="batch_no">Batch No</label><input class="form-control rounded-0" id="batch_no"></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="form-group"><button type="button" class="btn btn-flat btn-sm btn-primary" id="add_to_list">Add to List</button></div>
                            </div>
                        </div>
                    </fieldset>
                    <hr>

                    <table class="table w-100 table-striped table-bordered" id="list">
                        <colgroup>
                            <col width="5%">
                            <col width="10%">
                            <col width="10%">
                            <col width="25%">
                            <col width="25%">
                            <col width="25%">
                        </colgroup>
                        <thead>
                            <tr class="text-light bg-navy">
                                <th class="text-center py-1 px-2"></th>
                                <th class="text-center py-1 px-2">Qty</th>
                                <th class="text-center py-1 px-2">Item</th>
                                <th class="text-center py-1 px-2">Expiry Date</th>
                                <th class="text-center py-1 px-2">Batch No</th>
                                <th class="text-center py-1 px-2">Cost</th>
                                <th class="text-center py-1 px-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @if(isset($po))
                            @foreach($po->items as $item)
                            @php $grandTotal += $item->pivot->total; @endphp
                            <tr>
                                <td class="py-1 px-2 text-center"><button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button></td>
                                <td class="py-1 px-2 text-center qty">
                                    <span class="visible">{{ number_format($item->pivot->quantity) }}</span>
                                    <input type="hidden" name="item_id[]" value="{{ $item->id }}"><input type="hidden" name="qty[]" value="{{ $item->pivot->quantity }}"><input type="hidden" name="expiry_date[]" value="{{ $item->pivot->expiry_date }}"><input type="hidden" name="batch_no[]" value="{{ $item->pivot->batch_no }}"><input type="hidden" name="price[]" value="{{ $item->pivot->price }}"><input type="hidden" name="total[]" value="{{ $item->pivot->total }}">
                                </td>
                                <td class="py-1 px-2 item-name">{{ $item->name }}</td>
                                <td class="py-1 px-2 text-center expiry">{{ $item->pivot->expiry_date ?? 'N/A' }}</td>
                                <td class="py-1 px-2 text-center batch">{{ $item->pivot->batch_no ?? 'N/A' }}</td>
                                <td class="py-1 px-2 text-right price">{{ number_format($item->pivot->price, 2) }}</td>
                                <td class="py-1 px-2 text-right total-amount">{{ number_format($item->pivot->total, 2) }}</td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-right py-1 px-2" colspan="6">Sub Total</th>
                                <th class="text-right py-1 px-2 sub-total">0</th>
                            </tr>
                            <tr>
                                <th class="text-right py-1 px-2" colspan="6">
                                    Discount
                                    <input style="width:40px !important" name="discount_perc" type="number" step="any" min="0" max="100" value="{{ $po->discount_perc ?? 0 }}">%
                                    <input type="hidden" name="discount" value="{{ $po->discount ?? 0 }}">
                                </th>
                                <th class="text-right py-1 px-2 discount">{{ isset($po) ? number_format($po->discount) : 0 }}</th>
                            </tr>
                            <tr>
                                <th class="text-right py-1 px-2" colspan="6">
                                    Tax
                                    <input style="width:40px !important" name="tax_perc" type="number" step="any" min="0" max="100" value="{{ $po->tax_perc ?? 0 }}">%
                                    <input type="hidden" name="tax" value="{{ $po->tax ?? 0 }}">
                                </th>
                                <th class="text-right py-1 px-2 tax">{{ isset($po) ? number_format($po->tax) : 0 }}</th>
                            </tr>
                            <tr>
                                <th class="text-right py-1 px-2" colspan="6">
                                    Total
                                    <input type="hidden" name="amount" value="{{ $po->amount ?? 0 }}">
                                </th>
                                <th class="text-right py-1 px-2 grand-total">0</th>
                            </tr>
                        </tfoot>

                    </table>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="form-group"><label for="remarks">Remarks</label><textarea name="remarks" id="remarks" rows="3" class="form-control rounded-0">{{ $po->remarks ?? '' }}</textarea></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-flat btn-primary" type="submit">Save Purchase Order</button>
                    <a class="btn btn-flat btn-default" href="{{ route('purchase-orders.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const allItems = @json($items);

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        var itemSelect = $('#item_id');
        itemSelect.html('<option disabled selected></option>');
        if (allItems && allItems.length > 0) {
            allItems.forEach(function(item) {
                itemSelect.append('<option value="' + item.id + '" data-cost="' + item.cost + '" data-supplier="' + item.supplier_id + '">' + item.name + '</option>');
            });
        }
        itemSelect.trigger('change');

        $('#item_id').change(function() {
            var selectedOption = $(this).find('option:selected');
            var supplierId = selectedOption.attr('data-supplier');
            if (supplierId) {
                $('#supplier_id').val(supplierId).trigger('change.select2');
            }
        });

        $('#add_to_list').click(function() {
            var itemSelect = $('#item_id');
            var itemId = itemSelect.val();
            var itemName = itemSelect.find('option:selected').text();
            var cost = parseFloat(itemSelect.find('option:selected').attr('data-cost')) || 0;
            var qty = parseFloat($('#qty').val()) || 0;
            var expiry = $('#expiry_date').val() || '';
            var batch = $('#batch_no').val() || '';

            if (!itemId || qty <= 0) {
                // CHANGED: Fixed browser alert to format like your exact sample
                if (typeof alert_toast === "function") {
                    alert_toast('Please select an item and provide a valid quantity.', 'warning');
                }
                return;
            }

            $('#supplier_id').attr('readonly', true);
            var total = qty * cost;
            var row = `<tr>
                <td class="py-1 px-2 text-center"><button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button></td>
                <td class="py-1 px-2 text-center qty">
                    <span>${qty}</span>
                    <input type="hidden" name="item_id[]" value="${itemId}"><input type="hidden" name="qty[]" value="${qty}"><input type="hidden" name="expiry_date[]" value="${expiry}"><input type="hidden" name="batch_no[]" value="${batch}"><input type="hidden" name="price[]" value="${cost}"><input type="hidden" name="total[]" value="${total}">
                </td>
                <td class="py-1 px-2 item-name">${itemName}</td>
                <td class="py-1 px-2 text-center expiry">${expiry || 'N/A'}</td>
                <td class="py-1 px-2 text-center batch">${batch || 'N/A'}</td>
                <td class="py-1 px-2 text-right price">${cost.toFixed(2)}</td>
                <td class="py-1 px-2 text-right total-amount">${total.toFixed(2)}</td>
            </tr>`;

            $('#list tbody').append(row);
            calculateGrandTotal();

            $('#qty').val('');
            $('#expiry_date').val('');
            $('#batch_no').val('');
            itemSelect.val('').trigger('change');
        });

        $(document).on('click', '.rem_row', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal();
            if ($('#list tbody tr').length === 0) {
                $('#supplier_id').removeAttr('readonly');
            }
        });

        $('input[name="discount_perc"], input[name="tax_perc"]').on('input change', function() {
            calculateGrandTotal();
        });

        calculateGrandTotal();

        $('#po-form').submit(function(e) {
            e.preventDefault();
            if ($('#list tbody tr').length <= 0) {
                // CHANGED: Replaced native browser alert with alert_toast
                if (typeof alert_toast === "function") {
                    alert_toast('Please add at least one item.', 'warning');
                }
                return;
            }
            var id = $('input[name="id"]').val();
            var url = id ? "{{ url('/purchase-orders') }}/" + id : "{{ route('purchase-orders.store') }}";
            if (typeof start_loader === "function") start_loader();

            $.ajax({
                url: url,
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                error: err => {
                    console.log(err);

                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        var errors = err.responseJSON.errors;
                        var errorMessages = [];

                        Object.keys(errors).forEach(function(key) {
                            errorMessages.push(errors[key].join("<br>"));
                        });

                        if (typeof alert_toast === "function") {
                            alert_toast(errorMessages.join("<br>"), 'error');
                        }
                    } else {
                        if (typeof alert_toast === "function") {
                            alert_toast("An error occurred while saving.", 'error');
                        }
                    }

                    if (typeof end_loader === "function") end_loader();
                },
                success: function(resp) {
                    if (resp && resp.status === 'success') {
                        if (typeof alert_toast === "function") {
                            alert_toast("New Purchase Order was Successfully created.", 'success');
                        }

                        setTimeout(function() {
                            location.href = "{{ route('purchase-orders.index') }}";
                        }, 1000);

                    } else {

                        if (typeof alert_toast === "function") {



                            // Locate this block inside your $('#po-form').submit() AJAX error setup:
                            if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                                var errors = err.responseJSON.errors;
                                var errorMessages = [];

                                Object.keys(errors).forEach(function(key) {
                                    errorMessages.push(errors[key].join("<br>"));
                                });

                                // FIXED: Prepended "Validation Errors:<br>" so Dusk finds the exact text layout
                                alert_toast("Validation Errors:<br>" + errorMessages.join("<br>"), 'error');
                            }


                            alert_toast(resp.msg || 'An error occurred.', 'error');
                        }
                        if (typeof end_loader === "function") end_loader();
                    }
                }

            });
        });

    });

    function calculateGrandTotal() {
        var subTotal = 0;

        $('.total-amount').each(function() {
            subTotal += parseFloat($(this).text().replace(/,/g, '')) || 0;
        });

        var discountPerc = parseFloat($('input[name="discount_perc"]').val()) || 0;
        var discountAmt = subTotal * (discountPerc / 100);
        var afterDiscount = subTotal - discountAmt;

        var taxPerc = parseFloat($('input[name="tax_perc"]').val()) || 0;
        var taxAmt = afterDiscount * (taxPerc / 100);

        var grandTotal = afterDiscount + taxAmt;

        $('.sub-total').text(subTotal.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        $('.discount').text(discountAmt.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('input[name="discount"]').val(discountAmt.toFixed(2));

        $('.tax').text(taxAmt.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('input[name="tax"]').val(taxAmt.toFixed(2));

        $('.grand-total').text(grandTotal.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('input[name="grand_total"]').val(grandTotal.toFixed(2));
    }
</script>
@endpush