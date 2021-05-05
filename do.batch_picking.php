<?php
/*
Update History
==============
7/5/2018 10:23 AM HockLee
- new php file for batch picking

8/30/2018 4:00PM HockLee
- Fixed title error.
*/
$HTTP_REFERER = basename($_SERVER['HTTP_REFERER']);

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO') && $_REQUEST['a'] != 'print'&&!preg_match('/^pm.php/',basename($_SERVER['HTTP_REFERER']))) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("do.include.php");

class BATCH_PICKING extends Module{
	function _default(){
		$this->display();
	}

	function __construct($title){
        global $con, $smarty, $sessioninfo;

        parent::__construct($title);
	}

	function picking_list(){
		global $con, $smarty, $sessioninfo;

		$smarty->assign('PAGE_TITLE', 'Picking List by Batch');
		$smarty->display("do.batch_picking.picking_list.tpl");
	}

	function show_do(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		$batch_code = $form['batch_code'];
		$date_from = $form['date_range_from'];
		$date_to = $form['date_range_to'];
		$branch_id = $sessioninfo['branch_id'];

		$q_do = $con->sql_query("select do.id as 'do_id', do.do_no, do.checkout 
			from do 
			where branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." order by do.do_no asc");

		$row = $con->sql_numrows();
		if($row == 0){
			$smarty->assign('no_data_msg', 'No data. You may check the date or the DO has been checked out.');
		}

		$group_item = array();
		$do = array();
		$grand_total = array();
		$do_det = array();

		$q_group_item = $con->sql_query("select si.id as 'sku_item_id', si.sku_item_code as 'arms_code', si.mcode, si.artno, si.link_code as 'old_code', si.description, si.location, uom.code as uom_code, uom.fraction, sum(di.ctn) as ctn, round(sum(di.ctn)*uom.fraction) as 'uom_pcs', sum(di.pcs) as pcs, round((sum(di.ctn)*uom.fraction)+sum(di.pcs)) as 'total_qty' 
		from do_items di 
		left join do on do.id = di.do_id and do.branch_id = di.branch_id
		left join sku_items si on si.id = di.sku_item_id 
		left join uom on di.uom_id = uom.id 
		where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." 
		group by di.sku_item_id, di.uom_id 
		order by di.sku_item_id asc");

		while($d_group_item = $con->sql_fetchassoc($q_group_item)){
			$group_item[$d_group_item['description']]['sku_item_id'] = $d_group_item['sku_item_id'];
			$group_item[$d_group_item['description']]['arms_code'] = $d_group_item['arms_code'];
			$group_item[$d_group_item['description']]['mcode'] = $d_group_item['mcode'];
			$group_item[$d_group_item['description']]['artno'] = $d_group_item['artno'];
			$group_item[$d_group_item['description']]['old_code'] = $d_group_item['old_code'];
			$group_item[$d_group_item['description']]['description'] = $d_group_item['description'];
			$group_item[$d_group_item['description']]['location'] = $d_group_item['location'];
			$group_item[$d_group_item['description']]['pcs'] += $d_group_item['pcs'];
			$group_item[$d_group_item['description']]['total_qty'] += $d_group_item['total_qty'];
			
			$group_item[$d_group_item['description']]['uom_code'][] = $d_group_item['uom_code'];
			$group_item[$d_group_item['description']]['fraction'][] = $d_group_item['fraction'];
			$group_item[$d_group_item['description']]['uom_ctn'][] = $d_group_item['ctn'];
			$group_item[$d_group_item['description']]['uom_qty'][] = $d_group_item['uom_pcs'];
			$group_item[$d_group_item['description']]['uom_fra'][] = $d_group_item['uom_code']."_".$d_group_item['fraction'];
		}
		$con->sql_freeresult($q_group_item);

		while($do_info = $con->sql_fetchassoc($q_do)){
			$do_id = $do_info['do_id'];
			$smarty->assign('checkout', $do_info['checkout']);		

			$q_do_item = $con->sql_query("select di.ctn, di.pcs, round((di.ctn*uom.fraction)+di.pcs) as 'total_qty', di.sku_item_id, do.id as 'do_id', do.do_no, do.ref_no, si.sku_id, si.sku_item_code, si.mcode, si.link_code, si.description as 'si_desc', si.artno, si.location, uom.code as 'uom_code', uom.fraction, dt.* 
				from do_items di 
				left join do on do_id = do.id and di.branch_id = do.branch_id 
				left join sales_order so on so.order_no = do.ref_no 
				left join debtor dt on dt.integration_code = so.integration_code 
				left join sku_items si on di.sku_item_id = si.id 
				left join sku on si.sku_id = sku.id 
				left join uom on di.uom_id = uom.id 
				where di.do_id = $do_id and di.branch_id = $branch_id");
			
			$total_ctn = 0;
			$total_pcs = 0;
			
			while($do_item = $con->sql_fetchassoc($q_do_item)){
				$do[$do_info['do_no']][] = $do_item;

				// count total ctn, pcs and grand total
				$total_ctn += $do_item['ctn'];
				$total_pcs += $do_item['pcs'];
				$total_qty += $do_item['total_qty'];
				$grand = array('ttl_ctn' => $total_ctn, 'ttl_pcs' => $total_pcs);
				$grand_total[$do_info['do_no']] = $grand;
				$grand_total['total'] = $total_qty;
			}
			$con->sql_freeresult($q_do_item);
			
			$q_item = $con->sql_query("select di.sku_item_id, di.sku_item_id, si.description, si.sku_item_code, si.artno, si.mcode, si.link_code, si.location, uom.code as 'uom_code' 
				from do_items di 
				left join sku_items si on si.id = di.sku_item_id 
				left join uom on uom.id = di.uom_id 
				where di.do_id = $do_id and di.branch_id = ".mi($branch_id)."");

			while($get_item = $con->sql_fetchassoc($q_item)){
				$do_det[$get_item['sku_item_id']][$get_item['uom_code']] = $get_item;
			}
			$con->sql_freeresult($q_item);
		}

		foreach($do_det as $sku_item_id => $uom){
			foreach($uom as $uom_code => $value){
				foreach($do as $do_no => $do_item){
					foreach($do_item as $item_key => $item_value){
						if($item_value['sku_item_id'] == $sku_item_id && $item_value['uom_code'] == $uom_code){
								$data = array(
									'uom_code' => $item_value['uom_code'],
									'ctn' => $item_value['ctn'], 
									'pcs' => $item_value['pcs'], 
									'do_no' => $item_value['do_no']);	
								$do_det[$sku_item_id][$uom_code]['do'][] = $data;
						}
					}
				}
			}
		}
		
		$smarty->assign('batch_code', $batch_code);
		$smarty->assign('from', $date_from);
		$smarty->assign('to', $date_to);
		$smarty->assign('group_item', $group_item);
		$smarty->assign('do', $do);
		$smarty->assign('do_det', $do_det);
		$smarty->assign('grand_total', $grand_total);
		$smarty->assign('PAGE_TITLE', 'Picking List by Batch');
		$smarty->display('do.batch_picking.picking_list.tpl');

		$con->sql_freeresult($q_do);
	}

	function print_picking_list_by_item(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		$batch_code = $form['batch_code'];
		$date_from = $form['date_range_from'];
		$date_to = $form['date_range_to'];
		$branch_id = $sessioninfo['branch_id'];

		$q_do = $con->sql_query("select do.id as 'do_id', do.do_no, do.checkout 
			from do 
			where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." order by do.do_no asc");

		$row = $con->sql_numrows();
		if($row == 0){
			$smarty->assign('no_data_msg', 'No data. You may check the date or the DO has been checked out.');
		}

		$group_item = array();
		$do = array();
		$grand_total = array();
		$do_det = array();

		$q_group_item = $con->sql_query("select si.id as 'sku_item_id', si.sku_item_code as 'arms_code', si.mcode, si.artno, si.link_code as 'old_code', si.description, si.location, uom.code as uom_code, uom.fraction, sum(di.ctn) as ctn, round(sum(di.ctn)*uom.fraction) as 'uom_pcs', sum(di.pcs) as pcs, round((sum(di.ctn)*uom.fraction)+sum(di.pcs)) as 'total_qty' 
		from do_items di 
		left join do on do.id = di.do_id and do.branch_id = di.branch_id
		left join sku_items si on si.id = di.sku_item_id 
		left join uom on di.uom_id = uom.id 
		where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." 
		group by di.sku_item_id, di.uom_id 
		order by di.sku_item_id asc");

		while($d_group_item = $con->sql_fetchassoc($q_group_item)){
			$group_item[$d_group_item['description']]['sku_item_id'] = $d_group_item['sku_item_id'];
			$group_item[$d_group_item['description']]['arms_code'] = $d_group_item['arms_code'];
			$group_item[$d_group_item['description']]['mcode'] = $d_group_item['mcode'];
			$group_item[$d_group_item['description']]['artno'] = $d_group_item['artno'];
			$group_item[$d_group_item['description']]['old_code'] = $d_group_item['old_code'];
			$group_item[$d_group_item['description']]['description'] = $d_group_item['description'];
			$group_item[$d_group_item['description']]['location'] = $d_group_item['location'];
			$group_item[$d_group_item['description']]['pcs'] += $d_group_item['pcs'];
			$group_item[$d_group_item['description']]['total_qty'] += $d_group_item['total_qty'];
			
			$group_item[$d_group_item['description']]['uom_code'][] = $d_group_item['uom_code'];
			$group_item[$d_group_item['description']]['fraction'][] = $d_group_item['fraction'];
			$group_item[$d_group_item['description']]['uom_ctn'][] = $d_group_item['ctn'];
			$group_item[$d_group_item['description']]['uom_qty'][] = $d_group_item['uom_pcs'];
			$group_item[$d_group_item['description']]['uom_fra'][] = $d_group_item['uom_code']."_".$d_group_item['fraction'];
		}
		$con->sql_freeresult($q_group_item);

		while($do_info = $con->sql_fetchassoc($q_do)){
			$do_id = $do_info['do_id'];
			$smarty->assign('checkout', $do_info['checkout']);		

			$q_do_item = $con->sql_query("select di.ctn, di.pcs, round((di.ctn*uom.fraction)+di.pcs) as 'total_qty', di.sku_item_id, do.id as 'do_id', do.do_no, do.ref_no, si.sku_id, si.sku_item_code, si.mcode, si.link_code, si.description as 'si_desc', si.artno, si.location, uom.code as 'uom_code', uom.fraction, dt.* 
				from do_items di 
				left join do on do_id = do.id and di.branch_id = do.branch_id 
				left join sales_order so on so.order_no = do.ref_no 
				left join debtor dt on dt.integration_code = so.integration_code 
				left join sku_items si on di.sku_item_id = si.id 
				left join sku on si.sku_id = sku.id 
				left join uom on di.uom_id = uom.id 
				where di.branch_id = ".mi($branch_id)." and di.do_id = $do_id");
			
			$total_ctn = 0;
			$total_pcs = 0;
			
			while($do_item = $con->sql_fetchassoc($q_do_item)){
				$do[$do_info['do_no']][] = $do_item;

				// count total ctn, pcs and grand total
				$total_ctn += $do_item['ctn'];
				$total_pcs += $do_item['pcs'];
				$total_qty += $do_item['total_qty'];
				$grand = array('ttl_ctn' => $total_ctn, 'ttl_pcs' => $total_pcs);
				$grand_total[$do_info['do_no']] = $grand;
				$grand_total['total'] = $total_qty;
			}
			$con->sql_freeresult($q_do_item);
			
			$q_item = $con->sql_query("select di.sku_item_id, di.sku_item_id, si.description, si.sku_item_code, si.artno, si.mcode, si.link_code, si.location, uom.code as 'uom_code' 
				from do_items di 
				left join sku_items si on si.id = di.sku_item_id 
				left join uom on uom.id = di.uom_id 
				where di.branch_id = ".mi($branch_id)." and di.do_id = $do_id");

			while($get_item = $con->sql_fetchassoc($q_item)){
				$do_det[$get_item['sku_item_id']][$get_item['uom_code']] = $get_item;
			}
			$con->sql_freeresult($q_item);
		}

		foreach($do_det as $sku_item_id => $uom){
			foreach($uom as $uom_code => $value){
				foreach($do as $do_no => $do_item){
					foreach($do_item as $item_key => $item_value){
						if($item_value['sku_item_id'] == $sku_item_id && $item_value['uom_code'] == $uom_code){
								$data = array(
									'uom_code' => $item_value['uom_code'],
									'ctn' => $item_value['ctn'], 
									'pcs' => $item_value['pcs'], 
									'do_no' => $item_value['do_no']);	
								$do_det[$sku_item_id][$uom_code]['do'][] = $data;
						}
					}
				}
			}
		}
		
		$smarty->assign('batch_code', $batch_code);
		$smarty->assign('from', $date_from);
		$smarty->assign('to', $date_to);
		$smarty->assign('group_item', $group_item);
		$smarty->assign('do', $do);
		$smarty->assign('do_det', $do_det);
		$smarty->assign('grand_total', $grand_total);
		$smarty->display('do.batch_picking.picking_list_print_by_item.tpl');

		$con->sql_freeresult($q_do);
	}

	function print_picking_list_by_do(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		$batch_code = $form['batch_code'];
		$date_from = $form['date_range_from'];
		$date_to = $form['date_range_to'];
		$branch_id = $sessioninfo['branch_id'];

		$q_do = $con->sql_query("select do.id as 'do_id', do.do_no, do.checkout 
			from do 
			where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." order by do.do_no asc");

		$row = $con->sql_numrows();
		if($row == 0){
			$smarty->assign('no_data_msg', 'No data. You may check the date or the DO has been checked out.');
		}

		$group_item = array();
		$do = array();
		$grand_total = array();
		$do_det = array();

		$q_group_item = $con->sql_query("select si.id as 'sku_item_id', si.sku_item_code as 'arms_code', si.mcode, si.artno, si.link_code as 'old_code', si.description, si.location, uom.code as uom_code, uom.fraction, sum(di.ctn) as ctn, round(sum(di.ctn)*uom.fraction) as 'uom_pcs', sum(di.pcs) as pcs, round((sum(di.ctn)*uom.fraction)+sum(di.pcs)) as 'total_qty' 
		from do_items di 
		left join do on do.id = di.do_id and do.branch_id = di.branch_id
		left join sku_items si on si.id = di.sku_item_id 
		left join uom on di.uom_id = uom.id 
		where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." 
		group by di.sku_item_id, di.uom_id 
		order by di.sku_item_id asc");

		while($d_group_item = $con->sql_fetchassoc($q_group_item)){
			$group_item[$d_group_item['description']]['sku_item_id'] = $d_group_item['sku_item_id'];
			$group_item[$d_group_item['description']]['arms_code'] = $d_group_item['arms_code'];
			$group_item[$d_group_item['description']]['mcode'] = $d_group_item['mcode'];
			$group_item[$d_group_item['description']]['artno'] = $d_group_item['artno'];
			$group_item[$d_group_item['description']]['old_code'] = $d_group_item['old_code'];
			$group_item[$d_group_item['description']]['description'] = $d_group_item['description'];
			$group_item[$d_group_item['description']]['location'] = $d_group_item['location'];
			$group_item[$d_group_item['description']]['pcs'] += $d_group_item['pcs'];
			$group_item[$d_group_item['description']]['total_qty'] += $d_group_item['total_qty'];
			
			$group_item[$d_group_item['description']]['uom_code'][] = $d_group_item['uom_code'];
			$group_item[$d_group_item['description']]['fraction'][] = $d_group_item['fraction'];
			$group_item[$d_group_item['description']]['uom_ctn'][] = $d_group_item['ctn'];
			$group_item[$d_group_item['description']]['uom_qty'][] = $d_group_item['uom_pcs'];
			$group_item[$d_group_item['description']]['uom_fra'][] = $d_group_item['uom_code']."_".$d_group_item['fraction'];
		}
		$con->sql_freeresult($q_group_item);

		while($do_info = $con->sql_fetchassoc($q_do)){
			$do_id = $do_info['do_id'];
			$smarty->assign('checkout', $do_info['checkout']);		

			$q_do_item = $con->sql_query("select di.ctn, di.pcs, round((di.ctn*uom.fraction)+di.pcs) as 'total_qty', di.sku_item_id, do.id as 'do_id', do.do_no, do.ref_no, si.sku_id, si.sku_item_code, si.mcode, si.link_code, si.description as 'si_desc', si.artno, si.location, uom.code as 'uom_code', uom.fraction, dt.* 
				from do_items di 
				left join do on do_id = do.id and di.branch_id = do.branch_id 
				left join sales_order so on so.order_no = do.ref_no 
				left join debtor dt on dt.integration_code = so.integration_code 
				left join sku_items si on di.sku_item_id = si.id 
				left join sku on si.sku_id = sku.id 
				left join uom on di.uom_id = uom.id 
				where di.branch_id = ".mi($branch_id)." and di.do_id = $do_id");
			
			$total_ctn = 0;
			$total_pcs = 0;
			
			while($do_item = $con->sql_fetchassoc($q_do_item)){
				$do[$do_info['do_no']][] = $do_item;

				// count total ctn, pcs and grand total
				$total_ctn += $do_item['ctn'];
				$total_pcs += $do_item['pcs'];
				$total_qty += $do_item['total_qty'];
				$grand = array('ttl_ctn' => $total_ctn, 'ttl_pcs' => $total_pcs);
				$grand_total[$do_info['do_no']] = $grand;
				$grand_total['total'] = $total_qty;
			}
			$con->sql_freeresult($q_do_item);
			
			$q_item = $con->sql_query("select di.sku_item_id, di.sku_item_id, si.description, si.sku_item_code, si.artno, si.mcode, si.link_code, si.location, uom.code as 'uom_code' 
				from do_items di 
				left join sku_items si on si.id = di.sku_item_id 
				left join uom on uom.id = di.uom_id 
				where di.branch_id = ".mi($branch_id)." and di.do_id = $do_id");

			while($get_item = $con->sql_fetchassoc($q_item)){
				$do_det[$get_item['sku_item_id']][$get_item['uom_code']] = $get_item;
			}
			$con->sql_freeresult($q_item);
		}

		foreach($do_det as $sku_item_id => $uom){
			foreach($uom as $uom_code => $value){
				foreach($do as $do_no => $do_item){
					foreach($do_item as $item_key => $item_value){
						if($item_value['sku_item_id'] == $sku_item_id && $item_value['uom_code'] == $uom_code){
								$data = array(
									'uom_code' => $item_value['uom_code'],
									'ctn' => $item_value['ctn'], 
									'pcs' => $item_value['pcs'], 
									'do_no' => $item_value['do_no']);	
								$do_det[$sku_item_id][$uom_code]['do'][] = $data;
						}
					}
				}
			}
		}
		
		$smarty->assign('batch_code', $batch_code);
		$smarty->assign('from', $date_from);
		$smarty->assign('to', $date_to);
		$smarty->assign('group_item', $group_item);
		$smarty->assign('do', $do);
		$smarty->assign('do_det', $do_det);
		$smarty->assign('grand_total', $grand_total);
		$smarty->display('do.batch_picking.picking_list_print_by_do.tpl');

		$con->sql_freeresult($q_do);
	}

	function packing_input(){
		global $con, $smarty, $sessioninfo;

		$smarty->display("do.batch_picking.packing_input.tpl");
	}

	function show_do_for_packing(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		$batch_code = $form['batch_code'];
		$date_from = $form['date_from'];
		$date_to = $form['date_to'];
		$branch_id = $sessioninfo['branch_id'];

		$q_do = $con->sql_query("select do.id,do.do_no, do.do_date, di.id as do_items_id, do.checkout, dt.description, dt.area, si.sku_item_code, si.mcode, si.description, si.artno, si.link_code, round((di.ctn*uom.fraction)+di.pcs) as 'total_qty', uom.code, p.carton, p.weight_kg, p.pack_date, di.ctn, di.pcs 
			from do 
			left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id 
			left join sku_items si on di.sku_item_id = si.id 
			left join sales_order so on so.order_no = do.ref_no 
			left join debtor dt on dt.integration_code = so.integration_code 
			left join sku on si.sku_id = sku.id 
			left join uom on di.uom_id = uom.id 
			left join packing p on p.do_items_id = di.id and p.branch_id = di.branch_id 
			where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." order by do.do_no, di.id asc");

		$row = $con->sql_numrows();
		if($row == 0){
			$smarty->assign('no_data_msg', 'No data. You may check the date or the DO has been checked out.');
		}

		$do = array();

		while($do_info = $con->sql_fetchassoc($q_do)){
			$smarty->assign('checkout', $do_info['checkout']);
			$do[$do_info['do_no']][] = $do_info;
		}
		$con->sql_freeresult($q_do);

		$smarty->assign('batch_code', $batch_code);
		$smarty->assign('date_from', $date_from);
		$smarty->assign('date_to', $date_to);
		$smarty->assign('do', $do);
		$smarty->display('do.batch_picking.packing_input.tpl');
	}

	function save_packing(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		$batch_code = $form['batch_code'];
		$date_from = $form['date_from'];
		$date_to = $form['date_to'];
		$pack_date = $form['pack_date'];
		$carton = $form['carton'];
		$weight_kg = $form['weight_kg'];
		$do_id = $form['do_id'];
		$branch_id = $sessioninfo['branch_id'];
		$user_id = $sessioninfo['id'];

		$arr_insert = array();
		foreach($carton as $key1 => $value1){
			foreach($value1 as $key2 => $value2){
				$arr_insert[$key1]['carton'] = $value2;
			}
		}

		foreach($arr_insert as $key1 => $value1){
			foreach($weight_kg as $w_key1 => $w_value1){
				if($key1 == $w_key1){
					foreach($w_value1 as $w_key2 => $w_value2){
						$arr_insert[$key1]['weight_kg'] = $w_value2;
					}
				}
			}
		}

		foreach($arr_insert as $key1 => $value1){
			foreach($do_id as $d_key1 => $d_value1){
				if($key1 == $d_key1){
					foreach($d_value1 as $d_key2 => $d_value2){
						$arr_insert[$key1]['do_id'] = $d_value2;
					}
				}
			}
		}

		$upd['branch_id'] = $branch_id;
		$upd['user_id'] = $user_id;
		$upd['pack_date'] = $pack_date;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['active'] = 1;

		foreach($arr_insert as $do_items_id => $value){
			$q_row = $con->sql_query("select * from packing 
				where do_items_id = $do_items_id and active = 1");
			$num = $con->sql_numrows();

			$upd['do_items_id'] = $do_items_id;

			if($num <> 0){
				if(!$value['carton']){
					$upd['carton'] = 0;
				}else{
					$upd['carton'] = $value['carton'];
				}

				if(!$value['weight_kg']){
					$upd['weight_kg'] = 0;
				}else{
					$upd['weight_kg'] = $value['weight_kg'];
				}

				$q_update_packing = $con->sql_query("update packing set " . mysql_update_by_field($upd, array('carton', 'weight_kg', 'user_id', 'branch_id', 'last_update'))." where do_items_id = $do_items_id and branch_id = ".mi($branch_id)."");

				// update DO
				if($value['carton'] == 0 && $value['weight_kg'] == 0){
					$q_update_do_pack = $con->sql_query("update do set packed = 0 where branch_id = ".mi($branch_id)." and id = $value[do_id]");
				}else{
					$q_update_do_pack = $con->sql_query("update do set packed = 1 where branch_id = ".mi($branch_id)." and id = $value[do_id]");
				}
			}else{

				if(!$value['carton']){
					$upd['carton'] = 0;
				}else{
					$upd['carton'] = $value['carton'];
				}

				if(!$value['weight_kg']){
					$upd['weight_kg'] = 0;
				}else{
					$upd['weight_kg'] = $value['weight_kg'];
				}

				$q_packing = $con->sql_query("insert into packing " . mysql_insert_by_field($upd));

				// update DO
				$q_update_do_pack = $con->sql_query("update do set packed = 1 where branch_id = ".mi($branch_id)." and id = $value[do_id]");
			}

			$save_succeed = 'yes';
			$smarty->assign('save_succeed', $save_succeed);
		}

		$q_do = $con->sql_query("select do.id, do.do_no, do.do_date, di.id as do_items_id, dt.description, dt.area, si.sku_item_code, si.mcode, si.description, si.artno, si.link_code, round((di.ctn*uom.fraction)+di.pcs) as 'total_qty', uom.code, p.carton, p.weight_kg, p.pack_date, di.ctn, di.pcs 
			from do 
			left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id 
			left join sku_items si on di.sku_item_id = si.id 
			left join sales_order so on so.order_no = do.ref_no 
			left join debtor dt on dt.integration_code = so.integration_code 
			left join sku on si.sku_id = sku.id 
			left join uom on di.uom_id = uom.id 
			left join packing p on p.do_items_id = di.id and p.branch_id = di.branch_id 
			where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.checkout = 0 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)." order by do.do_no, di.id asc");

		$row = $con->sql_numrows();
		if($row == 0){
			$smarty->assign('no_data_msg', 'No data. You may check the date or the DO has been checked out.');
		}

		$do = array();

		while($do_info = $con->sql_fetchassoc($q_do)){
			$do[$do_info['do_no']][] = $do_info;
		}
		$con->sql_freeresult($q_do);

		$smarty->assign('batch_code', $batch_code);
		$smarty->assign('date_from', $date_from);
		$smarty->assign('date_to', $date_to);
		$smarty->assign('do', $do);
		$smarty->display('do.batch_picking.packing_input.tpl');
	}

	function ajax_search_batch_code(){
	    global $con, $smarty, $sessioninfo;
	    $branch_id = $sessioninfo['branch_id'];
	    $v = trim($_REQUEST['value']);
	    $LIMIT = 50;
	    // call with limit
		$result1 = $con->sql_query("select distinct(batch_code) as batch_code from do where branch_id = ".mi($branch_id)." and batch_code like ".ms('%'.replace_special_char($v).'%')." and active = 1 and approved = 1 order by batch_code limit ".($LIMIT+1));
	    print "<ul>";
		if ($con->sql_numrows($result1) > 0)
		{

		    if ($con->sql_numrows($result1) > $LIMIT)
		    {
				print "<li><span class=informal>Showing first $LIMIT items...</span></li>";
			}

			// generate list.
			while ($r = $con->sql_fetchrow($result1))
			{
				$out .= "<li title=".htmlspecialchars($r['batch_code'])."><span>".htmlspecialchars($r['batch_code']);
				$out .= "</span>";
				$out .= "</li>";
			}
	    }
	    else
	    {
	       print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
		}
		print $out;
	    print "</ul>";
		exit;
	}
}

$BATCH_PICKING = new BATCH_PICKING('Input Packing Information');

?>