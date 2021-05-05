<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ADMIN_UPDATE_SKU_MASTER_COST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ADMIN_UPDATE_SKU_MASTER_COST', BRANCH_CODE), "/index.php");

class ADMIN_UPDATE_SKU_MASTER_COST extends Module{
    var $headers = array(
		'1' => array("code" => "ARMS Code/MCode/Old Code",
                     "cost_price" => "Cost Price")
    );
    
    var $sample = array(
		'1' => array(
			array("10011", "9.99"),
			array("285070850000", "15.493"),
        )
    );
	var $attachment_path = "update_sku_master_cost";
            
    function __construct($title){
		$this->init();
 		parent::__construct($title);
	}
    
	function init(){
		global $smarty;
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/".$this->attachment_path))	check_and_create_dir("attachments/".$this->attachment_path);
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
    function _default(){
		$this->display();
	}
	
	function download_sample(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_UPDATE_SKU_MASTER_COST.csv");
		
		print join(",", array_values($this->headers[$_REQUEST['method']]));
		foreach($this->sample[$_REQUEST['method']] as $sample) {
			$data = array();
			foreach($sample as $d) {
				$data[] = $d;
			}
			print "\n\r".join(",", $data);
		}
	}
	
	function show_result(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		$file = $_FILES['csv_file'];	
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		$item_lists = array();
		$errm = array();
		$result = array();
		
		if(count($line) == count($this->headers[$form['method']])) {
			while($r = fgetcsv($f)){
				$error = array();
				$result['ttl_row']++;
				
				foreach($r as $tmp_row => $val){
					$r[$tmp_row] = utf8_encode(trim($val));
				}
				
				$ins = array();
				switch($form['method']) {
					case '1':
						$ins['code'] = trim($r[0]);
						$ins['cost_price'] = mf(trim($r[1]));
						break;
				}
				
				if($ins['code']) {
                    // check code from db
					$con->sql_query("select count(*) as ttl_match from sku_items where sku_item_code=".ms($ins['code'])." or mcode=".ms($ins['code'])." or link_code=".ms($ins['code']));
					$search_result = $con->sql_fetchassoc();
					$con->sql_freeresult();
                    
					if($search_result['ttl_match'] <= 0)	$error[] = "Item Not Found";
					elseif($search_result['ttl_match'] > 1)	$error[] = "Code is not unqiue, matched ".$search_result['ttl_match']." items";
                }else {
                    $error[] = "Empty Code";
                }
				
				if($ins['cost_price'] <= 0)	$error[] = "Cost Price must more than zero";
				
				if($error)	$ins['error'] = join(', ', $error);
                
                $item_lists[] = $ins;
				
				if($ins['error']){
					$result['error_row']++;
				}else{
					$result['import_row']++;
				}
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$time = time();
				$file_name = "UPDATE_SKU_MASTER_COST_".$time.".csv";
				$error_file_name = "error_".$file_name;
				
				$fp = fopen("attachments/".$this->attachment_path."/".$file_name, 'w');
				$fp_error = fopen("attachments/".$this->attachment_path."/".$error_file_name, 'w');
				
				fputcsv($fp, array_values($header));
				fputcsv($fp_error, array_values($header));
				
				foreach($item_lists as $r){
					fputcsv($fp, $r);
					if($r['error'])	fputcsv($fp_error, $r);
				}
				fclose($fp);
				fclose($fp_error);
				
				chmod("attachments/".$this->attachment_path."/".$file_name, 0777);
				chmod("attachments/".$this->attachment_path."/".$error_file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("error_file_name", $error_file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
			}else{
				$errm[] = "No data found on the file.";
				$smarty->assign("errm", $errm);
			}
		}else {
			$errm[] = "Column not match. Please re-check import file.";
			$smarty->assign("errm", "Column not match. Please re-check import file.");
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function download_file(){
		$f = trim($_REQUEST['f']);
		if(!$f){
			$error = "Filename is empty";
		}
		
		$filepath = "attachments/".$this->attachment_path."/".$f;
		if(!$error){
			if(!file_exists($filepath)){
				$error = "File '$f' Not Found";
			}
		}
		
		if($error){
			display_redir($_SERVER['PHP_SELF'], $this->page_title, $error);
			exit;
		}
		
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=$f");
		
		readfile($filepath);
		exit;
	}
	
	function ajax_start_import(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		
		$filepath = "attachments/".$this->attachment_path."/".$form['file_name'];
		$error_filepath = "attachments/".$this->attachment_path."/".$form['error_file_name'];
		
		if(!$form['file_name'] || !file_exists($filepath)){
			die("File no found.");
			exit;
		}
		
		$f = fopen($filepath, "rt");
		$line = fgetcsv($f);
		
		if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
		
		$ret = $error_list = array();
		$num_row = 0;
		while($r = fgetcsv($f)){
			// fix all text that contains special character to convert into utf8
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
			$imported = false;
			
			$ins = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
						$code = trim($r[0]);
						$ins['cost_price'] = mf(trim($r[1]));
						
						$filter = "sku_item_code=".ms($code)." or mcode=".ms($code)." or link_code=".ms($code);
						
						// check code from db
						$con->sql_query("select count(*) as ttl_match from sku_items where $filter");
						$search_result = $con->sql_fetchassoc();
						$con->sql_freeresult();
					
						if($search_result['ttl_match'] == 1){
							// check code from db
							$con->sql_query("select id, cost_price from sku_items where $filter order by id limit 1");
							$si = $con->sql_fetchassoc();
							$con->sql_freeresult();
						
							if($si){
								$sid = mi($si['id']);
								$con->sql_query("update sku_items set ".mysql_update_by_field($ins)." where id=$sid");
								$con->sql_query("update sku_items_cost set changed=1 where sku_item_id=$sid");
								$num_row++;
								$imported = true;
								log_br($sessioninfo['id'], 'UPDATE_SKU_COST', $sid, "SKU Items master cost update from ".mf($si['cost_price'])." to ".$ins['cost_price']);
							}
						}						
					}
					break;
			}
			
			if(!$imported){
				$error_list[] = $r;
			}			
		}
		
		if($error_list) {
			$fp = fopen($error_filepath, 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod($error_filepath, 0777);
		}
						
		if ($num_row > 0) {
			$ret['ok'] = 1;
		}else $ret['fail'] = 1;
		
		$ret = array();
		$ret['ok'] = 1;
		if($error_list){
			$ret['error_file_name'] = $form['error_file_name'];
		}	
		
		print json_encode($ret);
	}
}

$ADMIN_UPDATE_SKU_MASTER_COST = new ADMIN_UPDATE_SKU_MASTER_COST('Update SKU Master Cost');
?>