<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SOP_AUTOCOMPLETE extends Module{

    function _default(){
        // nothing
    }
    
	function load_autocomplete_user_list(){
		global $con, $smarty;
		$con->sql_query("select id, u from user order by u");
		while($r = $con->sql_fetchassoc()){
			$users[] = array('value'=>$r['u'], 'id'=>$r['id']);
		}
		$con->sql_freeresult();
		$ret['ok'] = 1;
		$ret['user_list'] = $users;
		print json_encode($ret);
	}
}

$SOP_AUTOCOMPLETE = new SOP_AUTOCOMPLETE('SOP AUTOCOMPLETE');
?>
