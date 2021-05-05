<?
/*
Revision History
================
4/24/2007 5:08:04 PM   Gary
- Added comment field
- when adding item, check if same item already requested in this branch

12/12/2007 1:57:54 PM gary
- add "having cost > 0" in get_cost (ignore grn with zero cost)

7/2/2009 3:34 PM Andy
- add get items details by ajax
- alter table po_request_items add sales_trend text
	
11/1/2009 2:30:19 AM yinsee
- show item already request detail (qty and user)

9/29/2010 3:04:11 PM Andy
- Show approve / reject by user.

9/20/2011 12:28:11 PM Justin
- Modified the round up for cost to base on config.

29-Jan-2016 14:13 Edwin
- More proper way to retrieve cost and sell price of sku_items when add po request

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

4/5/2016 11:12 AM Andy
- Removed the checking of config grn_do_transfer_update_cost.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_REQUEST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_REQUEST', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("po.include.php");

$branch_id = $sessioninfo['branch_id'];
$smarty->assign("PAGE_TITLE", "PO Request");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_items':
		    get_request_items();
  		    $smarty->display("po_request.request.items.tpl");
		    exit;

		case 'ajax_add_item':
		    $row = $_REQUEST;
		    $row['branch_id'] = $branch_id;
		    $row['user_id'] = $sessioninfo['id'];
		    $row['added'] = 'CURRENT_TIMESTAMP';
		    $sales_trend = $row['sales_trend'];
		    /*if($sales_trend){
				foreach($sales_trend as $key=>$s){
					foreach($s['qty'] as $m=>$v){
						$s_data['qty'][mi($m)]=$v;
					}
				}
			}*/
			$row['sales_trend'] = serialize($sales_trend);
			
		    // check if item already in PO REQUEST
		    $con->sql_query("select qty, user.u from po_request_items pri left join user on user_id = user.id where pri.active=1 and pri.status<=1 and pri.branch_id = $branch_id and pri.sku_item_id = ".mi($row['sku_item_id']));
		    $r = $con->sql_fetchrow();
		    if (!$r)
		    {
		        $temp = get_item_cost_sell($row['sku_item_id'],$row['branch_id']);
		        $row = array_merge($row, $temp);
				
				$con->sql_query("insert into po_request_items ".mysql_insert_by_field($row, array("branch_id","sku_item_id","qty","uom_id","balance","user_id","comment",'sales_trend','system_stock','cost','sell')));
		    }
		    else
		    {
		    	$smarty->assign("msg",sprintf($LANG['PO_REQUEST_ITEM_EXIST'],$r['u'],$r['qty']));
			}
		    get_request_items();
		    $smarty->display("po_request.request.items.tpl");
		    exit;
		    
		case 'get_last_po':
		    $sku_item_id = intval($_REQUEST['id']);
			$con->sql_query("select po_items.qty,po_items.order_uom_id from po_items left join po on po_items.po_id=po.id where po_items.sku_item_id=$sku_item_id and po_items.branch_id = $branch_id and po.approved and qty > 0 order by po.po_date desc limit 1");
			$r=$con->sql_fetchrow();
			print "$r[0],$r[1]";
			exit;

		case 'ajax_del_item':
		    $con->sql_query("delete from po_request_items where id=".mi($_REQUEST['id'])." and user_id=$sessioninfo[id] and branch_id=$branch_id");
		    get_request_items();
		    $smarty->display("po_request.request.items.tpl");
		    exit;
		    
		case 'get_item_details':
		    get_item_details();
		    exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

get_request_items();
$smarty->display("po_request.request.tpl");

function get_request_items()
{
	global $con, $smarty, $branch_id, $sessioninfo;
	if (!$t) $t = intval($_REQUEST['t']);
	switch($t)
	{
		case 1://choose status not process(new) 
        	$where ="po_request_items.status=0 and po_request_items.active=1 and po_request_items.user_id = $sessioninfo[id] and po_request_items.branch_id = $branch_id ";
		    //$where = "po_request_items.status=0 and po_request_items.active=1 and grn.status=1 and grn.approved and po_request_items.user_id = $sessioninfo[id] and grn.branch_id = $branch_id";
        	break;

		case 2://show approved item.
		    $where = "po_request_items.status=1 and po_request_items.active=1 and po_request_items.user_id = $sessioninfo[id] and po_request_items.branch_id = $branch_id";
		    break;

		case 3://show approved and used in PO item.
		   $where = "po_request_items.status=1 and po.approved=1 and po_request_items.active=1 and po_request_items.user_id = $sessioninfo[id] and po_request_items.branch_id = $branch_id";
		    break;
		    
		case 4://show rejected item
		   $where = "po_request_items.status=2 and po_request_items.active=1 and po_request_items.user_id = $sessioninfo[id] and po_request_items.branch_id = $branch_id";
		   break;
		   
		default:
		   $where = "po_request_items.status=0 and po_request_items.active=1 and po_request_items.user_id = $sessioninfo[id] and po_request_items.branch_id = $branch_id";
		   break;

	}
	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$con->sql_query("select count(*) from po_request_items
left join po_items on (po_items.id=po_request_items.po_item_id and po_items.branch_id=po_request_items.branch_id)
left join po on (po.id=po_items.po_id and po.branch_id=po_items.branch_id)
left join sku_items on po_request_items.sku_item_id = sku_items.id
left join user on po_request_items.user_id = user.id
where $where");
	$r = $con->sql_fetchrow();
	$total = $r[0];
	if ($total > $sz)
	{
	    if ($start > $total) $start = 0;
		// create pagination
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
		for ($i=0,$p=1;$i<$total;$i+=$sz,$p++)
		{
			$pg .= "<option value=$i";
			if ($i == $start)
			{
				$pg .= " selected";
			}
			$pg .= ">$p</option>";
		}
		$pg .= "</select>";
		$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
  	}
	$con->sql_query("select * from uom where active order by id");
	$smarty->assign("uom", $con->sql_fetchrowset());
	$smarty->assign("limit", $limit);
	
	$q1=$con->sql_query("select po_request_items.*, sku_items.sku_item_code,sku_items.artno, sku_items.mcode, sku_items.description as sku, user.u,uom.fraction as fraction, uom.code as uom_code,u2.u as approve_by_user, puom.code as packing_uom_code
from po_request_items
left join po_items on (po_items.id=po_request_items.po_item_id and po_items.branch_id=po_request_items.branch_id)
left join po on (po.id=po_items.po_id and po.branch_id=po_items.branch_id)
left join sku_items on po_request_items.sku_item_id = sku_items.id
left join user on po_request_items.user_id = user.id
left join user u2 on u2.id=po_request_items.approve_by
left join uom on uom.id=po_request_items.uom_id
left join uom puom on puom.id=sku_items.packing_uom_id
where $where order by po_request_items.added desc limit $start, $sz");
	while($r1=$con->sql_fetchrow($q1)){
		$temp=$r1;
		
		if(!$temp['cost']){
            $upd = get_item_cost_sell($temp['sku_item_id'], $temp['branch_id']);
            $con->sql_query("update po_request_items set ".mysql_update_by_field($upd)." where id=$temp[id] and branch_id=$temp[branch_id]") or die(mysql_error());
            $temp['cost'] = $upd['cost'];
            $temp['sell'] = $upd['sell'];
		}
	  	/*$q2=$con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost, grn_items.selling_price
from grn_items
left join uom on uom_id = uom.id
left join sku_items on sku_item_id = sku_items.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
where grn_items.branch_id = $r1[branch_id] and grn.approved and sku_item_code=$r1[sku_item_code] 
having cost > 0
order by grr.rcv_date desc limit 1");
 		$r2 = $con->sql_fetchrow($q2);
		$temp['grn_cost'] = $r2[0];
		$temp['grn_sell'] = $r2[1];

		
 	 	$q3=$con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost, po_items.selling_price
from po_items
left join sku_items on sku_item_id = sku_items.id
left join po on po_id = po.id and po.branch_id = po.branch_id
where po.active and po.approved and po_items.branch_id = $r1[branch_id] and sku_item_code=".ms($r1['sku_item_code'])." 
having cost > 0
order by po.po_date desc limit 1");
		
 		$r3 = $con->sql_fetchrow($q3);
		$temp['po_cost'] = $r3[0];
		$temp['po_sell'] = $r3[1];

	 	$q4=$con->sql_query("select cost_price,selling_price from sku_items where sku_item_code=".ms($r1['sku_item_code']));
 		$r4 = $con->sql_fetchrow($q4);
		$temp['master_cost'] = $r4[0];
		$temp['master_sell'] = $r4[1];*/
		
		$temp['sales_trend'] = unserialize($temp['sales_trend']);
		$r_items[]=$temp;
	}
	/*print "<pre>";
	print_r($r_items);
	print "</pre>";*/
	$smarty->assign("request_items",$r_items);
	//echo"<pre>";print_r($r_items);echo"</pre>";
}

function get_item_details(){
	global $con,$smarty,$sessioninfo;
	
	$sid = mi($_REQUEST['sku_item_id']);
	$bid = mi($sessioninfo['branch_id']);
	
	$data = get_sales_trend($sid);
    $con->sql_query("select qty from sku_items_cost where branch_id=$bid and sku_item_id=$sid") or die(mysql_error());
    $data['available_stock'] = $con->sql_fetchfield(0);
    
	$smarty->assign('item',$data);
	$smarty->display('po_request.request.item_details.tpl');
}

function get_item_cost_sell($sku_item_id, $branch_id){
	global $con, $config;
	$ret = array();
	
	$grn_filter[] = "grn.approved=1 and grn.active=1 and grr.active=1";
	$grn_filter[] = "grr_items.type<>'DO'";
	/*if(!$config['grn_do_transfer_update_cost']){  // don't count DO
		$grn_filter[] = "grr_items.type<>'DO'";
	}else{  // only count transfer DO
		$grn_filter[] = "(grr_items.type<>'DO' or (grr_items.type='DO' and do_type='transfer'))";
	}*/
	$grn_filter = join(' and ', $grn_filter);
  
	// get Cost from grn
    $q_grn = $con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,".mi($config['global_cost_decimal_points']).") as cost
								from grn_items
								left join uom on uom_id = uom.id
								left join sku_items on sku_item_id = sku_items.id
								left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
								left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
								left join grr_items on grr_items.id=grn.grr_item_id and grr_items.branch_id=grn.branch_id
								where grn_items.branch_id = $branch_id and grn.approved and sku_items.id=$sku_item_id and $grn_filter
								having cost > 0
								order by grr.rcv_date desc limit 1");
	$grn = $con->sql_fetchrow($q_grn);
	if($grn){
        $ret['cost'] = $grn[0];
	}else{
		// no grn , get last po
		$q_po = $con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
								from po_items
								left join sku_items on sku_item_id = sku_items.id
								left join po on po_id = po.id and po.branch_id = po.branch_id
								where po.active and po.approved and po_items.branch_id =$branch_id and sku_items.id=$sku_item_id
								having cost > 0
								order by po.po_date desc limit 1");

 		$po = $con->sql_fetchrow($q_po);
 		if($po){
            $ret['cost'] = $po[0];
		}else{
			// no grn and no po, get master
			$q_mas = $con->sql_query("select cost_price from sku_items where id=$sku_item_id");
	 		$mas = $con->sql_fetchrow($q_mas);
			$ret['cost'] = $mas[0];
		}
	}
	
	// get Sell from sku_items
	$con->sql_query("select ifnull(sip.price,si.selling_price) as sell
		from sku_items si
		left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
		where si.id=$sku_item_id");
	$tmp = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	$ret['sell'] = mf($tmp['sell']);
	
	return $ret;
}
?>
