<?php
/**/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['enable_tax']) js_redirect(sprintf($LANG['NEED_CONFIG']), "/index.php");
class TAX_LISTING extends Module{
	var $apply_list= array(
		"arms_fnb"=>"ARMS Fnb",
		"retail"=>"Retail"
	);
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		
 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->load_tax_list();
	    $this->display();
	}
	
	function load_tax_list(){
		global $con, $smarty;
		
		$tax_list = array();
		$q1=$con->sql_query("select * from tax order by id asc");
		while($r = $con->sql_fetchassoc($q1)){
			$apply_to_list = unserialize($r['tax_apply_to']);
			if($apply_to_list){
				$apply_to =array();
				foreach($apply_to_list as $key=>$val){
					$apply_to[] = $this->apply_list[$val];
				}
				$r['tax_apply_to'] = implode(", ",$apply_to);
			}else{
				$r['tax_apply_to'] = "";
			}
			$tax_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("tax_list", $tax_list);
	}
	
	function update(){
		global $con, $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		$id = mi($form['id']);
		
		$err = array();
		$q1=$con->sql_query("select * from tax where code=".ms($form['code'])." and id <> $id");
		if($con->sql_numrows($q1) > 0){
			$err[] ="This Tax Code has been used.";
		}
		$con->sql_freeresult($q1);
		
		if($err){
			$err = implode("\n", $err);
			print $err;
			return;
		}
		
		$upd = array();
		$upd['code'] = $form['code'];
		$upd['description'] = $form['description'];
		$upd['rate'] = $form['rate'];
		$upd['type'] = "sales";
		$upd['indicator_receipt'] = $form['indicator_receipt'];
		$upd['tax_apply_to'] = serialize($form['tax_apply_to']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if(!$id){
			$upd['user_id'] = $sessioninfo['id'];
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into tax ".mysql_insert_by_field($upd));
			log_br($sessioninfo['id'], 'TAX_LISTING', 0, "Added New Tax Code");
		}else{
			$is_updated = false;
			$q2 = $con->sql_query("update tax set ".mysql_update_by_field($upd)." where id=$id");
			if($con->sql_affectedrows($q2) > 0){
				$is_updated = true;
			}
			if($is_updated) log_br($sessioninfo['id'], 'TAX_LISTING', 0, "Updated Tax Code ID#$id");
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		
		print json_encode($ret);
	}
	
	function open(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		
		$con->sql_query("select * from tax where id=$id");
		$form=$con->sql_fetchrow();
		$form['tax_apply_to'] = unserialize($form['tax_apply_to']);
		$con->sql_freeresult();
		
		$smarty->assign("apply_list", $this->apply_list);
		$smarty->assign("form", $form);
		
		$ret = array();
		$ret['html'] = $smarty->fetch("admin.tax_listing.open.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function ajax_active_changed(){
		global $con, $sessioninfo;
		
		$form = $_REQUEST;
		$id = $form['id'];
		
		if(!$id){
			die("Invalid Tax ID.");
		}
		
		$upd = array();
		$upd['active'] = mi($form['active']);
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		$con->sql_query("update tax set ".mysql_update_by_field($upd)." where id =".mi($id));
		
		$ret = array();
		$ret['ok'] = 1;
		
		log_br($sessioninfo['id'], 'TAX_LISTING', 0, ($form['active'] ? 'Activate' : 'Deactivate')." Tax Code ID#$id");
		
		print json_encode($ret);
	}
}

$TAX_LISTING=new TAX_LISTING("Tax Listing");
?>
