<?php 
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM sales_list where id = '{$_GET['id']}'");
    if($qry->num_rows >0){
        foreach($qry->fetch_array() as $k => $v){
            $$k = $v;
        }
    }
}
?>
<style>
    /* ---------- Item picker grid ---------- */
    #item_picker {
        max-height: 360px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px;
        background: #fafafa;
    }
    #item_grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    .item-card {
        position: relative;
        background: #fff;
        border: 2px solid #e2e6ea;
        border-radius: 6px;
        padding: 10px 10px 8px;
        cursor: pointer;
        transition: box-shadow .12s ease, transform .12s ease, border-color .12s ease;
        user-select: none;
    }
    .item-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
        transform: translateY(-1px);
        border-color: #b8c2cc;
    }
    .item-card:active { transform: translateY(0); }
    .item-card.in-cart { border-color: #007bff; background: #f0f7ff; }
    .item-card.pulse { animation: cardPulse .3s ease; }
    @keyframes cardPulse {
        0% { transform: scale(1); }
        40% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .item-card .name {
        font-weight: 600;
        font-size: 13px;
        display: block;
        margin-bottom: 4px;
        line-height: 1.25;
    }
    .item-card .price {
        font-size: 13px;
        color: #007bff;
        font-weight: 600;
    }
    .item-card .stock-tag {
        display: inline-block;
        margin-top: 6px;
        font-size: 11px;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 8px;
    }
    .item-card .stock-tag.ok { background: #d4edda; color: #155724; }
    .item-card .stock-tag.low { background: #fff3cd; color: #856404; }
    .item-card .cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #007bff;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        line-height: 20px;
        border-radius: 10px;
        text-align: center;
        padding: 0 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,.3);
    }
    #item_search_empty {
        text-align: center;
        color: #6c757d;
        padding: 24px;
        font-style: italic;
        display: none;
    }

    /* ---------- Cart table ---------- */
    .editable-cell {
        cursor: pointer;
        border-bottom: 1px dashed #adb5bd;
    }
    .editable-cell:hover { background: #f0f7ff; }
    .editable-cell .cell-input {
        width: 90px;
        display: inline-block;
        padding: 2px 6px;
        height: 28px;
    }
    #list tbody tr.row-warning td { background-color: #fff8e6 !important; }
    #list tbody tr .item-name { font-weight: 600; }
    #list tbody tr .item-desc { color: #6c757d; font-size: 12px; }
    #empty-row td {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        font-style: italic;
    }
    tfoot .grand-total { font-size: 16px; font-weight: 700; }
</style>

<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0"><?php echo isset($id) ? "Sale Details - ".$sales_code : 'Create New Sale Record' ?></h4>
        <span class="badge badge-primary" style="font-size:14px;" id="header-total">GHS 0.00</span>
    </div>
    <div class="card-body">
        <form action="" id="sale-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <label class="control-label text-info">Sale Code</label>
                        <input type="text" class="form-control form-control-sm rounded-0" value="<?php echo isset($sales_code) ? $sales_code : 'Auto-generated on save' ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="client" class="control-label text-info">Client Name</label>
                            <input type="text" name="client" class="form-control form-control-sm rounded-0" value="<?php echo isset($client) ? $client : 'Guest' ?>" >
                        </div>
                    </div>
                    <?php if($_settings->userdata('type') == 1): ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date_created" class="control-label text-info">Date Created</label>
                            <input name="date_created" id="date_created" type="date" class="form-control form-control-sm rounded-0" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <hr>
                <fieldset>
                    <legend class="text-info">Select Items</legend>
                    <?php 
                        $item_arr = array();
                        $_items_arr = array();
                        $cost_arr = array();
                        $items_qry = $conn->query("SELECT * FROM `item_list` where status = 1 order by `name` asc");
                        while($row=$items_qry->fetch_assoc()):
                            $_items_arr[$row['id']] = $row;
                        endwhile;
                        $currentDate = date('Y-m-d');
                        $stock_items = $conn->query("SELECT * FROM `stock_list`");
                        $stock_items_arr = [];
                        while($stock_row=$stock_items->fetch_assoc()):
                            $stock_items_arr[$stock_row['id']] = $stock_row;
                        endwhile;

                        $stock_counts = [];
                        foreach ($_items_arr as $key => $item) {
                            $stock_counts[$item['id']] = [
                                "id" => $item['id'],
                                "name" => $item["name"],
                                'stock' => 0,
                                "cost" => $item['cost'],
                            ];
                        }
                        foreach ($stock_items_arr as $stock_item) {
                            $item_id = $stock_item['item_id'];
                            $type = (int)$stock_item['type'];
                            $quantity = (float)$stock_item['quantity'];
                            if (!isset($stock_counts[$item_id])) continue;
                            if ($type === 1) $stock_counts[$item_id]['stock'] += $quantity;
                            elseif ($type === 2) $stock_counts[$item_id]['stock'] -= $quantity;
                        }
                        foreach ($stock_counts as $item_id => $item_data) {
                            if ($item_data['stock'] > 0) {
                                $item_arr[$item_data['id']] = $stock_counts[$item_data['id']];
                                $cost_arr[$item_data['id']] = $_items_arr[$item_data['id']]['cost'];
                            }
                        }
                    ?>
                    <div class="form-group mb-2">
                        <input type="text" id="item_search" class="form-control rounded-0" placeholder="Search items by name...">
                    </div>
                    <div id="item_picker">
                        <div id="item_grid">
                            <?php foreach($item_arr as $item_id => $item): ?>
                                <div class="item-card"
                                     data-id="<?php echo $item_id ?>"
                                     data-name="<?php echo htmlspecialchars(strtolower($item['name'])) ?>">
                                    <span class="name"><?php echo htmlspecialchars($item['name']) ?></span>
                                    <span class="price">GHS <?php echo number_format($item['cost'],2) ?></span><br>
                                    <span class="stock-tag <?php echo $item['stock'] <= 5 ? 'low' : 'ok' ?>">
                                        <?php echo number_format($item['stock']) ?> in stock
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="item_search_empty">No items match your search.</div>
                    </div>
                    <div class="hint-text text-muted mt-1" style="font-size:12px;">
                        Click an item to add it &mdash; click again to add another. Edit quantity or price directly in the table below.
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
                        <?php 
                        $total = 0;
                        $has_rows = false;
                        if(isset($id)):
                        $qry = $conn->query("SELECT s.*,i.name,i.description FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
                        while($row = $qry->fetch_assoc()):
                            $has_rows = true;
                            $total += $row['total']
                        ?>
                        <tr data-id="<?php echo $row['item_id']; ?>">
                            <td class="py-1 px-2 text-center">
                                <button class="btn btn-outline-danger btn-sm rem_row" type="button" title="Remove"><i class="fa fa-times"></i></button>
                            </td>
                            <td class="py-1 px-2 item">
                                <span class="item-name"><?php echo $row['name']; ?></span><br>
                                <span class="item-desc"><?php echo $row['description']; ?></span>
                            </td>
                            <td class="py-1 px-2 text-center editable-cell qty-cell" data-value="<?php echo $row['quantity']; ?>">
                                <input type="hidden" name="item_id[]" value="<?php echo $row['item_id']; ?>">
                                <input type="hidden" name="qty[]" value="<?php echo $row['quantity']; ?>">
                                <input type="hidden" name="price[]" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="total[]" value="<?php echo $row['total']; ?>">
                                <span class="cell-display"><?php echo number_format($row['quantity']); ?></span>
                            </td>
                            <td class="py-1 px-2 text-right editable-cell price-cell" data-value="<?php echo $row['price']; ?>">
                                <span class="cell-display">GHS <?php echo number_format($row['price'],2); ?></span>
                            </td>
                            <td class="py-1 px-2 text-right total">
                                GHS <?php echo number_format($row['total'],2); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                        <?php if(!$has_rows): ?>
                        <tr id="empty-row">
                            <td colspan="5">No items added yet. Click an item above to get started.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="3">Total
                                <input type="hidden" name="amount" value="<?php echo isset($discount) ? $discount : 0 ?>">
                            </th>
                            <th class="text-right py-1 px-2 grand-total" colspan="2">GHS 0.00</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remarks" class="text-info control-label">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="3" class="form-control rounded-0"><?php echo isset($remarks) ? $remarks : '' ?></textarea>
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
        <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin?page=sales' ?>">Cancel</a>
    </div>
</div>
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
<script>
    var items = <?php echo json_encode($item_arr) ?>;    // { id: {id,name,stock,cost}, ... }
    var MARGIN_PERCENT = 20;

    $(function(){

        // ---------- Item search filter ----------
        $('#item_search').on('input', function(){
            var q = $(this).val().toLowerCase().trim();
            var visible = 0;
            $('.item-card').each(function(){
                var match = $(this).data('name').indexOf(q) !== -1;
                $(this).toggle(match);
                if(match) visible++;
            });
            $('#item_search_empty').toggle(visible === 0);
        });

        // ---------- Click a card to add / increment ----------
        $(document).on('click', '.item-card', function(){
            var item = String($(this).data('id'));
            addOrIncrementItem(item);
            var card = $(this);
            card.addClass('pulse');
            setTimeout(function(){ card.removeClass('pulse'); }, 300);
        });

        // ---------- Inline editable qty / price cells ----------
        $(document).on('click', '.editable-cell', function(){
            var cell = $(this);
            if(cell.find('input.cell-input').length) return; // already editing

            var current = parseFloat(cell.data('value')) || 0;
            var display = cell.find('.cell-display');
            display.hide();

            var input = $('<input type="number" step="any" min="0" class="form-control form-control-sm cell-input">').val(current);
            cell.append(input);
            input.trigger('focus');
            input.select();

            function commit(){
                var val = parseFloat(input.val());
                if(isNaN(val) || val < 0) val = current;
                input.remove();
                display.show();
                updateCellValue(cell, val);
            }

            input.on('blur', commit);
            input.on('keypress', function(e){
                if(e.which === 13){ e.preventDefault(); commit(); }
            });
            input.on('keydown', function(e){
                if(e.key === 'Escape'){ input.val(current); commit(); }
            });
        });

        $('#sale-form').submit(function(e){
            e.preventDefault();
            var _this = $(this)
            $('.err-msg').remove();
            if ($('table#list tbody tr[data-id]').length > 0) {
                $('#save-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                start_loader();
                $.ajax({
                    url:_base_url_+"classes/Master.php?f=save_sale",
                    data: new FormData($(this)[0]),
                    cache: false,
                    contentType: false,
                    processData: false,
                    method: 'POST',
                    type: 'POST',
                    dataType: 'json',
                    error:err=>{
                        console.log(err)
                        alert_toast("An error occured",'error');
                        end_loader();
                        $('#save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                    },
                    success:function(resp){
                        if(resp.status == 'success'){
                            location.replace(_base_url_+"admin/?page=sales/view_sale&id="+resp.id);
                        }else if(resp.status == 'failed' && !!resp.msg){
                            var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            end_loader();
                            $('#save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                        }else{
                            alert_toast("An error occured",'error');
                            end_loader();
                            $('#save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                            console.log(resp)
                        }
                        $('html,body').animate({scrollTop:0},'fast')
                    }
                })
            }else {
                alert_toast('Click an item above to add it before saving.','error');
                return false;
            }
        })

        if('<?php echo isset($id) && $id > 0 ?>' == 1){
            calc();
            refreshCartBadges();
            $('table#list tbody tr .rem_row').click(function(){
                rem($(this))
            })
        }
    })

    function addOrIncrementItem(item){
        var existing = $('table#list tbody').find('tr[data-id="'+item+'"]');
        var itemData = items[item];
        if(!itemData) return;

        if(existing.length > 0){
            var qtyCell = existing.find('.qty-cell');
            var newQty = (parseFloat(qtyCell.data('value')) || 0) + 1;
            updateCellValue(qtyCell, newQty);
        }else{
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
            tr.find('.total').text('GHS ' + total.toLocaleString('en-US',{minimumFractionDigits:2}));

            tr.find('.rem_row').click(function(){ rem($(this)) });
            $('table#list tbody').append(tr);
        }
        calc();
        refreshCartBadges();
    }

    function updateCellValue(cell, value){
        var tr = cell.closest('tr');
        var item = tr.attr('data-id');

        cell.data('value', value).attr('data-value', value);

        if(cell.hasClass('qty-cell')){
            cell.find('.cell-display').text(value);
            tr.find('[name="qty[]"]').val(value);

            // soft warning if qty exceeds known stock
            var stock = items[item] ? parseFloat(items[item].stock) || 0 : null;
            tr.toggleClass('row-warning', stock !== null && value > stock);
        }else if(cell.hasClass('price-cell')){
            cell.find('.cell-display').text('GHS ' + parseFloat(value).toFixed(2));
            tr.find('[name="price[]"]').val(value);
        }

        var qty = parseFloat(tr.find('[name="qty[]"]').val()) || 0;
        var price = parseFloat(tr.find('[name="price[]"]').val()) || 0;
        var total = qty * price;
        tr.find('[name="total[]"]').val(total);
        tr.find('.total').text('GHS ' + total.toLocaleString('en-US',{minimumFractionDigits:2}));

        calc();
        refreshCartBadges();
    }

    function rem(_this){
        _this.closest('tr').remove();
        calc();
        refreshCartBadges();
        if($('table#list tbody tr[data-id]').length <= 0){
            $('table#list tbody').append(
                '<tr id="empty-row"><td colspan="5">No items added yet. Click an item above to get started.</td></tr>'
            );
        }
    }

    // Show a small badge on each card reflecting the qty currently in the cart
    function refreshCartBadges(){
        $('.item-card').removeClass('in-cart').find('.cart-badge').remove();
        $('table#list tbody tr[data-id]').each(function(){
            var item = $(this).attr('data-id');
            var qty = $(this).find('[name="qty[]"]').val();
            var card = $('.item-card[data-id="'+item+'"]');
            if(card.length){
                card.addClass('in-cart');
                card.append('<span class="cart-badge">'+qty+'</span>');
            }
        });
    }

    function calc(){
        var grand_total = 0;
        $('table#list tbody input[name="total[]"]').each(function(){
            grand_total += parseFloat($(this).val()) || 0
        })
        var formatted = 'GHS ' + parseFloat(grand_total).toLocaleString('en-US',{minimumFractionDigits:2, maximumFractionDigits:2})
        $('table#list tfoot .grand-total').text(formatted)
        $('#header-total').text(formatted)
        $('[name="amount"]').val(parseFloat(grand_total))
    }
</script>