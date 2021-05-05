<pre>
<?php
// script to capture po items with possible new mcode and create as variety
// warning: this script should ONLY be runing in HQ as cronjob!!

/*
get items from newly approved po
if item not in sku list
	add
endif
*/
define('TERMINAL',1);
include("include/common.php");

// check new SKU
$bb = $con->sql_query("select id,code from branch");
while($b = $con->sql_fetchrow($bb))
{
	$bid = $b['id'];
	print "Checking new PO from $b[code]\n";
	$pp = $con->sql_query("select artno_mcode, sku_item_id, po_id, po_items.branch_id, po_items.id, selling_price, order_price from po_items left join po on po_id = po.id where po.sku_checked=0 and po.approved=1 and po.active=1 and po.branch_id = $bid order by po_items.po_id, po_items.id");
	while($p = $con->sql_fetchrow($pp))
	{
	    // if code "looks like" mcode... add without hesitate!!
	    if (preg_match("/^[0-9]+$/", $p['artno_mcode']) && (strlen($p['artno_mcode'])==8 || strlen($p['artno_mcode'])==12 || strlen($p['artno_mcode'])==13))
		{
		    //print "$p[branch_id] $p[po_id] $p[id] $p[artno_mcode] $p[sku_item_id] $p[selling_price] $p[order_price]\n";
		    // check if this code exist
		    $con->sql_query("select id from sku_items where mcode = " . ms($p['artno_mcode']) . " or artno = " . ms($p['artno_mcode']));
		    if ($con->sql_numrows()>0)
			{
			    //print $p['artno_mcode'] ." Already exist\n";
			    continue;
			}
		    
			// what's the next arms_code
			$con->sql_query("select max(sku_id), max(sku_item_code) from sku_items where sku_id in (select sku_id from sku_items where id=$p[sku_item_id])");
			$s = $con->sql_fetchrow();
			if (!$s)
			{
			    print "Error: Canot find sku_items for $p[sku_item_id]\n";
			    continue;
			}
			$sku_id = $s[0];
			$sku_item_code = $s[1]+1;
		    // add variety!
		    print "PO: $p[po_no] Adding $p[artno_mcode]... as $sku_item_code (SKU#$sku_id)";
		    $con->sql_query("insert into temp_sku_items (sku_id, sku_item_code, artno, mcode, link_code, description, selling_price, cost_price) select sku_id, $sku_item_code, '', '$p[artno_mcode]', '', description, $p[selling_price], $p[order_price] from temp_sku_items where id=$p[sku_item_id]");
		    if ($con->sql_affectedrows()>0)
		    {
				print "... ID# ".$con->sql_nextid();
			}
			else
			{
			    print "... Failed!";
			}
   			print "\n";
		}
		$con->sql_query("update po set sku_checked=1 where id=$p[po_id] and branch_id = $bid");
	}
}
exit;

// update all sku counts...
print "Updating SKU variety counter...\n";
$c1 = $con->sql_query("select sku_id, count(*) from sku_items group by sku_id");
while ($r = $con->sql_fetchrow($c1))
{
	$con->sql_query("update sku set varieties = $r[1] where id = $r[0]");
}
?>
