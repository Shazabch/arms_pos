<?
/*
Revision History
================
4/20/07 5:29:47 PM yinsee
- use GRR Received date instead of GRN Added date for date range comparison

11/21/2007 9:31:50 AM gary
- change the title to GRA Summary by Department.

11/17/2009 6:16:47 PM Andy
- change sql to get department id from gra

7/12/2010 5:11:37 PM Alex
- change date checking on gra.added if returned=0, gra.return_timestamp if returned=1, both if all

10/22/2010 3:53:27 PM Justin
- Fixed the Extra item which not from SKU amount does not sum up.
- Fixed the missing of vendor name and amount when view by vendor and it has only extra items.
- Fixed wrong output for those non-related department.
- Amended the amount round up from Items Not in SKU from 0 to 2.
- Replaced all the filter and vendor group by GRA instead of the GRA items.

10/29/2010 4:34:56 PM Alex
- add show cost privilege
- change use template goods_return_advice.summary_by_dept.table.tpl
- fix compare timestamp date bugs

6/24/2011 4:18:47 PM Andy
- Make all branch default sort by sequence, code.

9/15/2011 3:45:43 PM Justin
- Fixed the bugs where GRA missing by branch if filter branch as "All".
- Modified the status as below:
  => Saved - for those saved in GRA.
  => Completed (Not Returned) - for those already printed out the checklsit and awaiting for return.
  => Returned - for those already confirmed to return.
  
4/19/2012 9:48:59 AM Alex
- fix returned bugs while filtering => show_report()

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

9/25/2012 11:49 AM Justin
- Added to pickup disposal date.

7/9/2013 5:53 PM Justin
- Enhanced to  have checking on approved = 1.

4/20/2015 4:41 PM Justin
- Enhanced to have GST information.

5/21/2015 10:17 AM Justin
- Bug fixed sql errors.

6/29/2015 9:48 AM Justin
- Bug fixed on decimal points of rounding did not set.
- Bug fixed on non ARMS SKU items did not fully taken out all info.

11/30/2015 9:43 PM DingRen
- when calculate amount_gst, gst and amount and change to decimal 2 for extra

01-Feb-2016 14:48 Edwin
- Modified status filter list from "Saved" to "Saved & Waiting Approval" and "ALL" also include waiting approval query rules

29-Feb-2016 09:46 Edwin
- Bugs fixed on status filter in GRA summary

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

2/21/2017 2:55 PM Justin
- Bug fixed on branch code couldn't display while vendor is selected.

6/1/2017 2:58 PM Justin
- Bug fixed on terminated GRA will still show out when filter status "Completed".

6/9/2017 11:50 AM Justin
- Enhanced to have new status filter "Un-checkout".

7/3/2017 10:54 AM Justin
- Bug fixed on rounding issue that not tally with other GRA reports.

8/2/2017 2:19 PM Justin
- Bug fixed on filter "Date To" always added 1 day and which will causes the variance when compare with other GRA reports.
- Bug fixed on the GST and total GRA amount will not tally with the rest of GRA reports when the cost having decimal figures.

10/24/2017 4:23 PM Justin
- Bug fixed on system will not trigger any GRA if selecting same date for both date from/to.

5/7/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

12/12/2018 11:06 AM Justin
- Enhanced to show Rounding Adjust.

5/20/2019 4:54 PM William
- Pickup report_prefix for enhance "GRA".
*/
include("include/common.php");

$smarty->assign("sessioninfo",$sessioninfo);
if($config['gra_cost_decimal_points']) $dp = $config['gra_cost_decimal_points'];
else $dp = $config['global_cost_decimal_points'];
$smarty->assign("dp", $dp);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRA_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRA_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

if (isset($_REQUEST['branch_id']))
	$branch_id = intval($_REQUEST['branch_id']);
else
	$branch_id = $sessioninfo['branch_id'];


if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'print':
		
			$id = intval($_REQUEST['id']);
			// update the GRA amount
			$con->sql_query("select sum(qty*cost) from gra_items where gra_id=$id and branch_id = $branch_id");
			$amt = $con->sql_fetchrow();
			$con->sql_query("update gra set last_update=last_update,amount=".mf($amt[0])." where id=$id and branch_id = $branch_id");

			$con->sql_query("select gra.*, user.u, vendor.description as vendor,category.description as dept_code
from gra
left join user on gra.user_id = user.id
left join vendor on gra.vendor_id = vendor.id
left join category on gra.dept_id = category.id
where gra.id = $id and branch_id = $branch_id");
			$gra = $con->sql_fetchrow();
			if ($gra){
				$gra['misc_info'] = unserialize($gra['misc_info']);
  				$gra['extra']= unserialize($gra['extra']);
  				if($gra['extra']){
    				foreach ($gra['extra']['code'] as $idx=>$fn){
						$new[$idx]['code'] = $fn;
						$new[$idx]['description'] = $gra['extra']['description'][$idx];
   						$new[$idx]['cost'] = $gra['extra']['cost'][$idx];
   						$new[$idx]['qty'] = $gra['extra']['qty'][$idx];
						$new[$idx]['gst_id'] = $gra['extra']['gst_id'][$idx];
						$new[$idx]['gst_code'] = $gra['extra']['gst_code'][$idx];
						$new[$idx]['gst_rate'] = $gra['extra']['gst_rate'][$idx];
						$new[$idx]['doc_no'] = $gra['extra']['doc_no'][$idx];
						$new[$idx]['doc_date'] = $gra['extra']['doc_date'][$idx];
						$new[$idx]['reason'] = $gra['extra']['reason'][$idx];
					}
				}

				$q1=$con->sql_query("select gra_items.*, sku_item_code, artno, mcode,sku_items.link_code,
									 sku_items.description as sku,trade_discount_type.code as price_type,
									 gra_items.selling_price
									 from gra_items
									 left join sku_items on gra_items.sku_item_id = sku_items.id
									 left join sku on sku_items.sku_id=sku.id
									 left join trade_discount_type on sku.trade_discount_type=trade_discount_type.id
									 where gra_id = $id and gra_items.branch_id = $branch_id");
				$gra['items'] = $con->sql_fetchrowset();
				$smarty->assign("form", $gra);
				$smarty->assign("new", $new);

				// update printing counter
				if ($_REQUEST['a']=='print'){
				    if($_REQUEST['own_copy'])    $copy[] = 'own';
					if($_REQUEST['vendor_copy'])    $copy[] = 'vendor_copy';

					$con->sql_query("update gra set last_update=last_update,print_counter=print_counter+1 where gra.id = $id and branch_id = $branch_id");
					$con->sql_query("select * from branch where id = $branch_id");
					$smarty->assign("branch", $con->sql_fetchrow());

					$GRA_ITEMS_PER_PAGE = ($config['gra_print_item_per_page']>0)?$config['gra_print_item_per_page']:14;
					$GRA_ITEMS_PER_LAST_PAGE = $GRA_ITEMS_PER_PAGE - 5;

					$totalpage_sku = ceil(count($gra['items'])/$GRA_ITEMS_PER_PAGE);
					$totalpage_nonsku = ceil(count($new)/$GRA_ITEMS_PER_PAGE);
					$totalpage=$totalpage_sku+$totalpage_nonsku;

					$smarty->assign('show_total',1);

                    foreach($copy as $cp_type){
					    $smarty->assign('copy_type',$cp_type);
						// reset total
						$smarty->assign('total1_amt',0);
						$smarty->assign('total1_gst',0);
						$smarty->assign('total1_gst_amt',0);
					    $smarty->assign('total1_qty',0);
						$smarty->assign('ttl1_qty',0);
						$smarty->assign('ttl1_amt',0);
						$smarty->assign('ttl1_gst_amt',0);
						$smarty->assign('total_qty',0);
						$smarty->assign('total_amt',0);
						$smarty->assign('total_gst',0);
						$smarty->assign('total_gst_amt',0);
						$smarty->assign('ttl_qty',0);
						$smarty->assign('ttl_amt',0);
						$smarty->assign('ttl_gst',0);
						$smarty->assign('ttl_gst_amt',0);

						if($config['gra_alt_print_template'])    $print_tpl = $config['gra_alt_print_template'];
						else    $print_tpl = "goods_return_advice.$_REQUEST[a].tpl";
						
						if ($config['report_logo_by_branch']['gra'][get_branch_code($branch_id)]) $smarty->assign("alt_logo_img", $config['report_logo_by_branch']['gra'][get_branch_code($branch_id)]);

						if ($gra['items']){
							for ($i=0,$page=1;$page<=$totalpage_sku;$i+=$GRA_ITEMS_PER_PAGE,$page++){
								$smarty->assign("is_lastpage", ($page >= $totalpage));
						        $smarty->assign("page", "Page $page of $totalpage");

						        if($page >= $totalpage){
                                    $smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_LAST_PAGE);
								}else{
                                    $smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_PAGE);

								}
								$smarty->assign("items", array_slice($gra['items'],$i,$GRA_ITEMS_PER_PAGE));
						        $smarty->assign("start_counter", $i);
						        $smarty->assign("new", "");

								//$smarty->display("goods_return_advice.$_REQUEST[a].tpl");
								$smarty->display($print_tpl);
								$smarty->assign("skip_header",1);
								$total_p=$page;
							}
						}
					    if ($new){
							for($j=0,$page=1;$page<=$totalpage_nonsku;$j+=$GRA_ITEMS_PER_PAGE,$page++){
								/*for($k=0;$k<2;$k++){
									if($k==0)$smarty->assign("vendor_copy",1);
									else $smarty->assign("vendor_copy",0);

									$no_page=$total_p+$page;
									$smarty->assign("is_lastpage", ($page >= $totalpage_nonsku));
							        $smarty->assign("page", "Page $no_page of $totalpage");
							        $smarty->assign("start_counter", $j);
							        $smarty->assign("new", array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
									$smarty->display('goods_return_advice.print.tpl');
									$smarty->assign("skip_header",1);
								}*/

								$no_page=$total_p+$page;
								$smarty->assign('show_total',1);
								$smarty->assign("is_lastpage", ($no_page >= $totalpage));
						        $smarty->assign("page", "Page $no_page of $totalpage");
						        if($no_page >= $totalpage){
                                    $smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_LAST_PAGE);
								}else{
                                    $smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_PAGE);
								}
								$smarty->assign("new", array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
						        $smarty->assign("start_counter", $j);
/*						        print "<pre>";
						        print_r(array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
						        print "</pre>";
*/						        $smarty->assign("items", "");

						        $smarty->display($print_tpl);
								$smarty->assign("skip_header",1);
							}
						}
					}
				}
				else{
				    if($config['gra_'.$_REQUEST['a'].'_alt_print_template'])    $print_tpl = $config['gra_'.$_REQUEST['a'].'_alt_print_template'];
					else    $print_tpl = "goods_return_advice.$_REQUEST[a].tpl";
					$smarty->display($print_tpl);
					//$smarty->display("goods_return_advice.$_REQUEST[a].tpl");
				}

			}
			else{
			    $smarty->assign("url", "/goods_return_advice.php");
			    $smarty->assign("title", "Goods Return Advice");
			    $smarty->assign("subject", sprintf($LANG['GRA_INVALID_GRA_NO'], $id));
			    $smarty->display("redir.tpl");
			}
			exit;

	}
}


$smarty->assign("PAGE_TITLE", "GRA Summary by Department");

$con->sql_query("select id,code from branch where active=1 order by sequence,code");
$smarty->assign("branch", $con->sql_fetchrowset());

if (BRANCH_CODE == 'HQ')
	$con->sql_query("select id,description from vendor where id in (select distinct vendor_id from gra_items) order by description");
else
    $con->sql_query("select id,description from vendor where id in (select distinct vendor_id from gra_items where branch_id = $sessioninfo[branch_id]) order by description");
$smarty->assign("vendor", $con->sql_fetchrowset());

$con->sql_query("select id,description from category where level=2 and id in (".join(',',array_keys($sessioninfo['departments'])).") order by description");
$smarty->assign("dept", $con->sql_fetchrowset());

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

$smarty->display("goods_return_advice.summary_by_dept.tpl");

function show_report()
{
	global $con, $sessioninfo, $smarty, $config;
	$filter = array();

	if (!$_REQUEST['from'] or !$_REQUEST['to']) { print "Please select date from and to."; return;}

	$from_date = $_REQUEST['from'];
	$to_date = $_REQUEST['to'];

	if (BRANCH_CODE == 'HQ'){
	    if ($_REQUEST['branch_id']){
			$filter[] = "gra.branch_id = ".mi($_REQUEST['branch_id']);
		}
	}
	else{
	    $filter[] = "gra.branch_id = $sessioninfo[branch_id]";
	}

	$filter[] = "dept.id in (".join(",",array_keys($sessioninfo['departments'])).")";
	//if ($_REQUEST['department_id']) $where[] = "category.department_id = ".mi($_REQUEST['department_id']);
	if ($_REQUEST['department_id']) $filter[] = "gra.dept_id = ".mi($_REQUEST['department_id']);
	if ($_REQUEST['vendor_id']) $filter[] = "gra.vendor_id = ".mi($_REQUEST['vendor_id']);
	
	//$to_date = date("Y-m-d", strtotime('+1 day', strtotime($to_date)));
	$to_date = $to_date." 23:59:59";
	
	if (strcmp($_REQUEST['returned'],'')){
		if($_REQUEST['returned'] && $_REQUEST['returned'] != 3) $having = "having not_allow_checkout = 0";
		
		if($_REQUEST['returned'] == 0 || $_REQUEST['returned'] == 1){ 
			if($_REQUEST['returned'] == 0) {
				$filter[] = "gra.status in (0,2)";  // is saved and waiting approval
				$filter[] = "gra.approved = 0";
			}
			else {
				$filter[] = "gra.status = 0";	// is approved
				$filter[] = "gra.approved = 1"; 
			}
			$returned = 0;
		 	$filter[] = "gra.added between ".ms($from_date)." and ".ms($to_date);	
		}elseif($_REQUEST['returned'] == 2){ // is returned
			$returned = 1;
			$filter[] = "gra.status=0 and gra.approved = 1";
			$filter[] = "gra.return_timestamp between ".ms($from_date)." and ".ms($to_date);
		}elseif($_REQUEST['returned'] == 3){ // un-checkout
			$returned = 0;
			$filter[] = "gra.status in (0,2) and gra.returned = 0";
			$filter[] = "gra.added between ".ms($from_date)." and ".ms($to_date);
		}
		$filter[] = "gra.returned = ".mi($returned);
	}else{
		$filter[] = "((gra.status in (0,2) and gra.returned = 0 and gra.approved in (0,1) and gra.added between ".ms($from_date)." and ".ms($to_date).") or (gra.status = 0 and gra.returned = 1 and gra.approved = 1 and gra.return_timestamp between ".ms($from_date)." and ".ms($to_date)."))";
	}
	$where = join(" and ", $filter);

		//$returned=$_REQUEST['returned'];
	//here based on gra departments
	if($_REQUEST['vendor_id']){

		/*$r=$con->sql_query("select gra.*, vendor.description as vendor,category.description as dept_code, gra_items.vendor_id, vendor.description as vendor, dept.id as dept_id, dept.description as dept, branch.code as branch
	from gra
	left join gra_items on gra.id=gra_items.gra_id and gra.branch_id = gra_items.branch_id
	left join branch on gra_items.branch_id = branch.id
	left join vendor on gra_items.vendor_id = vendor.id
	left join sku_items on sku_item_id = sku_items.id
	left join sku on sku_id = sku.id
	left join category on sku.category_id = category.id
	left join category dept on category.department_id = dept.id
	 $where group by gra.id");*/

        $r=$con->sql_query("select gra.*, gra.amount,
        					gra.vendor_id,	gv.description as vendor,
							dept.description as dept_code, dept.id as dept_id, dept.description as dept,
							branch.code as branch,branch.report_prefix, count(if(gi.batchno=0, gi.id, null)) as not_allow_checkout,
							gv.code as vendor_code, if(bv.account_id = '' or bv.account_id is null, gv.account_id, bv.account_id) as account_id,
							sum(round(gi.gst, 2)) as gst, branch.code as branch_code
							from gra
							left join gra_items gi on gra.id=gi.gra_id and gra.branch_id = gi.branch_id
							left join branch on gra.branch_id = branch.id
							left join vendor gv on gra.vendor_id = gv.id
							left join branch_vendor bv on bv.vendor_id = gv.id and bv.branch_id = gra.branch_id
							left join category dept on gra.dept_id = dept.id
		 					where $where
							group by gra.id, gra.branch_id
							$having");

		if (!$con->sql_numrows($r))
		{
		    print "<p>-- No Record --</p>";
		    return;
		}

		$is_under_gst = $have_fc = $got_rounding_adj = 0;
		while ($gra = $con->sql_fetchassoc($r)){
			if($config['gra_enable_disposal']){
				if($gra['return_timestamp'] == 0 || !$gra['returned_by']) $gra['disposal_date'] = date("Y-m-d H:i:s", strtotime("+1 month", strtotime($gra['added'])));
				else $gra['disposal_date'] = $gra['return_timestamp'];
			}
			$gra['misc_info'] = unserialize($gra['misc_info']);
			$gra['extra']= unserialize($gra['extra']);

			/*
			$r1=$con->sql_query("select count(*) from gra_items left join gra on gra.id=gra_items.gra_id and gra.branch_id=gra_items.branch_id where gra.id=$gra[id] and batchno = 0 and gra.branch_id=$branch_id order by gra.last_update");
			$temp=$con->sql_fetchrow();
			$gra['not_allow_checkout'] = $temp[0];
			*/
			
			if($gra['is_under_gst']){
				$is_under_gst = 1;
				if($gra['extra']){
					foreach($gra['extra']['code'] as $idx=>$code){
						$qty = $gra['extra']['qty'][$idx];
						$cost = $gra['extra']['cost'][$idx];
						$gst_rate = $gra['extra']['gst_rate'][$idx];

						$row_amount=$qty*$cost;
						$row_gst_amt=round($row_amount * ((100+$gst_rate)/100),2);
						$row_amount=round($row_amount,2);
						$extra_gst=$row_gst_amt-$row_amount;

						//$extra_gst = round($qty*$cost*$gst_rate/100, 2);
						$gra['gst'] += $extra_gst;
					}
				}
			}else{
				$gra['gst'] = 0;
			}

			if($gra['currency_code']) $have_fc = 1;
			
			if($gra['rounding_adjust']) $got_rounding_adj = 1;
			
			$gra_list[]=$gra;
		}
		$con->sql_freeresult($r);
		//echo"<pre>";print_r($gra_list);echo"</pre>";
		$form=$_REQUEST;
		$form['is_summary']=1;
		$smarty->assign("form",$form);
		$smarty->assign("gra_list",$gra_list);
		$smarty->assign("have_fc", $have_fc);
		$smarty->assign("is_under_gst",$is_under_gst);
		$smarty->assign("got_rounding_adj",$got_rounding_adj);
		$smarty->display("goods_return_advice.list.tpl");
	}
	else{
		/*$con->sql_query("select gra.extra_amount, sum(qty) as qty, sum(qty*cost) as amt, gra_items.vendor_id, vendor.description as vendor, dept.id as dept_id, dept.description as dept, branch.code as branch
	from gra_items
	left join gra on gra.id=gra_items.gra_id and gra.branch_id = gra_items.branch_id
	left join branch on gra_items.branch_id = branch.id
	left join vendor on gra_items.vendor_id = vendor.id
	left join sku_items on sku_item_id = sku_items.id
	left join sku on sku_id = sku.id
	left join category on sku.category_id = category.id
	left join category dept on category.department_id = dept.id
	$where group by vendor_id, dept_id");*/

	    $q1 = $con->sql_query("select gra.extra_amount, sum(gi.qty) as qty, sum(gi.amount) as amt, gra.vendor_id,
							 gv.description as vendor, dept.id as dept_id, dept.description as dept, branch.code as branch,
							 count(if(gi.batchno=0, gi.id, null)) as not_allow_checkout, gra.is_under_gst, gra.extra,
							 sum(gi.gst) as gst, gra.currency_code, gra.currency_rate
							 from gra
							 left join gra_items gi on gra.id=gi.gra_id and gra.branch_id = gi.branch_id
							 left join branch on gi.branch_id = branch.id
							 left join vendor gv on gra.vendor_id = gv.id
							 left join sku_items on sku_item_id = sku_items.id
							 left join category dept on gra.dept_id = dept.id
							 where $where 
							 group by vendor_id, dept_id, gra.id, gra.branch_id
							 $having");

		if (!$con->sql_numrows($q1))
		{
		    print "<p>-- No Record --</p>";
		    return;
		}

		$is_under_gst = 0;
		$have_fc_list = array();
		while($r=$con->sql_fetchassoc($q1)){
			$r['misc_info'] = unserialize($r['misc_info']);
			$r['extra']= unserialize($r['extra']);
			$dept[$r['dept_id']] = $r['dept'];
			$vendor[$r['vendor_id']] = $r['vendor'];

			if($r['is_under_gst']){
				$is_under_gst = 1;
				if($r['extra']){
					foreach($r['extra']['code'] as $idx=>$code){
						$qty = $r['extra']['qty'][$idx];
						$cost = $r['extra']['cost'][$idx];
						$gst_rate = $r['extra']['gst_rate'][$idx];

						$row_amount=$qty*$cost;
						$row_gst_amt=round($row_amount * ((100+$gst_rate)/100),2);
						$row_amount=round($row_amount,2);
						$extra_gst=$row_gst_amt-$row_amount;

						//$extra_gst = round($qty*$cost*$gst_rate/100, 2);
						$r['extra_gst'] += $extra_gst;
						$r['extra_qty'] += $qty;
					}
				}
			}else{
				$r['extra_gst'] = $r['gst'] = 0;
			}

			$r['amt'] = round($r['amt'], 2);
			$r['gst'] = round($r['gst'], 2);
			
			// build up foreign amount
			if($r['currency_code']){
				$tb[$r['vendor_id']][$r['dept_id']]['foreign_amt'] += $r['amt'];
				$tb[$r['vendor_id']]['foreign_extra'] += $r['extra_amount'];
				$vendor_total[$r['vendor_id']]['foreign_amt'] += $r['amt']+$r['extra_amount'];
				$dept_total[$r['dept_id']]['foreign_amt'] += $r['amt'];
				$extra_total['foreign_amt'] += $r['extra_amount'];
				$final_total['foreign_amt'] += $r['amt']+$r['extra_amount'];
				
				// multiply with exchange rate
				$r['amt'] *= $r['currency_rate'];
				$r['extra_amount'] *= $r['currency_rate'];
			}
			
			$tb[$r['vendor_id']][$r['dept_id']]['qty'] += $r['qty'];
		    $tb[$r['vendor_id']][$r['dept_id']]['amt'] += $r['amt'];
		    $tb[$r['vendor_id']][$r['dept_id']]['gst'] += $r['gst'];
		    $tb[$r['vendor_id']][$r['dept_id']]['gst_amt'] += $r['amt']+$r['gst'];
		    $tb[$r['vendor_id']]['extra'] += $r['extra_amount'];
		    $tb[$r['vendor_id']]['extra_gst'] += $r['extra_gst'];
		    $tb[$r['vendor_id']]['extra_gst_amt'] += $r['extra_amount']+$r['extra_gst'];

			$vendor_total[$r['vendor_id']]['qty'] += $r['qty']+$r['extra_qty'];
		    $vendor_total[$r['vendor_id']]['amt'] += $r['amt']+$r['extra_amount'];
		    $vendor_total[$r['vendor_id']]['gst'] += $r['gst']+$r['extra_gst'];
		    $vendor_total[$r['vendor_id']]['gst_amt'] += $r['amt']+$r['gst']+$r['extra_amount']+$r['extra_gst'];
		    
		    
    	  	$dept_total[$r['dept_id']]['qty'] += $r['qty'];
	    	$dept_total[$r['dept_id']]['amt'] += $r['amt'];
	    	$dept_total[$r['dept_id']]['gst'] += $r['gst'];
	    	$dept_total[$r['dept_id']]['gst_amt'] += $r['amt']+$r['gst'];
	    	
	    	$extra_total['qty'] += $r['extra_qty'];
	    	$extra_total['amt'] += $r['extra_amount'];
	    	$extra_total['gst'] += $r['extra_gst'];
	    	$extra_total['gst_amt'] += $r['extra_amount']+$r['extra_gst'];
	    	
	    	$final_total['qty'] += $r['qty']+$r['extra_qty'];
	    	$final_total['amt'] += $r['amt']+$r['extra_amount'];
	    	$final_total['gst'] += $r['gst']+$r['extra_gst'];
	    	$final_total['gst_amt'] += $r['amt']+$r['gst']+$r['extra_amount']+$r['extra_gst'];
			
			if($r['currency_code']){
				$have_fc_list[$r['dept_id']] = 1;				
			}
	    	
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("tb",$tb);
		$smarty->assign("dept",$dept);
		$smarty->assign("vendor",$vendor);
		$smarty->assign("vendor_total",$vendor_total);
		$smarty->assign("dept_total",$dept_total);
		$smarty->assign("extra_total",$extra_total);
		$smarty->assign("final_total",$final_total);
		$smarty->assign("is_under_gst",$is_under_gst);
		$smarty->assign("have_fc_list",$have_fc_list);

		$smarty->display("goods_return_advice.summary_by_dept.table.tpl");
		
	}
}

