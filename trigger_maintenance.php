<?php
/*
2/12/2019 12:17 PM Andy
- New Trigger Maintenance Program.

2/21/2019 9:19 AM Andy
- Fixed trigger unable to add for arms-go s1app, s2app and s3app server.

5/21/2019 4:07 PM Andy
- Added program path when sending error message email.

9/5/2019 2:56 PM Andy
- Added update trigger feature.

10/7/2019 3:04 PM Andy
- Added trigger 'pos_trigger_membership_package_insert' and 'pos_trigger_membership_package_update'.

3/4/2020 3:16 PM Andy
- Added trigger 'pos_trigger_vendor_insert' and 'pos_trigger_vendor_update'.

3/11/2020 4:25 PM Andy
- Added trigger 'pos_trigger_brand_insert' and 'pos_trigger_brand_update'.

11/12/2020 2:57 PM Shane
- Added triggers 'pos_trigger_announcement_insert','pos_trigger_announcement_update'.
*/
define("TERMINAL",1);

include("include/common.php");
//$db_default_connection = array("localhost", "root", "", "yy");
//$db_default_connection = array("localhost", "root", "", "arms_segi");
//$db_default_connection = array(":/tmp/mysql.sock3", "root", "", "armstest");
//$db_default_connection = array("localhost", "root", "", "arms_cm");
//$db_default_connection = array("10.1.1.202", "arms", "Arms54321.", "armshq_cwm");
//$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);
error_reporting (E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
ini_set('memory_limit', '512M');
set_time_limit(0);

class TRIGGER_MAINTENANCE {
	var $trigger_folder = 'trigger_maintenance';
	var $trigger_list = array('pos_trigger_debtor_insert', 'pos_trigger_debtor_update', 'pos_trigger_membership_package_insert', 'pos_trigger_membership_package_update', 'pos_trigger_vendor_insert', 'pos_trigger_vendor_update', 'pos_trigger_brand_insert', 'pos_trigger_brand_update','pos_trigger_announcement_insert','pos_trigger_announcement_update');
	// 'pos_trigger_membership_credit_settings_insert','pos_trigger_membership_credit_settings_update','pos_trigger_membership_credit_promotion_insert','pos_trigger_membership_credit_promotion_update',
	
	function __construct(){
		$this->start();
	}
	
	function __destruct(){
		print "Finish.\n";
	}
	
	private function start(){
		// Add Missing Trigger
		$this->add_triggers();
		
		// Update Existing Trigger
		$this->update_trigger();
	}
	
	private function is_trigger_exists($trigger_name){
		global $con;
		
		$con->sql_query("show triggers where `Trigger`=".ms($trigger_name));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		return $tmp ? true : false;
	}
	
	private function add_triggers(){
		global $con, $CLI_Colors, $db_default_connection, $appCore;
		
		print "Start Checking Trigger\n\n";

		//print php_uname('n')."\n";
		
		$email_err_msg = '';
		foreach($this->trigger_list as $trigger_name){
			print "Trigger Name: ";
			print $CLI_Colors->getColoredString($trigger_name, "blue", "light_gray") . "\n";
			$file_path = $this->trigger_folder.'/'.$trigger_name.".sql";
			print "File Path: $file_path";
			if(file_exists($file_path)){
				print "\n";
				
				if(!$this->is_trigger_exists($trigger_name)){
					// Trigger Not Found in Database";
					print $CLI_Colors->getColoredString("Trigger Not Found in Database.", "yellow", "red") . "\n";
					
					// Add the trigger
					$command = "mysql --user={$db_default_connection[1]} --password='{$db_default_connection[2]}' ".($db_default_connection[0]=='localhost'?'':"--host='{$db_default_connection[0]}'")." -D {$db_default_connection[3]} < {$file_path}";
					$output = shell_exec($command);
					print $output;
					
					// Check again whether trigger is added
					if($this->is_trigger_exists($trigger_name)){
						print "Trigger Added.\n";
					}else{
						print "Failed to add Trigger.\n";
						$email_err_msg .= "Failed to add Trigger: $trigger_name.<br>";
					}
				}else{
					// Already have
					print "Database already have this trigger.\n";
				}
			}else{
				// SQL File not found
				print " - File Not Found\n";
				$email_err_msg .= "File Not Found: $file_path<br>";
			}
			print "\n";
		}

		if($email_err_msg){
			$email_err_msg = "Path: ".`pwd`."<br><br>".$email_err_msg;
			$result = $appCore->emailManager->sendEmailToARMS("Trigger Maintenance Failed", $email_err_msg);
			if($result['ok']){
				print "Error has been sent to ARMS.\n";
			}else{
				print $result['err']."\n";
			}
		}
	}
	
	private function update_trigger(){
		global $con, $CLI_Colors, $db_default_connection, $appCore;
		
		$setting_name = 'trigger_version';
		
		// Get Latest Trigger Version
		$con->sql_query("select * from system_settings where setting_name=".ms($setting_name));
		$system_settings = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$curr_version = mi($system_settings['setting_value']);
		print "Current Version: $curr_version\n";
		
		$update_folder = $this->trigger_folder."/update";
		$file_list = glob($update_folder."/update_tmp_trigger-*.sql");
		
		// Nothing to update
		if(!$file_list)	return;
		
		// Sort by version
		natsort($file_list);
		
		foreach($file_list as $file_path){
			list($dummy, $file_version) = explode("-", str_replace(".sql", "", basename($file_path)));
			
			if($curr_version >= $file_version)	continue;	// current version is later
			//print "file_version = $file_version\n";
			
			// Update the trigger
			$command = "mysql --user={$db_default_connection[1]} --password='{$db_default_connection[2]}' ".($db_default_connection[0]=='localhost'?'':"--host='{$db_default_connection[0]}'")." -D {$db_default_connection[3]} < {$file_path}";
			$output = shell_exec($command);
			print $output;
			
			$upd = array();
			$upd['setting_name'] = $setting_name;
			$upd['setting_value'] = $file_version;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("replace into system_settings ".mysql_insert_by_field($upd));
			
			print "Update to Version $file_version\n";
			$curr_version = $file_version;
		}
		print "Latest Version: $curr_version\n";
	}
}

class CLI_Colors {
	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
}

$CLI_Colors = new CLI_Colors();
$TRIGGER_MAINTENANCE = new TRIGGER_MAINTENANCE();

?>