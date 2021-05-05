<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_BANK_INTEREST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BANK_INTEREST', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect("Module is for HQ only", "/index.php");

//$maintenance->check(12);
$months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
$smarty->assign('months',$months);


class BANK_INTEREST extends Module{

	function _default(){
        $this->load_table_list(true);
        $this->display();
    }
    
    function load_table_list($sqlonly = false){
    	global $con, $smarty;
    	
    	$con->sql_query("select * from bank_interest order by date desc");
    	$smarty->assign('table', $con->sql_fetchrowset());
    	if(!$sqlonly)   $this->display('masterfile_bank_interest.table.tpl');
	}
    
    function ajax_open(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id>0){
			$con->sql_query("select * from bank_interest where id=$id");
			$smarty->assign('form', $con->sql_fetchrow());
		}
		$this->display('masterfile_bank_interest.open.tpl');
	}
	
	function ajax_save_interest(){
    	global $con, $smarty, $months;
    	
    	$id = mi($_REQUEST['id']);
    	$upd['year'] = mi($_REQUEST['year']);
    	$upd['month'] = mi($_REQUEST['month']);
    	$upd['interest_rate'] = mf($_REQUEST['interest_rate']);
    	
    	if(!$upd['year'] || !$upd['month']) die('Invalid year / month');
    	$upd['date'] = $upd['year'].'-'.$upd['month'].'-1';
    	$upd['last_update'] = 'CURRENT_TIMESTAMP';
    	
    	// check duplicate
    	$con->sql_query("select * from bank_interest where year=$upd[year] and month=$upd[month] and id<>$id");
    	if($con->sql_numrows()>0)   die($months[$upd['month']]." $upd[year] already used.");
    	
    	if($id>0){
			$con->sql_query("update bank_interest set ".mysql_update_by_field($upd)." where id=$id");
		}else{
		    $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into bank_interest ".mysql_insert_by_field($upd));
		}
		print "OK";
	}

	function ajax_delete_interest_rate(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		$con->sql_query("delete from bank_interest where id=$id");
		$this->load_table_list();
	}
}

$BANK_INTEREST = new BANK_INTEREST('Bank Interest Master File');
?>
