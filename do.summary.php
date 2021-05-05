<?php
/*
4/28/2009 5:37:00 PM Andy
- add "b2.description as do_branch_description" in sql at function generate_report()

5/14/2009 1:07:44 PM yinsee
- filter active=1 only

7/24/2009 4:52:27 PM Andy
- Add total amount sum by item

8/13/2009 10:40:27 AM Andy
- join do summary and invoice summary
* 
2009/10/19 15:39:21 PM Andy
- add filter to only get DO in status (0,1,3)

11/10/2009 3:23:09 PM Andy
- edit invoice amount calculation

4/22/2010 4:15:43 PM Andy
- Add debtor filter for credit sales DO summary

5/14/2010 11:18:14 AM Andy
- Add Sales Person Name filter in DO Summary.

5/20/2010 10:37:56 AM Andy
- DO summary under "deliver to", add description for debtor.

6/24/2011 4:05:34 PM Andy
- Make all branch default sort by sequence, code.

11/29/2011 6:01:19 PM Alex
- fix approve filter no results

2/6/2012 2:38:43 PM Justin
- Added new filter option "Sales Agent", this filter will replace the existing "Sales Person" as if found config.
- Added to join DO items table to pick up total cost.

2/21/2012 10:13:43 AM Justin
- Fixed the total cost wrongly calculated.

3/26/2012 5:05:32 PM Justin
- Added to pickup trade discount type list.
- Added new filter "price_type".

11/29/2012 2:03 PM Andy
- Fix some user will not appear in the user list bug.

5/27/2015 2:39 PM Justin
- Enhanced to show GST information.

7/21/2015 9:21 AM Joo Chia
- Assign do_items GST info into tpl to show.

7/22/2015 11:21 AM Joo Chia
- Enhanced to have total gst.
- Show GST Error for record under GST but do not have GST id.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

5/31/2018 5:31 PM Andy
- Bug fixed on open tag for PHP was missing.
- Bug fixed on using array() instead of "" when the data to be stored as array.

6/20/2018 5:05 PM Andy
- Change when filter approved DO to exclude checkout DO.

8/9/2019 3:38 PM William
- Fixed bug do reject will not display.
- Fixed bug "branch" and "deliver To" filter display not active branch.
*/
include("include/common.php");
include("include/excelwriter.php");
include("include/class.report.php");
//$con = new sql_db('gmark-hq.arms.com.my:4001','arms','4383659','armshq');

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("do.include.php");

/*if (!isset($_REQUEST['p'])) $_REQUEST['p'] = 'do';

if ($_REQUEST['p'] == 'invoice')
$smarty->assign("PAGE_TITLE", "Invoice Summary");
elseif ($_REQUEST['p'] == 'do')*/
$smarty->assign("PAGE_TITLE", "DO Summary");

if($sessioninfo['id'] != 1){
	$user_filter = "where (user.is_arms_user=0 or do.user_id=".mi($sessioninfo['id']).")";
}
$con->sql_query("select distinct(user.id) as id, user.u from do left join user on user_id = user.id $user_filter group by id");
$smarty->assign("user", $con->sql_fetchrowset());

$con->sql_query("select id,code from branch where active=1 order by sequence,code");
$smarty->assign("branch", $con->sql_fetchrowset());

$con->sql_query("select * from debtor where active=1 order by code");
$smarty->assign("debtors", $con->sql_fetchrowset());

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

if(($config['do_credit_sales_show_sales_person_name'] || $config['do_cash_sales_show_sales_person_name']) && !$config['masterfile_enable_sa']){
	$con->sql_query("select distinct(sales_person_name) as sales_person_name, sales_person_name as id from do where sales_person_name<>'' and sales_person_name is not null order by sales_person_name");
	$smarty->assign('sales_agent_list', $con->sql_fetchrowset());
}else{
	$con->sql_query("select concat(code, ' - ', name) as sales_person_name, id from sa where active=1 order by code, name");
}
$smarty->assign('sales_agent_list', $con->sql_fetchrowset());
$con->sql_freeresult();
//if (isset($_REQUEST['submit'])) generate_report();

$con->sql_query("select * from trade_discount_type order by code");
$smarty->assign('pt_list', $con->sql_fetchrowset());
$con->sql_freeresult();

if(isset($_REQUEST['output_excel'])) 
  export_excel();
else
{
    generate_report();
    $smarty->display("do.summary.tpl");
}
exit;

function generate_report($exc = false)
{
	global $smarty, $con, $config;
	//print_r($_REQUEST);
	
	$is_under_gst = 0;
	$bid = get_request_branch(true);
	if ($bid) $where[] = 'do.branch_id = '.$bid;
	if ($_REQUEST['user_id']>0) $where[] = 'do.user_id = '.mi($_REQUEST['user_id']);
	elseif ($_REQUEST['deliver_to']!='')
		$where[] = "(do.do_branch_id = ".mi($_REQUEST['deliver_to'])." or do.deliver_branch like '%\"".mi($_REQUEST['deliver_to'])."\"%')";
/*	if ($_REQUEST['status']==1)//draft
		$where[] = "do.status = 0";
	elseif ($_REQUEST['status']==2)//approved
		$where[] = "do.approved = 1 and do.checkout = 0 and do.active=1";
	elseif ($_REQUEST['status']==3)//checkout
		$where[] = "do.approved = 1 and do.checkout = 1 and do.active=1";
*/

	if ($_REQUEST['p'] == 'do'||$_REQUEST['p']==''){    // do
        $order = 'order by do.do_date';
	}
	
	if ($_REQUEST['p'] == 'invoice'||$_REQUEST['p']==''){   // invoice
        if($_REQUEST['p'] == 'invoice')	$_REQUEST['status'] = 3;
        
		//$order = 'order by do.do_no desc';
        
        if ($_REQUEST['markup'] == 1)
		{
			$where[] = "invoice_markup <> ''";
		}
		elseif ($_REQUEST['markup'] == 2)
		{
			$where[] = "(isnull(invoice_markup) or invoice_markup = 0)";
		}
	}
	
	$where[] = "branch.active=1";
	$where[] = "do.active=1";
	switch ($_REQUEST['status'])
	{
		case 1: // show saved DO
        	$where[] = "do.status in (0,1,2) and do.approved=0 and do.checkout=0";
        	break;
		case 2: // show approved
		    $where[] = "do.approved=1 and do.checkout=0";
		    break;
		case 3: // show checkout
		    $where[] = "do.approved=1 and do.checkout=1 ";
		    break;
	}
	if(!$_REQUEST['status'])	$where[] = "do.status in (0,1,2)";
	
	$where[] = "(do_date between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']).")";
	if($_REQUEST['invoice_type']=='credit_sales'&&$_REQUEST['debtor_id']){
		$where[] = "do.debtor_id=".mi($_REQUEST['debtor_id']);
	}
	
	if($_REQUEST['sales_person_name']){
		if($config['masterfile_enable_sa']){
			$sa_id = $_REQUEST['sales_person_name'];
			$where[] = "((do.mst_sa != '' and do.mst_sa is not null and do.mst_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%') or (di.dtl_sa != '' and di.dtl_sa is not null and di.dtl_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%'))";
		}else $where[] = "do.sales_person_name=".ms($_REQUEST['sales_person_name']);
	}
	
	if($_REQUEST['price_type']){
		$where[] = "((do.sheet_price_type != '' and do.sheet_price_type is not null and do.sheet_price_type = ".ms($_REQUEST['price_type']).") or (di.price_type = ".ms($_REQUEST['price_type'])."))";
	}
	
	if ($where)
	{
		$where = "where " . join(" and ", $where);
	}

	/*$sql = "select do.*, category.description as dept_name, branch.report_prefix as branch_prefix, branch.code as branch_name_1, b2.code as branch_name_2, bah.approvals, user.u as user_name, b2.description as do_branch_description,
	if(do.invoice_markup,
	(
	select sum(round(((di.ctn *rcv_uom.fraction + di.pcs)*di.cost_price)*(1+(do.invoice_markup*0.01)),2))
	 from do_items di
	left join uom rcv_uom on di.uom_id=rcv_uom.id
	where di.do_id=do.id and di.branch_id=do.branch_id
	)
	,do.total_amount) as total_amount2,debtor.code as debtor_code
from do
left join category on do.dept_id = category.id
left join branch on do.branch_id = branch.id
left join branch b2 on do.do_branch_id = b2.id
left join user on user.id = do.user_id
left join branch_approval_history bah on bah.id = do.approval_history_id and bah.branch_id = do.branch_id
left join debtor on debtor.id=do.debtor_id
$where $order";*/

	if($_REQUEST['invoice_type']=="open")
	{
	    if($_REQUEST['paid_status']!='all')
	    {
	        if($_REQUEST['paid_status']=='0')
	          $str_paid = " and (do.paid = ".ms($_REQUEST['paid_status'])." or do.paid is Null)" ;
	        else
	          $str_paid = " and do.paid = ".ms($_REQUEST['paid_status']);
	    }
	}

	$str_join = $str_paid." and do.do_type = ".ms($_REQUEST['invoice_type']);

    $sql = "select do.*, category.description as dept_name, branch.report_prefix as branch_prefix, branch.code as branch_name_1,
	 b2.code as branch_name_2, bah.approvals, user.u as user_name, b2.description as do_branch_description,
	debtor.code as debtor_code,debtor.description as debtor_desc, sum(di.cost*((di.ctn * u.fraction) + di.pcs)) as cost
from do
left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
left join category on do.dept_id = category.id
left join branch on do.branch_id = branch.id
left join branch b2 on do.do_branch_id = b2.id
left join user on user.id = do.user_id
left join branch_approval_history bah on bah.id = do.approval_history_id and bah.branch_id = do.branch_id
left join debtor on debtor.id=do.debtor_id
left join uom u on u.id = di.uom_id
$where $str_join group by do.id, do.branch_id $order";
	$total_gst_list = $total_non_gst = array();
	$do_list = array();
	
	//print $sql;
	$q2=$con->sql_query($sql);
	while ($r2= $con->sql_fetchrow($q2)){
 		$r2['open_info'] = unserialize($r2['open_info']);	
		$r2['deliver_branch']=unserialize($r2['deliver_branch']);
		if($r2['deliver_branch']){
			foreach ($r2['deliver_branch'] as $k=>$v){
				$q3=$con->sql_query("select code from branch where id=$v");
				$r3 = $con->sql_fetchrow($q3);
				$r2['d_branch']['id'][$k]=$v;
				$r2['d_branch']['name'][$k]=$r3['code'];
				$con->sql_freeresult($q3);
			}
		}

		$r2['gst_detail'] = array();
		if($r2['is_under_gst']) $is_under_gst = 1;
		
		if($r2['inv_no']){
			if(($config['do_transfer_have_discount']&&$r2['do_type']=='transfer')||($config['do_cash_sales_have_discount']&&$r2['do_type']=='open')||($config['do_credit_sales_have_discount']&&$r2['do_type']=='credit_sales'))
				$inv_amt = $r2['total_inv_amt'];
			else    $inv_amt = $r2['total_amount'];
			
			// if inv amt is zero , use default
			if(!$inv_amt)   $inv_amt = $r2['total_amount'];
			
			if($r2['invoice_markup'])   $inv_amt = $inv_amt+($inv_amt*($r2['invoice_markup']/100));
			$r2['total_amount2'] = $inv_amt;

			if($r2['is_under_gst']){
				$where2 = "where do.id=".mi($r2['id'])." and do.branch_id=".mi($r2['branch_id'])." and do.is_under_gst";

				$sql2 = "select di.gst_id, di.gst_code, di.gst_rate, sum(di.inv_line_gross_amt2) as ttl_gross_amt, sum(di.inv_line_gst_amt2) as ttl_gst_amt, sum(di.inv_line_amt2) as ttl_line_amt from do_items di
						left join do on di.do_id = do.id and di.branch_id = do.branch_id
						$where2 group by di.gst_id";	

				$q4=$con->sql_query($sql2);
				while ($r4= $con->sql_fetchrow($q4)){

					if($r4['gst_id'] && $r4['gst_id'] != 0) {
						// got gst

						$r2['gst_detail'][$r4['gst_id']]['gst_code'] = $r4['gst_code'];
						$r2['gst_detail'][$r4['gst_id']]['gst_rate'] = $r4['gst_rate'];
						$r2['gst_detail'][$r4['gst_id']]['ttl_gross_amt'] = $r4['ttl_gross_amt'];
						$r2['gst_detail'][$r4['gst_id']]['ttl_gst_amt'] = $r4['ttl_gst_amt'];
						$r2['gst_detail'][$r4['gst_id']]['ttl_line_amt'] = $r4['ttl_line_amt'];

						if(!isset($total_gst_list[$r4['gst_id']])){
							$total_gst_list[$r4['gst_id']]['g_gst_code'] = $r4['gst_code'];
							$total_gst_list[$r4['gst_id']]['g_gst_rate'] = $r4['gst_rate'];
						}
						$total_gst_list[$r4['gst_id']]['g_ttl_gross_amt'] += $r4['ttl_gross_amt'];
						$total_gst_list[$r4['gst_id']]['g_ttl_gst_amt'] += $r4['ttl_gst_amt'];
						$total_gst_list[$r4['gst_id']]['g_ttl_line_amt'] += $r4['ttl_line_amt'];
					
					} else {

						$r2['gst_detail']['error']['gst_code'] = 'GST Error';
						$r2['gst_detail']['error']['gst_rate'] = '';
						$r2['gst_detail']['error']['ttl_gross_amt'] += $r4['ttl_gross_amt'];
						$r2['gst_detail']['error']['ttl_gst_amt'] += $r4['ttl_gst_amt'];
						$r2['gst_detail']['error']['ttl_line_amt'] += $r4['ttl_line_amt'];
						
						$total_gst_error['g_ttl_gross_amt'] += $r4['ttl_gross_amt'];
						$total_gst_error['g_ttl_amt'] += $r4['ttl_gst_amt'];
						$total_gst_error['g_ttl_gst_amt'] += $r4['ttl_line_amt'];

					}
				}

				$con->sql_freeresult($q4);
			}else{
				$total_non_gst['inv_amt'] += $inv_amt;
			}
		}
		

		// get total cost
		$do_list[]=$r2;
	}
	$con->sql_freeresult($q2);
	
	$smarty->assign("do_list", $do_list);
	$smarty->assign("total_gst_list", $total_gst_list);
	$smarty->assign("total_non_gst", $total_non_gst);
	$smarty->assign("total_gst_error", $total_gst_error);
	$smarty->assign("is_under_gst", $is_under_gst);
	
}

function export_excel()
{
    
    global $smarty,$sessioninfo;

    /*$filename = "do.summary";
    generate_report(true);

    $excel = false;
  	//$file=basename(tempnam(getcwd(),'tmp'));
    $file = 'Delivery_Order_Summary';
  	$excel=new ExcelWriter($file);
  	if($excel->fp==false) die($excel->error);
    log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $file To Excel");
    fwrite($excel->fp, "<h1>".$smarty->get_template_vars("title")."</h1>");
  	$raw = $smarty->fetch("do.summary.tpl");
    
  	$start=0;
  	/*foreach(split("\n", $raw) as $line)
  	{
    		if (stristr($line, "<!-- end -->")) break;
    		if ($start)
    		{
    		    $line = preg_replace(
    				array('/\s*align=center/i', '/<img src="mods\/logo\/([^.]+)\.png"[^>]*>/i', '/<img[^>]+>/i', '/(onmouseover|onmouseout|onclick)="[^"]+"/i','/(onmouseover|onmouseout|onclick)=[^\s>]+/i'),
    				array('','\1','','',''),
    				$line);
    			 
    			fwrite($excel->fp, $line);
    		}
  	    if (stristr($line, "<!-- start -->")) $start=1;
  	}
    $excel->close();*/
    
    generate_report(true);
    Header('Content-Type: application/msexcel');
  	Header('Content-Disposition: attachment;filename=arms'.$file.'.xls');
  	$smarty->assign('no_header_footer','1');
  	print ExcelWriter::GetHeader();
   $smarty->display("do.summary.tpl");
   print ExcelWriter::GetFooter();
 
    readfile($file);
   
    
    exit;
}
