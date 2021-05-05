<?
/*
3/16/2009 9:30:00 AM Andy
- modify function init_table()

4/17/2009 1:09:22 PM yinsee
- check inventory status before print

4/18/2009 12:49:24 PM yinsee
- all "dont count" should be inclusive of the stock date

4/24/2009 3:05:00 PM Andy
- add sorting of artno before print

5/13/2009 10:11:00 AM Andy
- add privilege checking - CON_MONTHLY_REPORT

5/26/2009 12:54:04 PM yinsee
- get latest selling from selected month till end of month

6/15/2009 :4:00:00 PM Andy
- Changes on sql of GRN and POS (just small changes on date operator)

1/6/2010 4:25:19 PM Andy
- Add config 'ci_use_split_artno' to check whether split artno

2/1/2010 10:39:08 AM Andy
- Add new feature to allow use customized printing templates

2/5/2010 9:58:47 AM Andy
- Sort item by price type when print blank monthly report

3/15/2010 4:25:22 PM Andy
- Fix consignment print monthly report (openning qty bugs)

4/1/2010 5:35:24 PM Andy
- Monthly report change to only show active branch

5/7/2010 2:09:25 PM Andy
- report change to always sort artno by code,size.
- report change to only show consignment type SKU.

5/20/2010 4:19:05 PM Andy
- uncomment the checking for whether sku is up to date and edit the popup notice. (need config to turn on)

5/31/2010 2:54:17 PM Andy
- Stock balance and inventory calculation include CN(-)/DN(+), can see under SKU Masterfile->Inventory.
- Monthly Report lost qty will change to generate CN, over qty will change to generate DN.

6/22/2010 5:10:08 PM Andy
- Fix CN/DN wrong calculation bugs.

7/21/2010 11:25:51 AM Andy
- Add Checking if monthly report already confirm, it cannot be print again.

7/28/2010 1:38:35 PM Andy
- Add delete function to consignment monthly report (prompt to enter reason before delete).

11/15/2010 11:28:15 AM Andy
- Fix bugs for monthly report: if there is only 1 item in last page for the price type, it will get 'NONE' price type.

3/18/2011 5:52:47 PM Alex
- fix grn cost amount bugs

5/23/2011 10:35:28 AM Alex
- add sort by supermarket code, price, artno and group by category

6/15/2011 10:04:59 AM Alex
- load prefix settings from branch

6/17/2011 10:31:53 AM Andy
- Fix artno sorting bugs, only sort artno code,size if got split by artno.

7/6/2011 11:08:52 AM Andy
- Change split() to use explode()

8/2/2011 2:55:26 PM Andy
- Change report to show those SKU which have start or closing stock balance.

8/11/2011 3:34:48 PM Andy
- Fix wrong adjustment qty calculation. 

9/7/2011 11:27:14 AM Alex
- fix sorting of group by category

10/17/2011 4:32:27 PM Andy
- Add load category description.

11/24/2011 3:49:00 PM Andy
- Fix wrong price type.

12/12/2011 12:03:33 PM Andy
- Add delete "tmp_consignment_report" when print monthly report.

12/21/2011 10:32:07 AM Andy
- Change report to show those SKU which only have start stock balance.

5/16/2012 10:24:34 AM Justin
- Added new validation to check do date whether it is over than transaction end date while in consignment mode for following month + year.

5/25/2012 10:18:23 AM Justin
- Fixed bug of comparing 0000-00-00 transaction end date with current date and show error.

4/17/2015 11:21 AM Justin
- Enhanced to always pickup selling price include GST.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CON_MONTHLY_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CON_MONTHLY_REPORT', BRANCH_CODE), "/index.php");
include('consignment.include.php');

ini_set('memory_limit','256M');
ini_set('display_error',1);

$pagesize = $config['ci_monthly_report_print_item_per_page'] ? $config['ci_monthly_report_print_item_per_page'] : 32;
$months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
$years = array(intval(date('Y'))-1,intval(date('Y')),intval(date('Y'))+1);
$print_tpl = $config['ci_monthly_report_alt_print_template'] ? $config['ci_monthly_report_alt_print_template'] : 'consignment.print_monthly_report.blank_form.tpl';
 
$smarty->assign("PAGE_TITLE", "Print Monthly Report");

if (isset($_REQUEST['a']))
{
	switch ($_REQUEST['a'])
	{
		case 'print_empty':
  			// get current branch info
  			$con->sql_query("select * from branch where code=".ms(BRANCH_CODE)) or die(mysql_error());
  			$branch = $con->sql_fetchassoc();
  			$smarty->assign('branch',$branch);
  			$smarty->assign('subtitle_r',"Month Of: <div style=\"display:inline-block;width:120px\"></div><br>Promoter's Name:___________________________");
  			$smarty->assign('pagesize',$pagesize);
  			$smarty->assign('table',array(1));
		    $smarty->display($print_tpl);
		    exit;
		case 'print_blank_form':
		    print_blank_form();
		    exit;
		case 'check_report':
		    check_report();
		    exit;
		case 'load_from_branch':
			load_from_branch();
			exit;
		default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;
	}

}

init_table();
load_branch();
load_branch_group();

$smarty->assign('months',$months);
$smarty->assign('years',$years);
$smarty->display("consignment.print_monthly_report.tpl");

function init_table(){
	global $con;
	
	$con->sql_query("CREATE TABLE if not exists `consignment_report` ( `branch_id` int(11) NOT NULL, `sku_item_id` int(11) NOT NULL, `year` int(11) NOT NULL, `month` tinyint(4) NOT NULL,qty int default 0,price double default 0,adj int default 0,lost int default 0, PRIMARY KEY (`branch_id`,`sku_item_id`,`year`,`month`), index(qty,price,adj,lost) )");
	
	$con->sql_query("CREATE TABLE if not exists `tmp_consignment_report` ( user_id int not null,`branch_id` int(11) NOT NULL, `sku_item_id` int(11) NOT NULL, `year` int(11) NOT NULL, `month` tinyint(4) NOT NULL,qty int default 0,price double default 0,adj int default 0,lost int default 0, PRIMARY KEY (user_id,`branch_id`,`sku_item_id`,`year`,`month`), index(qty,price,adj,lost) )");
	
	$con->sql_query("CREATE TABLE if not exists `consignment_report_sku` ( `page_num` int(11) NOT NULL, `row_num` int(11) NOT NULL, `sku_item_id` int(11) default NULL, `art_no` char(20) default NULL, `open_qty` int(11) default NULL, `adj` int(11) default NULL, `qty_in` int(11) default NULL, `total_open_qty` int(11) default NULL, `price` double default NULL, `year` int(11) NOT NULL default '0', `month` int(11) NOT NULL default '0', `branch_id` int(11) NOT NULL default '0', PRIMARY KEY (`year`,`month`,`branch_id`,`page_num`,`row_num`), KEY `total_open_qty` (`total_open_qty`) ) ");
	
	$con->sql_query("CREATE TABLE if not exists `consignment_report_page_info` ( `branch_id` int(11) NOT NULL, `year` int(11) NOT NULL, `month` int(11) NOT NULL, `page` int(11) NOT NULL, `discount_code` char(10) default NULL, PRIMARY KEY (`branch_id`,`year`,`month`,`page`), KEY `discount_code` (`discount_code`) )");
	
	$con->sql_query("CREATE TABLE if not exists `consignment_report_export_history` ( `id` int(11) NOT NULL auto_increment, `branch_id` int(11) NOT NULL, `year` int(11) NOT NULL, `month` tinyint(4) NOT NULL, `date` date default NULL, `timestamp` timestamp NULL default NULL, `user_id` int(11) default NULL, `invoice_list` text, adj_list text, do_list text, PRIMARY KEY (`id`), KEY `branch_id` (`branch_id`,`year`,`month`,`date`,`timestamp`,`user_id`) ) ");
}

function print_blank_form(){
	global $con,$smarty,$pagesize,$months, $config, $print_tpl, $sessioninfo, $LANG;
	
	$branch_id = intval($_REQUEST['branch']);
	$month = intval($_REQUEST['month']);
	$year = intval($_REQUEST['year']);
	
	$con->sql_query("select * from monthly_report_list where branch_id=$branch_id and year=$year and month=$month");
	$form = $con->sql_fetchassoc();
	if($con->sql_numrows()>0){
	    if($form['status']==1){
			$errm[] = $LANG['MTHLY_RPT_EXISTED'];
		}
	}
	
	// check transaction end date
	// check deliver from
	if($branch_id){
		$dl_fr_info = array();
		$q1 = $con->sql_query("select trans_end_date from branch where id = ".mi($branch_id));
		$dl_fr_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		if($dl_fr_info['trans_end_date'] > 0){
			$trans_end_date = date("Y-m", strtotime($dl_fr_info['trans_end_date'])).'-1';
			$trans_end_times = strtotime($trans_end_date);
			$check_times = strtotime($year.'-'.$month.'-1');
			if($check_times >= $trans_end_times) $errm[] = sprintf($LANG['MSTBRANCH_OVER_TRANS_END_DATE'], get_branch_code($branch_id), "");
		}
	}

	if($errm){
		$smarty->assign("errm", $errm);
		init_table();
		load_branch();
		load_branch_group();

		$smarty->assign('months',$months);
		$smarty->assign('years',$years);
		$smarty->display("consignment.print_monthly_report.tpl");
		exit;
	}
	
	$from_Date = $year.'-'.$month.'-1';
	$y2 = $year;
	$m2 = $month+1;
	if($m2 > 12){
		$m2 = 1;
		$y2++;
	}
	//$to_Date = date("Y-m-d", strtotime("+1 day", strtotime($year.'-'.$month.'-'.days_of_month($month, $year))));
	$to_Date = $y2.'-'.$m2.'-1';
	
	// get current branch info
	$con->sql_query("select * from branch where code=".ms(BRANCH_CODE)) or die(mysql_error());
	$branch = $con->sql_fetchassoc();
	$smarty->assign('branch',$branch);
	
	// get selected branch info
    $con->sql_query("select * from branch where id=$branch_id") or die(mysql_error());
	$branch2 = $con->sql_fetchassoc();
	$subtitle_m = $branch2['description'];
	$smarty->assign('subtitle_m',$subtitle_m);

	//get history balance and cost price
	$q2=$con->sql_query("select sku_items_cost_history.*, sku_items_cost_history.sku_item_id as sid ,
(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$branch_id and sh.date <'$from_Date') as stock_date
from
sku_items_cost_history
where branch_id=$branch_id and date < '$from_Date' and date > 0
having stock_date=date order by null");
	while($r2=$con->sql_fetchassoc($q2)){
  		$data[$r2['sid']]['cost_history'] += $r2['qty'];
	}
	
	$con->sql_freeresult($q2);
	
	//GRN = get the rcvd qty, rcvd cost and grn qty
	$q4=$con->sql_query("select grn_items.sku_item_id as sid,
sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)),
	(grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) *
	if (grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost)) as total_rcv_cost,
if(grr.rcv_date>='$from_Date',1,0) as bal,

(rcv_date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$branch_id and sh.date <'$from_Date')) as dont_count

from grn_items
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
where grn.branch_id=$branch_id and rcv_date <'$to_Date' and grn.approved=1 and grn.status=1 and grn.active=1
group by bal, dont_count, sid order by null");

	while($r4=$con->sql_fetchassoc($q4)){
	    if(!$r4['dont_count'])
		{
		    if (!$r4['bal'])	$data[$r4['sid']]['grn'] += $r4['qty'];	// before 1 
			else	$data[$r4['sid']]['post_grn'] += $r4['qty'];	// after 1
		}
	}
	$con->sql_freeresult($q4);
	
	//ADJ = get adj in and adj out
	$q5=$con->sql_query("select
ai.sku_item_id as sid,
sum(qty) as qty,
if(qty>=0,'p','n') as type,
if(adjustment_date>='$from_Date',1,0) as bal,

(adjustment_date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = ai.sku_item_id and sh.branch_id=$branch_id and sh.date <'$from_Date')) as dont_count

from adjustment_items ai
left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
where ai.branch_id=$branch_id and adjustment_date<'$to_Date' and adj.approved=1 and adj.status<2
group by bal,type,dont_count, sid order by null");

	while($r5=$con->sql_fetchassoc($q5)){
	    if(!$r5['dont_count'])
		{
			if (!$r5['bal'])	$data[$r5['sid']]['adj'] += $r5['qty'];
			else	$data[$r5['sid']]['post_adj'] += $r5['qty'];
		}
	}
	$con->sql_freeresult($q5);
	
	//DO get do qty
	$q6=$con->sql_query("select
do_items.sku_item_id as sid,
sum(do_items.ctn *uom.fraction + do_items.pcs) as qty,
if(do_date>='$from_Date',1,0) as bal,

(do_date <=(select max(date) from sku_items_cost_history sh where sh.sku_item_id = do_items.sku_item_id and sh.branch_id=$branch_id and sh.date <'$from_Date')) as dont_count,
do_items.selling_price,
do_date,sp.last_update as sku_price_last_update,
if(sp.last_update>do_date,1,0) as use_sku_price,sp.price

from do_items
left join uom on do_items.uom_id=uom.id
left join do on do_id = do.id and do_items.branch_id = do.branch_id
left join sku_items_price sp on do_items.sku_item_id=sp.sku_item_id and sp.branch_id=do_items.branch_id
where do_items.branch_id=$branch_id and do_date<'$to_Date' and do.approved=1 and do.checkout=1 and do.status<2
group by bal,dont_count, sid order by null");

	while($r6=$con->sql_fetchassoc($q6)){
	    if(!$r6['dont_count']){
	        if(!$r6['bal']){
                $data[$r6['sid']]['do'] += $r6['qty'];
				if($r6['use_sku_price']){
				    // use the price from sku_items_price
	                $data[$r6['sid']]['selling_price'] += $r6['price'];
				}else{
				    // use do items selling price
	                $data[$r6['sid']]['selling_price'] += $r6['selling_price'];
				}
				$do_date[$r6['sid']] = $r6['do_date'];
			}else{
				$data[$r6['sid']]['post_do'] += $r6['qty'];
			}
		}
	}
	$con->sql_freeresult($q6);
	
	//GRA get the gra qty.
	$q7=$con->sql_query("select
gra_items.sku_item_id as sid,
sum(qty) as qty,
if(return_timestamp>='$from_Date',1,0) as bal,

(date(return_timestamp) <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = gra_items.sku_item_id and sh.branch_id=$branch_id and sh.date <'$from_Date')) as dont_count

from gra_items
left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
where gra.branch_id=$branch_id and return_timestamp <'$to_Date' and gra.status=0 and gra.returned=1
group by bal,dont_count, sid order by null");

	while($r7=$con->sql_fetchassoc($q7)){
	    if(!$r7['dont_count']){
	        if(!$r7['bal'])	$data[$r7['sid']]['gra'] += $r7['qty'];
	        else	$data[$r7['sid']]['post_gra'] += $r7['qty'];
		}
	}
	$con->sql_freeresult($q7);
	
	// POS
	$tbl="sku_items_sales_cache_b".$branch_id;

	$sql = "select
		si.id as sid,
		sum(qty) as qty,
        if(date>='$from_Date',1,0) as bal,
        
		(date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id =si.id and sh.branch_id=$branch_id and sh.date <'$from_Date')) as dont_count

		from $tbl pos
		left join sku_items si on si.id=pos.sku_item_id
		where date < '$to_Date'
		group by bal,si.id, dont_count order by null";
		$q_8 = $con->sql_query($sql,false,false);

		while($r = $con->sql_fetchassoc($q_8)){
			if(!$r['dont_count']){
			    if(!$r['bal'])	$data[$r['sid']]['pos'] += $r['qty'];
			    else	$data[$r['sid']]['post_pos'] += $r['qty'];
			}
		}
		$con->sql_freeresult($q8);
		
    	//FROM Credit Note
		$con->sql_query("select cn_items.sku_item_id as sid, sum(cn_items.ctn *uom.fraction + cn_items.pcs) as qty,
		if(cn.date>='$from_Date',1,0) as bal,(cn.date <=(select max(date) from sku_items_cost_history sh where sh.sku_item_id = cn_items.sku_item_id and sh.branch_id=$branch_id and sh.date <'$from_Date')) as dont_count
from cn_items
left join cn on cn.id=cn_items.cn_id and cn.branch_id=cn_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on cn_items.uom_id=uom.id
where cn.to_branch_id = $branch_id and cn.active=1 and cn.approved=1 and cn.status=1 and cn.date<'$to_Date' group by bal,dont_count, sid order by null");
		while($r=$con->sql_fetchassoc())
		{
		    if(!$r['dont_count'])
				if(!$r['bal'])	$data[$r['sid']]['cn'] += $r['qty'];
				else	$data[$r['sid']]['post_cn'] += $r['qty'];
		}
		$con->sql_freeresult();

		//FROM Debit Note
		
		$con->sql_query("select dn_items.sku_item_id as sid, sum(dn_items.ctn *uom.fraction + dn_items.pcs) as qty, 
		if(dn.date>='$from_Date',1,0) as bal,(dn.date <=(select max(date) from sku_items_cost_history sh where sh.sku_item_id = dn_items.sku_item_id and sh.branch_id=$branch_id and sh.date <'$from_Date')) as dont_count
from dn_items
left join dn on dn.id=dn_items.dn_id and dn.branch_id=dn_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on dn_items.uom_id=uom.id
where dn.to_branch_id = $branch_id and dn.active=1 and dn.approved=1 and dn.status=1 and dn.date <'$to_Date' group by bal,dont_count, sid order by null");
		while($r=$con->sql_fetchassoc())
		{
			if(!$r['dont_count'])
				if(!$r['bal'])	$data[$r['sid']]['dn'] += $r['qty'];
				else	$data[$r['sid']]['post_dn'] += $r['qty'];
		}
		$con->sql_freeresult();
		
		if($data){
			foreach($data as $sid=>$r){
			    $data[$sid]['open_bal'] = $r['cost_history']+$r['grn']+$r['adj']-$r['do']-$r['gra']-$r['pos']+$r['cn']-$r['dn'];
			    $data[$sid]['closing_bal'] = $data[$sid]['open_bal']+$r['post_grn']+$r['post_adj']-$r['post_do']-$r['post_gra']-$r['post_pos']+$r['post_cn']-$r['post_dn'];
			    //$data[$sid]['post_grn'] += 0-$r['do']-$r['gra']-$r['pos'];
			}
		}
		if($sessioninfo['id']==1){
            //print_r($data[250]);exit;
		}
		
		
	// clear the table
	$con->sql_query("delete from consignment_report_sku where year=$year and month=$month and branch_id=$branch_id") or die(mysql_error());
	
	// delete page information
	$con->sql_query("delete from consignment_report_page_info where branch_id=$branch_id and year=$year and month=$month") or die(mysql_error());
	$con->sql_freeresult();
	// store page information
	$page_info = array();
	$page_data = array();
	$page_info['branch_id'] = $branch_id;
	$page_info['year'] = $year;
	$page_info['month'] = $month;
	
	// default counters
	$item_count = 0;
	$last_page = 0;
	$last_discount_code = 'EMPTY';
	
	// get sku
	/*$sql = "select sku_items.*,if(sku_items_price.price,trade_discount_code,default_trade_discount_code) as discount_code
from sku_items
left join sku_items_price on branch_id=$branch_id and sku_items.id=sku_items_price.sku_item_id
left join sku on sku_items.sku_id=sku.id
order by discount_code desc,artno,sku_item_code";*/
	if ($_REQUEST['con_group_category']){
		$cat_sql = "select max(level) as max_level from category";
		$cat_rid = $con->sql_query($cat_sql) or die(mysql_error());
		$cat = $con->sql_fetchassoc($cat_rid);
		$con->sql_freeresult($cat_rid);
		
		for ($i=0;$i<=$cat['max_level'];$i++){
			$column[]="p".$i;
		}
		
		$order_by=",".join(', ',$column);
		
		$extrasql= ", $cat[max_level] as max_level $order_by";
		$left_join = "left join category_cache cc on sku.category_id=cc.category_id";
	}
	
	if ($_REQUEST['con_sort_by'] == "super"){
		$extrasql2= ", ssc.supermarket_code";
		$left_join2 = "left join sku_supermarket_code ssc on ssc.sku_item_id=sku_items.id  and ssc.branch_id=$branch_id";
	}

    $sql = "select sku_items.*,default_trade_discount_code as discount_code, c.description as cat_desc $extrasql $extrasql2
from sku_items
left join sku on sku_items.sku_id=sku.id
left join category c on c.id=sku.category_id
$left_join
$left_join2
where sku.sku_type='CONSIGN'
order by discount_code desc $order_by,artno,sku_item_code";

	$q_s = $con->sql_query($sql) or die(mysql_error());
	//if($sessioninfo['id']==1)   print_r($data[11646]);
	$item_count = 0;
	if($con->sql_numrows($q_s)>0){
	    $c_page = 1;
	    $c_row = 1;
		while($r = $con->sql_fetchassoc($q_s)){
		    $sid = $r['id'];
		    $r['balance'] = $data[$sid];
		    //$r['balance']['open_qty'] = $r['balance']['open_bal']-$r['balance']['post_grn']-$r['balance']['post_adj'];
		    //if($r['balance']['open_bal']<=0 && $r['balance']['closing_bal']<=0) continue;
			
			if($r['balance']['open_bal']<=0) continue;	// no opening 
			
			$r['artno'] = trim(preg_replace('/\s+/',' ',$r['artno']));
			if($config['ci_use_split_artno']){
				list($r['artno_code'],$r['artno_size']) = explode(" ",$r['artno'],2);
			}
			
			// check latest price (from the selected month)
			$q_cl = $con->sql_query("select price,trade_discount_code,added from sku_items_price_history where sku_item_id=".mi($sid)." and branch_id=$branch_id and added<".ms($to_Date)." order by added desc limit 1");
			$temp = $con->sql_fetchassoc($q_cl);
			//if($sessioninfo['id']==1 && $sid==5195)   print "select price,trade_discount_code,added from sku_items_price_history where sku_item_id=".mi($sid)." and branch_id=$branch_id and added<".ms($to_Date)." order by added desc limit 1";
			if($temp['added']>$do_date[$sid]){
			    // price history latest than DO Date, use new price and code
				$r['price'] = $temp['price'];
				$discount_code = $temp['trade_discount_code'];
			}elseif($data[$sid]['selling_price']){
			    // Do exists and do date is latest, use do price and code
                $r['price'] = $data[$sid]['selling_price'];
                $discount_code = $temp['trade_discount_code'];
                /*if($temp){
                    $discount_code = $temp['trade_discount_code'];
				}else{
                    $discount_code = $r['discount_code'];
				}*/
			}else{
			    // no DO , no price history, use master
				$r['price'] = $r['selling_price'];
				$discount_code = $r['discount_code'];
			}
			
			// if no discount code, use master
			if(!$discount_code) $discount_code = $r['discount_code'];
		    
		    $d_data[$discount_code][] = $r;
		    
		    $item_count++;
		    //if($item_count>100)	break;	// for testing, dont wan so many item
		}
	}
	if(!$d_data)    die("No items");
	if($sessioninfo['u']=='wsatp'){
        //print_r($d_data['O']);exit;
	}	
    
    // sort artno by code , size
    //print_r($_REQUEST);
	//print_r($d_data);
	foreach($d_data as $discount_code=>$item_list){
	    usort($d_data[$discount_code], "sort_d_data");
	}
	
    $con->sql_freeresult();
    $con->sql_freeresult($q_s);
    
    $c_page = 0;
	$c_row = 1;
	
    // 2/5/2010 9:57:06 AM added by andy - sort by price type
    @ksort($d_data);
	    
    foreach($d_data as $discount_code=>$item_list){
        $c_page++;
	    $c_row = 1;
	    
	    foreach($item_list as $r){
	        if($c_row>$pagesize){
	            $c_row = 1;
	            $c_page++;
			}
			
			// save page information - discount code
		    if($last_page!=$c_page){
		        $page_info['page'] = $c_page;
		        $page_info['discount_code'] = $discount_code;
				$con->sql_query("insert into consignment_report_page_info ".mysql_insert_by_field($page_info)) or die(mysql_error());
				$last_page = $c_page;
				$page_data[$c_page] = $discount_code;
			}

			$upd = array();
			$upd['page_num'] = $c_page;
			$upd['row_num'] = $c_row;
			$upd['sku_item_id'] = $r['id'];
			if($r['artno'])	$upd['art_no'] = $r['artno'];
			else    $upd['art_no'] = $r['sku_item_code'];
			$upd['open_qty'] = $r['balance']['open_qty'];
			$upd['adj'] = $r['balance']['adj'];
			$upd['qty_in'] = $r['balance']['grn'];
			$upd['total_open_qty'] = $r['balance']['open_bal'];
			
			if($config['enable_gst'] && $year && $month){
				$gst_date = date("Y-m-d", strtotime("-1 day", strtotime("+1 months", strtotime($year."-".$month."-01"))));
				$is_under_gst = check_gst_status(array('date'=>$gst_date, 'branch_id'=>$branch_id));

				if($is_under_gst){
					$gst_info = get_sku_gst("output_tax", $r['id']);
					$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['id']);
					//if($form['is_under_gst']) construct_gst_list('supply');
					$prms['selling_price'] = $r['price'];
					$prms['inclusive_tax'] = $is_inclusive_tax;
					$prms['gst_rate'] = $gst_info['rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					
					if($is_inclusive_tax == "no") $r['price'] = $gst_sp_info['gst_selling_price'];
				}
			}
			
			$upd['price'] = $r['price'];
			$upd['year'] = $year;
			$upd['month'] = $month;
			$upd['branch_id'] = $branch_id;

			$con->sql_query("insert into consignment_report_sku ".mysql_insert_by_field($upd)) or die(mysql_error());
			$table[$c_page][] = $r;
			$c_row++;
			$item_count++;
		}
	}
	
    delete_report($branch_id,$year,$month);

	//$page_total = ceil($item_count/$pagesize);
	$page_total = $c_page;

	//generate subtitle_r
	$sr = "Month Of: ".$months[$month]." $year <br>";
	//$sr .= "Promoter's Name: ".$branch2['contact_person'];
	$sr .= "Promoter's Name:___________________________";
	$days_of_month = days_of_month($month, $year);
	
	//print_r($table);
	if(!$table) print "No items";
	
	log_br($sessioninfo['id'], 'Consignment Report', '', "Print Consignment Report Branch ID: $branch_id year:$year month:$month");
	if($form){
		$con->sql_query("update monthly_report_list set last_update=now(),status=0 where branch_id=$branch_id and year=$year and month=$month");
	}else{
	    $upd = array();
	    $upd['branch_id'] = $branch_id;
	    $upd['year'] = $year;
	    $upd['month'] = $month;
	    $upd['added'] = 'CURRENT_TIMESTAMP';
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    
		$con->sql_query("insert into monthly_report_list ".mysql_insert_by_field($upd));
	}
	
	$smarty->assign('page_total',$page_total);
	$smarty->assign('data',$data);
	$smarty->assign('table',$table);
	$smarty->assign('subtitle_r',$sr);
	$smarty->assign('pagesize',$pagesize);
	$smarty->assign('page_data',$page_data);
	$smarty->assign('days_of_month',$days_of_month);
	$smarty->assign('month_label',$months[$month]);
	$smarty->display($print_tpl);
}

function check_report(){
	global $con, $config;
	
	$branch_id = intval($_REQUEST['branch_id']);
	$year = intval($_REQUEST['year']);
	$month = intval($_REQUEST['month']);
	
	$con->sql_query("select * from monthly_report_list where branch_id=$branch_id and year=$year and month=$month");
	$form = $con->sql_fetchassoc();
	if($con->sql_numrows()>0){
	    if($form['status']==1){
			die("Monthly Report already confirmed, cannot print again.");
		}
	}
	
	$con->sql_query("select count(*) from consignment_report where branch_id=$branch_id and year=$year and month=$month") or die(mysql_error());
	if($con->sql_fetchfield(0)>0){
		print "found";
		exit;
	}
	
	if($config['ci_monthly_report_check_inventory_updated']){
        $con->sql_query("select count(*) from sku_items_cost where branch_id=$branch_id and changed=1") or die(mysql_error());
		if($con->sql_fetchfield(0)>0){
			print "Inventory Notice: ".$con->sql_fetchfield(0)." sku balance not yet update. Please wait for 30 minutes and print again.";
			exit;
		}
	}
	
	print "OK";
	exit;
}

function delete_report($branch_id,$year,$month){
	global $con;
	
	$q_dr = $con->sql_query("delete from consignment_report where branch_id=$branch_id and year=$year and month=$month") or die(mysql_error());
	$q_dr = $con->sql_query("delete from tmp_consignment_report where branch_id=$branch_id and year=$year and month=$month") or die(mysql_error());
}

function sort_d_data($a,$b)
{
	global $config;
	
	//FIRST LEVEL SORTING
	//sort by category
	if ($_REQUEST['con_group_category']){
		for ($i=0;$i<=$a['max_level'];$i++){
			$cache="p".$i;
			if ($a[$cache] != $b[$cache])	return ($a[$cache]>$b[$cache]) ? 1:-1;
		}	
	}

	//SECOND LEVEL SORTING
	//sort by price or supermarket code 1st
	if ($_REQUEST['con_sort_by'] == 'price'){
		if ($a['price'] != $b['price'])	return ($a['price']>$b['price']) ? 1:-1;
	}elseif ($_REQUEST['con_sort_by'] == 'super'){
		if ($a['supermarket_code'] != $b['supermarket_code'])	return ($a['supermarket_code']>$b['supermarket_code']) ? 1:-1;	
	}

	//THIRD LEVEL SORTING
	// normal artno sorting
	if(!$config['ci_use_split_artno']){
		if($a['artno']==$b['artno'])	return 0;
		else	return ($a['artno']>$b['artno']) ? 1:-1;
	}	
	
	// advance artno sorting
	$sml = array('S','M','L','XL');
		
	// same code same size, return no change
    if (($a['artno_code']==$b['artno_code'])&&($a['artno_size']==$b['artno_size'])) return 0;
    // different code, compare code
    elseif($a['artno_code']!=$b['artno_code'])  return ($a['artno_code']>$b['artno_code']) ? 1:-1;
	// the following will be the comparison of same code
    elseif(!$b['artno_size'])   return -1;  // b no size, return smaller than
    elseif(!$a['artno_size'])   return 1;   // a no size, return bigger than
    // both got size
	else{
	    // split size from 14/16 to 14 and 16
	   	list($a_size1,$a_size2) = explode("/",$a['artno_size']);
		list($b_size1,$b_size2) = explode("/",$b['artno_size']);
			
		// check both size whether is numbering or S,M,L
		if(in_array($a_size1,$sml)) $a_is_sml = true;
		if(in_array($b_size1,$sml)) $b_is_sml = true;
		
		// using S,M,L,XL comparison if found both size contain those character
		if($a_is_sml&&$b_is_sml){
		    $a_size1 = strtoupper($a_size1);
		    $b_size1 = strtoupper($b_size1);
		    
		    if($a_size1==$b_size1)	return 0;
		    elseif($b_size1=='S')   return 1;
		    elseif($a_size1=='S')   return -1;
		    elseif($b_size1=='M')   return 1;
		    elseif($a_size1=='M')   return -1;
		    elseif($b_size1=='L')   return 1;
		    elseif($a_size1=='L')   return -1;
		    elseif($b_size1=='XL')   return 1;
		    elseif($a_size1=='XL')   return -1;
			
		}elseif($a_is_sml&&!$b_is_sml)	return -1;  // a is S,M,L , b is number
		elseif($b_is_sml&&!$a_is_sml)	return 1;   // a is number , b is S,M,L
		else{
			// using numeric comparison of size
			return ($a_size1>$b_size1) ? 1 : -1;
		}
	}
}

function load_from_branch(){
	global $con;	

	$b_rid=$con->sql_query("select * from branch where id=".$_REQUEST['branch']);
	
	$b=$con->sql_fetchassoc($b_rid);	
	
	$json['con_sort_by']=$b['con_sort_by'];
	$json['con_split_artno']=$b['con_split_artno'];
	$json['con_group_category']=$b['con_group_category'];
	$con->sql_freeresult($b_rid);
	
	print json_encode($json);
}
?>