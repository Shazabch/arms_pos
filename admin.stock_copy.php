<?php
/*

REVISION HISTORY
================
11/16/2010 3:36:05 PM Alex
- created by me (script by andy)


*/

set_time_limit(0);
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Stock_copy extends Module{

	function __construct($title){
		global $con, $smarty;

		$con->sql_query("select * from branch where active=1 order by sequence");

		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}

		$smarty->assign("branches", $branches);

		parent::__construct($title);
	}

	function _default(){
		$_REQUEST['from_date'] =date("Y-m-d");
		$_REQUEST['to_date'] =date("Y-m-d");

		$this->display();
		exit;
	}

	function copy_stock(){
		global $sessioninfo,$con, $LANG, $smarty;

		// transfer stock balance to new opening
		$from_date = $_REQUEST['from_date'];
        $to_date= $_REQUEST['to_date'];

		$from_bid = $_REQUEST['from_branch_id'];
		$to_bid = $_REQUEST['to_branch_id'];

		if ($from_bid == $to_bid && $from_date == $to_date){
			$err[] = $LANG['SC_INVALID_TRANSFER'];
            $smarty->assign('err',$err);
		}else{
		
			$con->sql_query("delete from stock_check where branch_id=$to_bid and date=".ms($to_date));

			$sb_tbl_from = "stock_balance_b".$from_bid."_".date('Y', strtotime($from_date));
			$q1 = $con->sql_query("select sb.sku_item_id,sb.qty,si.sku_item_code
				from $sb_tbl_from sb
				left join sku_items si on si.id=sb.sku_item_id
				where ".ms($from_date)." between sb.from_date and sb.to_date and si.sku_item_code is not null");
			$item_no = 0;
			$sid_list = array();
			$total_rows = $con->sql_numrows();

			while($r = $con->sql_fetchrow($q1)){
				$item_no++;
				$sid_list[] = mi($r['sku_item_id']);

				$upd = array();
				$upd['branch_id'] = $to_bid;
				$upd['date'] = $to_date;
				$upd['sku_item_code'] = $r['sku_item_code'];
				$upd['scanned_by'] = $sessioninfo['id'];
				$upd['location'] = $from_bid;
				$upd['shelf_no'] = $from_date;
				$upd['item_no'] = $item_no;
				$upd['qty'] = $r['qty'];
				$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));

				if(count($sid_list)>1000){
					$con->sql_query("update sku_items_cost set changed=1 where branch_id=$to_bid and sku_item_id in (".join(',', $sid_list).")");
					$sid_list = array();
				}
			}
			if($sid_list){
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=$to_bid and sku_item_id in (".join(',', $sid_list).")");
				$sid_list = array();
			}

			$success[]= sprintf($LANG['SC_SUCCESS'],$total_rows);
			$smarty->assign('success', $success);
		}
		$this->display();
	}
	
	function reset_stock(){
		global $con, $LANG, $smarty;

		$from_date = $_REQUEST['from_date'];
        $to_date= $_REQUEST['to_date'];

		$from_bid = $_REQUEST['from_branch_id'];
		$to_bid = $_REQUEST['to_branch_id'];

		$con->sql_query("delete from stock_check where branch_id=$to_bid and date=".ms($to_date)." and location=$from_bid and shelf_no=".ms($from_date));

		$success[]= sprintf($LANG['SC_RESET'],	$con->sql_affectedrows());
		$smarty->assign('success', $success);

		$this->display();
	}
}

$Stock_Copy = new Stock_copy('Stock Copy');

?>
