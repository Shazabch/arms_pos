<?php
/*
8/1/2016 5:37 PM Andy
- Fixed privilege checking.

11/3/2016 11:03 AM Andy
- Enhanced to able to import GST Start Date and GST Registration Number.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999 && !privilege('ALLOW_IMPORT_DEBTOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ALLOW_IMPORT_DEBTOR', BRANCH_CODE), "/index.php");

class IMPORT_DEBTOR extends Module{
    var $headers = array(
        '1' => array("code" => "Code",
                     "description" => "Name",
                     "company_no" => "Company No",
                     "term" => "Term",
                     "credit_limit" => "Credit Limit",
                     "phone_1" => "Phone #1",
                     "phone_2" => "Phone #2",
                     "phone_3" => "Fax",
                     "contact_person" => "Contact Person",
                     "contact_email" => "Contact Email",
                     "address_1" => "Address #1",
                     "address_2" => "Address #2",
                     "address_3" => "Address #3",
                     "address_4" => "Address #4",
                     "area" => "Area",
                     "default_mprice" => "Default MPrice",
					 "gst_start_date" => "GST Start Date",
					 "gst_register_no" => "GST Registration Number"
					 )
    );
    
    var $sample = array(
		'1' => array(
            array("B101", "ETHIN TRADING", "ETH86534", "25", "10000", "04-5896541", "012-4698325", "04-5896542", "MR ENG", "sales@ethin.com", "34 JALAN INDUSTRI JURU", "INDUSTRI JURU FASA 4", "13600 PERAI", "MALAYSIA", "", "member1"),
			array("B102", "Test TRADING", "TEST1234", "0", "10000", "", "", "04-5556542", "MR Tan", "sales@test.com", "1 JALAN Testing", "taman Testing 3", "PERAI", "MALAYSIA","", "", "2015-04-01", "21343556")
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
		if (!is_dir("attachments/debtor_import"))	check_and_create_dir("attachments/debtor_import");
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
	}
    
    function download_sample_debtor(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_debtor.csv");
		
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
        global $con, $smarty, $config;
        
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
                        $ins['code'] = strtoupper(trim($r[0]));
						$ins['description'] = strtoupper(trim($r[1]));
						$ins['company_no'] = strtoupper(trim($r[2]));
						$ins['term'] = trim($r[3]);
                        $ins['credit_limit'] = trim($r[4]);
						$ins['phone_1'] = trim($r[5]);
						$ins['phone_2'] = trim($r[6]);
						$ins['phone_3'] = trim($r[7]);
						$ins['contact_person'] = strtoupper(trim($r[8]));
						$ins['contact_email'] = trim($r[9]);
						$ins['address_1'] = strtoupper(trim($r[10]));
						$ins['address_2'] = strtoupper(trim($r[11]));
						$ins['address_3'] = strtoupper(trim($r[12]));
						$ins['address_4'] = strtoupper(trim($r[13]));
                        $ins['area'] = trim($r[14]);
                        $ins['debtor_mprice_type'] = trim($r[15]);
                        $ins['gst_start_date'] = trim($r[16]);
                        $ins['gst_register_no'] = trim($r[17]);
                        break;
                }
                
                if($ins['code']) {
                    // check code from db for duplication
					$con->sql_query("select * from debtor where code = ".ms($ins['code'])." limit 1");
					if($con->sql_numrows() > 0) $error[] = 'Duplicate Code';
					$con->sql_freeresult();
                    
                    // check code from import file for duplication
					if(!in_array($ins['code'], $code_list))	$code_list[] = $ins['code'];
					else {
						if(!in_array('Duplicate Code', $error))	$error[] = 'Duplicate Code';
					}
                }else {
                    $error[] = "Empty Code";
                }
				
				if($ins['gst_register_no']){
					$ins['gst_start_date'] = date("Y-m-d", strtotime($ins['gst_start_date']));
					if(strtotime($ins['gst_start_date']) < strtotime($config['global_gst_start_date'])){
						$ins['gst_start_date'] = $config['global_gst_start_date'];
					}
				}else{
					$ins['gst_start_date'] = "";
				}
                
                if(!$ins['description'])	$error[] = 'Empty Company Name';
                
                if($error)	$ins['error'] = join(', ', $error);
                
                $item_lists[] = $ins;
				
				if($ins['error'])	$result['error_row']++;
				else				$result['import_row']++;
            }
            
            $ret = array();
			if($item_lists){
				$header = $this->headers[$form['method']];
				if($result['error_row'] > 0)	$header[] = 'Error';
				
				$file_name = "debtor_".time().".csv";
				
				$fp = fopen("attachments/debtor_import/".$file_name, 'w');
				fputcsv($fp, array_values($header));
				foreach($item_lists as $r){
					fputcsv($fp, $r);
				}
				fclose($fp);
				
				chmod("attachments/debtor_import/".$file_name, 0777);
				
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
    
    function ajax_import_debtor(){
        global $con, $sessioninfo;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/debtor_import/".$form['file_name'])){
			die("File no found.");
			exit;
		}
        
        $f = fopen("attachments/debtor_import/".$form['file_name'], "rt");
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
            
            $debtor_ins = array();
            switch ($form['method']) {
				case '1':
					if(!$r[$error_index]) {
                        $debtor_ins['code'] = $r[0];
						$debtor_ins['description'] = $r[1];
						$debtor_ins['company_no'] = $r[2];
						$debtor_ins['term'] = $r[3];
                        $debtor_ins['credit_limit'] = $r[4];
						$debtor_ins['phone_1'] = $r[5];
						$debtor_ins['phone_2'] = $r[6];
						$debtor_ins['phone_3'] = $r[7];
						$debtor_ins['contact_person'] = $r[8];
						$debtor_ins['contact_email'] = $r[9];
						$debtor_ins['address'] = $r[10];
						if($r[11])	$debtor_ins['address'] .= "\n".$r[11];
						if($r[12])	$debtor_ins['address'] .= "\n".$r[12];
						if($r[13])  $debtor_ins['address'] .= "\n".$r[13];
                        $debtor_ins['area'] = $r[14];
                        $debtor_ins['debtor_mprice_type'] = $r[15];
                        $debtor_ins['gst_start_date'] = $r[16];
                        $debtor_ins['gst_register_no'] = $r[17];
                        $debtor_ins['active'] = 1;
                        $con->sql_query("insert into debtor ".mysql_insert_by_field($debtor_ins));
                        $num = $con->sql_affectedrows();
                        
                        if ($num > 0)	$num_row++;
                    }else{
                        $error_list[] = $r;
                    }
                    break;
            }
        }
        
        if($error_list) {
			$fp = fopen("attachments/debtor_import/invalid_".$form['file_name'], 'w');
			fputcsv($fp, array_values($line));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/debtor_import/invalid_".$form['file_name'], 0777);
		}
						
		if ($num_row > 0) {
			if($error_list)	$ret['partial_ok'] = 1;
			else	$ret['ok'] = 1;
		}else $ret['fail'] = 1;

		print json_encode($ret);
    }
}

$IMPORT_DEBTOR = new IMPORT_DEBTOR("Import Debtor");
?>