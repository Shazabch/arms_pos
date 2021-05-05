<?php
/*
7/5/2011 2:26:33 PM Andy
- Add checking for category.

1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_SUPERMARKET_CODE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SUPERMARKET_CODE', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect($LANG['HQ_ONLY'], "/index.php");

//$maintenance->check(12);
include_once('consignment.include.php');

class SUPERMARKET_CODE extends Module{
	function __construct($title){
		global $con, $smarty;

		if(!$_REQUEST['skip_init_load'])    $this->init_load();

		parent::__construct($title);
	}
	
	function _default(){
        $this->load_vendor();
        $this->load_price_type();
        
        if($_REQUEST['load_data']){
			$this->load_data();
		}
		
        $this->display();
    }
    
    private function init_load(){
		$this->branch_group = load_branch_group();
		$this->branches = load_branch();
	}
	
	private function load_vendor(){
		global $con, $smarty;
		
		$con->sql_query("select id,code,description,prefix_code from vendor order by description,prefix_code");
		while($r = $con->sql_fetchassoc()){
			$this->vendors[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('vendors', $this->vendors);
	}
	
	private function load_price_type(){
		global $con, $smarty;
		
		$con->sql_query("select * from trade_discount_type order by code");
		while($r = $con->sql_fetchassoc()){
			$this->price_type[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('price_type', $this->price_type);
	}
	
	private function load_data(){
		global $con, $smarty;
		
		//print_r($_REQUEST);
		
		$bid = mi($_REQUEST['branch_id']);
		$vid = mi($_REQUEST['vendor_id']);
		$cat_id = mi($_REQUEST['category_id']);
		$price_type = trim($_REQUEST['price_type']);
		$scode_filter = trim($_REQUEST['scode_filter']);
		
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$cat_id)	$err[] = "Please select category.";
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		if(isset($_REQUEST['sku_code_list2'])){
			$sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list2']));
			
		    // select sku item id list
	     	$con->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)") or die(mysql_error());
	     	$sid_list = array();
			while($r = $con->sql_fetchassoc()){
				$sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$con->sql_freeresult();
		}
		
		$filter = array();
		if($vid)	$filter[] = "sku.vendor_id=$vid";
		if($price_type)	$filter[] = "if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code)=".ms($price_type);
		if($scode_filter == 'got_scode'){
			$filter[] = "(ssc.supermarket_code is not null and ssc.supermarket_code<>'')";
		}elseif($scode_filter == 'no_scode'){
			$filter[] = "(ssc.supermarket_code is null or ssc.supermarket_code='')";
		}
		if($cat_id){
			$cat_info = get_category_info($cat_id);
			$filter[] = "cc.p".$cat_info['level']."=$cat_id";
		}
		if($sid_list)	$filter[] = "si.id in (".join(',',$sid_list).")";
		
		$report_title = array();
		$report_title[] = "Branch: ".$this->branches[$bid]['code'];
		$report_title[] = "Vendor: ".($vid ? $this->vendors[$vid]['description'] : 'All');
		$report_title[] = "Price Type: ".($price_type ? $price_type : 'All');
		$report_title[] = "Category: ".($cat_id ? $cat_info['description'] : 'All');
		
		if($filter)	$filter = 'where '.join(' and ', $filter);
		else	$filter = '';
		
		$sql = "select si.id,si.sku_item_code,si.description,si.artno,ssc.supermarket_code,if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as price_type
from sku_items si
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
left join sku_supermarket_code ssc on ssc.branch_id=$bid and  ssc.sku_item_id=si.id
left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
$filter
order by si.sku_item_code";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$this->data[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
	
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('data', $this->data);
		$smarty->assign('group_item',$group_item);
	}
	
	function ajax_save_supermarket_code(){
		global $con, $smarty, $sessioninfo;
		
		$supermarket_code_list = $_REQUEST['supermarket_code'];
		$bid = mi($_REQUEST['branch_id']);
		
		if(!$supermarket_code_list)	die('There is no item to save.');
		if(!$bid)	die('No branch is selected.');
		
		$updated_count = 0;
		foreach($supermarket_code_list as $sid=>$supermarket_code){
			$supermarket_code = trim($supermarket_code);
			
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['sku_item_id'] = $sid;
			$upd['supermarket_code'] = $supermarket_code;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			// check current
			$con->sql_query("select * from sku_supermarket_code where branch_id=$bid and sku_item_id=$sid");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// if not exists
			if(!$tmp)	$upd['added'] = 'CURRENT_TIMESTAMP';
			if($tmp['supermarket_code'] == $supermarket_code)	continue;	// no need update if same
			
			$con->sql_query("insert into sku_supermarket_code ".mysql_insert_by_field($upd)." on duplicate key update
				supermarket_code=".ms($upd['supermarket_code']).",
				last_update=CURRENT_TIMESTAMP
			");
			
			log_br($sessioninfo['id'], 'SUPERMARKET CODE', $sid, "Update: (Branch ID#$bid, SKU Item ID#$sid, From '$tmp[supermarket_code]' to '$supermarket_code')");
			
			$updated_count++;
		}
		
		$ret['ok'] = 1;
		$ret['msg'] = "$updated_count item(s) supermarket code updated.";
		
		print json_encode($ret);
	}
}

$SUPERMARKET_CODE = new SUPERMARKET_CODE('Supermarket Code Master File');
?>
