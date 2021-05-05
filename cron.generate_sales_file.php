<?php
/*
1/11/2016 6:03 PM Andy
QBMall format changes
- Remove leading 0 from month and day.

1/15/2016 10:44 AM Andy
- Fix qbmall filename.

5/26/2016 4:45 PM Andy
- Change qbmall ftp transfer method from ASCII to Binary.

5/8/2017 8:59 AM Khausalya
- Enhanced changes from RM to use config setting.

10/27/2017 11:16 AM Andy
- Added KCM format.

11/24/2017 3:41 PM Andy
- Change KCM format to version 1.5.

12/22/2017 10:09 AM Andy
- Added Atria Mall format.

1/23/2018 2:49 PM Justin
- Enhanced always check file size and regenerate sales if file size is zero.

1/25/2018 3:51 PM Andy
- Fixed to not generate file when sales amount is zero - Atria format.

7/27/2018
- Added Sunway Giza Mall format.

7/10/2019 1:34 PM Andy
- Added new Queens Bay Mall 2 Format.

9/7/2020 4:47 PM Andy
- Added KLCC Mall format.
*/
if(php_sapi_name() == 'cli'){
	define('TERMINAL',1);
	ob_end_clean();
}

require_once('include/common.php');
require_once('counter_collection.include.php');

//ini_set('memory_limit', '512M');
set_time_limit(0);
//error_reporting (E_ALL ^ E_NOTICE);

$folder_name = 'generated_files';
if(!is_dir($folder_name)){
	check_and_create_dir($folder_name);
}
if(defined('TERMINAL')){	// terminal

	if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
		@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps x\n";
	}else{
		@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps ax\n";
	}

	if (count($exec)>1)
	{
		print date("[H:i:s m.d.y]")." Another process is already running\n";
		print_r($exec);
		exit;
	}

	$arg = $_SERVER['argv'];
	$date = date("Y-m-d", strtotime("-1 day"));

	switch($arg[1]){
		// php cron.generate_sales_file.php -type=tpp -date=2013-12-12 -branch=hq
		case 'qbmall':	//	TPProperty

			if(!$config['qbmall_setting'])	die("No QB Mall Setting.");

			$QBMALL=new QBMALL();
			$QBMALL->setDate($date);
			$QBMALL->start();
			break;
		// php cron.generate_sales_file.php kcm -branch=dev -recent_day=3
		case 'kcm':	//	Kuantan City Mall

			if(!$config['kcm_setting'])	die("No Kuantan City Mall Setting.");

			$KCM = new KCM();
			$KCM->checkArgs($arg);
			$KCM->start();
			break;
		// php cron.generate_sales_file.php atria -branch=dev -recent_day=3
		// php cron.generate_sales_file.php atria -branch=dev -date=2017-11-15
		// php cron.generate_sales_file.php atria -branch=dev -date=1999-01-01
		case 'atria':	// Atria Mall
			$Atria = new Atria();
			$Atria->checkArgs($arg);
			$Atria->start();
			break;
		case 'sunway_giza':	// php cron.generate_sales_file.php sunway_giza -branch=dev -recent_day=3
			include_once('mall_api/sunway_giza/mall_api.php');
			break;
		case 'qbmall2':	// php cron.generate_sales_file.php qbmall2 -branch=dev -recent_day=3
			include_once('mall_api/qbmall/mall_api.php');
			break;
		case 'klcc':	// php cron.generate_sales_file.php klcc -branch=dev -recent_day=3
			include_once('mall_api/klcc/mall_api.php');
			break;
		default:
			die("Invalid Export Type. (".$arg[1].")\n");
			break;
	}

	//die("TERMINAL mode is not supported.\n");
	// check if myself is running, exit if yes
	/*if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
		@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps x\n";
	}else{
		@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps ax\n";
	}

	if (count($exec)>1)
	{
		print date("[H:i:s m.d.y]")." Another process is already running\n";
		print_r($exec);
		exit;
	}

	$available_type = array(
		'tpp'	// TPProperty
	);

	$arg = $_SERVER['argv'];
	array_shift($arg);	// remove first param

	//$export_type = $available_type[0];
	$date = date("Y-m-d", strtotime("-1 day"));

	while($a = array_shift($arg)){
		if(preg_match('/^-type=/', $a)){	// change type
			$tmp = str_replace('-type=', '', $a);
			if(!$tmp || !in_array($tmp, $available_type))	die("Invalid -type. Either (".join(',', $available_type).")\n");

			$export_type = $tmp;
		}elseif(preg_match('/^-date=/', $a)){	// change date
			$tmp = date("Y-m-d", strtotime(str_replace('-date=', '', $a)));
			if(!$tmp)	die("Invalid Date.\n");
			if(date("Y", strtotime($tmp))<2010)	die("Date $tmp is invalid. Date must start at 2010-01-01.\n");
			$date = $tmp;
		}elseif(preg_match('/^-branch=/', $a)){
			$tmp = trim(str_replace('-branch=', '', $a));
			if(!$tmp)	die("Invalid branch.\n");
			$selected_branch_code = $tmp;
		}
		else{
			die("Unknown option $a\n");
		}
	}

	switch($export_type){
		// php cron.generate_sales_file.php -type=tpp -date=2013-12-12 -branch=hq
		case 'tpp':	//	TPProperty
			$TPPROPERTY = new TPPROPERTY();
			$TPPROPERTY->setDate($date);
			if($selected_branch_code)	$TPPROPERTY->setSelectedBranch($selected_branch_code);
			$TPPROPERTY->start();
			break;
		default:
			die("Invalid Export Type. ($export_type)\n");
			break;
	}*/
}else{	// WEB
	switch($_REQUEST['type']){
		// cron.generate_sales_file.php?type=tpp&a=generate_file&date=2013-12-12&branch_code=hq&counter_name=001
    // cron.generate_sales_file.php?type=tpp&a=generate_file&date=2014-01-01&branch_code=JPO&counter_name=101
		case 'tpp':
			switch($_REQUEST['a']){
				case 'generate_file':
					$TPPROPERTY = new TPPROPERTY();
					$TPPROPERTY->setWebBased(true);
					$TPPROPERTY->setSelectedBranch($_REQUEST['branch_code']);
					$TPPROPERTY->setDate($_REQUEST['date']);
					$TPPROPERTY->setCounterName($_REQUEST['counter_name']);
					$TPPROPERTY->start();
					break;
			}

			break;
		case 'qbmall':
			/*
				cron.generate_sales_file.php?type=qbmall&a=generate_file&date=2016-01-03
				cron.generate_sales_file.php?type=qbmall&a=generate_file&date=2016-05-22
			*/
			if(!$config['qbmall_setting'])	die("No QB Mall Setting.");

			switch($_REQUEST['a']){
				case 'generate_file':
					$QBMALL=new QBMALL();
					$QBMALL->setWebBased(true);
					$QBMALL->setDate($_REQUEST['date']);
					$QBMALL->start();
				break;
				default:
					die("Invalid Request");
			}
			break;
		default:
			die("Invalid Export Type. (".$_REQUEST['type'].")\n");
			break;
	}

}

class QBMALL{
	var $branch_list = array();
	var $sales_date = '';
	var $generate_info_filename = '%s_%s_H_%s.csv';
	var $web_based = false;
	var $verbose = true;
	var $selected_bid = 0;
	var $folder_path="qbmall_data/";

	function __construct()
	{
		if(!is_dir($this->folder_path)) check_and_create_dir($this->folder_path);
	}

	function start(){
		global $con, $config;

		if(!$config['qbmall_setting'])	die("No QB Mall Setting.");

		$header=array("EID","TxnYear","TxnMonth","TxnDate","TxnHour","TxnAmount","TxnVoid");

		foreach($config['qbmall_setting'] as $branch_code=>$settings){
			$folder_path=$this->folder_path.$branch_code."/";

			if(!is_dir($folder_path)) check_and_create_dir($folder_path);

			$q1 = $con->sql_query("select id,code from branch where active=1 and code=".ms($branch_code));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			$sql="select branch_id, date, EXTRACT(HOUR FROM end_time) as hour, round(sum(amount),2) as amount, cancel_status
							from pos
							where branch_id=".$r['id']."
							and date=".ms($this->export_date)."
							group by hour,cancel_status
							order by hour";

			$q2=$con->sql_query($sql);

			$year=date('Y',strtotime($this->export_date));
			$month=mi(date('m',strtotime($this->export_date)));
			$day=mi(date('d',strtotime($this->export_date)));

			$list=array();
			while($r = $con->sql_fetchassoc($q2)){
				$idx=$r['date']."-".$r['hour'];
				if(!isset($list[$idx])){
					$list[$idx]=array();
					$list[$idx]["EID"]=$settings['EID'];
					$list[$idx]["TxnYear"]=$year;
					$list[$idx]["TxnMonth"]=$month;
					$list[$idx]["TxnDate"]=$day;
					$list[$idx]["TxnHour"]=$r['hour'];
					$list[$idx]["TxnAmount"]=0;
					$list[$idx]["TxnVoid"]=0;
				}

				if($r['cancel_status']){
					$list[$idx]["TxnVoid"]+=$r['amount'];
				}
				else{
					$list[$idx]["TxnAmount"]+=$r['amount'];
				}
			}
			$con->sql_freeresult($q2);

			if(!empty($list)){
				foreach($list as $data){
					$filename=$folder_path.$data["TxnYear"]."/";
					if(!is_dir($filename)) check_and_create_dir($filename);
					$filename=$filename.$data["TxnMonth"]."/";
					if(!is_dir($filename)) check_and_create_dir($filename);
                    $filename=$filename.$data["TxnDate"]."/";
                    if(!is_dir($filename)) check_and_create_dir($filename);

					$filename=sprintf($filename.$this->generate_info_filename,$data["EID"],substr($data["TxnYear"],-2,2).sprintf("%02d",$data["TxnMonth"]).sprintf("%02d",$data["TxnDate"]),$data["TxnHour"]);

					if(file_exists($filename)) unlink($filename);
					$fp = fopen($filename, 'w');

					//my_fputcsv($fp, $header);
					//my_fputcsv($fp, $data);
					//my_fputcsv($fp, array("END"));
					fwrite($fp, implode(",",$header)."\r\n");
					fwrite($fp, implode(",",$data)."\r\n");
					fwrite($fp, "END");

					fclose($fp);
					chmod($filename,0777);

					$this->upload_file($filename,$settings['server_ftp_info']);
				}
			}
		}
	}

	function setDate($date){
		$this->export_date = $date;
	}

	function setWebBased($wb){
		$this->web_based = $wb ? true : false;
		if($this->web_based)	$this->verbose = false;
	}

	function setSelectedBranch($branch_code){
		if(!$branch_code)	die("No Branch Code.");

		$selected_bid = 0;
		foreach($this->branch_list as $bid=>$b){
			if(strtolower($b['code']) == strtolower($branch_code)){
				$selected_bid = $bid;
				break;
			}
		}

		if(!$selected_bid)	die("Invalid Branch Code ($branch_code)");

		$this->selected_bid = $selected_bid;
	}

	private function upload_file($filename,$server_ftp_info){
		global $config;

		if(!$filename)	die("No File to Upload.");

		print "Connecting to QB Mall Server. . .\n";

		$file = $filename;
		$remote_file = basename($file);

		// set up basic connection
		$conn_id = ftp_connect($server_ftp_info['ip'],$server_ftp_info['port']);
		if(!$conn_id){
			print "QB Mall Server Cannot be connect.\n";
			return;
		}

		// login with username and password
		$login_result = ftp_login($conn_id, $server_ftp_info['username'], $server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login to QB Mall Server.\n";
			ftp_close($conn_id);
			return;
		}
        ftp_pasv($conn_id, true);
		ftp_chdir($conn_id, $server_ftp_info['path']);

		// upload a file
		print "Sending $file -> $remote_file\n";
		if (ftp_put($conn_id, $remote_file, $file, FTP_BINARY)) {
			echo "successfully uploaded $file\n";
		} else {
			$error=error_get_last();
			echo "There was a problem while uploading $file\n";
			print_r($error);
		}

		// close the connection
		ftp_close($conn_id);
	}
}

class TPPROPERTY {
	var $branch_list = array();
	var $sales_date = '';
	var $generate_info_filename = 'generate_info.txt';
	var $web_based = false;
	var $verbose = true;
	var $selected_bid = 0;
	var $selected_counter_id = 0;

	function __construct()
	{
		global $con, $config;

		// get branch list
		$this->branch_list = array();
		$q1 = $con->sql_query("select id,code from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc($q1)){
			// get counter list
			$q2 = $con->sql_query("select id,network_name from counter_settings where branch_id=".mi($r['id'])." and active=1 order by network_name");
			while($c = $con->sql_fetchassoc($q2)){
				$r['counter_list'][$c['id']] = $c;
			}
			$con->sql_freeresult($q2);

			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
	}

	function setDate($date){
		$this->sales_date = $date;
	}

	function setWebBased($wb){
		$this->web_based = $wb ? true : false;
		if($this->web_based)	$this->verbose = false;
	}

	function setSelectedBranch($branch_code){
		if(!$branch_code)	die("No Branch Code.");

		$selected_bid = 0;
		foreach($this->branch_list as $bid=>$b){
			if(strtolower($b['code']) == strtolower($branch_code)){
				$selected_bid = $bid;
				break;
			}
		}

		if(!$selected_bid)	die("Invalid Branch Code ($branch_code)");

		$this->selected_bid = $selected_bid;
	}

	function setCounterName($counter_name){
		if(!$this->selected_bid)	die("You must specified branch first.");

		if(!$this->branch_list[$this->selected_bid])  die("Branch ID #".$this->selected_bid." does not have counter.");

		$counter_name = strtolower(trim($counter_name));
		if(!$counter_name)	die("No counter name.");

		$selected_counter_id = 0;

		foreach($this->branch_list[$this->selected_bid]['counter_list'] as $counter_id => $counter_info){
			if(strtolower($counter_info['network_name']) == strtolower($counter_name)){
				$selected_counter_id = $counter_id;
				break;
			}
		}

		if(!$selected_counter_id)	die("Invalid counter name.");

		$this->selected_counter_id = $selected_counter_id;
	}

	function start(){
		global $con, $config, $mm_discount_col_value, $folder_name;

		if(!$config['tpproperty_setting'])	die("No TPProperty Setting.");

		$date = $this->sales_date;
		if(!$date)	die("No date is given.");
		if(!$this->selected_bid)	die("No branch is given.");
		if(!$this->selected_counter_id)	die("No Counter is given.");

		$ret = array();
		$ret['date'] = $date;

		//print "Date: $date";
		$bid = $this->selected_bid;
		$b = $this->branch_list[$bid];
		if(!$b)	die("Cant get branch info.");

		$ret['branch_code'] = $b['code'];
		$ret['branch_id'] = $bid;

		// tenant no (by branch)
		$tenant_no = trim($config['tpproperty_setting']['branch_info'][$b['code']]['tenant_no']);
		if(!$tenant_no){
			die("No TENANT NO.for this branch.(".$b['code'].")");
		}

		// counter
		$counter_id = $this->selected_counter_id;
		$counter_info = $b['counter_list'][$counter_id];

		if(!$counter_info)	die("Cant get counter info.");

		$ret['counter_name'] = $counter_info['network_name'];
		$ret['counter_id'] = $counter_id;

		/////////////////// START ///////////////////
		$file_str = '';

		// counter file information
		$info = array(
			'file_stat' => 'OPENED',
			'tenant_no' => $tenant_no,
			'file_no' => 0,
			'counter_name' => $counter_info['network_name'],
			'sales_date' => $date,
			'shilf_no' => 0,
			'last_receipt_no' => '',
			'last_cashier' => '',
			'pos_row_no' => 0
		);

		// get POS
		$q_pos = $con->sql_query("select * from pos where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and cancel_status=0 order by id");

		if($con->sql_numrows($q_pos)>0){	// got POS
			// need to check the max file_no here
			//$info['file_no']++;
			$info['file_no'] = $this->get_new_file_no($info);

			// COUNTER HEADER
			$header_printed = false;
			$last_cashier_id = 0;

			while($pos = $con->sql_fetchassoc($q_pos)){
				$info['last_receipt_no'] = $pos['receipt_no'];
				$info['pos_row_no']++;

				// when change cashier, increase shift no
				if($last_cashier_id != $pos['cashier_id']){
					$info['shilf_no']++;
					$info['last_cashier'] = $pos['cashier_id'];

					$last_cashier_id = $pos['cashier_id'];

				}

				if(!$header_printed){
					$file_str = $this->add_line($file_str, 1, $info, $counter_info);	// print header
					$header_printed = true;
				}

				// POS HEADER
				$file_str = $this->add_line($file_str, 101, $info, $pos);

				$q_pi = $con->sql_query("select pi.*, if(dept.id is null,'UNCAT',dept.description) as dept_name, if(cat3.id is null,'UNCAT',cat3.description) as cat3_name, si.sku_item_code
				from pos_items pi
				left join sku_items si on si.id=pi.sku_item_id
				left join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join category_cache cc on cc.category_id=c.id
				left join category dept on dept.id=cc.p2
				left join category cat3 on cat3.id=cc.p3
				where pi.branch_id=".mi($pos['branch_id'])." and pi.date=".ms($pos['date'])." and pi.counter_id=".mi($pos['counter_id'])." and pi.pos_id=".mi($pos['id'])."
				order by pi.item_id");

				while($pi = $con->sql_fetchassoc($q_pi)){
					// POS ITEMS
					$file_str = $this->add_line($file_str, 111, $info, $pi);
				}
				$con->sql_freeresult($q_pi);

				$q_pp = $con->sql_query("select pp.* from pos_payment pp where pp.branch_id=".mi($pos['branch_id'])." and pp.date=".ms($pos['date'])." and pp.counter_id=".mi($pos['counter_id'])." and pp.pos_id=".mi($pos['id'])." and pp.adjust=0");
				$pp_data = array(
					'total_sales' => $pos['amount'],
					'total_discount' => 0,
				);

				$pp_list = array();
				while($pp = $con->sql_fetchassoc($q_pp)){
					if(in_array($pp['type'], array('Discount', $mm_discount_col_value))){
						$pp_data['total_discount'] += $pp['amount'];
					}elseif($pp['type'] == 'Rounding'){
						$pp_date['rounding'] += $pp['amount'];
					}
					$pp_list[] = $pp;
				}
				$con->sql_freeresult($q_pp);

				// TOTAL SALES & DISCOUNT
				$file_str = $this->add_line($file_str, 121, $info, $pp_data);

				if($pp_list){
					foreach($pp_list as $pp){
						if(in_array($pp['type'], array('Discount', $mm_discount_col_value, 'Rounding'))){
							continue;
						}

						$currency_arr = pp_is_currency($pp['remark'], $pp['amount']);
			            if($currency_arr['is_currency']){   // it is foreign currency
			            	$pp['is_currency'] = true;
							$pp['currency_amt'] = round($currency_arr['currency_amt'], 2);
							$pp['currency_rate'] = mf($currency_arr['currency_rate']);
							$pp['rm_amt'] = round($currency_arr['rm_amt'], 2);
						}

						// POS PAYMENT
						$file_str = $this->add_line($file_str, 131, $info, $pp);
					}
				}

				// CHANGES
				$tmp = array(
					'is_changes' => true,
					'type' => 'Cash',
					'amount' => $pos['amount_change']
				);
				$file_str = $this->add_line($file_str, 131, $info, $tmp);
			}
		}

		$con->sql_freeresult($q_pos);

		;
		if($file_str){
			$info['file_stat'] = 'CLOSED';
			// COUNTER FOOTER
			$file_str = $this->add_line($file_str, 1, $info, $counter_info);

			/*if($this->web_based){
				print str_replace("\n", "<br />", $file_str);
			}else{
				print $file_str;
			}*/


			$file_name = $info['tenant_no']."_".$counter_info['network_name']."_".$info['file_no']."_".date("ymdHi").".txt";
			file_put_contents($folder_name."/".$file_name, $file_str);

			if($this->web_based){
				$ret['data'] = $file_str;
			}

			//$this->upload_file($bid, $folder_name."/".$file_name);
		}else{
			die("No POS.");
		}

		//print_r($ret);

		if($this->web_based){
			print serialize($ret);
		}
	}

	function add_line($str, $cmd_code, $info, $params){
		global $config;
		if(!$cmd_code || !$info || !$params)	die("Invalid Input.\n");

		switch($cmd_code){
			case 1:
				// COUNTER HEADER / FOOTER
				$data = array(
					'OPENED' => $info['file_stat'],
					'TENANT_NO' => $info['tenant_no'],
					'POS_NO' => $info['counter_name'],
					'RECEIPT_NO' => $info['pos_row_no'], // yesterday last pos or today last pos
					'TRAN_FILE_NO' => $info['file_no'],
					'DATE' => date("Ymd"),
					'TIME' => date("H:i:s"),
					'USER_ID' => $info['last_cashier'], // yesterday last cashier or today last cashier
					'SALE_DATE' => date("Ymd", strtotime($info['sales_date']))
				);
				$str .= "1|".join("|", $data)."\n";
				break;
			case 101:
				// POS HEADER
				$data = array(
					'RECEIPT_NO' => $info['pos_row_no'],
					'SHIFT_NO' => $info['shilf_no'],
					'DATE' => date("Ymd", strtotime($params['end_time'])),
					'TIME' => date("H:i:s", strtotime($params['end_time'])),
					'USER_ID' => $params['cashier_id'],
					'MANUAL_RECEIPT' => $params['receipt_no'],
					'REFUND_RECEIPT' => '',
					'REASON_CODE' => '',
					'SALESMAN_CODE' => '',
					'TABLE_NO' => '',
					'CUST_COUNT' => '',
					'TRAINING' => 'N',
					'TRAN_STATUS' => 'SALE'
				);
				$str .= "101|".join("|", $data)."\n";
				break;
			case 111:
				// POS ITEMS
				$data = array(
					'ITEM_CODE' => $params['sku_item_code'],
					'ITEM_QTY' => $params['qty'],
					'ORG_PRICE' => round($params['price']/$params['qty'], 2),
					'NEW_PRICE' => round(($params['price'] - $params['discount'])/$params['qty'], 2),
					'ITEM_FLAG' => $params['discount'] ? 'P' : '',
					'TAX_CODE' => 'G',
					'DISCOUNT_CODE' => '',
					'DISCOUNT_AMT' => round($params['discount'], 2),
					'ITEM_DEPT' => substr(preg_replace(array('/\s+/','/\|/'), '', $params['dept_name']),0,8),
					'ITEM_CATG' => substr(preg_replace(array('/\s+/','/\|/'), '', $params['cat3_name']),0,8),
					'LABEL_KEYS' => '',
					'ITEM_COMM' => '',
					'ITEM_NSALES' => round($params['price'] - $params['discount'], 2),
					'DISCOUNT_BY' => round($params['discount'], 2),
					'DISCOUNT_SIGN' => '$',
					'ITEM_STAX' => '0',
					'PLU_CODE' => ''
				);
				$str .= "111|".join("|", $data)."\n";
				break;
			case 121:
				// TOTAL SALES & DISCOUNT
				$data = array(
					'SALES' => round($params['total_sales'],2),
					'DISCOUNT' => round($params['total_discount'],2),
					'CESS' => '',
					'CHARGES' => '',
					'TAX' => '',
					'TAX_TYPE' => 'E',
					'EXEMPT_GST' => 'Y',
					'DISCOUNT_CODE' => '',
					'OTHER_CHG' => '',
					'DISCOUNT_PER' => '',
					'ROUNDING_AMT' => round($params['rounding'],2)
				);
				$str .= "121|".join("|", $data)."\n";
				break;
			case 131:
				// POS PAYMENT
				$data = array(
					'TYPE' => $params['is_changes'] ? 'C' : 'T',
					'PAYMENT_NAME' => $params['type'],
					'CURR_CODE' => $params['is_currency'] ? $params['type'] : $config["arms_currency"]["code"],
					'BUY_RATE' => $params['is_currency'] ? $params['currency_rate'] : 1,
					'AMOUNT' => round($params['amount'], 2),
					'REMAKRS_1' => $params['remark'],
					'REMARKS_2' => '',
					'BASE_AMT' => $params['is_currency'] ? round($params['rm_amt'],2) : round($params['amount'], 2)
				);
				$str .= "131|".join("|", $data)."\n";
				break;
			default:
				die("Invalid CMD_CODE ($cmd_code)\n");
		}

		return $str;
	}

	private function get_new_file_no($info){
		global $folder_name;

		$new_file_no = 1;
		$arr = array();

		if(file_exists($folder_name."/".$this->generate_info_filename)){
			$arr = unserialize(file_get_contents($folder_name."/".$this->generate_info_filename));
		}

		if($arr){
			$new_file_no = mi($arr[$info['tenant_no']][$info['counter_name']]['file_no'])+1;
		}

		if($new_file_no>9999)	$new_file_no = 1;

		$arr[$info['tenant_no']][$info['counter_name']]['file_no'] = $new_file_no;
		file_put_contents($folder_name."/".$this->generate_info_filename, serialize($arr));
		@chmod($folder_name."/".$this->generate_info_filename, 0777);

		return $new_file_no;
	}

	private function upload_file($bid, $filename){
		global $config;

		if(!$filename)	die("No File to Upload.");
		$bcode = $this->branch_list[$bid]['code'];
		if(!$bcode){
			print "No Branch Code.";
			return;
		}
		if(!$config['tpproperty_setting']['branch_info'][$bcode]['server_ftp_info']){
			print "No FTP SERVER to upload.";
			return;
		}

		print "Connecting to TPP Server. . .";

		$file = basename($filename);
		$remote_file = $file;

		// set up basic connection
		$conn_id = ftp_connect($config['tpproperty_setting']['branch_info'][$bcode]['server_ftp_info']['ip']);
		if(!$conn_id){
			print "TPP Server Cannot be connect.";
			return;
		}

		// login with username and password
		$login_result = ftp_login($conn_id, $config['tpproperty_setting']['branch_info'][$bcode]['server_ftp_info']['username'], $config['tpproperty_setting']['branch_info'][$bcode]['server_ftp_info']['pass']);
		if(!$login_result){
			print "Failed to login to TPP Server.";
			return;
		}
		// upload a file
		print "Sending $file. . .";
		if (ftp_put($conn_id, $remote_file, $file, FTP_ASCII)) {
			echo "successfully uploaded $file";
		} else {
			echo "There was a problem while uploading $file";
		}

		// close the connection
		ftp_close($conn_id);
	}
};

class KCM{
	var $branch_info = array();
	var $generate_info_filename = 'KCM_%s_%s.csv';
	var $bid = 0;
	var $folder_path="kcm_data/";
	var $date_from;
	var $date_to;
	var $regen = false;
	
	// FTP
	var $conn_id;
	
	function __construct()
	{
		if(!is_dir($this->folder_path)) check_and_create_dir($this->folder_path);
	}
	
	function checkArgs($arg){
		global $con, $config;
		
		if(!$config['kcm_setting'])	die("No Kuantan City Mall Setting.\n");
		
		if(!isset($arg) || count($arg)<=2)	die("Arguments Required.\n");
		
		// remove 1st and 2nd arguments.
		$a = array_shift($arg);
		$a = array_shift($arg);
		
		while($a = array_shift($arg)){
			if(preg_match('/^-date=/', $a)){	// date
				$tmp = date("Y-m-d", strtotime(str_replace('-date=', '', $a)));
				if(!$tmp)	die("Invalid Date.\n");
				if(date("Y", strtotime($tmp))<2010)	die("Date $tmp is invalid. Date must start at 2010-01-01.\n");
				$date = $tmp;
			}elseif(preg_match('/^-date_from=/', $a)){	// date_from
				$tmp = date("Y-m-d", strtotime(str_replace('-date_from=', '', $a)));
				if(!$tmp)	die("Invalid Date From.\n");
				if(date("Y", strtotime($tmp))<2010)	die("Date $tmp is invalid. Date must start at 2010-01-01.\n");
				$date_from = $tmp;
			}elseif(preg_match('/^-date_to=/', $a)){	// date_to
				$tmp = date("Y-m-d", strtotime(str_replace('-date_to=', '', $a)));
				if(!$tmp)	die("Invalid Date To.\n");
				if(date("Y", strtotime($tmp))<2010)	die("Date $tmp is invalid. Date must start at 2010-01-01.\n");
				$date_to = $tmp;
			}elseif(preg_match('/^-branch=/', $a)){	// branch
				$tmp = trim(str_replace('-branch=', '', $a));
				if(!$tmp)	die("Invalid branch.\n");
				$bcode = $tmp;
			}elseif(preg_match('/^-yesterday$/', $a)){	// use yesterday
				$date = date("Y-m-d", strtotime("-1 day"));
			}elseif(preg_match('/^-regen$/', $a)){	// regenerate
				$this->regen = true;
			}elseif(preg_match('/^-recent_day=/', $a)){	// use yesterday
				$num = mi(str_replace('-recent_day=', '', $a));
				if($num<=0)	die("Recent Day must more than zero.\n");
				
				$date_to = date("Y-m-d", strtotime("-1 day"));
				$date_from = date("Y-m-d", strtotime("-".$num." day"));
			}
			else{
				die("Unknown option $a\n");
			}
		}
		
		// check branch
		if(!$bcode){
			die("Please provide branch code by using -branch=\n");
		}
		$con->sql_query("select id,code from branch where code=".ms($bcode));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp){
			die("Invalid branch code '$bcode'.\n");
		}
		$this->branch_info = $tmp;
		$this->bid = mi($this->branch_info['id']);
		$this->bcode = strtoupper($this->branch_info['code']);
		
		if(!isset($config['kcm_setting'][$this->bcode])){
			die("No config found for branch '$this->bcode'.\n");
		}
		
		// check date
		if(!$date && !$date_from && !$date_to){
			die("Please provide date by using -date= or -date_from= or -date_to=\n");
		}
		if(!$date && ($date_from || $date_to)){
			if(!$date_from){
				die("Please provide -date_from=\n");
			}
			if(!$date_to){
				die("Please provide -date_to=\n");
			}
			if(strtotime($date_to) < strtotime($date_from)){
				die("'Date To' cannot earlier than 'Date From'.\n");
			}
			$this->date_from = $date_from;
			$this->date_to = $date_to;
			
		}else{
			$this->date_from = $this->date_to = $date;
		}
	}

	function start(){
		global $con, $config;
		
		print "\n";
		// Connect to Remote Server
		$this->connect_remote_server();
		print "\n";
		
		// Generate Sales File
		$this->generate_file();
		print "\n";
		
		// Upload Sales File
		$this->upload_file();
		
		// close the connection
		ftp_close($this->conn_id);
		print "Done.\n";
	}
	
	private function connect_remote_server(){
		global $config;
		
		if(!isset($config['kcm_setting'][$this->bcode]['server_ftp_info']))	die("Server FTP Info not found.\n");
		
		$server_ftp_info = $config['kcm_setting'][$this->bcode]['server_ftp_info'];
		
		print "Connecting to Remote Server at '".$server_ftp_info['ip']."'\n";
		
		// set up basic connection
		$this->conn_id = ftp_connect($server_ftp_info['ip'],$server_ftp_info['port']);
		if(!$this->conn_id){
			die("Remote Server Cannot be connect.\n");
		}
		print "Connected.\n";
		
		// login with username and password
		print "Attemp to Login...\n";
		$login_result = ftp_login($this->conn_id, $server_ftp_info['username'], $server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login.\n";
			ftp_close($this->conn_id);
			exit;
		}
		print "Login Success.\n";
        ftp_pasv($this->conn_id, true);
		
		// no need to change directory
		//ftp_chdir($this->conn_id, $server_ftp_info['path']);
	}
	
	private function generate_file(){
		print "Generate Files...\n";
		// loop date
		for($d1 = strtotime($this->date_from),$d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_file_by_date(date("Y-m-d", $d1));
		}
	}
	
	private function upload_file(){
		print "Upload Files...\n";
		// loop date
		for($d1 = strtotime($this->date_from),$d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->upload_file_by_date(date("Y-m-d", $d1));
		}
	}
	
	private function get_folder_path($date){
		// check & create folder by branch
		$folder_path = $this->folder_path.$this->bcode."/";
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
	
		$year = date('Y',strtotime($date));
		$month = mi(date('m',strtotime($date)));
		
		// check & create folder by year
		$folder_path = $folder_path.$year;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by month
		$folder_path=$folder_path."/".$month;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		return $folder_path;
	}
	
	private function get_uploaded_folder_path($date){
		$folder_path = $this->get_folder_path($date);
		
		// check & create uploaded folder
		$uploaded_folder_path = $folder_path."/uploaded";
		if(!is_dir($uploaded_folder_path)) check_and_create_dir($uploaded_folder_path);
		
		return $uploaded_folder_path;
	}
	
	private function get_filename($date){
		global $config;
		
		$settings = $config['kcm_setting'][$this->bcode];
		$filename = sprintf($this->generate_info_filename, $settings['tenant_code'], date('Ymd',strtotime($date)));
		
		return $filename;
	}
	
	private function generate_file_by_date($date){
		global $con, $config;
		
		print "Generating file for $date";
		
		$folder_path = $this->get_folder_path($date);
		$uploaded_folder_path = $this->get_uploaded_folder_path($date);
		$filename = $this->get_filename($date);
		
		if(!$this->regen){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		$sql = "select p.receipt_ref_no, date_format(p.end_time, '%Y%m%d%H%i') as bill_time, round((select sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id),2) as cash_sales
			from pos p 
			join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
			where p.branch_id=$this->bid and p.date=".ms($date)." and p.cancel_status=0 order by p.receipt_ref_no";
		$q1 = $con->sql_query($sql);
		//print "num rows = ".$con->sql_numrows($q1);
		if($con->sql_numrows($q1) > 0){
			$fp = fopen($folder_path."/".$filename, 'w');
			$sales_amt = 0;
			while($r = $con->sql_fetchassoc($q1)){
				$sales_amt += round($r['cash_sales'],2);
				//$data = array($r['bill_time'], $r['receipt_ref_no'], round($r['cash_sales'],2),0,0,0,0,0,0);
				//$success = fputcsv($fp, $data);
				//if(!$success)	die("Failed to write to file.\n");
			}
			if($sales_amt){
				$data = array(0,0,0,0,0,0, $sales_amt,0,0);
				$success = fputcsv($fp, $data);
				if(!$success)	die("Failed to write to file.\n");
			}else{
				print " - Sales Amount Zero.\n";
			}
		}else{
			print " - No Data.\n";
		}
		
		$con->sql_freeresult($q1);
		
		if(isset($fp) && $fp){
			fclose($fp);
			chmod($folder_path."/".$filename,0777);
			print " - Done. ".$folder_path."/".$filename."\n";
		}
	}
	
	private function upload_file_by_date($date){
		global $config;
		
		
		$settings = $config['kcm_setting'][$this->bcode];
		$folder_path = $this->get_folder_path($date);
		$uploaded_folder_path = $this->get_uploaded_folder_path($date);
		$filename = $this->get_filename($date);
		
		print "Checking ".$folder_path."/".$filename;
		if(!file_exists($folder_path."/".$filename)){
			print " - No file to upload.\n";
			return;
		}
		
		// Upload
		$full_filepath = $folder_path."/".$filename;
		print " - Uploading...";
		
		if (ftp_put($this->conn_id, $filename, $full_filepath, FTP_BINARY)) {
			print "successfully uploaded.\n";
		} else {
			$error=error_get_last();
			print "There was a problem while uploading.\n";
			print_r($error);
			return;
		}

		// Move to uploaded folder
		rename($full_filepath, $uploaded_folder_path."/".$filename);
	}
}

class ATRIA{
	var $branch_info = array();
	var $generate_info_filename = '%s_%s.txt';
	var $bid = 0;
	var $folder_path="atria_data/";
	var $date_from;
	var $date_to;
	var $regen = false;
	var $config_name = 'atria_setting';
	var $mall_name = 'Atria Mall';
	var $configuration = array();
	
	// FTP
	var $conn_id;
	
	function __construct()
	{
		global $config;
		
		if(!isset($config[$this->config_name]) || !$config[$this->config_name])	die("No ".$this->mall_name." Setting.\n");
		
		$this->configuration = $config[$this->config_name];
		
		if(!is_dir($this->folder_path)) check_and_create_dir($this->folder_path);
	}
	
	function checkArgs($arg){
		global $con;
		
		if(!isset($arg) || count($arg)<=2)	die("Arguments Required.\n");
		
		// remove 1st and 2nd arguments.
		$a = array_shift($arg);
		$a = array_shift($arg);
		
		while($a = array_shift($arg)){
			if(preg_match('/^-date=/', $a)){	// date
				$tmp = date("Y-m-d", strtotime(str_replace('-date=', '', $a)));
				if(!$tmp)	die("Invalid Date.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date = $tmp;
			}elseif(preg_match('/^-date_from=/', $a)){	// date_from
				$tmp = date("Y-m-d", strtotime(str_replace('-date_from=', '', $a)));
				if(!$tmp)	die("Invalid Date From.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date_from = $tmp;
			}elseif(preg_match('/^-date_to=/', $a)){	// date_to
				$tmp = date("Y-m-d", strtotime(str_replace('-date_to=', '', $a)));
				if(!$tmp)	die("Invalid Date To.\n");
				if(date("Y", strtotime($tmp))<1999)	die("Date $tmp is invalid. Date must start at 1999-01-01.\n");
				$date_to = $tmp;
			}elseif(preg_match('/^-branch=/', $a)){	// branch
				$tmp = trim(str_replace('-branch=', '', $a));
				if(!$tmp)	die("Invalid branch.\n");
				$bcode = $tmp;
			}elseif(preg_match('/^-yesterday$/', $a)){	// use yesterday
				$date = date("Y-m-d", strtotime("-1 day"));
			}elseif(preg_match('/^-regen$/', $a)){	// regenerate
				$this->regen = true;
			}elseif(preg_match('/^-recent_day=/', $a)){	// use yesterday
				$num = mi(str_replace('-recent_day=', '', $a));
				if($num<=0)	die("Recent Day must more than zero.\n");
				
				$date_to = date("Y-m-d", strtotime("-1 day"));
				$date_from = date("Y-m-d", strtotime("-".$num." day"));
			}
			else{
				die("Unknown option $a\n");
			}
		}
		
		// check branch
		if(!$bcode){
			die("Please provide branch code by using -branch=\n");
		}
		$con->sql_query("select id,code from branch where code=".ms($bcode));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp){
			die("Invalid branch code '$bcode'.\n");
		}
		$this->branch_info = $tmp;
		$this->bid = mi($this->branch_info['id']);
		$this->bcode = strtoupper($this->branch_info['code']);
		
		if(!isset($this->configuration[$this->bcode])){
			die("No config found for branch '$this->bcode'.\n");
		}
		
		// check date
		if(!$date && !$date_from && !$date_to){
			die("Please provide date by using -date= or -date_from= or -date_to=\n");
		}
		if(!$date && ($date_from || $date_to)){
			if(!$date_from){
				die("Please provide -date_from=\n");
			}
			if(!$date_to){
				die("Please provide -date_to=\n");
			}
			if(strtotime($date_to) < strtotime($date_from)){
				die("'Date To' cannot earlier than 'Date From'.\n");
			}
			$this->date_from = $date_from;
			$this->date_to = $date_to;
			
		}else{
			$this->date_from = $this->date_to = $date;
		}
	}

	function start(){
		global $con;
		
		print "\n";
		// Connect to Remote Server
		$this->connect_remote_server();
		print "\n";
		
		// Generate Sales File
		$this->generate_file();
		print "\n";
		
		// Upload Sales File
		$this->upload_file();
		
		// close the connection
		ftp_close($this->conn_id);
		print "Done.\n";
	}
	
	private function connect_remote_server(){		
		if(!isset($this->configuration[$this->bcode]['server_ftp_info']))	die("Server FTP Info not found.\n");
		
		$server_ftp_info = $this->configuration[$this->bcode]['server_ftp_info'];
		
		print "Connecting to Remote Server at '".$server_ftp_info['ip']."'\n";
		
		// set up basic connection
		$this->conn_id = ftp_connect($server_ftp_info['ip'],$server_ftp_info['port']);
		if(!$this->conn_id){
			die("Remote Server Cannot be connect.\n");
		}
		print "Connected.\n";
		
		// login with username and password
		print "Attemp to Login...\n";
		$login_result = ftp_login($this->conn_id, $server_ftp_info['username'], $server_ftp_info['pass']);
		if(!$login_result){
			print "Failed to login.\n";
			ftp_close($this->conn_id);
			exit;
		}
		print "Login Success.\n";
        ftp_pasv($this->conn_id, true);
		
		// no need to change directory
		//ftp_chdir($this->conn_id, $server_ftp_info['path']);
	}
	
	private function generate_file(){
		print "Generate Files...\n";
		// loop date
		for($d1 = strtotime($this->date_from),$d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->generate_file_by_date(date("Y-m-d", $d1));
		}
	}
	
	private function upload_file(){
		print "Upload Files...\n";
		// loop date
		for($d1 = strtotime($this->date_from),$d2 = strtotime($this->date_to); $d1 <= $d2; $d1+=86400){
			$this->upload_file_by_date(date("Y-m-d", $d1));
		}
	}
	
	private function get_folder_path($date){
		// check & create folder by branch
		$folder_path = $this->folder_path.$this->bcode."/";
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
	
		$year = date('Y',strtotime($date));
		$month = mi(date('m',strtotime($date)));
		
		// check & create folder by year
		$folder_path = $folder_path.$year;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		// check & create folder by month
		$folder_path=$folder_path."/".$month;
		if(!is_dir($folder_path)) check_and_create_dir($folder_path);
		
		return $folder_path;
	}
	
	private function get_uploaded_folder_path($date){
		$folder_path = $this->get_folder_path($date);
		
		// check & create uploaded folder
		$uploaded_folder_path = $folder_path."/uploaded";
		if(!is_dir($uploaded_folder_path)) check_and_create_dir($uploaded_folder_path);
		
		return $uploaded_folder_path;
	}
	
	private function get_filename($date){
		$settings = $this->configuration[$this->bcode];
		$filename = sprintf($this->generate_info_filename, $settings['tenant_code'], date('dmY',strtotime($date)));
		
		return $filename;
	}
	
	private function generate_file_by_date($date){
		global $con;
		
		print "Generating file for $date";
		
		$folder_path = $this->get_folder_path($date);
		$uploaded_folder_path = $this->get_uploaded_folder_path($date);
		$filename = $this->get_filename($date);
		
		if(!$this->regen){	// no regenerate, check whether file exists
			if(file_exists($uploaded_folder_path."/".$filename) && filesize($uploaded_folder_path."/".$filename) > 0){
				print " - File Uploaded\n";
				return;
			}
		}
		
		// delete the file
		if(file_exists($folder_path."/".$filename)) unlink($folder_path."/".$filename);
		
		$sql = "select sum(round((select sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) from pos_items pi where pi.branch_id=p.branch_id and pi.counter_id=p.counter_id and pi.date=p.date and pi.pos_id=p.id)+ifnull(p.service_charges-p.service_charges_gst_amt,0),2)) as cash_sales
			from pos p 
			join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date and pf.finalized=1
			where p.branch_id=$this->bid and p.date=".ms($date)." and p.cancel_status=0";
			//print $sql;
		$q1 = $con->sql_query($sql);
		//print "num rows = ".$con->sql_numrows($q1);
		if($con->sql_numrows($q1) > 0 || $date == '1999-01-01'){	// 1999-01-01 is official testing upload data
			$sales_amt = 0;
			while($r = $con->sql_fetchassoc($q1)){
				$sales_amt += round($r['cash_sales'],2);
			}
			if($date == '1999-01-01'){
				$sales_amt = 493.22;
			}
			if($sales_amt){
				$data = array($this->configuration[$this->bcode]['tenant_code'], date("dmY", strtotime($date)),$sales_amt);
				
				$fp = fopen($folder_path."/".$filename, 'w');
				$success = fputcsv($fp, $data);
				if(!$success)	die("Failed to write to file.\n");
			}else{
				print " - Sales Amount Zero.\n";
			}
		}else{
			print " - No Data.\n";
		}
		
		$con->sql_freeresult($q1);
		
		if(isset($fp) && $fp){
			fclose($fp);
			chmod($folder_path."/".$filename,0777);
			print " - Done. ".$folder_path."/".$filename."\n";
		}
	}
	
	private function upload_file_by_date($date){		
		$settings = $this->configuration[$this->bcode];
		$folder_path = $this->get_folder_path($date);
		$uploaded_folder_path = $this->get_uploaded_folder_path($date);
		$filename = $this->get_filename($date);
		
		print "Checking ".$folder_path."/".$filename;
		if(!file_exists($folder_path."/".$filename)){
			print " - No file to upload.\n";
			return;
		}
		
		// Upload
		$full_filepath = $folder_path."/".$filename;
		print " - Uploading...";
		
		if (ftp_put($this->conn_id, $filename, $full_filepath, FTP_BINARY)) {
			print "successfully uploaded.\n";
		} else {
			$error=error_get_last();
			print "There was a problem while uploading.\n";
			print_r($error);
			return;
		}

		// Move to uploaded folder
		rename($full_filepath, $uploaded_folder_path."/".$filename);
	}
}
?>
