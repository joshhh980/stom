<?php
require './Service.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class API extends DBConnection {


private function parseNumeric($value)
    {
        $value = (string)$value;
        // Match an optional minus sign, digits with optional thousands
        // separators, and an optional decimal portion.
        if (preg_match('/-?\d[\d,]*\.?\d*/', $value, $matches)) {
            return (float)str_replace(',', '', $matches[0]);
        }
        return 0.0;
    }

    private function parseDateTime($value)
    {
        if (is_numeric($value)) {
            try {
                $dateObj = ExcelDate::excelToDateTimeObject((float)$value);
                return $dateObj->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // fall through to string parsing below
            }
        }
 
        $timestamp = strtotime((string)$value);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }
 
        return date('Y-m-d H:i:s');
    }


    public function importData()
    {
        $conn = $this->conn;
        $conn->begin_transaction();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];

            try {
                // Load the Excel file structure
                $spreadsheet = IOFactory::load($fileTmpPath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                $invoice_number;
                $amount;
                $po_id;
                $discount_perc;
                $discount;
                $tax_perc;
                $tax;
                $remarks;
                $date_created;
                $item_id;
                $expiry_date = [];
                $batch_no = [];
                $expected_quantity = [];
                $actual_quantity = [];
                $price = [];
                $total = [];
                $item_ids = [];
                $unit = [];
                $supplier_id;

                // Loop rows (Skip index 0 if it contains headers)
                foreach ($rows as $index => $row) {
                    if ($index === 0) continue; 

                    $supplier_name = $this->conn->real_escape_string($row[14]); 
                    $supplier_query = "SELECT id FROM `supplier_list` WHERE `name` = '{$supplier_name}' LIMIT 1";
                    $res = $this->conn->query($supplier_query);
                    if($res->num_rows > 0){
                            $item_data = $res->fetch_assoc();
                            $supplier_id   = $item_data['id'];
                    }

                    $item_name = $this->conn->real_escape_string($row[10]); 
                    $item_qty = (int) $row[9];                     
                    $item_cost_price = $this->parseNumeric($row[12]);  


                    $item_query = "SELECT id FROM `item_list` WHERE `name` = '{$item_name}' LIMIT 1";

                    $res = $this->conn->query($item_query);

                    if($res->num_rows > 0){
                        $item_data = $res->fetch_assoc();
                        $item_id   = $item_data['id'];
                        
//                        $db->query("UPDATE `stock_list` SET `quantity` = `quantity` + {$qty} WHERE `item_id` = '{$item_id}'");

                    }else {

                     $item_data_str = "`name`='{$item_name}', `cost`='{$item_cost_price}', `supplier_id`='{$supplier_id}', `description` = 'N/A'";

                    $res = Service::save_item(
                        $conn,
                        $item_data_str);    

                        $item_id = $conn->insert_id;
//                        $this->conn->query("INSERT INTO stock_list (item_id, quantity, price) VALUES ('$item_name', '$qty', '$price')");

                    }

                    if(!$res){
                        throw new Exception("Error Saving Item: " . $this->conn->error);
                        
                    }

                    $item_ids[] = $item_id;

 
                    $invoice_number = $row[15];
                    $amount = $this->parseNumeric($row[0]);
                    $discount_perc = $this->parseNumeric($row[1]);
                    $discount = $this->parseNumeric($row[2]);
                    $tax_perc = $this->parseNumeric($row[3]);
                    $tax = $this->parseNumeric($row[4]);
                    $remarks = $row[5];
                    $date_created = $this->parseDateTime($row[6]);
                    


                    $expiry_date[] = $this->parseDateTime($row[7]);
                    $batch_no[] = $row[8];
                    $expected_quantity[] = $row[9];
                    $actual_quantity[] = $row[11];
                    $price[] = $this->parseNumeric($row[12]);
                    $total[] = $this->parseNumeric($row[13]);
                    $unit[] = 1;
                    
                

                }

                $po_data_str = "`amount`='{$amount}', `discount_perc`='{$discount_perc}', "
                    . "`discount`='{$discount}', `tax_perc`='{$tax_perc}', `tax`='{$tax}', "
                    . "`remarks`='{$remarks}', `date_created`='{$date_created}', `supplier_id` = '{$supplier_id}'";
 

                $resp = Service::save_po(
                $conn,    
                [
                    "data" => $po_data_str,
                    'item_ids' => $item_ids,
                    "expiry_date" => $expiry_date,
                    "batch_no" => $batch_no,
                    "qty" => $expected_quantity,
                    "price" => $price,
                    "total" => $total,
                ]);

                if($resp['status'] == 'success'){
                    $po_id = $resp['id'];
                }else{
                    throw new Exception($resp['msg']);
                }      
                
                if (isset($_GET['receive']) && $_GET['receive'] === 'true') {
                    $receiving_data_str = "`form_id`='{$po_id}', `from_order`='1', "
                    . "`amount`='{$amount}', `discount_perc`='{$discount_perc}', `discount`='{$discount}', "
                    . "`tax_perc`='{$tax_perc}', `tax`='{$tax}', `remarks`='{$remarks}', "
                    . "`date_created`='{$date_created}'";

                    Service::save_receiving(
                    $conn,    
                    [
                        "data" => $receiving_data_str,
                        "item_id" => $item_ids,
                        "expiry_date" => $expiry_date,
                        "batch_no" => $batch_no,
                        "oqty" => $expected_quantity,
                        "qty" => $actual_quantity,
                        "price" => $price,
                        "total" => $total,
                        "unit" => $unit,
                        "po_id" => $po_id,
                        "from_order"=> '1',
                    ]
                    );
                 }


		    $conn->commit();
                echo "Data successfully imported!";

            } catch (Exception $e) {
                $conn->rollback();
                var_dump("Error loading file: " . $e->getMessage());
            }
        }

    }


    public function exportProducts()
    {
        $conn = $this->conn;

        try {
            // Fetch items along with their supplier names (adjust table/column names if needed)
            $query = "SELECT i.id, i.name, i.cost, i.description, s.name as supplier_name 
                    FROM `item_list` i 
                    LEFT JOIN `supplier_list` s ON i.supplier_id = s.id";
            
            $result = $conn->query($query);

            if (!$result) {
                throw new Exception("Error fetching items: " . $conn->error);
            }

            // Initialize PhpSpreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Items List');

            // Set Excel Headers
            $headers = ['ID', 'Item Name'];
            $columnIndex = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($columnIndex, 1, $header);
                $sheet->getStyleByColumnAndRow($columnIndex, 1)->getFont()->setBold(true);
                $columnIndex++;
            }

            // Populate Data Rows
            $rowIndex = 2;
            while ($row = $result->fetch_assoc()) {
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $row['id']);
                $sheet->setCellValueByColumnAndRow(2, $rowIndex, $row['name']);
                $rowIndex++;
            }

            // Auto-size columns for cleaner output
            for ($col = 1; $col <= count($headers); $col++) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }

            // Clear any previous output buffers to avoid file corruption
            if (ob_get_length()) {
                ob_end_clean();
            }

            // Define filename
            $filename = 'items_export_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Stream file download directly to the browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (Exception $e) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            var_dump("Error exporting items: " . $e->getMessage());
        }
    }


}