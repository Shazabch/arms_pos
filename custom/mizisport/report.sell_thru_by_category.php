<?php
/*
*/

include("../../include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SELL_THRU_BY_CATEGORY extends Module{
    function __construct($title){
		global $con, $smarty, $config;

		$this->view_type = array(1=>'Aging',2=>'Price Points');
		
		$smarty->assign("view_type", $this->view_type);

		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');

		// load branches
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();
		
    	parent::__construct($title);
    }
	
	function _default(){
		$this->display("mizisport/report.sell_thru_by_category.tpl");
		exit;
	}

	function show_report(){
		$this->process_form();
		$this->generate_report();
		$this->display("mizisport/report.sell_thru_by_category.tpl");
	}

	private function run_report($bid){
        global $con, $smarty,$sessioninfo, $config;

		$con_multi = new mysql_multi();
		$cid_list[$bid] = array();

		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		if($this->filter) $filter = " where ".join(" and ", $this->filter);
		
		if($this->level == 3){
			if($this->view_type == 1){ // view by aging
				$group_by = "group by csc.date";
				$order_by = "order by csc.date desc";
			}else{ // view by price points
				$group_by = "group by csc.amount";
				$order_by = "order by csc.amount";
			}
		}else{
			$group_by = "group by cc.p".mi($this->level);
			$order_by = "order by c.description";
		}
		
		// sales by category
		$q1 = $con_multi->sql_query("select cc.*, c.root_id, sum(csc.amount) as amount, csc.date,
									 cc.p".mi($this->level)." as category_id, c.description,
									 cc.p1 as mst_cid
									 from category_sales_cache_b$bid csc
									 left join category_cache cc on cc.category_id = csc.category_id
									 left join category c on c.id = cc.p".mi($this->level)."
									 $filter
									 $group_by
									 $order_by");

		while($r = $con_multi->sql_fetchassoc($q1)){
			if($this->level == 3){
				if($this->view_type == 1){ // view by aging
					$this->table[$bid]['1st Age']['description'] = "Within 3 Months";
					$this->table[$bid]['2nd Age']['description'] = "Within 4 to 6 Months";
					$this->table[$bid]['3rd Age']['description'] = "Within 7 to 12 Months";
					$this->table[$bid]['4th Age']['description'] = "More Than 1 Year";
					if(strtotime("-3 months", time()) <= strtotime($r['date'])){ // 0 ~ 3 months
						$this->table[$bid]['1st Age']['amount'] += $r['amount'];
					}elseif(strtotime("-6 months", time()) <= strtotime($r['date']) && strtotime("-3 months", time()) > strtotime($r['date'])){ // 4 ~ 6 months
						$this->table[$bid]['2nd Age']['amount'] += $r['amount'];
					}elseif(strtotime("-1 year", time()) <= strtotime($r['date']) && strtotime("-6 months", time()) > strtotime($r['date'])){ // 7 ~ 12 months
						$this->table[$bid]['3rd Age']['amount'] += $r['amount'];
					}else{ // > 13 months
						$this->table[$bid]['4th Age']['amount'] += $r['amount'];
					}
				}else{
					foreach($config['report_price_range'] as $row=>$f){
						if(!$this->table[$bid][$f['from'].$f['to']]['description']){
							if(!mf($f['to']) && $f['from']){
								$this->table[$bid][$f['from'].$f['to']]['description'] = " Above RM".$f['from'];
							}else{
								$this->table[$bid][$f['from'].$f['to']]['description'] = "Between RM".$f['from']." - RM".$f['to'];
							}
						}
						$this->table[$bid][$f['from'].$f['to']]['amount']+=0;
						if(($r['amount'] >= mf($f['from']) || !mf($f['from'])) && $r['amount'] <= mf($f['to'])){
							$this->table[$bid][$f['from'].$f['to']]['description'] = "Between RM".$f['from']." - RM".$f['to'];
							$this->table[$bid][$f['from'].$f['to']]['amount'] += $r['amount'];
						}elseif(!mf($f['to']) && $r['amount'] >= $f['from']){
							$this->table[$bid][$f['from'].$f['to']]['description'] = " Above RM".$f['from'];
							$this->table[$bid][$f['from'].$f['to']]['amount'] += $r['amount'];
						}
					}
				}
			}else{
				if(!$r['description']) $r['description'] = "Un-categorized";
				$this->table[$bid][$r['category_id']]['mst_cid'] = $r['mst_cid'];
				$this->table[$bid][$r['category_id']]['category_id'] = $r['category_id'];
				$this->table[$bid][$r['category_id']]['description'] = $r['description'];
				$this->table[$bid][$r['category_id']]['amount'] += $r['amount'];
				if($this->level == 2) $this->table[$bid][$r['category_id']]['prev_cat'] = $r['p'.$this->level];
				else $this->table[$bid][$r['category_id']]['prev_cat'] = $r['p2'];
			}
		}
		$con_multi->sql_freeresult($q1);	
			
		if($this->sb_filter) $sb_filter = " where ".join(" and ", $this->sb_filter);
		// get stock balance
		$q2 = $con->sql_query("select cc.*, si.id, cc.p".mi($this->level)." as category_id, c.description, cc.p1 as mst_cid
							   from sku_items si
							   left join sku on sku.id = si.sku_id
							   left join category_cache cc on cc.category_id = sku.category_id
							   left join category c on c.id = cc.p".mi($this->level)."
							   $sb_filter");
		
		while($r1 = $con->sql_fetchassoc($q2)){
			$q3 = $con->sql_query("select sum(qty * cost) as sb_amount, to_date
								   from stock_balance_b".$bid."_".$this->sb_year." 
								   where sku_item_id=".mi($r1['id'])." and ".ms($this->sb_date)." between from_date and to_date limit 1");
			
			$sb_info = $con->sql_fetchrow($q3);
			$con->sql_freeresult($q3);

			if($sb_info['sb_amount']){
				if($this->level == 3){
					if($this->view_type == 1){ // view by aging
						if(strtotime("-3 months", time()) <= strtotime($sb_info['to_date'])){ // 0 ~ 3 months
							$this->table[$bid]['1st Age']['description'] = "Within 3 Months";
							$this->table[$bid]['1st Age']['sb_amount'] += $sb_info['sb_amount'];
						}elseif(strtotime("-6 months", time()) <= strtotime($sb_info['to_date']) && strtotime("-3 months", time()) > strtotime($sb_info['to_date'])){ // 4 ~ 6 months
							$this->table[$bid]['2nd Age']['description'] = "Within 4 to 6 Months";
							$this->table[$bid]['2nd Age']['sb_amount'] += $sb_info['sb_amount'];
							$option = 2;
						}elseif(strtotime("-1 year", time()) <= strtotime($sb_info['to_date']) && strtotime("-6 months", time()) > strtotime($sb_info['to_date'])){ // 7 ~ 12 months
							$this->table[$bid]['3rd Age']['description'] = "Within 7 to 12 Months";
							$this->table[$bid]['3rd Age']['sb_amount'] += $sb_info['sb_amount'];
						}else{ // > 13 months
							$this->table[$bid]['4th Age']['description'] = "More Than 1 Year";
							$this->table[$bid]['4th Age']['sb_amount'] += $sb_info['sb_amount'];
						}
					}else{
						foreach($config['report_price_range'] as $row=>$f){
							if(($sb_info['sb_amount'] >= mf($f['from']) || !mf($f['from'])) && $sb_info['sb_amount'] <= mf($f['to'])){
								$this->table[$bid][$f['from'].$f['to']]['description'] = "Between RM".$f['from']." - RM".$f['to'];
								$this->table[$bid][$f['from'].$f['to']]['sb_amount'] += $sb_info['sb_amount'];
							}elseif(!mf($f['to']) && $sb_info['sb_amount'] >= $f['from']){
								$this->table[$bid][$f['from'].$f['to']]['description'] = " Above RM".$f['from'];
								$this->table[$bid][$f['from'].$f['to']]['sb_amount'] += $sb_info['sb_amount'];
							}
						}
					}
				}else{
					if(!$r1['description']) $r1['description'] = "Un-categorized";
					$this->table[$bid][$r1['category_id']]['mst_cid'] = $r1['mst_cid'];
					$this->table[$bid][$r1['category_id']]['category_id'] = $r1['category_id'];
					$this->table[$bid][$r1['category_id']]['description'] = $r1['description'];
					$this->table[$bid][$r1['category_id']]['sb_amount'] += $sb_info['sb_amount'];
					if($this->level == 2) $this->table[$bid][$r1['category_id']]['prev_cat'] = $r1['p'.$this->level];
					else $this->table[$bid][$r1['category_id']]['prev_cat'] = $r1['p2'];
				}
			}
		}
		$con->sql_freeresult($q2);
		$con_multi->close_connection();
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sell_thru_by_category_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Voucher Activation Report To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}
	
    function generate_report(){
		global $con, $smarty;

		$this->table = array();
		if(!$this->level) $this->level = 1;
		if($this->level != 1) $this->prev_level = $this->level-1;
		else $this->prev_level = $this->level;
		
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}
		//print_r($this->table);
		
		$this->report_title[] = "Date From ".strtoupper($this->date_from)." to ".strtoupper($this->date_to);

		if($this->type) $this->report_title[] = "Type: ".$this->type_list[$this->type];
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('level', $this->level);
	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['date_from']){
			if($_REQUEST['date_to']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['date_to'])));
			else{
				$_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['date_to'] || strtotime($_REQUEST['date_from']) > strtotime($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['date_from'])));
		}

		// check if the date is more than 1 month
		$end_date =date("Y-m-d",strtotime("+1 year",strtotime($_REQUEST['date_from'])));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;

		$this->date_from = $_REQUEST['date_from'];
		$this->sb_year = date("Y", strtotime($_REQUEST['date_from']));
		$this->sb_date = date("Y-m-d", strtotime("-1 day", strtotime($_REQUEST['date_from'])));
		$this->date_to = $_REQUEST['date_to'];
		$this->view_type = $_REQUEST['view_type'];
		$this->category_id = $_REQUEST['category_id'];
		$this->cid = $_REQUEST['cid'];
		$this->all_category = $_REQUEST['all_category'];

		if(!$this->category_id && !$this->all_category && !$this->cid){
			$err[] = "Please select a category.";
			$smarty->assign("err", $err);
			$this->display("mizisport/report.sell_thru_by_category.tpl");
			exit;
		}
		
		$this->load_branch_list();

		$this->filter = $this->sb_filter = array();
		$this->filter[] = "date(csc.date) between ".ms($this->date_from)." and ".ms($this->date_to);
		
		//if($this->type) $this->filter[] = "mp.type = ".ms($this->type);
		if($this->category_id && !$this->all_category){
			$cat_info = get_category_info($this->category_id);
			$this->filter[] = "cc.p".mi($cat_info['level'])." = ".mi($this->category_id);
			$this->sb_filter[] = "cc.p".mi($cat_info['level'])." = ".mi($this->category_id);
		}
		//parent::process_form();
	}
	
	function load_branch_list(){
		global $sessioninfo;

		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			$bgid = explode(",",$_REQUEST['branch_id']);
			if($bgid[1] || $branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$bgid[1]] as $bid=>$b){
						$this->branch_id_list[] = $bid;
					}
				}
				$this->report_title[] = "Branch Group: ".$this->branches_group['header'][$bgid[1]]['code'];
			}elseif($branch_id){  // single branch selected
			    $this->branch_id_list[] = $branch_id;
                $this->report_title[] = "Branch: ".get_branch_code($branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->branch_id_list[] = $bid;
				}
				$this->report_title[] = "Branch: All";
			}
		}else{  // Branches mode
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}
	}

	function load_branch_group($id=0){
		global $con,$smarty;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con->sql_query("select * from branch_group $where",false,false);
		if($con->sql_numrows()<=0) return;
		while($r = $con->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();		

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con->sql_fetchassoc()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con->sql_freeresult();
		
		$this->branch_group = $branch_group;

		$smarty->assign('branch_group',$branch_group);
		$smarty->assign('branches_group',$branch_group);
		return $branch_group;
	}
	
	function ajax_show_details(){
		global $smarty;
		
		$this->level = $_REQUEST['level'];
		$this->process_form();
		$this->filter[] = "cc.p".mi($this->level-1)." = ".mi($_REQUEST['cid']);
		$this->generate_report();

		$smarty->assign("cid", $_REQUEST['cid']);
		$smarty->assign("bid", $_REQUEST['branch_id']);
		foreach($this->table as $bid=>$c_list){
			foreach($c_list as $item){
				$smarty->assign("item", $item);
				$smarty->display("mizisport/report.sell_thru_by_category.row.tpl");
			}
		}
	}
}

$SELL_THRU_BY_CATEGORY = new SELL_THRU_BY_CATEGORY('Sell Thru by Category Report');
?>
