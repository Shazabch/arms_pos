<?
/*
4/25/2017 10:31 AM Khausalya
- Enhanced hanges from RM to use onfig setting. 

10/14/2019 11:57 AM William
- Fixed bug Voucher value limit maximum value as 999.99
- Fix bug "Voucher Setup" will delete voucher value when voucher value invalid.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level'] < 9999) js_redirect("Do not have permission to access.", "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_VOUCHER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER', BRANCH_CODE), "/index.php");
if (!privilege('MST_VOUCHER_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER_SETUP', BRANCH_CODE), "/index.php");

class Voucher_Setup extends Module{
	
	function __construct($title){
		global $con, $smarty, $config;

		$con->sql_query('select voucher_value from mst_voucher_setup order by voucher_value');
		while($r=$con->sql_fetchassoc()){
			$voucher[] = $r['voucher_value'];
		}
		$con->sql_freeresult();
		$smarty->assign('voucher',$voucher);

 		parent::__construct($title);
	}

	function _default(){
	    $this->display();
	    exit;
	}
	
	function save_form(){
		global $con, $smarty, $sessioninfo, $config, $LANG;
		$form=$_REQUEST["mytext"];
		asort($form);
		$result = array_unique($form);
		
		//error checking
		$err = array();
		foreach ($result as $key => $value){
			if (!preg_match('/^\\d+(\\.\\d{1,2})?$/D',  $value) || $value == 0) $err[] = "Invalid data type.";
			if ($value >= 1000) $err[] = "Voucher maximum value is 999.99";
		}
		
		if(!$err){
			$q1 = $con->sql_query("delete from mst_voucher_setup");
			foreach ($result as $key => $value){
				$id = $key + 1;
				$voucher = array();
				$voucher["id"] = mi($id);
				$voucher["voucher_value"] = $value;
				$con->sql_query("insert into mst_voucher_setup ".mysql_insert_by_field($voucher));
				$num = $con->sql_affectedrows();
				log_br($sessioninfo['id'], 'VOUCHER SETUP',$sessioninfo['branch_id'],"Voucher Value: " . $config["arms_currency"]["symbol"] .$voucher['voucher_value']);
			}
		}
		
		$smarty->assign('voucher',$result);
		if($err)  $smarty->assign("err",$err);
        $this->display();
		if ($num > 0){
			print "<script>alert('Done');</script>";
		}
	}
}

$Voucher = new Voucher_Setup("Voucher Setup");
?>