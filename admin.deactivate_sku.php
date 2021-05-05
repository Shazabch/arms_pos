<?php
/*
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999 && !privilege('ALLOW_IMPORT_DEACTIVATE_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ALLOW_IMPORT_DEACTIVATE_SKU', BRANCH_CODE), "/index.php");

class DEACTIVATE_SKU extends Module{
    var $headers = array(
		'1' => array("sku_item_code" => "ARMS CODE",
                     "mcode" => "MCODE",
                     "artno" => "ARTNO",
					 "link_code" => "OLD CODE")
    );
    
    var $sample = array(
		'1' => array(
			'sample_1' => array("280444500000", "9555453601539", "44510", "998750")
        )
    );
            
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
		if (!is_dir("attachments/deactivate_sku"))	check_and_create_dir("attachments/deactivate_sku");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
	
	function download_sample(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_deactivate_sku.csv");
		
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
		$file = $_FILES['import_csv'];	
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		
		$item_lists = $code_list = array();
		
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
						$ins['sku_item_code'] = strtoupper(trim($r[0]));
						$ins['mcode'] = trim($r[1]);
						$ins['artno'] = trim($r[2]);
						$ins['link_code'] = trim($r[3]);
						break;
				}
				
				$filter = "";
				if($ins['sku_item_code']){ // got sku item code
					$filter = "sku_item_code = ".ms($ins['sku_item_code']);
                }elseif($ins['mcode']){ // got mcode
					$filter = "mcode = ".ms($ins['mcode']);
				}elseif($ins['artno']){ // got artno
					$filter = "artno = ".ms($ins['artno']);
				}elseif($ins['link_code']){ // got old code
					$filter = "link_code = ".ms($ins['link_code']);
				}else{
                    $error[] = "Empty Code";
                }
				
				if($filter){
					$q1 = $con->sql_query("select * from sku_items where ".$filter);
					if($con->sql_numrows($q1) > 1){ // found more than 1 sku item matched
						$error[] = "Multiple SKU Items matched";
					}elseif($con->sql_numrows($q1) == 0){ // no result found
						$error[] = "No SKU Item matched";
					}
				}
				
				if($error)	$ins['error'] = join(', ', $error);
                
                $item_lists[] = $ins;
				
				if($ins['error']) $result['error_row']++;
				else $result['import_row']++;
			}
			
			$ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "deactivate_sku_".time().".csv";
				
				$fp = fopen("attachments/deactivate_sku/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/deactivate_sku/".$file_name, 0777);
				
				$smarty->assign("result", $result);
				$smarty->assign("file_name", $file_name);
				$smarty->assign("item_header", array_values($header));
				$smarty->assign("item_lists", $item_lists);
			}else{
				$smarty->assign("errm", "No data found on the file.");
			}
		}else {
			$smarty->assign("errm", "Column not match. Please re-check import file.");
		}
		
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}
	
	function ajax_deactivate_sku(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/deactivate_sku/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/deactivate_sku/".$form['file_name'], "rt");
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
			
			$upd = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]){
						$filter = "";
						if($r[0]){ // got sku item code
							$filter = "sku_item_code = ".ms($r[0]);
						}elseif($r[1]){ // got mcode
							$filter = "mcode = ".ms($r[1]);
						}elseif($r[2]){ // got artno
							$filter = "artno = ".ms($r[2]);
						}else{ // got old code
							$filter = "link_code = ".ms($r[3]);
						}
						$q1 = $con->sql_query("select * from sku_items where ".$filter);
						$si_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						
						if(!$si_info){ // just in case couldn't get the sku but it got record during show result
							$r[$error_index] = "No SKU Item matched";
							$error_list[] = $r;
							continue;
						}

						$upd = array();
						$upd['active'] = 0;
						$upd['lastupdate'] = "CURRENT_TIMESTAMP";
						$q1 = $con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id = ".mi($si_info['id']));
						$num = $con->sql_affectedrows($q1);
						
						if ($num > 0){
							$num_row++;
							$reason = "Deactivated by using CSV import";
							log_br($sessioninfo['id'], 'MASTERFILE_SKU_ACT', $si_info['id'], $reason);
						}
					}else {
						$error_list[] = $r;
					}
					break;
			}
		}
		
		if($error_list) {
			$fp = fopen("attachments/deactivate_sku/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/deactivate_sku/invalid_".$form['file_name'], 0777);
		}
						
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;

		print json_encode($ret);
	}
}

$DEACTIVATE_SKU = new DEACTIVATE_SKU("Deactivate SKU by CSV");
?>