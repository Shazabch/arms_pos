<?php
/*
8/11/2011 7:06:37 PM Alex
- create by me

8/15/2011 12:06:24 PM Alex
- add sku type, active, block po item filter

8/19/2011 1:51:16 PM Alex
- add show multiple type price in a page

9/21/2011 11:56:18 AM Alex
- fix category level control

3/20/2012 11:29:26 AM Andy
- Add vendor and brand filter.

2/15/2017 2:23 PM Andy
- Change the title of 'Masterfile SKU Price List' to 'SKU Price List'. 

11/15/2019 4:01 PM William
- Enhanced to add checking when "Sort by" is select the selection "Price List" not selected.

11/18/2019 4:50 PM William
- Added checking when "Price List" is not selected.

2/25/2021 5:05 PM Andy
- Increased memory_limit to 2G.
*/
include("include/common.php");
ini_set('memory_limit', '2G');
set_time_limit(0);
$maintenance->check(59);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

class MASTERFILE_SKU_PRICE_LIST extends Module{
	var $brand_list = array();
	var $vendor_list = array();
	
	function __construct($title){
		global $con, $config, $smarty;
		
		//Branches
		$branch_rid=$con->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc($branch_rid)){
			$branches[$r['id']] = $r['code'];
		}
		$con->sql_freeresult($branch_rid);

		$smarty->assign("branches", $branches);

		//sku type
		$sku_type_rid=$con->sql_query("select * from sku_type where active=1");
		while($r = $con->sql_fetchassoc($sku_type_rid)){
			$sku_type[$r['code']] = $r['code'];
		}
		$con->sql_freeresult($sku_type_rid);
		$smarty->assign("sku_type", $sku_type);
		
		// brand
		$con->sql_query("select id,description from brand order by description");
		while($r = $con->sql_fetchassoc()){
			$this->brand_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign("brand_list", $this->brand_list);
		
		// vendor
		$con->sql_query("select id,description from vendor order by description");
		while($r = $con->sql_fetchassoc()){
			$this->vendor_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign("vendor_list", $this->vendor_list);
		
		$bid  = get_request_branch();
		$this->bid=$bid;
        
		$_REQUEST['branch_id']=$bid;

		parent::__construct($title);
	}
	
	function _default(){
		$this->display();
		exit;
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
//		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "masterfile_sku_price_list_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Masterfile Sku Price List To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();
	    exit;
	}
	
	function validate_data($form){
		if (!$this->bid)	$err[]="Missing branch";	
		if (!$form['price_list'])	$err[]="Missing price list";	
		if (!$form['category_id'])	$err[]="Missing category";	
		if($form['sort_by']!= 'sku_item_code' && $form['sort_by']!= 'mcode' && $form['sort_by']!= 'artno' && $form['sort_by']!= 'description'){
			if($form['price_list']){
				if(!in_array($form['sort_by'],$form['price_list']))  $err[]="Cannot sort by '".$form['sort_by']."' when price list is not select '".$form['sort_by']."'.";	
			}
		}
		return $err;
	}

	function show_report(){
		global $con,$smarty;
		
		//title
		$form=$_REQUEST;
		$err=$this->validate_data($form);
		if ($err){
			$smarty->assign("err",$err);
		}else{		
			$this->generate_report();
		}
		$this->display();
	}
	
	function generate_report(){
		global $con, $config, $smarty;
		$form=$_REQUEST;
		$branch_id=$this->bid;
		$left_join='';
		$brand_id = trim($_REQUEST['brand_id']);
		$vendor_id = mi($_REQUEST['vendor_id']);
		
		$price_col=count($form['price_list']);
		
		$arr_report_title[]="Branch: ".get_branch_code($this->bid);
		
		$arr_report_title[]="Sku Type: ".ucfirst($form['sku_type']);
		
		$status=ucfirst($form['status']);
		if ($status != "All"){
			$status = $form['status'] ? "Active" : "Inactive"; 
		}
		
		$arr_report_title[]="Status: ".$status;

		foreach ($form['price_list'] as $pcode){
			$form['price_list'][$pcode] = ucfirst($pcode); 
		}
		
		$arr_report_title[]="Price List: ". join(", ", $form['price_list']);

		if($form['blocked_po'])	$arr_report_title[]="Blocked Item in PO: ".ucfirst($form['blocked_po']);
		
		if ($form['category_id']){
			$sql="select * from category where id=$form[category_id]";
			$con->sql_query($sql);
			$cat=$con->sql_fetchassoc();
			$level=$cat['level'];
			$con->sql_freeresult();
			$arr_report_title[]="Category: ".$cat['description'];

    		// check one more level for grouping
    		$con->sql_query("select max(level) from category") or die(mysql_error());
    		$max_level = $con->sql_fetchfield(0);
    		$con->sql_freeresult();
    		if($level<$max_level)	$one_more_level = $level+1;
    		else    $one_more_level = $level;

			$filter[]="cc.p$level=$form[category_id]";
			$left_join.=" left join category_cache cc using (category_id)
			left join category on cc.p$one_more_level = category.id";
		}
		

		$time_printed=date("Y-m-d H:i:s");
		$smarty->assign("time_printed",$time_printed);
	
		$sip_filter=" and sip.branch_id=$branch_id";

		$extra_sql='';
		if ($form['price_list']){
			if ($form['price_list']["normal"]){
				$extra_sql=", ifnull(sip.price, si.selling_price) as ".$form['price_list']["normal"];
				$left_join.= " left join sku_items_price sip on si.id=sip.sku_item_id $sip_filter ";
			}
			
			//if still got other price list 
			if ($form['price_list']){
				$count='';
				
				foreach($form['price_list'] as $price_key => $price_code){
					if ($price_key=='normal')	continue;
					$count++;
					
					$extra_sql.=", sim$count.price as $price_code";

					$left_join.= " left join sku_items_mprice sim$count on si.id=sim$count.sku_item_id and sim$count.type=".ms($price_code)." and sim$count.branch_id=$branch_id ";
				}
			}
		}

		if ($form['sku_type']!= 'all')	$filter[]="sku.sku_type=".ms($form['sku_type']);
		if ($form['status']!= 'all')	$filter[]="si.active=$form[status]";

		if($form['blocked_po']){
			if($form['blocked_po']=='yes'){
				$filter[] = "si.block_list like ".ms("%i:$branch_id;s:2:\"on\";%");
			}elseif($form['blocked_po']=='no'){
				$filter[] = "(si.block_list not like ".ms("%i:$branch_id;s:2:\"on\";%")." or si.block_list is null)";
			}
		}
		
		if($vendor_id>0){
			$filter[] = "sku.vendor_id=$vendor_id";
			$arr_report_title[] = "Vendor: ".$this->vendor_list[$vendor_id]['description'];
		}else	$arr_report_title[] = "Vendor: All";

		if($brand_id===''){
			$arr_report_title[] = "Brand: All";
		}else{
			if($brand_id>0){
				$arr_report_title[] = "Brand: ".$this->brand_list[$brand_id]['description'];
			}else{
				$arr_report_title[] = "Brand: UNBRANDED";
			}
			$filter[]= "sku.brand_id=".mi($brand_id);
		}
		if ($filter)	$where="where ".implode(" and ",$filter);

		$order_by="order by $form[sort_by]";
		
		$sql="select si.* $extra_sql
			from sku_items si
			left join sku on si.sku_id=sku.id
			$left_join
			$where
			$order_by";

		$sql_rid=$con->sql_query($sql);
		while ($r=$con->sql_fetchassoc($sql_rid)){
			$table[$r['id']]['sku_item_code']=$r['sku_item_code'];
			$table[$r['id']]['mcode']=$r['mcode'];
			$table[$r['id']]['artno']=$r['artno'];
			$table[$r['id']]['description']=$r['description'];

			foreach($form['price_list'] as $price_code){
				$table[$r['id']]['price'][$price_code]=$r[$price_code];
			}
		}
		
		$report_title=implode("&nbsp;&nbsp;&nbsp;&nbsp;", $arr_report_title);
		
		$smarty->assign("generate",1);
		$smarty->assign("table",$table);
		$smarty->assign("price_col",$price_col);
		$smarty->assign("report_title",$report_title);
	}
}

$MASTERFILE_SKU_PRICE_LIST = new MASTERFILE_SKU_PRICE_LIST("SKU Price List");
?>
