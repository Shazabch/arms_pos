<?php
/*
iSMS gateway class
- provide credit check and send sms function

2011-12-27 - yinsee
change default SMS type to 1 (single byte)

12/26/2013 11:22 AM Justin
- Enhanced send_sms to return success count.
- Enhanced the success count always include return empty result or contains "success" wording.
- Enhanced for success count always sum up total numbers sliced.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

1/10/2020 10:47 AM Andy
- Enhanced to add "agreedterm=YES" when send_sms.

1/10/2020 5:58 PM Andy
- Prepend config.server_name in front of all sms message.
*/
class isms {
	var $URL = "http://isms.com.my/";

	function __construct()
	{
		global $config;
		
		if (!isset($config['isms_user'])||!isset($config['isms_pass']))
		{
			die("Cannot use iSMS, config missing\n");
		}
	}
	
	function get_credit()
	{
		global $config;

		return @file_get_contents($this->URL."isms_balance.php?un=$config[isms_user]&pwd=$config[isms_pass]");
	}
	
	function send_sms($numbers, $message)
	{
		global $config;
		
		// Prepend Server Name
		if($server_name = trim($config['server_name'])){
			$message = $server_name.": ".$message;
		}
		
		// auto detect message type should use unicode or single byte
		$message = trim(str_replace("\r","",$message));
		$type = 1;
		if (mb_strlen($message,'UTF-8')!=strlen($message)) $type=2;
		
		$success_count = 0;
		while($numbers)
		{
			$number_list = array();
			$n = join(";", $number_list=array_splice($numbers, $n, 100));
			// print ($this->URL."isms_send.php?un=$config[isms_user]&pwd=$config[isms_pass]&dstno=$n&msg=".urlencode($message)."&type=$type&sendid=wsatp");
			$ret = @file_get_contents($this->URL."isms_send.php?un=$config[isms_user]&pwd=$config[isms_pass]&dstno=$n&msg=".urlencode($message)."&type=$type&sendid=wsatp&agreedterm=YES");
	///		print ($this->URL."isms_send.php?un=$config[isms_user]&pwd=$config[isms_pass]&dstno=$n&type=$type&sendid=&msg=".urlencode($message));
			/*print "<li> $n ";
			
			if ($ret=='') 
				print " >> OK";
			else
				print " >> $ret";*/
			if(!$ret || preg_match("/success/", strtolower($ret))){
				$success_count = $success_count + count($number_list);
			}
		}
		
		return $success_count;
	}
}
?>