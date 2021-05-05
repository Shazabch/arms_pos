<?php

include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");

class Promotion_Module extends Scan_Product{
	function __construct($title){
		//$this->dumpdata($_SESSION);
		//$this->dumpdata($_REQUEST);
		//$_SESSION['promotion']['id'] = 928;
		global $sessioninfo;
		$_SESSION['scan_product']['type'] = 'promotion';
		$_SESSION['scan_product']['name'] = isset($_SESSION['promotion']['id']) ? 'Promotion#'.$_SESSION['promotion']['id'] : '';
		parent::__construct($title);
	}
	
	function init_module(){
		global $con, $smarty;
		$smarty->assign('PAGE_TITLE', $this->title);
		$smarty->assign('module_name', $this->title);
		$smarty->assign('top_include','promotion.top_include.tpl');
		$smarty->assign('btm_include','promotion.btm_include.tpl');
	}
	
	function default_(){
		$this->show_setting();
	}
	
	function create(){
		unset($_SESSION['promotion']);
		$this->default_();
	}
	
	function show_scan_product(){
		global $con, $smarty;

		$_SESSION['scan_product']['type'] = 'promotion';
		$_SESSION['scan_product']['name'] = 'Promotion#'.$_SESSION['promotion']['id'];

		$product_code = strtoupper($_REQUEST['product_code']);
		// cut last digit
		$product_code2 = strtoupper(substr($product_code,0,strlen($product_code)-1));
		
		if (!empty($product_code) || !empty($product_code2)) {
			$filter[] = "(si.mcode=".ms($product_code)." or si.mcode=".ms($product_code2).")";
			$filter[] = "(si.link_code=".ms($product_code)." or si.link_code=".ms($product_code2).")";
			$filter[] = "(si.sku_item_code=".ms($product_code)." or si.sku_item_code=".ms($product_code2).")";
			$filter[] = "(si.artno=".ms($product_code)." or si.artno=".ms($product_code2).")";
			$filter = join(' or ',$filter);

			$sql = $con->sql_query("select si.id from sku_items si where $filter");

			if($con->sql_numrows($sql) == 1 && $_REQUEST['auto_add_item']){
				$sku_info = $con->sql_fetchassoc($sql);
				$con->sql_freeresult($sql);
				$_REQUEST['item_qty'][$sku_info['id']] = 1;
				$this->add_items();
				unset($_REQUEST);
				$smarty->assign("auto_add", 1);
			}
		}
		
		$this->search_product();
	}

	function show_setting(){
		global $con, $smarty, $sessioninfo;
		$this->default_load();
		
		$con->sql_query("select id, title, user_id as owner_id from promotion where id = ".mi($_SESSION['promotion']['id'])." and branch_id = ".mi($sessioninfo['branch_id']));
		$form = $con->sql_fetchassoc();
		$smarty->assign('form', $form);
		
		$smarty->assign('promotion_tab', 'setting');
		$smarty->display('promotion.index.tpl');
	}
	
	private function default_load(){
		global $con,$smarty,$sessioninfo;
		$con->sql_query("select up.user_id as id, u.u from user_privilege up left join user u on up.user_id = u.id where up.privilege_code = 'PROMOTION' and up.branch_id = ".mi($sessioninfo['branch_id'])." order by u.u");
		$smarty->assign('owners',$con->sql_fetchrowset());
	}
	
	function save_setting(){
		
		global $con, $sessioninfo;
		//$this->dumpdata($_REQUEST);die;
		
		if ($_SESSION['promotion']['id']) {
			$upd = array(
				'user_id'	=>	$_REQUEST['owner_id'],
				'title'		=>	$_REQUEST['title'],
			);
			$con->sql_query("update promotion set ".mysql_update_by_field($upd)." where id=".mi($_SESSION['promotion']['id'])." and branch_id=".mi($sessioninfo['branch_id']));
		}
		else {
			$ins = array(
				'branch_id'	=>	$sessioninfo['branch_id'],
				'user_id'	=>	$_REQUEST['owner_id'],
				'title'		=>	$_REQUEST['title'],
				'date_from'	=>	date('Y-m-d'),
				'date_to'	=>	date('Y-m-d'),
				'time_from'	=>	'00:00:00',
				'time_to'	=>	'23:59:00',
			);
			$con->sql_query("insert into promotion ".mysql_insert_by_field($ins));
			$id = $con->sql_nextid();
			$_SESSION['promotion']['id'] = $id;
		}
		
		header("Location: $_SERVER[PHP_SELF]?a=show_scan_product");
	}
	
	function view_items(){
		global $con, $smarty, $sessioninfo;
		
		$records_per_page = 10;
		
		$page = $_REQUEST['page'] ? mi($_REQUEST['page']) : 1;
		$start_row = (($page-1)*$records_per_page)+1;
		$end_row = $start_row + ($records_per_page-1);
		$con->sql_query("select count(*) as c from promotion_items where promo_id = ".mi($_SESSION['promotion']['id'])." and branch_id = ".mi($sessioninfo['branch_id']));
		$r = $con->sql_fetchassoc();
		$total_rows = mi($r['c']);
		$total_page = mi(ceil($total_rows/$records_per_page));
		
		if ($end_row > $total_rows) $end_row = $total_rows;
		
		$smarty->assign('page',$page);
		$smarty->assign('records_per_page',$records_per_page);
		$smarty->assign('page_list',range(1,$total_page));
		$smarty->assign('total_page',$total_page);
		$smarty->assign('start_row',$start_row);
		$smarty->assign('end_row',$end_row);
		$smarty->assign('total_rows',$total_rows);
		$skip = $start_row-1;
		
		$con->sql_query("select pi.id, si.sku_item_code, si.description from promotion_items pi left join sku_items si on pi.sku_item_id = si.id where pi.promo_id = ".mi($_SESSION['promotion']['id'])." and pi.branch_id = ".mi($sessioninfo['branch_id'])." limit $skip,$records_per_page");
		$smarty->assign('items',$con->sql_fetchrowset());
		
		$smarty->assign('promotion_tab', 'view_items');
		$smarty->display('promotion.view_items.tpl');
	}
	
	function add_item_by_grn_barcode(){
		global $con, $smarty;

		$code = trim($_REQUEST['product_code']);
		if (empty($code)) return;
		
		if (preg_match("/^00/", $code))	{ // form ARMS' GRN barcoder
			$sku_item_id=mi(substr($code,0,8));
			$sql = "select id from sku_items where id = ".mi($sku_item_id);
		}
		else {	// from ATP GRN Barcode, try to search the link-code 
			$linkcode=substr($code,0,7);
			$sql = "select id from sku_items where link_code = ".ms($linkcode);
		}
		$q1 = $con->sql_query($sql);
		$r1 = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		
		if ($r1) {
			$_REQUEST['item_qty'][$r1['id']] = 1;
			$this->add_items();
		}
		elseif (!$r1) $this->err[] = "The item (".$code.") not found!";
	}
	
	function add_items(){
		global $con, $sessioninfo;
		//$this->dumpdata($_REQUEST);die;
		
		$con->sql_query("select user_id from promotion where id = ".mi($_SESSION['promotion']['id'])." and branch_id = ".mi($sessioninfo['branch_id']));
		$r = $con->sql_fetchassoc();
		
		foreach ($_REQUEST['item_qty'] as $sid => $dummmy) {
			
			$ins = array(
				'promo_id'		=>	$_SESSION['promotion']['id'],
				'branch_id'		=>	$sessioninfo['branch_id'],
				'user_id'		=>	mi($r['user_id']),
				'sku_item_id'	=>	mi($sid),
				'brand_id'		=>	0,
			);
			$con->sql_query("insert into promotion_items ".mysql_insert_by_field($ins));
		}
		
		return array('success'=>true);
	}
	
	function delete_items(){
		//$this->dumpdata($_REQUEST);die;
		global $con, $sessioninfo;
		$con->sql_query("delete from promotion_items where id in (".join(',',array_keys($_REQUEST['item_chx'])).") and promo_id = ".mi($_SESSION['promotion']['id'])." and branch_id = ".mi($sessioninfo['branch_id']));
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function dumpdata($what) {
		echo '<pre>';
		print_r($what);
		echo '</pre>';
	}
}

$Promotion_Module = new Promotion_Module('Promotion');

?>
