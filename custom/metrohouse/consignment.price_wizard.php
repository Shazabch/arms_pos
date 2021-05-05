<?php
/*
18/3/15   yinsee
- new function for metrohouse GST markup
*/

include("../../include/common.php");
// ini_set('display_errors',1);
// error_reporting(E_ALL);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
//if (!privilege('MST_SKU_UPDATE_PRICE') && !privilege('MST_SKU_UPDATE_FUTURE_PRICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE_PRICE or MST_SKU_UPDATE_FUTURE_PRICE', BRANCH_CODE), "/index.php");

class GST_PRICE_WIZARD extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;

		$this->init_selection();
		$this->display("metrohouse/consignment.price_wizard.tpl");
	}


	function init_selection(){
		global $con, $smarty;

		$q1 = $con->sql_query("select * from branch where active=1 order by sequence, code");
		
		while($r = $con->sql_fetchassoc($q1)){
			$branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("branch_list", $branch_list);
	}

	function get_selling_price_for_branch($bid)
	{
		global $con;

		$q1 = $con->sql_query("select si.*, if(sip.price is null,si.selling_price,sip.price) as price, 
												   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code, 
												   sku.default_trade_discount_code 
												   from sku_items si
												   left join sku on sku_id = sku.id 
												   left join sku_items_price sip on sip.sku_item_id = si.id and branch_id = ".mi($bid)." 
												   order by artno");
		// $price_info = $con->sql_fetchrowset($q1);
		// $con->sql_freeresult($q1);

		return $q1;
	}

	function valid_artno($artno) {
		preg_match('/(\d{4,5})/', $artno, $matches);
		$number_part = $matches[1];

		if (preg_match('/^DD/', $artno)) {
			return true;
		}

		if (preg_match('/^PK/', $artno) && strlen($number_part)==4 && $number_part >= 2300 && $number_part <= 2999) {
			return true;
		}

		if (preg_match('/^PG/', $artno) && strlen($number_part)==4 && $number_part >= 1900 && $number_part <= 1999) {
			return true;
		}

		if (strlen($number_part)==5 && $number_part >= '08300' && $number_part <= '09999') {
			return true;
		}

		if (strlen($number_part)==4 && $number_part >= 1299 && $number_part <= 1999) {
			return true;
		}

		if (strlen($number_part)==4 && $number_part >= 3737 && $number_part <= 3999) {
			return true;
		}

		if (strlen($number_part)==5 && $number_part >= 50090 && $number_part <= 59999) {
			return true;
		}

		if (strlen($number_part)==4 && $number_part >= 6615 && $number_part <= 6999) {
			return true;
		}

		if (strlen($number_part)==4 &&$number_part >= 7276 && $number_part <= 7999) {
			return true;
		}

		return false;
	}

	function show_price() {

		global $smarty, $con;

		$bid = intval($_REQUEST['branch_id']);
		$this->init_selection();
		// print_r($list);

		// $smarty->assign("price_list", $list);
		$smarty->assign("nofooter", true);
		$this->display("metrohouse/consignment.price_wizard.tpl");

		$con->sql_query("select * from branch where id=$bid");
		$branch = $con->sql_fetchassoc();
		print "<br><br><h2>$branch[code] - $branch[description]</h2>";

		print "<table border=1 cellpadding=2 cellspacing=0>";
		print "<tr><th>Art No</th><th>Description</th><th>Price</th><th>New Price</th><th>%</th></tr>";
		$list = $this->get_selling_price_for_branch($bid);
		while($r = $con->sql_fetchassoc($list))
		{
			// filter artnos
			if (!$this->valid_artno($r['artno'])) continue;
			$newprice = round($r['price'] * 1.06 + 0.90) - 0.10;
			$percent = intval(($newprice - $r['price'])/$r['price'] * 100) . '%';
			print "<tr><td>$r[artno]</td><td>$r[description]</td><td>$r[price]</td><td>$newprice</td><td>$percent</td></tr>";
		}
		print "</table>";
		
	}

	function download_price() {

		global $con;

		$bid = intval($_REQUEST['branch_id']);

		$con->sql_query("select * from branch where id=$bid");
		$branch = $con->sql_fetchassoc();


		header("Content-type: text/csv"); 
		header("Content-Disposition: attachment; filename='$branch[code]-$branch[description].csv'"); 

		print "$branch[code],$branch[description]\n\n";
		print "Art No,Description,Price,New Price,%\n";

		$list = $this->get_selling_price_for_branch($bid);

		while($r = $con->sql_fetchassoc($list))
		{
			// filter artnos
			if (!$this->valid_artno($r['artno'])) continue;
			$newprice = round($r['price'] * 1.06 + 0.90) - 0.10;
			$percent = intval(($newprice - $r['price'])/$r['price'] * 100) . '%';
			print "$r[artno],$r[description],$r[price],$newprice,$percent\n";
		}
	}

	// todo: apply prices
}
$GST_PRICE_WIZARD = new GST_PRICE_WIZARD("Consignment GST Price Wizard");
?>
