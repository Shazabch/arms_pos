<?php
/*
8/18/2010 11:11:07 AM Justin
- Modified to have print report and export to excel format.

8/27/2010 4:19:52 PM Justin
- Modified to include cash need info.

6/24/2011 5:00:19 PM Andy
- Make all branch default sort by sequence, code.

1/14/2013 2:39 PM Justin
- Enhanced to pickup Voucher value and codes.

07/23/2013 04:07 PM Justin
- Added to pickup cost.
*/

include("include/common.php");
//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP')&&!privilege('RPT_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP and RPT_MEMBERSHIP', BRANCH_CODE), "/index.php");
$maintenance->check(34);

class Membership_Redemption_Ranking extends Module{
    function __construct($title){
        global $con, $smarty;

        if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		if(mi($_REQUEST['show_rows'])<=0)  $_REQUEST['show_rows'] = 10;
        // branches
        $con->sql_query("select * from branch order by sequence,code") or die(mysql_error());
        while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		// branch group
		// load header
		$con->sql_query("select * from branch_group");
		while($r = $con->sql_fetchrow()){
		    $branches_group['header'][$r['id']] =$r;
		}

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id
		order by branch.sequence, branch.code");
		while($r = $con->sql_fetchrow()){
	        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}

		$smarty->assign('branches_group',$branches_group);
		$smarty->assign('branches',$branches);

        parent::__construct($title);
	}
	
	function _default(){
		global $con,$smarty;
		
		$this->display();
	}
	
	function show_ranking($type='default'){
	    global $con,$smarty;

	   	if($type=='export_excel'){
		    $export_excel = true;
            include_once("include/excelwriter.php");
		}

	    $date_from = $_REQUEST['from'];
	    $date_to = $_REQUEST['to'];
	    $postcode = trim($_REQUEST['postcode']);
	    $state = trim($_REQUEST['state']);
	    $city = trim($_REQUEST['city']);
	    $top_or_btm = $_REQUEST['top_or_btm'];
	    $show_rows = mi($_REQUEST['show_rows']);
	    $sort_by = $_REQUEST['sort_by'];
	    $branch_id =get_request_branch(true);
	    $branches_group = $smarty->get_template_vars('branches_group');
	    
	    $this->top_or_btm = $top_or_btm;
	    $this->sort_by = $sort_by;
	    
		//print_r($_REQUEST);
		
		$filter = array();
		$filter[] = "mr.date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "mr.active=1 and mr.status=0";
		if($postcode)   $filter[] = "m.postcode=".ms($postcode);
		if($state)  $filter[] = "m.state=".ms($state);
		if($city)  $filter[] = "m.city=".ms($city);
		
		if($branch_id){
			if($branch_id>0){   // single branch
				$filter[] = "mr.branch_id=$branch_id";
			}else{  // branch group
				$bgid = abs($branch_id);
				if(!$branches_group['items'][$bgid])    $err[] = "Invalid Branch ID";
				else{
				    $bid_list = array();
					foreach($branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$filter[] = "mr.branch_id in (".join(',',$bid_list).")";
				}
			}
		}
		
		$filter = "where ".join(' and ',$filter);
		
		$sql ="select mr.*,m.postcode,m.state,m.city,mri.sku_item_id,mri.qty,mri.pt_need,mri.cash_need,si.sku_item_code,si.artno,si.mcode,si.description, mri.is_voucher, mri.voucher_value, mri.voucher_code, mri.cost
from membership_redemption mr
left join membership m on m.nric=mr.nric
left join membership_redemption_items mri on mri.membership_redemption_id=mr.id and mri.branch_id=mr.branch_id
left join sku_items si on si.id=mri.sku_item_id
$filter";

		//print $sql;
		$con->sql_query($sql) or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			if(!$r['sku_item_id']) continue;
			if($r['is_voucher'] && $r['voucher_code']){
				$r['voucher_code'] = join(", ", unserialize($r['voucher_code']));
				if($items_details[$r['sku_item_id']]['voucher_code']) $r['voucher_code'] = $items_details[$r['sku_item_id']]['voucher_code'].", ".$r['voucher_code'];
			}
			if($items_details[$r['sku_item_id']]['voucher_code']) $r['is_voucher'] = true;
			$items_details[$r['sku_item_id']] = $r;
			
			$table[$r['sku_item_id']]['sku_item_id'] = $r['sku_item_id'];
			$table[$r['sku_item_id']]['qty'] += $r['qty'];
			$table[$r['sku_item_id']]['pt_need'] += $r['pt_need'] * $r['qty'];
			$table[$r['sku_item_id']]['cash_need'] += $r['cash_need'] * $r['qty'];
		}
		$con->sql_freeresult();
		//print_r($table);
		
		if($table) usort($table, array($this, sort_data)); // sort table
		
		// cut down row
		if(count($table)>$show_rows){
			for($i = count($table)-1; $i>=$show_rows; $i--){
                unset($table[$i]);
			}
		}
		
		$smarty->assign('table',$table);
		$smarty->assign('items_details',$items_details);

		if($export_excel){
			$smarty->assign("no_header_footer", 1);
			$tmpname = "Redemption_Ranking_".time();
	        $output = "/tmp/".$tmpname.".xls";
            file_put_contents($output, ExcelWriter::GetHeader().$smarty->fetch("membership.redemption_ranking.tpl").ExcelWriter::GetFooter());
            exec("cd /tmp; zip -9 $tmpname.zip $tmpname*.xls");
			//ob_end_clean();
			//log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_REQUEST[title] To Excel($_REQUEST[report_title])");

			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=$tmpname.zip");
			readfile("/tmp/$tmpname.zip");
		}elseif($type=='print_report'){
			$smarty->assign("no_header_footer", 1);
			$smarty->assign("skip_header", 1);
			$smarty->display("membership.redemption_ranking.tpl");
		}else{
			$this->display();
		}
		
	}
	
	private function sort_data($a, $b){
	    $col = $this->sort_by;
	    
		if($this->top_or_btm=='top'){
			if($a[$col]>$b[$col])   return -1;
			elseif($a[$col]<$b[$col])   return 1;
		}else{
            if($a[$col]>$b[$col])   return 1;
			elseif($a[$col]<$b[$col])   return -1;
		}
	}

	function print_report(){
		$this->show_ranking('print_report');
	}

	function export_excel(){
		$this->show_ranking('export_excel');
	}
}

$Membership_Redemption_Ranking = new Membership_Redemption_Ranking('Redemption Ranking');
?>
