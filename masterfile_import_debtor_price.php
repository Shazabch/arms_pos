<?php
/*
4/28/2020 1:40 PM William
- Fixed bug upload debtor price.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_DEBTOR_CSV_UPDATE_PRICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_DEBTOR_CSV_UPDATE_PRICE', BRANCH_CODE), "/index.php");
class IMPORT_DEBTOR extends Module{
    var $headers = array("code" => "Debtor Code", "item_code" => "Item Code", "price" => "Price");
    var $sample = array("00000Y", "280000690000", "25.50");
    function __construct($title){
		$this->init();
 		parent::__construct($title);
	}
    
    function _default(){
		$this->display();
	}
	
	function init(){
		global $smarty;
		
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/update_debtor_price"))	check_and_create_dir("attachments/update_debtor_price");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
    
    function download_sample(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_update_debtor_price.csv");
		
		print join(", ", array_values($this->headers)) . "\n\r";
		$data = array();
		foreach($this->sample as $d) {
			$data[] = $d;
		}
		print join(",", $data) . "\n\r";
	}
	
	//check debtor code exist
	function is_debtor_code_exist($debtor_code){
		global $con;
		
		if(!$debtor_code) return false;
		
		$q1 = $con->sql_query("select id from debtor where code=".ms($debtor_code));
		$r1 = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		
		if($r1['id']) return $r1['id'];
		else return false;
	}
    
	//check sku item code exist
	function is_sku_item_code_exist($sku_item_code){
		global $con;
		
		if(!$sku_item_code) return false;
		
		$q1 = $con->sql_query("select id from sku_items where sku_item_code=".ms($sku_item_code));
		$r1 = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		
		if($r1['id']) return $r1['id'];
		else return false;
	}
	
    function show_result(){
        global $con, $smarty, $sessioninfo;
        
		$form = $_REQUEST;
		$file = $_FILES['update_csv'];
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
        $item_lists = $code_list = $err = $error_list = array();
        if(count($line) == count($this->headers)) {
            while($r = fgetcsv($f)){
				$error = array();
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
                
                $ins = array();
                $ins['code'] = strtoupper(trim($r[0]));
				$ins['item_code'] = strtoupper(trim($r[1]));
				$ins['price'] = mf($r[2]);

				if(!$ins['code'] && !$ins['item_code']) continue;
				$result['ttl_row']++;
                
                if(!$ins['code'])  $error[] = "Empty Code";
				if(!$ins['item_code'])  $error[] = "Empty Item Code";
				if($ins['price']){
					if(!is_numeric($ins['price'])) $error[] = "Price must numeric digit";
				}else{
					 $error[] = "Empty Price";
				}
				
				$debtor_code = $this->is_debtor_code_exist($ins['code']);
				if(!$debtor_code) $error[] = "Debtor Code ".$ins['code']." is not exist";
				
				$sku_item_code = $this->is_sku_item_code_exist($ins['item_code']);
				if(!$sku_item_code) $error[] = "SKU Item Code ".$ins['item_code']." is not exist";
				
                if($error)	$ins['error'] = join("<br />", $error);
                $item_lists[] = $ins;
				
				if($ins['error']){
					$error_list[] = $ins;
					$result['error_row']++;
				}
            }
			
            $ret = array();
			if($item_lists){
				$header = $this->headers;
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "debtor_".time().".csv";
				
				$fp = fopen("attachments/update_debtor_price/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/update_debtor_price/".$file_name, 0777);
				
				if(BRANCH_CODE == 'HQ'){
					$branches = array();
					$con->sql_query("select id, code from branch where active=1 order by sequence, code");
					while($r=$con->sql_fetchrow()){
						$branches[] = $r;
					}
					$con->sql_freeresult();
					$smarty->assign("branch", $branches);
				}
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
				
				// generate error list into CSV
				if($error_list) {
					$fp = fopen("attachments/update_debtor_price/invalid_".$file_name, 'w');
					fputcsv($fp, array_values($header));
					
					foreach($error_list as $r){
						if($r['error']){
							$r['error'] = str_replace("<br />", ",", $r['error']);
						}
						fputcsv($fp, $r);
					}
					fclose($fp);
					chmod("attachments/update_debtor_price/invalid_".$file_name, 0777);
				}
            }else{
				$err[] = "No data found on the file.";
			}
        }else{
			$err[] = "Column not match. Please re-check import file.";
		}
		
		$smarty->assign("err", $err);
		$smarty->assign("form", $form);
		$this->display();
    }
    
    function ajax_import_debtor_price(){
        global $con, $smarty, $sessioninfo, $appCore;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/update_debtor_price/".$form['file_name'])){
			die("File no found.");
			exit;
		}
        
        $f = fopen("attachments/update_debtor_price/".$form['file_name'], "rt");
		$line = fgetcsv($f);
        
        if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
        
        $ret = $error_list = array();
		$num_row = 0;
		while($r = fgetcsv($f)){
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
            
            $debtor_ins = $params = $branch_id_list = array();

			if(!$r[$error_index]) {
				$debtor_ins['code'] = $r[0];
				$debtor_ins['item_code'] = $r[1];
				$debtor_ins['price'] = $r[2];
				
				$params['debtor_id'] = $this->is_debtor_code_exist($debtor_ins['code']);
				$params['sid'] = $this->is_sku_item_code_exist($debtor_ins['item_code']);
				$params['price'] = mf($debtor_ins['price']);
				
				if(BRANCH_CODE != 'HQ') $branch_id_list[] = $sessioninfo['branch_id'];
				else $branch_id_list = array_values($form['branch_list']);
				
				$params['user_id'] = $sessioninfo['id'];
				foreach($branch_id_list as $key=>$bid){
					$params['bid'] = $bid;
					$result = $appCore->skuManager->updateDebtorPrice($params);
					$num_row += 1;
				}
				
			}else{
				$error_list[] = $r;
			}
        }
        
        if($error_list) {
			$fp = fopen("attachments/update_debtor_price/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/update_debtor_price/invalid_".$form['file_name'], 0777);
		}
		
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;

		print json_encode($ret);
		log_br($sessioninfo['id'], "IMPORT_DEBTOR_PRICE", 0, "Import Debtor Price by csv Successfully, Files Reference: ".$form['file_name'].$xtra_info);
    }
}

$IMPORT_DEBTOR = new IMPORT_DEBTOR("Import / Update Debtor Price by CSV");
?>