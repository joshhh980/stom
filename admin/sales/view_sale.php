<?php
$qry = $conn->query("SELECT * FROM sales_list r  where id = '{$_GET['id']}'");
if ($qry->num_rows > 0) {
    foreach ($qry->fetch_array() as $k => $v) {
        $$k = $v;
    }
}
?>
<!-- Hidden container holding your data elements safely -->
<div class="d-none">
    <div id="print_out">
        <div class="meta-section">
            <div style="margin-bottom: 4px;"><span class="bold">Sales Code:</span> <?php echo isset($sales_code) ? $sales_code : '' ?></div>
            <div style="margin-bottom: 4px;"><span class="bold">Client Name:</span> <?php echo isset($client) ? $client : 'Guest' ?></div>
            <div style="margin-bottom: 4px;"><span class="bold">Date:</span> <?php echo isset($date_created) ? $date_created : '' ?></div>
        </div>
        <h4 class="receipt-heading">Items</h4>
        <table class="receipt-table">
            <thead>
                <tr>
                    <th align="left">Item</th>
                    <th align="center" style="width: 40px;">Qty</th>
                    <th align="right">Cost</th>
                    <th align="right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                $qry = $conn->query("SELECT s.*,i.name,i.description FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
                while ($row = $qry->fetch_assoc()):
                    $total += $row['total']
                ?>
                    <tr>
                        <td align="left"><?php echo $row['name'] ?></td>
                        <td align="center"><?php echo number_format($row['quantity']) ?></td>
                        <td align="right"><?php echo number_format($row['price'], 2) ?></td>
                        <td align="right"><?php echo number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" align="left">Total</th>
                    <th align="right" class="grand-total-val"><?php echo isset($amount) ? number_format($amount, 2) : 0 ?></th>
                </tr>
            </footer>
        </table>
        <?php if (!empty($remarks)) : ?>
            <div style="margin-top: 10px; font-size: 22px;">
                <span class="bold">Remarks:</span>
                <p style="margin: 2px 0 0 0;"><?php echo $remarks ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- On-Screen Admin Dashboard Card View Layout (Standard Styles Retained) -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Sales Record - <?php echo $sales_code ?></h4>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label text-info">Sales Code</label>
                    <div><?php echo isset($sales_code) ? $sales_code : '' ?></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="client" class="control-label text-info">Client Name</label>
                        <div><?php echo isset($client) ? $client : '' ?></div>
                    </div>
                </div>
            </div>
            <h4 class="text-info">Items</h4>
            <table class="table table-striped table-bordered" id="list">
                <colgroup>
                    <col width="10%">
                    <col width="50%">
                    <col width="20%">
                    <col width="20%">
                </colgroup>
                <thead>
                    <tr class="text-light bg-navy">
                        <th class="text-center py-1 px-2">Qty</th>
                        <th class="text-center py-1 px-2">Item</th>
                        <th class="text-center py-1 px-2">Cost</th>
                        <th class="text-center py-1 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Reset query resource index back to first element row for secondary on-screen render loops
                    $qry->data_seek(0);
                    while ($row = $qry->fetch_assoc()):
                    ?>
                        <tr>
                            <td class="py-1 px-2 text-center"><?php echo number_format($row['quantity']) ?></td>
                            <td class="py-1 px-2">
                                <?php echo $row['name'] ?> <br>
                                <?php echo $row['description'] ?>
                            </td>
                            <td class="py-1 px-2 text-right"><?php echo number_format($row['price'], 2) ?></td>
                            <td class="py-1 px-2 text-right"><?php echo number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="3">Total</th>
                        <th class="text-right py-1 px-2 grand-total"><?php echo isset($amount) ? number_format($amount, 2) : 0 ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-success" type="button" id="print-pos-receipt">Print POS Receipt</button>
        <a class="btn btn-flat btn-primary" href="<?php echo base_url . '/admin?page=sales/manage_sale&id=' . (isset($id) ? $id : '') ?>">Edit</a>
        <a class="btn btn-flat btn-dark" href="<?php echo base_url . '/admin?page=sales' ?>">Back To List</a>
    </div>
</div>
<script>
    $(function() {
        $('#print-pos-receipt').click(function(e) {
            e.preventDefault();
            
            // Clone the bare data from the hidden container div
            var printContents = $('#print_out').clone();
            
            // Reconstruct a pristine standalone context completely isolated from framework styles
            var thermalHTML = '<!DOCTYPE html><html><head>';
            thermalHTML += '<title></title>'; // Explicitly empty title prevents browser headers/footers
            thermalHTML += '<style>';
            thermalHTML += '@media print {';
            thermalHTML += '  @page { margin: 0 !important; size: 80mm auto !important; }'; // Auto height cuts right where content ends
            thermalHTML += '  html, body { margin: 0 !important; padding: 0 !important; width: 80mm !important; max-width: 80mm !important; height: auto !important; font-family: "Courier New", Courier, monospace !important; background: #fff; color: #000; }';
            
            // ─── THE CORRECTION: SHIFT THE BOUNDARIES INWARD FROM 80MM TO 72MM ───
            thermalHTML += '  .receipt-container { width: 80mm; padding: 0 4mm !important; box-sizing: border-box; margin: 0; }';
            
            thermalHTML += '  .store-title { font-size: 18px !important; font-weight: 900; text-align: center; margin: 2px 0; text-transform: uppercase; }';
            thermalHTML += '  .receipt-heading { font-size: 15px !important; font-weight: bold; border-bottom: 1px dashed #000; margin: 8px 0 4px 0; padding-bottom: 2px; text-transform: uppercase; }';
            thermalHTML += '  .meta-section { font-size: 13px !important; line-height: 1.3; }';
            thermalHTML += '  .bold { font-weight: bold !important; }';
            thermalHTML += '  .divider { border-top: 1px dashed #000 !important; margin: 5px 0; width: 100%; }';
            thermalHTML += '  .receipt-table { width: 100% !important; border-collapse: collapse !important; margin-top: 5px; table-layout: fixed; }';
            thermalHTML += '  .receipt-table th, .receipt-table td { font-size: 13px !important; line-height: 1.3; padding: 4px 2px !important; font-family: "Courier New", Courier, monospace !important; word-wrap: break-word; }';
            thermalHTML += '  .receipt-table tbody tr { border-bottom: 1px dashed #000; }';
            thermalHTML += '  .receipt-table tfoot tr th { font-size: 14px !important; font-weight: bold; padding-top: 6px !important; border-top: 1px solid #000; }';
            thermalHTML += '}';
            thermalHTML += '</style></head><body>';
            
            // Structured top branding area formatted specifically for the 80mm canvas
            thermalHTML += '<div class="receipt-container">';
            thermalHTML += '  <h4 class="store-title"><?php echo $_settings->info("name") ?></h4>';
            thermalHTML += '  <div style="font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 4px;">SALES RECEIPT</div>';
            thermalHTML += '  <div class="divider"></div>';
            
            // Append data payload body elements smoothly
            thermalHTML += '  <div class="meta-section">' + printContents.find('.meta-section').html() + '</div>';
            thermalHTML += '  <h4 class="receipt-heading">Items</h4>';
            thermalHTML += '  <table class="receipt-table">' + printContents.find('.receipt-table').html() + '</table>';
            
            // Check and clean rendering block paths for conditional remarks
            if(printContents.find('.bold:contains("Remarks:")').length > 0) {
                thermalHTML += '  <div class="divider"></div>';
                thermalHTML += '  <div class="meta-section">' + printContents.find('.bold:contains("Remarks:")').parent().html() + '</div>';
            }
            
            thermalHTML += '  <div class="divider"></div>';
            thermalHTML += '  <div style="font-size: 13px; font-weight: bold; text-align: center; margin-top: 12px;">THANK YOU FOR YOUR BUSINESS!</div>';
            thermalHTML += '</div>';
            thermalHTML += '</body></html>';
            
            // Open window runtime target reference without location context tags
            var nw = window.open("", "", "width=800,height=900,left=250,location=no,titlebar=no,menubar=no");
            nw.document.write(thermalHTML);
            nw.document.close();
            
            // Timeout delay structures guarantee asset parsing processes completely before calling print
            setTimeout(() => {
                nw.print();
                setTimeout(() => {
                    nw.close();
                }, 150);
            }, 500);
        });
    });
</script>
