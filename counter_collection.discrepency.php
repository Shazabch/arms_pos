<?php
/*
3/28/2011 4:24:32 PM Justin
- Added to retrieve extra info when view receipt info in item details.

3/29/2011 10:51:59 AM Justin
- Added member no information for receipt detail.
- fixed the bugs where cannot close the curtain whenever tick on close button.

9/3/2012 11:47 AM Fithri
- Item details - show barcode
*/
include("include/common.php");
//if (BRANCH_CODE != 'HQ') die("Please run in HQ");
//if (BRANCH_CODE == 'JITRA')
// $con = new sql_db("hq.aneka.com.my:3306", "arms_slave", "arms_slave", "armshq");

// show transaction details?
if ($_REQUEST['a']=='item_details')
{
	$con->sql_query("select pi.id, p.counter_id, u.u as cashier_name, p.receipt_no, p.pos_time, p.member_no, pi.pos_id, 
					 amount_change, pi.qty, pi.price, pi.barcode, pi.discount, si.mcode, si.sku_item_code, si.description
					 from pos p
					 left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
					 left join sku_items si on pi.sku_item_id = si.id
					 left join user u on u.id = p.cashier_id
					 where p.branch_id = ".mi($_REQUEST['branch_id'])." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.id = ".mi($_REQUEST['pos_id']));
	
	$items = $con->sql_fetchrowset();
	$smarty->assign('items',$items);
	
	$smarty->assign("amount_change", $items[0]['amount_change']);
	
	$con->sql_query("select * from pos_payment where adjust=0 and branch_id = ".mi($_REQUEST['branch_id'])." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id=".mi($_REQUEST['pos_id']));
	$smarty->assign('payment',$con->sql_fetchrowset());
	
	$con->sql_query("select * from pos p where p.branch_id = ".mi($_REQUEST['branch_id'])." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.id = ".mi($_REQUEST['pos_id']));
	$p = $con->sql_fetchrow();
	//print "<div style=\"float:right\">Membership: $p[member_no]</div>";
	$smarty->assign('editable',1);
	$smarty->display('counter_collection.item_details.tpl');
	exit;
}
elseif ($_REQUEST['a']=='update_item_details')
{
	
	$con->sql_query("update pos_items set `$_REQUEST[field]` = ".ms($_REQUEST['value'])." where id = ".mi($_REQUEST['id'])." and branch_id = ".mi($_REQUEST['branch_id'])." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id=".mi($_REQUEST['pos_id'])) or die(mysql_error());
	print $_REQUEST['value'];
	exit;
}


$dt = isset($_REQUEST['date']) ? ms(dmy_to_sqldate($_REQUEST['date'])) : 'date_add(CURDATE(), interval -1 month)';
//$bid = get_request_branch();

$con->sql_query("select date,branch_id,counter_id,pos_id,sum(amount) from pos_payment where adjust=0 and date > $dt and type <> 'Rounding' group by date,branch_id,counter_id, pos_id") or die(mysql_error());
while($r=$con->sql_fetchrow())
{
	$payments[$r[0]][$r[1]][$r[2]][$r[3]] = round($r[4],2);
}

$con->sql_query("select date,branch_id,counter_id, pos_id,sum(price-discount) from pos_items where date > $dt group by date,branch_id,counter_id, pos_id") or die(mysql_error());
while($r=$con->sql_fetchrow())
{
	$positems[$r[0]][$r[1]][$r[2]][$r[3]] = round($r[4],2);
}
$smarty->assign('PAGE_TITLE','POS Discrepency Check');
$smarty->display("header.tpl");

// get all pos from the date
$r1 = $con->sql_query("select branch.code, cs.network_name, pos.*, pp.amount as round_amt from pos left join pos_payment pp on pp.type='Rounding' and pp.branch_id=pos.branch_id and pp.counter_id=pos.counter_id and pp.pos_id = pos.id  and pp.date = pos.date
left join counter_settings cs on pos.branch_id = cs.branch_id and pos.counter_id = cs.id
left join branch on pos.branch_id = branch.id 
where pos.cancel_status=0 and pos.date > $dt");

$errcount = 0;
while($pos=$con->sql_fetchrow($r1))
{
	$pos[amount_change] = round($pos[amount_change],2);
	$pos[amount] = round($pos[amount],2);
	$pos[round_amt] = round($pos[round_amt],2);

	$is_err = false;
	$str = "<tr><td>$pos[date]</td><td>$pos[code] (#$pos[branch_id])</td><td>$pos[network_name] (#$pos[counter_id])</td><td><a href=\"javascript:void(trans_detail($pos[counter_id],$pos[cashier_id],'$pos[date]',$pos[id],$pos[branch_id]))\">$pos[receipt_no]</a> (#$pos[id])</td><td>$pos[amount_tender]</td>";
	
	// compare tender with payments
	$pt = $payments[$pos['date']][$pos['branch_id']][$pos['counter_id']][$pos['id']];
	if ($pos['amount_tender']!=$pt)
	{
		$is_err = true;
		$errcount++;
		if (round($pos['amount']-$pos['round_amt'],2) > $pt)
		{
			$erm = "Resync";
		}
		else
		{
			$erm = "BUG?";
		}
		$str .= "<td class=hilite>$pt($erm)</td>";
	}
	else
		$str .= "<td>$pt</td>";
		
	$str .= "<td>$pos[amount_change]</td><td>$pos[amount]</td><td>$pos[round_amt]</td>";
	
	// compare bill amount with items
	$pi = $positems[$pos['date']][$pos['branch_id']][$pos['counter_id']][$pos['id']];
	if (round($pos['amount']-$pos['round_amt'],2)!=$pi)
	{
		
		if (!$is_err) $errcount++;
		$is_err = true;
		if (round($pos['amount']-$pos['round_amt'],2) > $pi)
		{
			if ($pos['amount']>0) $erm = "Resync";
		}
		else
		{
			$erm = "BUG?";
		}
		$str .= "<td class=hilite>$pi($erm)</td>";
		
	}
	else
		$str .= "<td>$pi</td>";
	$str .= "</tr>";
	
	if ($is_err) {
		if ($errcount==1) print "<h2>Discrepency detected</h2><table class=tb cellspacing=0 cellpadding=4><tr bgcolor=#ffee99><th>Date</th><th>Branch</th><th>Counter</th><th>Receipt No</th><th>Tender</th><th>Pm.Types</th><th>Change</th><th>Amount</th><th>Rounding</th><th>Items</th></tr>";
		print $str;
	}
}
if ($errcount)  
	print "</table><br /><hr><br />";
else
	print "<h1>No discrepency</h1>";

?>
<style>
.hilite {
	color:#f00;
	background:#ff0;
}
td.editable{
	text-decoration:underline;
	color:green;
	cursor:pointer;
}
</style>

<script>

function trans_detail(counter_id,cashier_id,date,pos_id,branch_id)
{
	curtain(true);
	center_div('div_item_details');
	
    $('div_item_details').show();
	$('div_item_content').update('Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.discrepency.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date,
			branch_id: branch_id
		}
	});
}

function editable(counter_id,date,pos_id,branch_id,id,obj,field)
{
	var val = obj.innerHTML;
	val = prompt('Enter the value', val);
	if (val==undefined) return;
	
	if (!confirm('Change value to '+val+' ?')) return;
	
	new Ajax.Request('counter_collection.discrepency.php',
	{
	    method: 'post',
	    parameters:{
			a: 'update_item_details',
			counter_id: counter_id,
			id: id,
			pos_id: pos_id,
			date: date,
			branch_id: branch_id,
			field:field,
			value:val
		},
		onComplete: function(m){
			trans_detail(counter_id,0,date,pos_id,branch_id);
		}
	});
}
</script>

<!-- Item Details -->
<div id="div_item_details" class="curtain_popup" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="hidediv('div_item_details'); curtain(false);" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>
<!-- End of Item Details-->
