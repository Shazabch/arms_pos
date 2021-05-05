<?php
/*
8/25/2015 4:41 PM Andy
- Change to include path of __DIR__ to dirname(__FILE__)

3/14/2016 3:30 PM Andy
- Fix load default year list bug.

2/24/2017 4:37 PM Andy
- Add poManager.

10/5/2017 4:45 PM Andy
- Add posManager.

12/13/2017 1:35 PM Andy
- Add salesAgentManager.

3/28/2018 11:55 AM Andy
- Add workOrderManager.

3/29/2018 9:57 AM Andy
- Add currencyManager.
- Add grnManager.

6/13/2018 2:20 PM Andy
- Add emailManager.
- Fixed function generateEditTime().
- New function newGUID()

9/27/2018 3:15 PM Andy
- Add announcementManager.

2/12/2019 5:56 PM Andy
- Added doManager.

3/5/2019 4:23 PM Andy
- Added new appCore function removeLineBreak().

4/12/2019 5:07 PM Andy
- Added new appCore function array_strval().

5/21/2019 11:13 AM Andy
- Added new appCore function makePDF().

5/22/2019 3:44 PM Andy
- Added stockTakeManager.

6/25/2019 4:41 PM Andy
- Added new appCore function generateRandomCode().

6/27/2019 4:21 PM Andy
- Added memberManager.

7/12/2019 9:32 AM Andy
- Added new appCore function createPayloadJson() and sendMobilePushNotification()
- Those function are for membership mobile push notification.

8/26/2019 10:59 AM Andy
- Added new appCore function removeLinebreakAndWhitespace().

8/30/2019 10:42 AM Andy
- Added couponManager.
- Added cronManager.

9/24/2019 11:18 PM Andy
- Added appCore function isValidUploadImageFile().

10/14/2019 5:24 PM Andy
- Enhanced to hide the warning from appCore.sendMobilePushNotification()

10/25/2019 11:38 AM Andy
- Added appCore function checkAndExtendConfig().

10/30/2019 5:13 PM Andy
- Added attendanceManager.
- Enhanced appCore.isValidDateFormat() to able to check date based on format.
- Added appCore->dayList.

11/22/2019 3:26 PM Andy
- Enhance appCore function "generateRandomCode" to can generate number only.
- Added appCore function send_sms_single().
- Enhanced appCore function createPayloadJson() to can pass screen_tag.

12/24/2019 3:20 PM Justin
- added new appCore function "generateMaxID".

2/11/2020 5:26 PM Andy
- Change appCore function "sendMobilePushNotification" FIREBASE_API to use config.push_notification_firebase_api_key

3/2/2020 4:46 PM Justin
- Add grrManager.

4/2/2020 1:56 PM William
- Enhanced generateMaxID to able to change connection to use con or hqcon.

4/15/2020 11:16 AM William
- Added new appCore function "is_month_closed".

10/14/2020 9:32 AM Andy
- Added new appCore function "generateQRCodeImage".

2/1/2021 10:35 AM Andy
- Fixed appCore function "makePDF" unable to create pdf in some server.
*/
include_once(dirname(__FILE__).'/reportManager.php');
include_once(dirname(__FILE__).'/branchManager.php');
include_once(dirname(__FILE__).'/skuManager.php');
include_once(dirname(__FILE__).'/vendorManager.php');
include_once(dirname(__FILE__).'/brandManager.php');
include_once(dirname(__FILE__).'/categoryManager.php');
include_once(dirname(__FILE__).'/cnoteManager.php');
include_once(dirname(__FILE__).'/gstManager.php');
include_once(dirname(__FILE__).'/uomManager.php');
include_once(dirname(__FILE__).'/approvalFlowManager.php');
include_once(dirname(__FILE__).'/userManager.php');
include_once(dirname(__FILE__).'/poManager.php');
include_once(dirname(__FILE__).'/posManager.php');
include_once(dirname(__FILE__).'/salesAgentManager.php');
include_once(dirname(__FILE__).'/workOrderManager.php');
include_once(dirname(__FILE__).'/currencyManager.php');
include_once(dirname(__FILE__).'/grnManager.php');
include_once(dirname(__FILE__).'/emailManager.php');
include_once(dirname(__FILE__).'/announcementManager.php');
include_once(dirname(__FILE__).'/doManager.php');
include_once(dirname(__FILE__).'/stockTakeManager.php');
include_once(dirname(__FILE__).'/memberManager.php');
include_once(dirname(__FILE__).'/couponManager.php');
include_once(dirname(__FILE__).'/cronManager.php');
include_once(dirname(__FILE__).'/attendanceManager.php');
include_once(dirname(__FILE__).'/grrManager.php');

class appCore{
	// common variable
	public $con;
	public $smarty;
	public $monthsList = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
	public $dayList = array(1=>'Mon',	2=>'Tue',	3=>'Wed',	4=>'Thu',	5=>'Fri',	6=>'Sat',	7=>'Sun');
	public $haveSmarty = false;
	public $phpFileUploadErrors = array(
		0 => 'There is no error, the file uploaded with success',
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		3 => 'The uploaded file was only partially uploaded',
		4 => 'No file was uploaded',
		6 => 'Missing a temporary folder',
		7 => 'Failed to write file to disk.',
		8 => 'A PHP extension stopped the file upload.',
	);

	// module manager
	public $reportManager;
	public $branchManager;
	public $skuManager;
	public $vendorManager;
	public $brandManager;
	public $categoryManager;
	public $cnoteManager;
	public $gstManager;
	public $uomManager;
	public $approvalFlowManager;
	public $userManager;
	public $poManager;
	public $posManager;
	public $salesAgentManager;
	public $workOrderManager;
	public $currencyManager;
	public $grnManager;
	public $emailManager;
	public $announcementManager;
	public $doManager;
	public $stockTakeManager;
	public $memberManager;
	public $couponManager;
	public $cronManager;
	public $attendanceManager;
	public $grrManager;
	
	// need to get throgh getter
	private $yearsList;

	function __construct(){
		global $smarty, $con;

		$this->con = $con;

		$this->reportManager = new reportManager();
		$this->branchManager = new branchManager();
		$this->skuManager = new skuManager();
		$this->vendorManager = new vendorManager();
		$this->brandManager = new brandManager();
		$this->categoryManager = new categoryManager();
		$this->cnoteManager = new cnoteManager();
		$this->gstManager = new gstManager();
		$this->uomManager = new uomManager();
		$this->approvalFlowManager = new approvalFlowManager();
		$this->userManager = new userManager();
		$this->poManager = new poManager();
		$this->posManager = new posManager();
		$this->salesAgentManager = new salesAgentManager();
		$this->workOrderManager = new workOrderManager();
		$this->currencyManager = new currencyManager();
		$this->grnManager = new grnManager();
		$this->emailManager = new emailManager();
		$this->announcementManager = new announcementManager();
		$this->doManager = new doManager();
		$this->stockTakeManager = new stockTakeManager();
		$this->memberManager = new memberManager();
		$this->couponManager = new couponManager();
		$this->cronManager = new cronManager();
		$this->attendanceManager = new attendanceManager();
		$this->grrManager = new grrManager();
		
		if(isset($smarty) && $smarty){
			$this->smarty = $smarty;
			$this->haveSmarty = true;
			// assign own object into smarty
			$this->smarty->assign_by_ref('appCore', $this);
		}
		
		// Check which cron to run
		$this->cronManager->checkCronToRun();
		
		// Check and Extend Config
		$this->checkAndExtendConfig();
	}
	
	private function checkAndExtendConfig(){
		global $config;
		
		// If got marketplace, need to extend sku mprice
		if($config['arms_marketplace_settings'] && $config['marketplace_sku_mprice_type']){
			// Auto add Marketplace MPrice into SKu MPrice
			if(!$config['sku_multiple_selling_price'])	$config['sku_multiple_selling_price'] = array();
			foreach($config['marketplace_sku_mprice_type'] as $tmp_mprice_type){
				if(!in_array($tmp_mprice_type, $config['sku_multiple_selling_price'])){
					$config['sku_multiple_selling_price'][] = $tmp_mprice_type;
				}
			}
		}
	}

	// getter
	// function to get year list
	public function getYearList(){
		if(!is_array($this->yearsList)){
			$this->yearsList = array();

			// get from pos
			$q1 = $this->con->sql_query("select year(min(date)) as min_year, year(max(date)) as max_year from pos where date>0");
			while ($r = $this->con->sql_fetchassoc($q1)){
	            $min_year = $r['min_year'];
	            $max_year = $r['max_year'];
			}
			$this->con->sql_freeresult($q1);
			
			$count_year = $max_year - $min_year;
			
			for($i=0; $i<=$count_year; $i++){
				$tmp_year = $min_year+$i;
				$this->yearsList[$tmp_year] = $tmp_year;
			}

			// get from DO
			$q2 = $this->con->sql_query("select year(min(do_date)) as min_year, year(max(do_date)) as max_year from do where do_date>0");
			while ($r = $this->con->sql_fetchassoc($q2)){
	            $min_year = $r['min_year'];
	            $max_year = $r['max_year'];
			}
			$this->con->sql_freeresult($q2);
			
			$count_year = $max_year - $min_year;
			
			for($i=0; $i<=$count_year; $i++){
				$tmp_year = $min_year+$i;
				if(!$this->yearsList[$tmp_year]){
					$this->yearsList[$tmp_year] = $tmp_year;
				}
			}
			
			krsort($this->yearsList);
		}

		return $this->yearsList;
	}

	// function to check whether the provided params is date format
	// return boolean
	//public function isValidDateFormat($date){
	//	return (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/",$date)) ? true : false;
	//}
	
	public function isValidDateFormat($date, $format = 'Y-m-d') {
		$dateObj = DateTime::createFromFormat($format, $date);
		return $dateObj && $dateObj->format($format) == $date;
	}

	// function to get month label
	// return string
	public function getMonthLabel($m){
		return $this->monthsList[$m];
	}

	// function to generate temporary id
	// return int tmpID
	public function generateTempID(){
		$tmpID = time();

		// check session to prevent duplicate key
		if(isset($_SESSION)){
			if(isset($_SESSION['tempID']) && $tmpID <= $_SESSION['tempID']){
				$tmpID = $_SESSION['tempID']+1;
			}
			$_SESSION['tempID'] = $tmpID;
		}
		return $tmpID;
	}
	
	// function to generate edit time
	// return int editTime
	public function generateEditTime(){
		$editTime = time();

		// check session to prevent duplicate key
		if(isset($_SESSION)){
			if(isset($_SESSION['editTime']) && $editTime <= $_SESSION['editTime']){
				$editTime = $_SESSION['editTime']+1;
			}
			$_SESSION['editTime'] = $editTime;
		}
		return $editTime;
	}
	
	// function to get expired edit time
	// return int expiredTime
	public function getExpiredEditTime(){
		$editTime = time();
		$expiredTime = strtotime("-7 day", $editTime);
		
		return $expiredTime;
	}
	
	/*
	 *
	 * Generate 128 bits of random data
	 * @see http://tools.ietf.org/html/rfc4122#section-4.4
	 * @return    string
	 * 
	 */
	public function newGUID()	// guidv4()
	{
		$data = openssl_random_pseudo_bytes( 16 );
		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 ); // set version to 0100
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
	}
	
	public function removeLineBreak($str){
		return preg_replace('/\s+/', ' ',$str);
	}
	
	public function array_strval($arr){
		foreach($arr as $key => $v){
			if(is_array($v)){
				$arr[$key] = $this->array_strval($v);
			}else{
				$arr[$key] = strval($v);
			}
		}
		
		return $arr;
	}
	
	public function makePDF($htmlFile, $pdfFile){
		if(!$htmlFile || !$pdfFile)	return false;
		
		$command = "wkhtmltopdf";
		$ex_cmd = "$command $htmlFile $pdfFile";
		//print $ex_cmd;
		unlink($pdfFile);
		$output = shell_exec($ex_cmd);
		if(file_exists($pdfFile)){
			return $pdfFile;
		}else{
			// Try using xvfb-run
			$output = shell_exec("xvfb-run ".$ex_cmd);
			if(file_exists($pdfFile)){
				return $pdfFile;
			}else{
				return false;
			}
			
		}
	}
	
	public function generateRandomCode($code_length = 10, $number_only = false){
		$alpha_list = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$num_list = array(0,1,2,3,4,5,6,7,8,9);
		
		if($number_only){
			// Number Only
			$rand_char = $num_list;
		}else{
			// Character + Number
			$rand_char = array_merge($alpha_list, $num_list, $num_list, $num_list);
		}
		
		$code = '';
		
		for($i = 0; $i < $code_length; $i++){
			$j = rand(0, count($rand_char)-1);
			$code .= $rand_char[$j];
		}
		
		return $code;
	}
	
	//Create json file to send to Apple/Google Servers with notification request and body
	public function createPayloadJson($title, $body, $screen_tag = '') {
		//Badge icon to show at users ios app icon after receiving notification
		$badge = "1";
		$sound = 'default';
		$content_available = "1";
		$message = array(
			"title" => $title,
			"body" => $body,
			"screenTag" => $screen_tag,
			"pnIndex" => $this->newGUID()
		);

		$payload = array();
		$payload['aps'] = array('alert' => $message, 'badge' => intval($badge), 'sound' => $sound, 'content-available' => $content_available);
		return json_encode($payload);
	}
	
	// Function to send Push Notification to Mobile
	public function sendMobilePushNotification($user_device_type, $user_device_key, $payload_info){
		global $config;
		
		if(!$config['enable_push_notification'])	return false;
		
		//Default result
		$success = -1;
		//Change depending on where to send notifications - either production or development
		//$pem_preference = "development";//"production";
		//$user_device_type = $user_mobile_info['user_device_type'];
		//$user_device_key = $user_mobile_info['user_mobile_token'];

		if ($user_device_type == "ios") {

			$apns_url = NULL;
			$apns_cert = NULL;
			//Apple server listening port
			$apns_port = 2195;

			if($config['test_push_notification']){
				$apns_url = 'gateway.sandbox.push.apple.com';
				$apns_cert = dirname(__FILE__).'/arms_push_notification_cert-dev.pem';
			}else{
				$apns_url = 'gateway.push.apple.com';
				$apns_cert = dirname(__FILE__).'/arms_push_notification_cert-prod.pem';
			}

			$stream_context = stream_context_create();
			stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);

			$apns = @stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);

			$apns_message = chr(0) . chr(0) . chr(32) . @pack('H*', str_replace(' ', '', $user_device_key)) . chr(0) . chr(strlen($payload_info)) . $payload_info;

			if ($apns) {
				$success = fwrite($apns, $apns_message);
			}
			@socket_close($apns);
			@fclose($apns);

		}
		else if ($user_device_type == "android") {

			// API access key from Google API's Console
			//define('API_ACCESS_KEY', ADD_HERE);
			// define('FIREBASE_TOKEN', ADD_HERE);
			//define('FIREBASE_API', 'AAAAa1y62aQ:APA91bF8qMBL-8F8MA70eheMRvrCujG0g9i_nQi-rtMllIuxO7d-kPJ120eP-aPdnC-OirpLXVgbwP-4Exe4EmL7_qSw5hJDAaAL0oNDBtqDjQnGx07UV_IrCrRDB4OO8eyF4NKQHF_3');
			if(!$config['push_notification_firebase_api_key'])	return false;
			
			$cloud_message_server = "firebase"; // Enum, "gcm" or "firebase"

			// prep the bundle
			$msg = array
			(
				'message' 	=> json_decode($payload_info)->aps->alert->body,
				'title'		=> json_decode($payload_info)->aps->alert->title,
				'subtitle'	=> '',
				'tickerText'=> '',
				'vibrate'	=> 1,
				'sound'		=> 1,
				'largeIcon'	=> 'large_icon',
				'smallIcon'	=> 'small_icon'
			);

			if($cloud_message_server == "gcm"){
				$fields = array(
					'registration_ids'  => array($user_device_key),
					'data' => $msg
				);  
			} else {
				$fields = array(
					'to' => $user_device_key,
					'data' => $msg
				);
			}
			

			$url = ($cloud_message_server=="gcm")?'https://android.googleapis.com/gcm/send':'https://fcm.googleapis.com/fcm/send';
			$headers = array
			(
				'Authorization: key=' . $config['push_notification_firebase_api_key'],
				'Content-Type: application/json'
			);

			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, $url );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch);
			curl_close($ch);
			
			//print_r($result);
			if($result['success']){
				$success = 1;
			}
		}
		return $success > 0;
	}
	
	public function removeLinebreakAndWhitespace($str){
		return preg_replace('/\s*/m', '', $str);
	}
	
	public function isValidUploadImageFile($file_obj){
		// Object Type is wrong
		if(!isset($file_obj['tmp_name']))	return array("error" => 'tmp_name not found.');
		
		// Got Upload Error
		if($file_obj['error']>0){
			return array('error' => $this->phpFileUploadErrors[$file_obj['error']]);
		}
		
		// Not Upload File
		if(!is_uploaded_file($file_obj['tmp_name'])){
			return array("error" => 'File Error');
		}
		
		// TMP File Not Exists
		if(!file_exists($file_obj['tmp_name'])){
			return array("error" => 'TMP File Not Exists.');
		}
		
		// Invalid Extension
		if(!preg_match("/\.(jpg|jpeg|png|gif)$/i", $file_obj['name'], $ext)){
			return array("error" => 'File Extension must be (jpg, jpeg, png or gif).');
		}
		//print_r($ext);exit;
		$ret = array();
		$ret['ok'] = 1;
		$ret['ext'] = $ext[1];
		
		return $ret;
	}
	
	public function send_sms_single($mobile_num, $sms_msg, $params = array()){
		global $con, $config, $sessioninfo;
		
		// Server no turn on isms config
		if(!$config['isms_user'] || !$config['isms_pass']){
			return false;
		}
		
		$mobile_num = preg_replace("/[^0-9]/", "", trim($mobile_num));
		$sms_msg = trim($sms_msg);
		
		if(!$mobile_num || !$sms_msg)	return false;
		
		// Branch ID
		$bid = isset($params['branch_id']) ? mi($params['branch_id']) : mi($sessioninfo['branch_id']);
		if($bid <=0)	$bid = 1;
		
		// User ID
		$user_id = isset($params['user_id']) ? mi($params['user_id']) : mi($sessioninfo['id']);
		if($user_id <=0)	$user_id = 1;
		
		// Member Card No
		$member_card_no = isset($params['member_card_no']) ? trim($params['member_card_no']) : '';
		
		$upd = array();
		$guid = $this->newGUID();
		$upd['guid'] = $guid;
		$upd['branch_id'] = $bid;
		$upd['user_id'] = $user_id;
		$upd['member_card_no'] = $member_card_no;
		$upd['mobile_num'] = $mobile_num;
		$upd['message'] = $sms_msg;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into isms_history ".mysql_insert_by_field($upd));
		
		include_once("include/class.isms.php");
		$isms = new iSMS();
		$success = $isms->send_sms(array($mobile_num), $sms_msg);
		
		if($success){	// Send SMS Success
			$remaining_cc = $isms->get_credit();
			log_br($user_id, 'Notification', 0, "Send SMS to Mobile Number: ".$mobile_num." user(s) (Remaining credit: $remaining_cc)");
			
			$upd = array();
			$upd['success'] = 1;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update isms_history set ".mysql_update_by_field($upd)." where guid=".ms($guid));
		}
		
		return $success;
	}
	
	public function generateNewID($tbl_name, $filters="", $prms=array()){
		global $con, $hqcon;
		
		if(!$tbl_name) return; // stop if no table name provided
		
		$filter = $for_update = "";
		// if found filters included from the params
		if(isset($filters) && $filters){
			if(is_array($filters)){ // join it if the filter was setup in array style
				$filter = "where ".join(" and ", $filters);
			}else $filter = "where ".$filters; // otherwise straight put the filter without joining it
		}
		
		// lock the table by default if no set to skip it
		if(!$prms['skip_update']) $for_update = " for update";
		
		// if got set different column name to get the ID
		$col_name = "id"; // default as "id"
		if($prms['col_name']) $col_name = $prms['col_name'];
		
		if($prms['hq_con']){  // if got set use hqcon then change the con to use hq_con
			if(!$hqcon) $hqcon = connect_hq();
			$q1 = $hqcon->sql_query("select max($col_name) as max_id from $tbl_name ".$filter.$for_update);
			$tmp = $hqcon->sql_fetchassoc($q1);
			$hqcon->sql_freeresult($q1);
		}else{
			$q1 = $con->sql_query("select max($col_name) as max_id from $tbl_name ".$filter.$for_update);
			$tmp = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
		}
		$new_id = mi($tmp['max_id'])+1;
		
		return $new_id;
	}
	
	public function is_month_closed($date){
		global $con;
		
		$con->sql_query("select max(year) as year from monthly_closing where closed=1");
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		$con->sql_query("select year, max(month) as month from monthly_closing where closed=1 and year=".mi($r['year']));
		$r1 = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		$latest_closed_month = strtotime(date("Y-m-t", strtotime($r1["year"]."-".$r1["month"]."-01")));
		$use_date =  strtotime(date("Y-m-d", strtotime($date)));
		
		if($use_date <= $latest_closed_month){
			return true;
		}
	}
	
	public function generateQRCodeImage($filename, $str, $params = array()){
		// Make sure cache folder is exists
		check_and_create_dir(dirname(__FILE__)."/phpqrcode/cache");
		
		// Load QR Code Library
		require_once(dirname(__FILE__)."/phpqrcode/qrlib.php");
		
		// Generate QR Code
		QRcode::png($str, $filename, 'L', 8, 2);
	}
}

$appCore = new appCore();
?>
