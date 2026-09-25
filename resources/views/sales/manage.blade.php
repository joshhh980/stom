@extends('layouts.app')

@section('content')
@include('sales.partials.styles')

<div class="container-fluid p-4">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            {{ isset($sale) ? "Sale Details - " . $sale->sales_code : 'Create New Sale Record' }}
        </h4>
        <span class="badge badge-primary" style="font-size:14px;" id="header-total">
            GHS {{ number_format($sale->amount ?? 0, 2) }}
        </span>
    </div>
    <div class="card-body">
        <form action="" id="sale-form">
            @csrf
            <input type="hidden" name="id" value="{{ $sale->id ?? '' }}">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <label class="control-label text-info">Sale Code</label>
                        <input type="text" class="form-control form-control-sm rounded-0"
                            value="{{ $sale->sales_code ?? 'Auto-generated on save' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="client" class="control-label text-info">Client Name</label>
                            <input type="text" name="client" class="form-control form-control-sm rounded-0"
                                value="{{ $sale->client ?? 'Guest' }}">
                        </div>
                    </div>

                    @if(auth()->user()?->isAdmin())
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date_created" class="control-label text-info">Date Created</label>
                            <input name="date_created" id="date_created" type="date" class="form-control form-control-sm rounded-0"
                                value="{{ isset($sale) ? \Carbon\Carbon::parse($sale->date_created)->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                    </div>
                    @endif
                </div>
                <hr>


                <fieldset>
                    <legend class="text-info">Select Items</legend>
                    <div class="form-group mb-2">
                        <input type="text" id="item_search" class="form-control rounded-0" placeholder="Search items by name...">
                    </div>
                    <div id="item_picker">
                        <div id="item_grid">
                            @foreach($items as $item)
                            <div class="item-card {{ $currentItems->contains('item_id', $item->id) ? 'in-cart' : '' }}" data-id="{{ $item->id }}" data-name="{{ strtolower($item->name) }}">
                                <span class="name">{{ $item->name }}</span>
                                <span class="price">GHS {{ number_format($item->cost, 2) }}</span><br>
                                <span class="stock-tag {{ $item->available_stock <= 5 ? 'low' : 'ok' }}">
                                    {{ number_format($item->available_stock) }} in stock
                                </span>
                            </div>
                            @endforeach
                        </div>
                        <div id="item_search_empty">No items match your search.</div>
                    </div>
                </fieldset>
                <hr>


                <table class="table table-striped table-bordered" id="list">
                    <colgroup>
                        <col width="8%">
                        <col width="34%">
                        <col width="18%">
                        <col width="20%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr class="text-light bg-navy">
                            <th class="text-center py-1 px-2"></th>
                            <th class="text-center py-1 px-2">Item</th>
                            <th class="text-center py-1 px-2">Qty</th>
                            <th class="text-center py-1 px-2">Price</th>
                            <th class="text-center py-1 px-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currentItems as $row)
                        <tr data-id="{{ $row->item_id }}">
                            <td class="py-1 px-2 text-center">
                                <button class="btn btn-outline-danger btn-sm rem_row" type="button" title="Remove"><i class="fa fa-times"></i></button>
                            </td>
                            <td class="py-1 px-2 item">
                                <span class="item-name">{{ $row->item->name }}</span><br>
                                <span class="item-desc">{{ $row->item->description }}</span>
                            </td>

                            <!-- Qty inputs belong in the qty cell -->
                            <td class="py-1 px-2 text-center editable-cell qty-cell" data-value="{{ $row->quantity }}">
                                <input type="hidden" name="item_id[]" value="{{ $row->item_id }}">
                                <input type="hidden" name="qty[]" value="{{ $row->quantity }}">
                                <span class="cell-display">{{ number_format($row->quantity) }}</span>
                            </td>

                            <!-- Price inputs belong in the price cell -->
                            <td class="py-1 px-2 text-right editable-cell price-cell" data-value="{{ $row->price }}">
                                <input type="hidden" name="price[]" value="{{ $row->price }}">
                                <span class="cell-display">GHS {{ number_format($row->price, 2) }}</span>
                            </td>

                            <!-- Total inputs belong in the total cell -->
                            <td class="py-1 px-2 text-right total">
                                <input type="hidden" name="total[]" value="{{ $row->total }}">
                                <span class="cell-display-total">GHS {{ number_format($row->total, 2) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-row">
                            <td colspan="5">No items added yet. Click an item above to get started.</td>
                        </tr>
                        @endforelse

                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="3">Total</th>
                            <th class="text-right py-1 px-2 grand-total" colspan="2">GHS {{ number_format($sale->amount ?? 0, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remarks" class="text-info control-label">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="3" class="form-control rounded-0">{{ $sale->remarks ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-primary" type="submit" form="sale-form" id="save-btn">
            <i class="fa fa-save"></i> Save
        </button>
        <a class="btn btn-flat btn-dark" href="{{ route('sales.index') }}">Cancel</a>
    </div>
</div>
</div>

{{-- Hidden HTML Clone Row Template Layer --}}
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button" title="Remove"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 item">
            <span class="item-name"></span><br>
            <span class="item-desc"></span>
        </td>
        <td class="py-1 px-2 text-center editable-cell qty-cell" data-value="0">
            <input type="hidden" name="item_id[]">
            <input type="hidden" name="qty[]">
            <input type="hidden" name="price[]">
            <input type="hidden" name="total[]">
            <span class="cell-display"></span>
        </td>
        <td class="py-1 px-2 text-right editable-cell price-cell" data-value="0">
            <span class="cell-display"></span>
        </td>
        <td class="py-1 px-2 text-right total"></td>
    </tr>
</table>
@endsection
@push('scripts')
<script>
    var items = @js($items);
    var MARGIN_PERCENT = 20;


    $(function() {

        $('#item_search').on('input', function() {
            var q = $(this).val().toLowerCase().trim();
            var visible = 0;
            $('.item-card').each(function() {
                var match = $(this).data('name').indexOf(q) !== -1;
                $(this).toggle(match);
                if (match) visible++;
            });
            $('#item_search_empty').toggle(visible === 0);
        });

        $(document).on('click', '.item-card', function() {
            var item = String($(this).data('id'));
            addOrIncrementItem(item);
            var card = $(this);
            card.addClass('pulse');
            setTimeout(function() {
                card.removeClass('pulse');
            }, 300);
        });

        $(document).on('click', '.editable-cell', function() {
            var cell = $(this);
            if (cell.find('input.cell-input').length) return;

            var current = parseFloat(cell.data('value')) || 0;
            var display = cell.find('.cell-display');
            display.hide();

            var input = $('<input type="number" step="any" min="0" class="form-control form-control-sm cell-input">').val(current);
            cell.append(input);
            input.trigger('focus');
            input.select();

            function commit() {
                var val = parseFloat(input.val());
                if (isNaN(val) || val < 0) val = current;
                input.remove();
                display.show();
                updateCellValue(cell, val);
            }

            input.on('blur', commit);
            input.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    commit();
                }
            });
            input.on('keydown', function(e) {
                if (e.key === 'Escape') {
                    input.val(current);
                    commit();
                }
            });
        });

        $('#sale-form').submit(function(e) {
            e.preventDefault();
            var _this = $(this)
            $('.err-msg').remove();
            if ($('table#list tbody tr[data-id]').length > 0) {
                $('#save-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                start_loader();
                $.ajax({
                    url: "{{ route('sales.store') }}",
                    data: new FormData($(this)[0]),
                    cache: false,
                    contentType: false,
                    processData: false,
                    method: 'POST',
                    type: 'POST',
                    dataType: 'json',
                    error: err => {
                        console.log(err)
                        alert_toast("An error occured", 'error');
                        end_loader();
                        $('#save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                    },
                    success: function(resp) {
                        if (resp.status == 'success') {
                            location.replace(`{{ route('sales.index') }}`);
                        } else if (resp.status == 'failed' && !!resp.msg) {
                            var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            end_loader();
                            $('#save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                        } else {
                            alert_toast("An error occured", 'error');
                            end_loader();
                            $('#save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                            console.log(resp)
                        }
                        $('html,body').animate({
                            scrollTop: 0
                        }, 'fast')
                    }
                })
            } else {
                alert_toast('Click an item above to add it before saving.', 'error');
                return false;
            }
        })

        calc();
        refreshCartBadges();
        $('table#list tbody tr[data-id] .rem_row').click(function() {
            rem($(this))
        })
    })

    function addOrIncrementItem(item) {
        var existing = $('table#list tbody').find('tr[data-id="' + item + '"]');
        var itemData = items[item];
        if (!itemData) return;

        if (existing.length > 0) {
            var qtyCell = existing.find('.qty-cell');
            var newQty = (parseFloat(qtyCell.data('value')) || 0) + 1;
            updateCellValue(qtyCell, newQty);
        } else {
            $('#empty-row').remove();
            var cost = parseFloat(itemData.cost) || 0;
            var suggestedPrice = cost > 0 ? +(cost * (1 + MARGIN_PERCENT / 100)).toFixed(2) : 0;

            var tr = $('#clone_list tr').clone();
            tr.attr('data-id', item);
            tr.find('.item-name').text(itemData.name || 'N/A');
            tr.find('.item-desc').text(itemData.description || '');

            var qtyCell = tr.find('.qty-cell');
            qtyCell.data('value', 1).attr('data-value', 1);
            qtyCell.find('[name="item_id[]"]').val(item);
            qtyCell.find('[name="qty[]"]').val(1);
            qtyCell.find('.cell-display').text('1');

            var priceCell = tr.find('.price-cell');
            priceCell.data('value', suggestedPrice).attr('data-value', suggestedPrice);
            priceCell.find('.cell-display').text('GHS ' + suggestedPrice.toFixed(2));
            qtyCell.find('[name="price[]"]').val(suggestedPrice);

            var total = suggestedPrice * 1;
            qtyCell.find('[name="total[]"]').val(total);
            tr.find('.total').text('GHS ' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2
            }));

            tr.find('.rem_row').click(function() {
                rem($(this))
            });
            $('table#list tbody').append(tr);
        }
        calc();
        refreshCartBadges();
    }

    function updateCellValue(cell, value) {
        var tr = cell.closest('tr');
        var item = tr.attr('data-id');

        cell.data('value', value).attr('data-value', value);

        if (cell.hasClass('qty-cell')) {
            cell.find('.cell-display').text(value);
            tr.find('[name="qty[]"]').val(value);

            // soft warning if qty exceeds known stock
            var stock = items[item] ? parseFloat(items[item].stock) || 0 : null;
            tr.toggleClass('row-warning', stock !== null && value > stock);
        } else if (cell.hasClass('price-cell')) {
            cell.find('.cell-display').text('GHS ' + parseFloat(value).toFixed(2));
            tr.find('[name="price[]"]').val(value);
        }

        var qty = parseFloat(tr.find('[name="qty[]"]').val()) || 0;
        var price = parseFloat(tr.find('[name="price[]"]').val()) || 0;
        var total = qty * price;
        tr.find('[name="total[]"]').val(total);
        tr.find('.total').text('GHS ' + total.toLocaleString('en-US', {
            minimumFractionDigits: 2
        }));

        calc();
        refreshCartBadges();
    }

    function rem(_this) {
        _this.closest('tr').remove();
        calc();
        refreshCartBadges();
        if ($('table#list tbody tr[data-id]').length <= 0) {
            $('table#list tbody').append(
                '<tr id="empty-row"><td colspan="5">No items added yet. Click an item above to get started.</td></tr>'
            );
        }
    }

    // Show a small badge on each card reflecting the qty currently in the cart
    function refreshCartBadges() {
        $('.item-card').removeClass('in-cart').find('.cart-badge').remove();
        $('table#list tbody tr[data-id]').each(function() {
            var item = $(this).attr('data-id');
            var qty = parseFloat($(this).find('[name="qty[]"]').val()) || 0;

            if (qty > 0) {
                var card = $('.item-card[data-id="' + item + '"]');
                if (card.length > 0) {
                    card.addClass('in-cart');
                    card.append('<span class="cart-badge badge badge-primary position-absolute" style="top:5px; right:5px;">' + qty + '</span>');
                }
            }
        });
    }

    // Calculate overall totals for the cart/sale
    function calc() {
        var grandTotal = 0;
        $('table#list tbody tr[data-id]').each(function() {
            var qty = parseFloat($(this).find('[name="qty[]"]').val()) || 0;
            var price = parseFloat($(this).find('[name="price[]"]').val()) || 0;
            grandTotal += qty * price;
        });

        var formattedTotal = 'GHS ' + grandTotal.toLocaleString('en-US', {
            minimumFractionDigits: 2
        });

        $('.grand-total, #header-total').text(formattedTotal);
    }
</script>
@endpush