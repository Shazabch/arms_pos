<?php

/*
5/24/2010 10:37:48 AM Alex
- create new spbt report

6/21/2010 4:52:54 PM Alex
- Change count soi qty to doi qty while deliver

7/5/2010 3:53:32 PM Alex
- Change title to sales order and check do status while request deliver

7/9/2010 4:35:36 PM Alex
-Add area filter

1/21/2011 4:46:43 PM Alex
- change use report_server

6/24/2011 6:36:30 PM Andy
- Make all branch default sort by sequence, code.

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

class SPBT_SUMMARY extends Report
{
	//Set column
	var $output_col = 3;

	private function run_report($bid_list)
	{
	    global $con_multi, $smarty;

		$num=1;
		
		$bid = join(",",$bid_list);
		if ($_REQUEST['batch_code']!='all'){
			$filter[]= "so.batch_code=".ms($_REQUEST['batch_code']);
		}

       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter_checkout="and do.checkout=1";


		if ($_REQUEST['area_code']!='all'){
			if ($_REQUEST['area_code']=='Undefined')
				$filter[]="d.area=''";
			else
			    $filter[]="d.area=".ms($_REQUEST['area_code']);
		}

		if ($filter)	$filter= " and ".join(' and ', $filter);

		$sql = "select soi.sku_item_id, si.receipt_description as si_rdes,si.artno as si_artno, d.area as d_area, sum((soi.ctn*uom.fraction)+soi.pcs) as qty
				from sales_order_items soi
				left join sales_order so on so.id=soi.sales_order_id and so.branch_id=soi.branch_id
				left join uom on uom.id=soi.uom_id
				left join sku_items si on si.id= soi.sku_item_id
				left join debtor d on d.id=so.debtor_id
				where so.active=1 and so.status=1 and so.approved=1 and soi.branch_id in ($bid) $filter
				group by d_area, soi.sku_item_id
				order by si_rdes";

		$con_multi->sql_query($sql) or die(sql_error());

		if($con_multi->sql_numrows()>0){
		    while($t = $con_multi->sql_fetchrow()){

                if ($_REQUEST['view_type']=='deliver')
					$area[$t['d_area']]['items'][$t['sku_item_id']]['order_qty'] += $t['qty'];
				else
                	$area[$t['d_area']]['items'][$t['sku_item_id']]['qty'] = $t['qty'];
				

                $items[$t['sku_item_id']]['si_des'] = $t['si_des'];
//                $items[$t['sku_item_id']] = $num;
                
                if ( $si_id != $t['sku_item_id']){
	                $des[$num]['si_rdes'] = $t['si_rdes'];
   	                $des[$num]['si_artno'] = $t['si_artno'];
	                $des[$num]['num'] = $num;
	                $des[$num]['sku_item_id'] = $t['sku_item_id'];
//	                $des[$num]['qua']= $t['qty'];
					$num += 1;
					$si_id= $t['sku_item_id'];
                }
			}

			$con_multi->sql_freeresult();
		}

		if ($_REQUEST['view_type']=='deliver'){
		$sql2 = "select soi.sku_item_id, si.receipt_description as si_rdes, si.description as si_des,d.area as d_area,  sum((doi.ctn*uom.fraction)+doi.pcs) as qty
				from sales_order_items soi
				left join sales_order so on so.id=soi.sales_order_id and so.branch_id=soi.branch_id
				left join sku_items si on si.id= soi.sku_item_id
				left join debtor d on d.id=so.debtor_id
				left join do on do.ref_no=so.order_no and do.ref_tbl='sales_order'
				left join do_items doi on doi.do_id=do.id and doi.branch_id=do.branch_id and doi.sku_item_id=soi.sku_item_id
				left join uom on uom.id=doi.uom_id
				where soi.branch_id in ($bid)
				and do.active=1 and do.status=1 and do.approved=1 and so.delivered=1 $filter_checkout $filter
				group by d_area, soi.sku_item_id
				order by si_rdes";

			$con_multi->sql_query($sql2) or die(sql_error());

			if($con_multi->sql_numrows()>0){
			    while($t = $con_multi->sql_fetchrow()){

					$area[$t['d_area']]['items'][$t['sku_item_id']]['qty'] = $t['qty'];
//					$area[$t['d_area']][$t['d_id']]['items'][$t['sku_item_id']]['deliver_qty'] += $t['qty'];
				}

				$con_multi->sql_freeresult();
			}
		}

		//print $sql;exit;
        $num=1;
		$col = count($items);
		$create_row=ceil($col/$this->output_col);

		for ($j=1;$j<=$this->output_col;$j++){

			for ($r=1;$r<=$create_row;$r++){
			    if ($des[$num])	$row[$r][$num]= $num;

				$num += 1;
			}
		}

		$get_row=count($row);
		for ($s=2;$s<=$get_row;$s++){
			$count_row1=count($row[$s-1]);
			$count_row2=count($row[$s]);

			if ($count_row2<$count_row1 ) $row[$s][$num]=$num;
			$num +=1 ;
		}

		
//		print_r($area);
//		print_r($items);
//		print_r($row);
//		print_r($des);
		$smarty->assign('area',$area);
		$smarty->assign('row',$row);
		$smarty->assign('des',$des);
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

		$report_title = join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header);
        $report_title .= "&nbsp;&nbsp;&nbsp;&nbsp;View Type: ".ucwords($_REQUEST['view_type'].$add_title);
		$smarty->assign('report_title',$report_title);

	}

	function process_form()
	{

	}

	function ajax_change_area_code()
	{
		global $con_multi, $smarty;

/*
       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter="and do.checkout=1";
       	else    $filter="";
*/

		$branch_id =get_request_branch(true);

		$sel1 = "<select class='form-control ' name='batch_code'>";
		$sel2 = "<select class='form-control ' name='area_code' onchange='change_batch_code();'>";
        $sel2_data = "<option value='all' />----All----</option>";

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
	        $sel1_data = "<option value='all' />----All----</option>";
		    while($b = $con_multi->sql_fetchrow()){

				if (!isset($batch[$b['batch_code']])){
					$sel1_data .= "<option value='".$b['batch_code']."' />".$b['batch_code']."</option>";

					$batch[$b['batch_code']]=1;
				}

				//Change to undefined area if empty area
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

		$sel1 .= $sel1_data."</select>";
		$sel2 .= $sel2_data."</select>";

		$ret['sel1'] = $sel1;
		$ret['sel2'] = $sel2;
		print json_encode($ret);

	}

	function ajax_change_code()
	{
		global $con_multi, $smarty;

/*
       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter="and do.checkout=1";
       	else    $filter="";
*/
		if ($_REQUEST['area_code']=='all')	$filter_area='';
		else{
			if ($_REQUEST['area_code']=='Undefined')
				$filter_area="and d.area=''";
			else
			    $filter_area="and d.area=".ms($_REQUEST['area_code']);
		}

		$branch_id =get_request_branch(true);
		
		print "<select  name='batch_code'>";

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

		if($con_multi->sql_numrows()>0){
	        print "<option value='all' />----All----</option>";
		    while($b = $con_multi->sql_fetchrow()){
				$batch[$b['batch_code']]=1;
				print "<option value='".$b['batch_code']."' />".$b['batch_code']."</option>";
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
/*
       	if ($_REQUEST['checkout']=="true" or $_REQUEST['checkout']=="on")   $filter="and do.checkout=1";
       	else    $filter="";
*/
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
	}
}

$con_multi = new mysql_multi();
$report = new SPBT_SUMMARY('Sales Order Summary Report');
$con_multi->close_connection();

?>
