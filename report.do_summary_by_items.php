<?php
/**/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
class DO_SUMMARY_BY_ITEMS extends Module{
	public function __construct($title){
		global $con_multi, $appCore, $smarty, $sessioninfo, $config;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		// date
		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month +1 day"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		
		//branch
		$branch =array();
		$con_multi->sql_query("select id, code, report_prefix from branch where active=1 order by sequence, code");
		while($r=$con_multi->sql_fetchassoc()){
			$branch[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("branch", $branch);
		$this->branch = $branch;
		
		//user
		$users = array();
		if($sessioninfo['id'] != 1)  $user_filter = "where (user.is_arms_user=0 or do.user_id=".mi($sessioninfo['id']).")";
		$con_multi->sql_query("select distinct(user.id) as id, user.u from do left join user on user_id = user.id $user_filter group by id");
		while($r2=$con_multi->sql_fetchassoc()){
			$users[$r2['id']] = $r2;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("users", $users);
		$this->users = $users;
		
		//debtor
		$debtors = array();
		$con_multi->sql_query("select * from debtor where active=1 order by code");
		while($r3=$con_multi->sql_fetchassoc()){
			$debtors[$r3['id']] = $r3;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("debtors", $debtors);
		$this->debtors = $debtors;
		
		//sales agent
		$sales_agent_list =array();
		if(($config['do_credit_sales_show_sales_person_name'] || $config['do_cash_sales_show_sales_person_name']) && !$config['masterfile_enable_sa']){
			$qry4 = "select distinct(sales_person_name) as sales_person_name, sales_person_name as id from do where sales_person_name<>'' and sales_person_name is not null order by sales_person_name";
		}else{
			$qry4 = "select concat(code, ' - ', name) as sales_person_name, id from sa where active=1 order by code, name";
		}
		$con_multi->sql_query($qry4);
		while($r4=$con_multi->sql_fetchassoc()){
			$sales_agent_list[$r4['id']] = $r4;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('sales_agent_list', $sales_agent_list);
		$this->sales_agent_list = $sales_agent_list;
		
		
		$smarty->assign("form", $_REQUEST);
		parent::__construct($title);
	}
	
	function _default(){
		$this->display();
		exit;
	}
	
	public function show_report(){
        global $smarty, $sessioninfo, $con_multi, $config;
		
		$form = $_REQUEST;
		$report_header = $where = $err = array();
		$do_type_list = array("transfer"=>"Transfer", "open"=>"Cash Sales", "credit_sales"=>"Credit Sales");
		$do_status_list = array(1=>"Draft / Waiting for Approval", 2=>"Approved", 3=>"Checkout");
		
		$branch_id = mi($form['branch_id']);
		$date_from = $form['date_from'];
		$date_to = $form['date_to'];
		$user_id = mi($form['user_id']);
		$deliver_to = mi($form['deliver_to']);
		$status = $form['status'];
		$do_type = $form['do_type'];
		$paid_status = $form['paid_status'];
		$sales_person_name = $form['sales_person_name'];
		$debtor_id = mi($form['debtor_id']);
		
		//branch
		if($branch_id){
			$where[] = "do.branch_id =".$branch_id;
			if (BRANCH_CODE == 'HQ') $report_header[] = "Branch: ".$this->branch[$branch_id]['code'];;
		}else{
			$report_header[] = "Branch: All";
		}
		
		//date
		if($date_from) $report_header[] = "Date From: $date_from";
		else   $err[] = "Invalid Date From";
		
		if($date_to)   $report_header[] = "Date To: $date_to";
		else   $err[] = "Invalid Date To";
		
		//user
		if($user_id){
			$where[] = 'do.user_id = '.$user_id;
			$report_header[] = "By User: ".$this->users[$user_id]['u'];
		}else{
			$report_header[] = "By User: All";
		}
		
		//deliver to
		if($deliver_to){
			$where[] = "(do.do_branch_id = ".$deliver_to." or do.deliver_branch like '%\"".$deliver_to."\"%')";
			$report_header[] = "Delivery To: ".$this->branch[$deliver_to]['code'];;
		}else{
			$report_header[] = "Delivery To: All";
		}
		
		//do type
		if($do_type){
			$where[] = "do.do_type =".ms($do_type);
			$report_header[] = "DO Type: ".$do_type_list[$do_type];
		}else{
			$report_header[] = "DO Type: All";
		}
		
		//status
		if(!$status){
			$where[] = "do.status in (0,1)";
			$report_header[] = "Status: All";
		}else{
			switch ($status){
				case 1: // show saved DO
					$where[] = "do.status in (0) and do.approved=0 and do.checkout=0";
					break;
				case 2: // show approved
					$where[] = "do.approved=1 and do.checkout=0";
					break;
				case 3: // show checkout
					$where[] = "do.approved=1 and do.checkout=1 ";
					break;
			}
			$report_header[] = "Status: ".$do_status_list[$status];
		}
		
		//paid status
		if($do_type == 'open'){
			if($paid_status == 'all'){
				$report_header[] = "Paid Status: All";
			}else{
				if($paid_status =='0'){
					$where[] = "(do.paid = ".ms($paid_status)." or do.paid is Null)" ;
					$report_header[] = "Paid Status: Unpaid";
				}else{
					$where[] = "do.paid = ".ms($paid_status);
					$report_header[] = "Paid Status: Paid";
				}
			}
		}
		
		//sales agent
		if($do_type && $do_type!= 'transfer'){
			if($config['masterfile_enable_sa'] || (($do_type == 'open' && $config['do_cash_sales_show_sales_person_name']) || ($do_type == 'credit_sales' && $config['do_credit_sales_show_sales_person_name']) )){
				if($sales_person_name){
					if($config['masterfile_enable_sa']){
						$sa_id = $sales_person_name;
						$where[] = "((do.mst_sa != '' and do.mst_sa is not null and do.mst_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%') or (di.dtl_sa != '' and di.dtl_sa is not null and di.dtl_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%'))";
					}else{
						$where[] = "do.sales_person_name=".ms($sales_person_name);
					}
					$report_header[] = "Sales Agent: ".$this->sales_agent_list[$sales_person_name]['sales_person_name'];
				}else{
					$report_header[] = "Sales Agent: All";
				}
			}
		}
		
		//debtor
		if($do_type =='credit_sales'){
			if($debtor_id){
				$where[] = "do.debtor_id=".$debtor_id;
				$report_header[] = "Debtor: ".$this->debtors[$debtor_id]['code']." - ".$this->debtors[$debtor_id]['description'];
			}else{
				$report_header[] = "Debtor: All";
			}
		}
		
		//error
		if($err){
			$smarty->assign("err", $err);
			$this->_default();
		}
		
		$where[] = "do.active=1";
		$where[] = "(do.do_date between ".ms($date_from)." and ".ms($date_to).")";
		if ($where)  $where = "where " . implode(" and ", $where);
		
		
		$table = array();
		$q1 = $con_multi->sql_query($qry="select do.id, do.do_date as do_date, do.do_no, do.status, do.do_type, do.approved, do.checkout, do.branch_id,
			do.do_branch_id, do.deliver_branch, si.id as sku_item_id, si.sku_item_code as sku_item_code, si.link_code, si.mcode, si.artno, 
			si.description as description, di.ctn, di.pcs, di.selling_price as selling_price, di.cost as cost, di.cost_price as cost_price, 
			uom.code, uom.fraction as uom_fraction, di.ctn_allocation, di.pcs_allocation, di.selling_price_allocation, di.id as do_items_id,
			(di.cost * (di.pcs + (di.ctn * uom.fraction))) as total_cost,
			((di.pcs +(di.ctn * uom.fraction))* (di.cost_price/uom.fraction)) as total_amount, 
			(di.selling_price * (di.pcs + (di.ctn * uom.fraction))) as total_selling
			from do
			left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
			left join sku_items si on si.id = di.sku_item_id
			left join branch on do.branch_id = branch.id
			left join uom on uom.id = di.uom_id
			$where
			group by do.id, do.branch_id, di.sku_item_id 
			order by do_date, do_items_id");
		//echo $qry;
		while($r= $con_multi->sql_fetchassoc($q1)){
			$do_id = mi($r['id']);
			$do_items_id = mi($r['do_items_id']);
			
			if($do_items_id){
				$do_branch_id = mi($r['do_branch_id']);
				$deliver_branch = unserialize($r['deliver_branch']);
				
				//multi deliver branch
				if(!$do_branch_id && $deliver_branch){
					$ctn_allocation = unserialize($r['ctn_allocation']);
					$pcs_allocation = unserialize($r['pcs_allocation']);
					$selling_price_allocation = unserialize($r['selling_price_allocation']);
					
					if($deliver_to){  //filter by single deliver_branch
						$r['ctn'] = $ctn_allocation[$deliver_to];
						$r['pcs'] = $pcs_allocation[$deliver_to];
						$r['selling_price'] = $selling_price_allocation[$deliver_to] ? mf($selling_price_allocation[$deliver_to]) : mf($r['selling_price']);
						if(($r['pcs'] + ($r['ctn'] * $r['uom_fraction'])) != 0){
							$r['total_selling'] = mf($r['selling_price'] * ($r['pcs'] + ($r['ctn'] * $r['uom_fraction'])));
							$r['total_cost'] = mf($r['cost'] * ($r['pcs'] + ($r['ctn'] * $r['uom_fraction'])));
							$r['total_amount'] = mf(($r['pcs'] + ($r['ctn'] * $r['uom_fraction'])) * ($r['cost_price'] / $r['uom_fraction']));
						}
					}else{   //filter by all deliver_branch
						foreach($deliver_branch as $k=>$bid){
							$ctn = $ctn_allocation[$bid];
							$pcs = $pcs_allocation[$bid];
							$r['ctn'] += $ctn;
							$r['pcs'] += $pcs;
							$selling_price = $selling_price_allocation[$bid] ? mf($selling_price_allocation[$bid]) : mf($r['selling_price']);
							if(($pcs + ($ctn * $r['uom_fraction'])) != 0){
								$r['total_selling'] += mf($selling_price * ($pcs + ($ctn * $r['uom_fraction'])));
								$r['total_cost'] += mf($r['cost'] * ($pcs + ($ctn * $r['uom_fraction'])));
								$r['total_amount'] += mf(($pcs + ($ctn * $r['uom_fraction'])) * ($r['cost_price'] / $r['uom_fraction']));
							}
						}
						if(($r['pcs'] + ($r['ctn'] * $r['uom_fraction'])) != 0){
							$r['selling_price'] = mf($r['total_selling']/($r['pcs'] + ($r['ctn'] * $r['uom_fraction'])));
						}
					}
				}
				
				$r['do_type'] = $do_type_list[$r['do_type']];
				if($status){
					$r['status'] = $do_status_list[$status];
				}else{
					if($r['approved'] == 1){
						if($r['checkout'] == 1)  $r['status'] = "Checkout";
						else  $r['status'] = "Approved";
					}else{
						if($r['status'] == 0 || $r['status'] == 1)  $r['status'] = "Draft / Waiting for Approval";
					}
				}
				
				$report_prefix = $this->branch[$r['branch_id']]['report_prefix'];
				$formatted=sprintf("%05d",$do_id);
				if(!$r['do_no']){
					if($r['approved']){
						$r['do_no'] = $report_prefix.$formatted."(DD)";
					}else{
						if($r['status'] == 1) $r['do_no'] = $report_prefix.$formatted."(PD)";
						else $r['do_no'] = $report_prefix.$formatted;
					}
				}
				$table[$r['branch_id']][$do_id]['do_items'][] = $r;
			}
		}
		$con_multi->sql_freeresult($q1);
		
		$smarty->assign("report_header",implode('&nbsp;&nbsp;&nbsp;&nbsp;', $report_header));
		$smarty->assign("table", $table);
		$this->display();
	}
	
	function output_excel(){
		global $smarty, $sessioninfo;
		
		include("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=do_summary_by_items'.time().'.xls');
		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();

		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Do Summary By Items To Excel");
		exit;
	}
}
$DO_SUMMARY_BY_ITEMS = new DO_SUMMARY_BY_ITEMS('DO Summary By Items');
?>