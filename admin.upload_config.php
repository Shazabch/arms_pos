<?php

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Upload_Config_CSV extends Module
{
	
	function __construct($title)
	{
 		parent::__construct($title);
	}
	
	function _default()
	{
		$this->display();
	}
	
	function view_sample()
	{
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_sku.csv");
		print file_get_contents('config_sample.csv');
	}
	
	function upload()
	{
		global $con, $smarty;
		
		$file = $_FILES['import_csv'];
		$result = array();
		
		if (($handle = fopen($file['tmp_name'], "r")) !== false)
		{
			while (($data = fgetcsv($handle)) !== false)
			{
				$r = array();
				
				$num = count($data);
				if ($num != 2 && $num != 3) { //column count is incorrect, skip it
					$r['error'] = join(',',$data).' : <u>Columns count does not match</u>';
					$result[] = $r;
					continue;
				}
				
				$config_name = trim($data[0]);
				$config_val = trim($data[1]);
				$config_desc = trim($data[2]);
				
				if ($config_val == 'yes') {
					$config_val_1 = '1';
					$color = '#0404B4';
				}
				elseif ($config_val == 'no') {
					$config_val_1 = '0';
					$color = '#B40431';
				}
				else {
					$config_val_1 = $config_val;
					$color = '#DF3A01';
				}
				
				//config of type array is not supported, yet
				$sql = "select type from config_master where config_name = ".ms($config_name)." limit 1";
				$con->sql_query($sql);
				$type = $con->sql_fetchassoc();
				if ($type['type'] == 'array') {
					$r['error'] = join(',',$data).' : <u>Config type array is not supported</u>';
					$result[] = $r;
					continue;
				}
				
				$sql = "update config_master set active=1, value=".ms($config_val_1)." where config_name = ".ms($config_name)." limit 1";
				$con->sql_query($sql);
				
				$r['name'] = $config_name;
				$r['value'] = $config_val;
				$r['color'] = $color;
				$r['desc'] = $config_desc;
				
				$result[] = $r;
			}
			fclose($handle);
		}
		$smarty->assign('show_result', true);
		$smarty->assign('result', $result);
		$this->display();
	}
}

$Upload_Config_CSV = new Upload_Config_CSV('Upload Config CSV');

?>
