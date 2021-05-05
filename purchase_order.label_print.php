<?php
	include("include/common.php");

	// po number must be given
	// print to where... printer port? file??
	if (!isset($_REQUEST['po_no']) || !isset($_REQUEST['send_to']))
	{
		print "<script>alert('Error: po_no and send_to not defined.');</script>";
		exit;	
	}
	
	$con->sql_query("select id, branch_id from po where po_no = " . ms($_REQUEST['po_no']));
	$r=$con->sql_fetchrow();
	if (!$r)
	{
		print "<script>alert('Invalid PO Number $_REQUEST[po_no]')</script>";
		exit;
	}
	
	$poid = $r[0];
	$branch_id = $r[1];
	$send_to = $_REQUEST['send_to'];
	
	$con->sql_query("select ".ms($_REQUEST['po_no'])." as po_no, sku_item_code, (qty*order_uom_fraction+qty_loose)+(foc*order_uom_fraction+foc_loose) as qty, default_trade_discount_code, artno, po_items.selling_price, receipt_description from po_items left join sku_items on po_items.sku_item_id = sku_items.id left join sku on sku_items.sku_id = sku.id where po_id = $poid and branch_id = $branch_id order by po_items.id");
	$smarty->assign("items", $con->sql_fetchrowset());
	
	$fp = fopen($send_to, "w");
	fwrite($fp, $smarty->fetch("purchase_order.label_print.datamax.tpl"));
	fclose($fp);
	
	print "<script>alert('The barcode file for PO No. $_REQUEST[po_no] is ready.\\nPlease use ATP Barcode to print the barcodes.');</script>";
?>
