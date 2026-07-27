<?php
class Service {
static function save_item($conn, $data)
{
    if(empty($id)){
        $sql = "INSERT INTO `item_list` set {$data} ";

    }else{
        $sql = "UPDATE `item_list` set {$data} where id = '{$id}' ";
    }
    return $conn->query($sql);
}

/**
 * @param DBConnection $conn;
 * @param array{
 * 	id: number;
 *  data: string;
 * } $params
 * 
 * @return array $params
 * 
 */
static function save_po($conn, $params)
{
    extract($params);
	if(empty($id)){
		$prefix = "PO";
		$code = sprintf("%'.04d",1);
		while(true){
			$check_code = $conn->query("SELECT * FROM `purchase_order_list` where po_code ='".$prefix.'-'.$code."' ")->num_rows;
			if($check_code > 0){
				$code = sprintf("%'.04d",$code+1);
			}else{
				break;
			}
		}
		$po_code = $prefix."-".$code;
	}
	$data .= ", `po_code` = '{$po_code}' ";
    if(empty($id)){
        $sql = "INSERT INTO `purchase_order_list` set {$data}";
    }else{
        $sql = "UPDATE `purchase_order_list` set {$data} where id = '{$id}'";
    }
    $save = $conn->query($sql);
    if($save){
        $resp['status'] = 'success';
        if(empty($id)) $po_id = $conn->insert_id;
        else $po_id = $id;
        $resp['id'] = $po_id;
        $data = "";
        foreach($item_ids as $k => $item_id){
            if(!empty($data)) $data .=", ";
            $_expiry_date = $expiry_date[$k] ?? NULL;
            $_batch_no = $batch_no[$k] ?? NULL;

            $expiry_date_val = (!empty($_expiry_date)) ? "'".$conn->real_escape_string($_expiry_date)."'" : "NULL";
            $batch_no_val = (!empty($_batch_no)) ? "'".$conn->real_escape_string($_batch_no)."'" : "NULL";

            $data .= "('{$po_id}','{$item_id}','{$qty[$k]}','{$price[$k]}','{$total[$k]}'";
            $data .= ",{$expiry_date_val}";
            $data .= ",{$batch_no_val}";
            $data .= ")";
        }
        if(!empty($data)){
            $conn->query("DELETE FROM `po_items` where po_id = '{$po_id}'");
            $save = $conn->query("INSERT INTO `po_items` (`po_id`,`item_id`,`quantity`,`price`,`total`, `expiry_date`, `batch_no`) VALUES {$data}");
            if(!$save){
                $resp['status'] = 'failed';
                if(empty($id)){
                    $conn->query("DELETE FROM `purchase_order_list` where id '{$po_id}'");
                }
                $resp['msg'] = 'PO has failed to save. Error: '.$conn->error;
                $resp['sql'] = "INSERT INTO `po_items` (`po_id`,`item_id`,`quantity`,`price`,`total`, `expiry_date`, `batch_no`) VALUES {$data}";
            }
        }
    }else{
        $resp['status'] = 'failed';
        $resp['msg'] = 'An error occured. Error: '.$conn->error;
    }
    return $resp;
}

static function save_receiving($conn, $params)
{
    extract($params);
    $bo_code;
    if(empty($id)){
        $prefix = "BO";
        $code = sprintf("%'.04d",1);
        while(true){
            $check_code = $conn->query("SELECT * FROM `back_order_list` where bo_code ='".$prefix.'-'.$code."' ")->num_rows;
            if($check_code > 0){
                $code = sprintf("%'.04d",$code+1);
            }else{
                break;
            }
        }
        $bo_code = $prefix."-".$code;
    }else{
        $get = $conn->query("SELECT * FROM back_order_list where receiving_id = '{$id}' ");
        if($get->num_rows > 0){
            $res = $get->fetch_array();
            $bo_id = $res['id'];
            $bo_code = $res['bo_code'];	
        }else{

            $prefix = "BO";
            $code = sprintf("%'.04d",1);
            while(true){
                $check_code = $conn->query("SELECT * FROM `back_order_list` where bo_code ='".$prefix.'-'.$code."' ")->num_rows;
                if($check_code > 0){
                    $code = sprintf("%'.04d",$code+1);
                }else{
                    break;
                }
            }
            $bo_code = $prefix."-".$code;

        }
    }
	// $data .= ", `bo_code` = '{$bo_code}' ";
	if(empty($id)){
		$sql = "INSERT INTO `receiving_list` set {$data}";
	}else{
		$sql = "UPDATE `receiving_list` set {$data} where id = '{$id}'";
	}
	$save = $conn->query($sql);
	if($save){
		$resp['status'] = 'success';
		if(empty($id)) $r_id = $conn->insert_id;
		else $r_id = $id;
		$resp['id'] = $r_id;
		if(!empty($r_id)){
			$stock_ids = $conn->query("SELECT stock_ids FROM `receiving_list` where id = '{$r_id}'")->fetch_array()['stock_ids'];
			$conn->query("DELETE FROM `stock_list` where id in ({$stock_ids})");
		}
		$stock_ids = array();
		foreach($item_id as $k =>$v){
			$stock_data = "";
			$_expiry_date = $expiry_date[$k] ?? NULL;
			$_batch_no = $batch_no[$k] ?? NULL;

			$expiry_date_val = (!empty($_expiry_date)) ? "'".$conn->real_escape_string($_expiry_date)."'" : "NULL";
			$batch_no_val = (!empty($_batch_no)) ? "'".$conn->real_escape_string($_batch_no)."'" : "NULL";

			$stock_data .= "('{$v}','{$qty[$k]}','{$price[$k]}','{$unit[$k]}','{$total[$k]}', 1";
			$stock_data .= ",{$expiry_date_val}";
			$stock_data .= ",{$batch_no_val}";
			$stock_data .= ")";
			$sql = "INSERT INTO stock_list (`item_id`,`quantity`,`price`,`unit`,`total`,`type`, `expiry_date`, `batch_no`) VALUES {$stock_data}";
			$conn->query($sql);
			$stock_ids[] = $conn->insert_id;
			if($qty[$k] < $oqty[$k]){
				$bo_ids[] = $k;
			}
		}

		if(count($stock_ids) > 0){
			$stock_ids = implode(',',$stock_ids);
			$conn->query("UPDATE `receiving_list` set stock_ids = '{$stock_ids}' where id = '{$r_id}'");
		}
		if(isset($bo_ids)){
			$conn->query("UPDATE `purchase_order_list` set status = 1 where id = '{$po_id}'");
			if($from_order == 2){
				$conn->query("UPDATE `back_order_list` set status = 1 where id = '{$form_id}'");
			}
			if(!isset($bo_id)){
				$sql = "INSERT INTO `back_order_list` set 
						bo_code = '{$bo_code}',	
						receiving_id = '{$r_id}',	
						po_id = '{$po_id}',	
						supplier_id = '{$supplier_id}',	
						discount_perc = '{$discount_perc}',	
						tax_perc = '{$tax_perc}',
						date_created = '{$date_created}'
					";
			}else{
				$sql = "UPDATE `back_order_list` set 
						receiving_id = '{$r_id}',	
						po_id = '{$form_id}',	
						supplier_id = '{$supplier_id}',	
						discount_perc = '{$discount_perc}',	
						tax_perc = '{$tax_perc}',
						date_created = '{$date_created}'
						where bo_id = '{$bo_id}'
					";
			}
			$bo_save = $conn->query($sql);
			if(!isset($bo_id))
			$bo_id = $conn->insert_id;
			$stotal =0; 
			$data = "";
			foreach($item_id as $k =>$v){
				if(!in_array($k,$bo_ids))
					continue;
				$total = ($oqty[$k] - $qty[$k]) * $price[$k];
				$stotal += $total;
				if(!empty($data)) $data.= ", ";
				$_expiry_date = $expiry_date[$k] ?? NULL;
			$_batch_no = $batch_no[$k] ?? NULL;

			$expiry_date_val = (!empty($_expiry_date)) ? "'".$conn->real_escape_string($_expiry_date)."'" : "NULL";
			$batch_no_val = (!empty($_batch_no)) ? "'".$conn->real_escape_string($_batch_no)."'" : "NULL";

			$data .= "('{$bo_id}', '{$v}','".($oqty[$k] - $qty[$k])."','{$price[$k]}','{$total}'";
			$data .= ",{$expiry_date_val}";
			$data .= ",{$batch_no_val}";
			$data .= ")";
			}
			$conn->query("DELETE FROM `bo_items` where bo_id='{$bo_id}'");
			$save_bo_items = $conn->query("INSERT INTO `bo_items` (`bo_id`,`item_id`,`quantity`,`price`,`total`, `expiry_date`, `batch_no`) VALUES {$data}");
			if($save_bo_items){
				$discount = $stotal * ($discount_perc /100);
				$stotal -= $discount;
				$tax = $stotal * ($tax_perc /100);
				$stotal += $tax;
				$amount = $stotal;
				$conn->query("UPDATE back_order_list set amount = '{$amount}', discount='{$discount}', tax = '{$tax}' where id = '{$bo_id}'");
			}

		}else{
			$conn->query("UPDATE `purchase_order_list` set status = 2 where id = '{$po_id}'");
			if($from_order == 2){
				$conn->query("UPDATE `back_order_list` set status = 2 where id = '{$form_id}'");
			}
		}
	}else{
		throw new Exception($conn->error);
			
	}
}
}