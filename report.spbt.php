<?php

/*
5/27/2010 5:19:16 PM Alex
- create new spbt report

6/21/2010 4:53:00 PM Alex
- Change count soi qty to doi qty while deliver

7/5/2010 3:53:32 PM Alex
- Change title to sales order and check do status while request deliver

7/9/2010 4:35:36 PM Alex
-Add area filter

1/21/2011 4:46:43 PM Alex
- change use report_server

6/24/2011 6:35:56 PM Andy
- Make all branch default sort by sequence, code.

11/23/2011 2:13:59 PM Andy
- Add can view by qty or amt.

12/5/2011 2:20:04 PM Alex
- fix separate parts bugs

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

4/20/2017 10:25 AM Justin
- Enhanced to have privilege checking.
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
if (!privilege('SO_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SO_REPORT', BRANCH_CODE), "/index.php");
//$maintenance->check(13);

class SPBT extends Report
{
	var $column_limit = 100;

    private function run_report($bid_list)
	{
	    global $con, $smarty, $con_multi;

        $parts = 1;
        $countc = 1;
        $col_limit = $this->column_limit;
		$bid = join(",",$bid_list);
		$batch_code = $_REQUEST['batch_code'];

		if ($_REQUEST['area_code']=='all')	$filter_area='';
		else{
			if ($_REQUEST['area_code']=='Undefined')
				$filter_area="and d.area=''";
			else
			    $filter_area="and d.area=".ms($_REQUEST['area_code']);
		}

		$sql = "select soi.branch_id, soi.sku_item_id, si.receipt_description as si_rdes, si.artno as si_artno,si.description as si_des,d.id as d_id,d.code as d_code,d.description as d_des,d.area as d_area, ((soi.ctn*uom.fraction)+soi.pcs) as qty, ((soi.ctn*uom.fraction)+soi.pcs)*(soi.selling_price/uom.fraction) as amt, soi.item_discount_amount
				from sales_order_items soi
				left join sales_order so on so.id=soi.sales_order_id and so.branch_id=soi.branch_id
				left join uom on uom.id=soi.uom_id
				left join sku_items si on si.id= soi.sku_item_id
				left join debtor d on d.id=so.debtor_id
				where so.active=1 and so.status=1 and so.approved=1 and soi.branch_id in ($bid) and so.batch_code='".$batch_code."' $filter_area
				order by si_rdes";
		//print $sql;
		$con_multi->sql_query($sql) or die(sql_error());

		$sid_list = array();
		if($con_multi->sql_numrows()>0){
		    while($t = $con_multi->sql_fetchrow()){
				$amt = round($t['amt'] - $t['item_discount_amount'],2);

				$area[$t['d_area']][$t['d_id']]['d_id'] = $t['d_id'];
				$area[$t['d_area']][$t['d_id']]['d_des'] = $t['d_des'];
				$area[$t['d_area']][$t['d_id']]['d_code'] = $t['d_code'];

				if ($_REQUEST['view_type']=='deliver'){
					$area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['order_qty'] += $t['qty'];
					$area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['order_amt'] += $amt;
				}else{
					$area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['qty'] += $t['qty'];
					$area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['amt'] += $amt;
				}

				$total[$t['d_area']]['order'][$t['d_id']]['total_qty'] += $t['qty'];
				$total[$t['d_area']]['order'][$t['d_id']]['total_amt'] += $amt;
				
				if (!$t['si_rdes']){
					$si_artno=join('<br />',str_split($t['si_artno']));
	                $items[$parts][$t['sku_item_id']]['si_artno'] = $si_artno;
				}
				else{
					$si_rdes=join('<br />',str_split($t['si_rdes']));
	                $items[$parts][$t['sku_item_id']]['si_artno'] = $si_rdes;
				}

//                $items[$parts][$t['sku_item_id']]['si_des'] = $t['si_des'];
//                $items[$t['sku_item_id']]['total_qty'] += $t['qty'];

				//Count column limitation
				if ($countc == $col_limit){
					$parts += 1;
					$countc = 0;
				}
				
				if(!in_array($t['sku_item_id'], $sid_list))	$countc += 1;
				$sid_list[$t['sku_item_id']]=$t['sku_item_id'];
				//if ($skuitemid != $t['sku_item_id'])	$countc += 1;
			 	//$skuitemid =$t['sku_item_id'];
			 	
			 	if ($_REQUEST['view_type']=='deliver'){
					unset($area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['qty']);
					unset($area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['amt']);
				}
			}
			unset($sid_list);
		}
		$con_multi->sql_freeresult();

       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter="and do.checkout=1";
       	else    $filter="";

		if ($_REQUEST['view_type']=='deliver'){
			$sql2 = "select soi.branch_id, soi.sku_item_id, si.receipt_description as si_rdes, si.sku_item_code as si_code, si.artno as si_artno,si.description as si_des,d.id as d_id,d.code as d_code,d.description as d_des,d.area as d_area,  ((doi.ctn*uom.fraction)+doi.pcs) as qty, ((soi.ctn*uom.fraction)+soi.pcs)*(soi.selling_price/uom.fraction) as amt, soi.item_discount_amount
					from sales_order_items soi
					left join sales_order so on so.id=soi.sales_order_id and so.branch_id=soi.branch_id
					left join sku_items si on si.id= soi.sku_item_id
					left join debtor d on d.id=so.debtor_id
					left join do on do.ref_no=so.order_no and do.ref_tbl='sales_order'
					left join do_items doi on doi.do_id=do.id and doi.branch_id=do.branch_id and doi.sku_item_id=soi.sku_item_id
					left join uom on uom.id=doi.uom_id
					where so.batch_code='".$batch_code."'
					and soi.branch_id in ($bid)
					and do.active=1 and do.status=1 and do.approved=1 and so.delivered=1 $filter $filter_area
					order by si_rdes";
			//print $sql2;
			$con_multi->sql_query($sql2) or die(sql_error());

			if($con_multi->sql_numrows()>0){
			    while($t = $con_multi->sql_fetchrow()){
			    	$amt = round($t['amt'] - $t['item_discount_amount'],2);
			    	
					$area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['qty'] += $t['qty'];
					$area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['amt'] += $amt;
					$total[$t['d_area']]['deliver'][$t['d_id']]['total_qty'] += $t['qty'];
					$total[$t['d_area']]['deliver'][$t['d_id']]['total_amt'] += $amt;
				}
			}
			$con_multi->sql_freeresult();
		}

		//print $sql;exit;

		$col = count($items);
		for ($i=1;$i<=$col;$i++){
           	$colspan[$i] = count($items[$i]);
		}
		
		//print_r($area);
//		print_r($items);
//        print_r($colspan);
        //print_r($total);

		$smarty->assign('area',$area);
		$smarty->assign('total',$total);
		$smarty->assign('items',$items);
		$smarty->assign('col',$colspan);
		$smarty->assign('parts',$parts);
	}

	function generate_report()
	{
		global $con, $smarty;

       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $add_title="(Check Out)";
       	else    $add_title="";

		$branch_id =get_request_branch(true);
		$branches_group = $this->branch_group;
	    $con->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}

		$bid_list = array();
		if($branch_id>0){   // selected single branch
            $bid_list[] = $branch_id;
            $report_header[] = "Branch: ".$branches[$branch_id]['code'];
		}else{
			if($branch_id<0){   // negative branch id is branch group
                $bgid = abs($branch_id);
				if(!$branches_group['items'][$bgid])    $err[] = "Invalid Branch.";
				else{
					foreach($branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$report_header[] = "Branch Group: ".$branches_group['header'][$bgid]['code'];
				}
			}else{  // all branches
				foreach($branches as $b){
                    $bid_list[] = $b['id'];
				}
				$report_header[] = "Branch: All";
			}
		}

		$this->run_report($bid_list);
		$this->default_values();
        $table = $this->table;
		
		$report_header[] = "View By: ".ucwords($this->view_by);
		
		$report_title = join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header);
        $report_title .= "&nbsp;&nbsp;&nbsp;&nbsp;View Type: ".ucwords($_REQUEST['view_type'].$add_title);
		
		$smarty->assign('report_title',$report_title);
	}

	function process_form()
	{
		$this->view_by = trim($_REQUEST['view_by']);
		if($this->view_by != 'qty' && $this->view_by != 'amt')	$this->view_by = 'qty';
	}

	function ajax_change_area_code()
	{
		global $smarty, $con_multi;

       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter="and do.checkout=1";
       	else    $filter="";
       	
		$branch_id =get_request_branch(true);

		$sel1 = "<select class='form-control' name='batch_code'>";
		$sel2 = "<select class='form-control' name='area_code' onchange='change_batch_code();'>";
        $sel2_data = "<option value='all' />----All----</option>";

/*
		if ($_REQUEST['view_type']=='deliver') {
			$sql2 = "Select distinct d.area, so.batch_code from sales_order so
			        left join do on do.ref_tbl='sales_order' and do.ref_no=so.order_no and do.branch_id=so.branch_id
					left join debtor d on d.id=so.debtor_id
					where do.active=1 and do.status=1 and do.approved=1 and so.delivered=1 and so.branch_id=$branch_id $filter
					order by d.area, so.batch_code";

		}else{
	      
		}
*/
  		$sql2 = "Select distinct d.area, so.batch_code from sales_order so
	   				left join debtor d on d.id=so.debtor_id
					where so.active=1 and so.status=1 and so.approved=1 and so.branch_id=$branch_id
					order by d.area, so.batch_code";

		$con_multi->sql_query($sql2) or die(sql_error());


		if($con_multi->sql_numrows()>0){
		    while($b = $con_multi->sql_fetchrow()){

				if (!isset($batch[$b['batch_code']])){
					$sel1 .= "<option value='".$b['batch_code']."' />".$b['batch_code']."</option>";
				
					$batch[$b['batch_code']]=1;
				}
				
		        if (!$b['area'])    $area_code='Undefined';
		        else    $area_code=$b['area'];

				if (!isset($area[$area_code])){
   					$sel2_data .= "<option value='".$area_code."' />".$area_code."</option>";
					$area[$area_code]=1;
				}
			}

			$con_multi->sql_freeresult();
		}
		else{
        	$sel2_data = "<option value='' />-- No Data --</option>";
		}

		$sel1 .= "</select>";
		$sel2 .= $sel2_data."</select>";

		$ret['sel1'] = $sel1;
		$ret['sel2'] = $sel2;
		print json_encode($ret);
	}

	function ajax_change_code()
	{
		global $con_multi, $smarty;

       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter="and do.checkout=1";
       	else    $filter="";

		if ($_REQUEST['area_code']=='all')	$filter_area='';
		else{
			if ($_REQUEST['area_code']=='Undefined')
				$filter_area="and d.area=''";
			else
			    $filter_area="and d.area=".ms($_REQUEST['area_code']);
		}
		$branch_id =get_request_branch(true);
		
		print "<select name='batch_code'>";
/*
		if ($_REQUEST['view_type']=='deliver') {
			$sql2 = "Select distinct d.area, so.batch_code from sales_order so
			        left join do on do.ref_tbl='sales_order' and do.ref_no=so.order_no and do.branch_id=so.branch_id
					left join debtor d on d.id=so.debtor_id
					where do.active=1 and do.status=1 and do.approved=1 and so.delivered=1 $filter and so.branch_id=$branch_id $filter_area
					order by d.area, so.batch_code";

		}else{
		}
*/
        $sql2 = "Select distinct d.area, so.batch_code from sales_order so
	   				left join debtor d on d.id=so.debtor_id
					where so.active=1 and so.status=1 and so.approved=1 and so.branch_id=$branch_id $filter_area
					order by d.area, so.batch_code";

 		$con_multi->sql_query($sql2) or die(sql_error());
//print $sql2;

		if($con_multi->sql_numrows()>0){
		    while($b = $con_multi->sql_fetchrow()){
			    if (!isset($batch[$b['batch_code']])){
					print "<option value='".$b['batch_code']."' />".$b['batch_code']."</option>";
					$batch[$b['batch_code']]=1;
				}
			}
			
			$con_multi->sql_freeresult();
		}

		print "</select>";
	}

	function default_values()
	{
		global $con, $smarty,$sessioninfo, $con_multi;
		$i=0;
		$area=array();
		$batch=array();
		$after_batch=array();
		
       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter="and do.checkout=1";
       	else    $filter="";

  		if (BRANCH_CODE=="HQ"){
			if ($_REQUEST['branch_id'])	$branch_id =get_request_branch(true);
			else{

				$con->sql_query("select id from branch where active=1 order by sequence,code");

				while($r = $con->sql_fetchrow()){
					$branches[$i] = $r['id'];
					$i += 1;
				}
	            $branch_id=$branches[0];
			}
		}
		else	$branch_id=$sessioninfo['branch_id'];

/*
		if ($_REQUEST['view_type']=='deliver') {
			$sql2 = "Select distinct d.area, so.batch_code from sales_order so
			        left join do on do.ref_tbl='sales_order' and do.ref_no=so.order_no and do.branch_id=so.branch_id
					left join debtor d on d.id=so.debtor_id
					where do.active=1 and do.status=1 and do.approved=1 and so.delivered=1 $filter and so.branch_id=$branch_id 
					order by d.area, so.batch_code";

		}else{
		}
*/

        $sql2 = "Select distinct d.area, so.batch_code from sales_order so
	   				left join debtor d on d.id=so.debtor_id
					where so.active=1 and so.status=1 and so.approved=1 and so.branch_id=$branch_id
					order by d.area, so.batch_code";

		$con_multi->sql_query($sql2) or die(sql_error());

		if($con_multi->sql_numrows()>0){
		    while($b = $con_multi->sql_fetchrow()){
		        if (!$b['area'])    $area_code='Undefined';
				else    $area_code=$b['area'];
		        if (!isset($area[$area_code])) $area[$area_code]=1;
		        if (!isset($batch[$b['batch_code']])) $batch[$b['batch_code']]=1;
				$after_batch[$area_code][$b['batch_code']]=1;
			}
			$con_multi->sql_freeresult();
		}

        $smarty->assign('d_area',$area);
        
        if ($_REQUEST['area_code']=='all' || !$_REQUEST['area_code']) $smarty->assign('batch',$batch);
        else	$smarty->assign('batch',$after_batch[$_REQUEST['area_code']]);
        
 //       if (!$_REQUEST['area_code'] || $_REQUEST['area_code']=='all') $smarty->assign('batch',$batch);
 //       else	$smarty->assign('batch',$after_batch[$_REQUEST['area_code']]);

	}
}

$con_multi = new mysql_multi();
$report = new SPBT('Sales Order Report');
$con_multi->close_connection();
?>
