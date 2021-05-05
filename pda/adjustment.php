<?php
/*
8/17/2012 9:30 AM Justin
- Bug fixed on adjustment cannot get branch ID.

8/29/2012 1:36 PM Andy
- Add privilege checking for DO, GRR, GRN, Adj, Stock Take and Voucher.

3/26/2013 5:12 PM Justin
- Bug fixed on system straight die the page with error, causing some PDA hardware cannot run properly.

4/6/2018 1:56 PM Andy
- Enhanced PDA Adjustment to check module_type = adjustment.

1/9/2020 1:17 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.
*/
include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
if (!privilege('ADJ')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ADJ', BRANCH_CODE), "/pda");

class Adjustment_Module extends Scan_Product{
    function __construct($title){
        global $sessioninfo;
        
        $_SESSION['scan_product']['type'] = 'Adjustment';
		$_SESSION['scan_product']['name'] = isset($_SESSION['adj']['id']) ? $_SESSION['adj']['report_prefix'].str_pad($_SESSION['adj']['id'], 5, 0, STR_PAD_LEFT) : '';
		
	    /*if(isset($_REQUEST['branch_id'])){
			if($_REQUEST['branch_id'] != $sessioninfo['branch_id']){    // prevent edit other branch
				header("Location: $_SERVER[PHP_SELF]");
				exit;
			}
		}*/
		parent::__construct($title);
	}
	
    function init_module(){
	    global $con, $smarty;
		// alter any default value, such as $this->scan_templates and $this->result_templates

		$smarty->assign('PAGE_TITLE', $this->title);
		$smarty->assign('module_name', $this->title);
		$smarty->assign('top_include','adjustment.top_include.tpl');
		$smarty->assign('btm_include','adjustment.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if($id && $branch_id){
			$this->reset_session_adjustment($id,$branch_id);
		}else{
            $id = mi($_SESSION['adj']['id']);
            $branch_id = mi($_SESSION['adj']['branch_id']);
		}

		if($id>0){
			$this->show_scan_product();
		}else{
			$this->show_setting();
		}
	}
	
	function show_scan_product(){
		global $con, $smarty, $config;

		$id = mi($_SESSION['adj']['id']);
        $branch_id = mi($_SESSION['adj']['branch_id']);
        $report_prefix = $_SESSION['adj']['report_prefix'];
        
		// check DO exists or not
		$con->sql_query("select *
		from adjustment a
		where a.id=$id and a.branch_id=$branch_id and a.active=1");
		$form = $con->sql_fetchrow();

		if(!$form){
            js_redirect('Invalid Adjustment', "index.php");
            exit;
		}else{
			$form['search_var'] = $_SESSION['adj']['find_adjustment'];

			$form['is_config_adj_type'] = false;
			if($config['adjustment_type_list']){
				foreach($config['adjustment_type_list'] as $config_adj){
					if(strtoupper($config_adj['name']) == $form['adjustment_type']){
						$form['is_config_adj_type'] = true;
						$form['adj_type'] = $config_adj['adj_type'];
						break;
					}
				}
			}
			
			$smarty->assign("form", $form);
		}

		/*$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$so_no = $report_prefix.sprintf('%05d',$id);*/

		$_SESSION['scan_product']['type'] = 'ADJ';
		$_SESSION['scan_product']['name'] = $report_prefix.str_pad($id, 5, "0", STR_PAD_LEFT);

		$product_code = strtoupper($_REQUEST['product_code']);
		// cut last digit
		$product_code2 = strtoupper(substr($product_code,0,strlen($product_code)-1));
		
		$filter[] = "(si.mcode=".ms($product_code)." or si.mcode=".ms($product_code2).")";
		$filter[] = "(si.link_code=".ms($product_code)." or si.link_code=".ms($product_code2).")";
		$filter[] = "(si.sku_item_code=".ms($product_code)." or si.sku_item_code=".ms($product_code2).")";
		$filter[] = "(si.artno=".ms($product_code)." or si.artno=".ms($product_code2).")";
		$filter = join(' or ',$filter);

		$sql = $con->sql_query("select si.* from sku_items si where $filter");

		if($con->sql_numrows($sql) == 1 && $_REQUEST['auto_add_item']){
			$sku_info = $con->sql_fetchassoc($sql);
			$con->sql_freeresult($sql);
			$_REQUEST['item_qty'][$sku_info['id']] = 1;
			$this->add_items();
			unset($_REQUEST);
			$smarty->assign("auto_add", 1);
		}
		
		// get item info
		$con->sql_query("select count(*) as total_item,sum(ai.qty) as total_pcs
		from adjustment_items ai where ai.adjustment_id=$id and ai.branch_id=$branch_id");
		$smarty->assign('items_details',$con->sql_fetchrow());
		
		$this->search_product();
	}

	function new_adj(){
		unset($_SESSION['adj']);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function show_setting(){
		global $con, $smarty;

		$this->default_load();

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id || !$branch_id){
            $id = mi($_SESSION['adj']['id']);
            $branch_id = mi($_SESSION['adj']['branch_id']);
		}
		
		if($id>0 && $branch_id>0){
		    $this->reset_module_session($id, $branch_id);
			$con->sql_query("select a.*, b.code as branch_code, b.description, b.report_prefix
							 from adjustment a
							 left join branch b on b.id = a.branch_id
							 where a.id=$id and a.branch_id=$branch_id");
			
			$form = $con->sql_fetchrow();
			if($_SESSION['adj']['find_adjustment']) $form['find_adjustment'] = $_SESSION['adj']['find_adjustment'];
			elseif($_REQUEST['find_adjustment']) $form['find_adjustment'] = $_REQUEST['find_adjustment'];
			$smarty->assign('form', $form);
		}
		$smarty->assign('adj_tab', 'setting');
		$smarty->display('adjustment.index.tpl');
	}
	
	private function default_load(){
		global $con, $smarty, $sessioninfo;

		if ($sessioninfo['level'] < 9999){
			if (!$sessioninfo['departments'])
				$depts = "id in (0)";
			else
				$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
		}
		else{
			$depts = 1;
		}
		// show department option
		$con->sql_query("select id, description from category where active = 1 and level = 2 and $depts order by description");
		$smarty->assign("dept", $con->sql_fetchrowset($q1));
		
		// all branches
		$con->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$smarty->assign('branches',$branches);
	}
	
	function save_setting(){
		global $con, $smarty, $sessioninfo, $config, $appCore;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$debtor_id = mi($_REQUEST['debtor_id']);
		$order_date = $_REQUEST['order_date'];

        $upd = array();

        $upd['adjustment_date'] = $_REQUEST['adjustment_date'];
		$upd['adjustment_type'] = $_REQUEST['adjustment_type'];
        $upd['dept_id'] = $_REQUEST['dept_id'];
		$upd['remark'] = $_REQUEST['remark'];
		$upd['user_id'] = $sessioninfo['id'];
		$upd['approved'] = 0;
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		
		// validating
        if(!$upd['adjustment_date'])   $err[] = "Date cannot be empty";
		if(!$upd['adjustment_type'])  $err[] = "Adjustment Type cannot be empty";
        if(!$upd['dept_id'])   $err[] = "No Department was selected";
		
		if($err){
			$this->default_load();
			$smarty->assign('form',$upd);
			$smarty->assign('err',$err);
			$smarty->display('adjustment.index.tpl');
			exit;
		}

		// existing adjustment
		if($id>0){
            $con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id");
			
			$con->sql_query("select adjustment_type from adjustment where id = ".mi($id)." and branch_id = ".mi($branch_id));
			$adj_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			if($config['adjustment_type_list']){
				foreach($config['adjustment_type_list'] as $config_adj){
					if(strtoupper($config_adj['name']) == $adj_info['adjustment_type']){
						$adj_type = $config_adj['adj_type'];
						break;
					}
				}
			}
			
			if($adj_type){
				if($adj_type == "+") $filter = " and qty < 0";
				else $filter = " and qty > 0";
				
				$con->sql_query("update adjustment_items set qty = 0 where adjustment_id = ".mi($id)." and branch_id = ".mi($branch_id).$filter);
			}
			
		}else{  // new adjustment
			$upd['id'] = $appCore->generateNewID("adjustment","branch_id=".mi($branch_id));
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['status'] = 0;
			
			$con->sql_query("insert into adjustment ".mysql_insert_by_field($upd));
			$id = $upd['id'];
		}

		$this->reset_module_session($id,$branch_id);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	private function reset_module_session($id,$branch_id){
	    global $con;

	    $con->sql_query("select a.id, a.branch_id, b.report_prefix
						 from adjustment a 
						 left join branch b on b.id = a.branch_id
						 where a.id=$id and a.branch_id=$branch_id");

		$form = $con->sql_fetchrow();
		if(!$form)	js_redirect('Invalid Adjustment', "index.php");
		if($_REQUEST['find_adjustment']) $form['find_adjustment'] = $_REQUEST['find_adjustment'];
		elseif($_SESSION['adj']['find_adjustment']) $form['find_adjustment'] = $_SESSION['adj']['find_adjustment'];
        $_SESSION['adj'] = $form;
	}
	
	function view_items(){
		global $con, $smarty, $config;
		$id = mi($_SESSION['adj']['id']);
        $branch_id = mi($_SESSION['adj']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}else{
			$q1 = $con->sql_query("select adjustment_type from adjustment where id = ".mi($id)." and branch_id = ".mi($branch_id));
			$form = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$form['is_config_adj_type'] = false;
			if($config['adjustment_type_list']){
				foreach($config['adjustment_type_list'] as $config_adj){
					if(strtoupper($config_adj['name']) == $form['adjustment_type']){
						$form['is_config_adj_type'] = true;
						$form['adj_type'] = $config_adj['adj_type'];
						break;
					}
				}
			}
			if($_SESSION['adj']['find_adjustment']) $form['find_adjustment'] = $_SESSION['adj']['find_adjustment'];
			elseif($_REQUEST['find_adjustment']) $form['find_adjustment'] = $_REQUEST['find_adjustment'];
			$smarty->assign("form", $form);
		}

		// load item list
        $con->sql_query("select ai.*,si.sku_item_code,si.description as sku_description, si.doc_allow_decimal
						 from adjustment_items ai
						 left join sku_items si on si.id=ai.sku_item_id
						 where ai.adjustment_id=$id and ai.branch_id=$branch_id order by ai.id");
        $smarty->assign('items',$con->sql_fetchrowset());

		$smarty->assign('adj_tab', 'view_items');
		$smarty->display('adjustment.view_items.tpl');
	}
	
	function add_items(){
		global $con, $config, $sessioninfo, $appCore;

	    $id = mi($_SESSION['adj']['id']);
        $branch_id = mi($_SESSION['adj']['branch_id']);

		$q1 = $con->sql_query("select adjustment_type from adjustment where id = ".mi($id)." and branch_id = ".mi($branch_id));
		$adj_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$adj_type = "";
		if($config['adjustment_type_list']){
			foreach($config['adjustment_type_list'] as $config_adj){
				if(strtoupper($config_adj['name']) == $adj_info['adjustment_type']){
					$adj_type = $config_adj['adj_type'];
					break;
				}
			}
		}
		
		//print_r($_REQUEST);
		$items = $_REQUEST['item_qty'];
		if($items){
			foreach($items as $sid=>$qty){
				if($qty!=0){
					if($adj_type == "+") $qty = abs($qty);
					elseif($adj_type == "-" && $qty > 0) $qty *= -1;

					$item_need_add[$sid] = round($qty, $config['global_qty_decimal_points']);
				}
			}

			if($item_need_add){
				// get items info
				$q1 = $con->sql_query("select si.id as sku_item_id, sic.qty as stock_balance, 
									   ifnull(sic.grn_cost, si.cost_price) as cost,
									   ifnull(sip.price, si.selling_price) as selling_price
									   from sku_items si
									   left join sku on sku_id = sku.id
									   left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
									   left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
									   where si.id in (".join(',',array_keys($item_need_add)).")");
				$total_pcs = 0;
				$total_amount = 0;
				
				while($item = $con->sql_fetchassoc($q1)){
					$sid = $item['sku_item_id'];
					$pcs = $item_need_add[$sid];
					$item['id'] = $appCore->generateNewID("adjustment_items","branch_id=".mi($branch_id));
					$item['qty'] = $pcs;
					$item['branch_id'] = $branch_id;
					$item['adjustment_id'] = $id;
					$item['user_id'] = $sessioninfo['id'];

					$con->sql_query("insert into adjustment_items ".mysql_insert_by_field($item, array('id', 'branch_id','adjustment_id','user_id','sku_item_id','qty','cost','selling_price','stock_balance')));
					
					$total_pcs += mi($pcs);
					$total_amount += mf($pcs*$item['selling_price']);
				}
				$con->sql_freeresult($q1);

				$ret['success'] = true;
			}
		}else{
            $ret['error'][] = "No items found";
		}

		return $ret;
	}
	
	function save_items(){
        global $con, $smarty;
		$id = mi($_SESSION['adj']['id']);
        $branch_id = mi($_SESSION['adj']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
        if($_REQUEST['item']){
			foreach($_REQUEST['item'] as $dummy=>$aid){
				$qty = $_REQUEST['p_item_qty'][$aid] - $_REQUEST['n_item_qty'][$aid];
				$con->sql_query("update adjustment_items set qty=".mf($qty)." where branch_id=".mi($branch_id)." and adjustment_id=".mi($id)." and id=".mi($aid));
			}
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function delete_items(){
		global $con, $smarty;
		$id = mi($_SESSION['adj']['id']);
        $branch_id = mi($_SESSION['adj']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		if($_REQUEST['item_chx']){
            $con->sql_query("delete from adjustment_items where 
							 adjustment_id=$id and branch_id=$branch_id 
							 and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function open(){
		global $con, $smarty, $sessioninfo, $config;

		if(isset($_REQUEST['find_adjustment'])){
			$id = mi($_REQUEST['find_adjustment']);
			$adj_list = array();

			if($config['adjustment_branch_selection'] && $config['single_server_mode']){
				// do nothing
			}else{
				if (!$sessioninfo['departments']){
					$depts = "(0)";
				}else{
					$depts = "(" . join(",", array_keys($sessioninfo['departments'])) . ")";
				}
				
				if ($sessioninfo['level']<9999){
					if ($sessioninfo['level']>=800){
						$where[] = "(adj.dept_id in $depts)";
					}elseif($sessioninfo['level']>=400){
						$where[] = "(adj.branch_id = ".mi($sessioninfo['branch_id'])." and adj.dept_id in $depts)";
					}else{
						$where[] = " adj.user_id = ".mi($sessioninfo['id']);
					}
				}

				if(BRANCH_CODE != 'HQ'){
					$where[] = " adj.branch_id=".mi($sessioninfo['branch_id']);
				}
			}
			
			$where[] = "adj.id=$id and adj.active=1 and adj.approved=0 and adj.status=0 and adj.module_type='adjustment'";
			
			
			$sql = "select adj.*, b.report_prefix
			from adjustment adj
			left join branch b on b.id=adj.branch_id
			where ".join(" and ", $where);

			$con->sql_query($sql);
			if($con->sql_numrows()<=0){
				$err[] = "No Adjustment Found with $id.";
			}else{
				while($r = $con->sql_fetchrow()){
					$adj_list[] = $r;
				}

				$smarty->assign('adj_list',$adj_list);
			}
		}
		$smarty->assign('err',$err);
		$smarty->display('adjustment.search.tpl');
	}
	
	function change_adjustment(){
	    global $con,$smarty;

        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id || !$branch_id){
           js_redirect('Invalid Adjustment', "index.php");
           exit;
		}else{
			$this->reset_module_session($id,$branch_id);
		}

		header("Location: $_SERVER[PHP_SELF]");
	}

	function add_item_by_grn_barcode(){
		global $con, $smarty;

		$code = trim($_REQUEST['product_code']);
		$si_info=get_grn_barcode_info($code,false);
		if ($si_info['sku_item_id']){
			$sku_item_id = $si_info['sku_item_id'];
			$qty = mf($si_info['qty_pcs']);
			//$selling_price = mf($si_info['selling_price']);
			//if(isset($si_info['new_cost_price'])) $cost_price = $si_info['new_cost_price'];
		}
		
		if($si_info && !$si_info['err']){
			$_REQUEST['item_qty'][$sku_item_id] = $qty;
			$this->add_items();
			// get item info
			$con->sql_query("select count(*) as total_item, sum(qty) as total_pcs from adjustment_items where adjustment_id=".mi($_SESSION['adj']['id'])." and branch_id=".mi($_SESSION['adj']['branch_id'])) or die(mysql_error());
			$smarty->assign('items_details',$con->sql_fetchrow());
		}elseif($code) $this->err[] = "The item (".$code.") not found!";
	}
}

$Adjustment_Module = new Adjustment_Module('Adjustment');
?>
