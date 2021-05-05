<?php/*1/17/2011 2:52:16 PM Alex- change use report_server5/31/2011 1:03:30 PM Justin- Rename the "grn_batch_items" into "sku_batch_items".6/8/2011 5:56:21 PM  Justin- Fixed the bugs where wrong calculating on remaining days.6/20/2011 11:14:30 AM Justin- Fixed the wrong calculation of batch item remain/expired days.6/22/2011 5:07:11 PM Justin- Added date filter.7/6/2011 12:23:07 PM Andy- Change split() to use explode()4/28/2014 11:51 AM Fithri- add option to filter out inactive SKU items6/18/2014 9:53 AM Fithri- report privilege & config checking is set to be the same as in menu (menu.tpl)2/21/2020 1:32 PM William- Enhanced to change connection "$con" to use report server connection "$con_multi".*/include("include/common.php");include("include/class.report.php");if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");if (!$config['enable_sn_bn']) js_redirect($LANG['REPORT_CONFIG_NOT_FOUND'], "/index.php");if (!$_REQUEST['date']) $_REQUEST['date'] = date('Y-m-d');// set the stock age$status_desc = array(0 => '-- All --', 1 => 'Confirmed', 2 => 'Unconfirmed');$status_val = array(0 => 0,  1 => 1,  2 => 2);$smarty->assign('status_desc', $status_desc);$smarty->assign('status_val', $status_val);// set report fixed row display$smarty->assign('report_row', 25);class Batch_No extends Report{	private function run_report($bid,$tbl_name=''){        global $con, $smarty,$sessioninfo,$con_multi;		$vendors = $this->vendors;		//$use_grn = $this->use_grn;        $category_id = $this->category_id;        $date = $this->date;        $view_type = $this->view_type;        $days = mi($this->days);				$times = strtotime($date);				if($bid) $filter[] = "sbi.branch_id in (".$bid.")";		if(isset($vendors) && $vendors != '') $filter[] = "grn.vendor_id = ".$vendors;		if($category_id) $filter[] = "sku.category_id = ".mi($category_id);				if($view_type == 1){ // more than how many days			$defined_date = date("Y-m-d",strtotime("-$days days", $times));			$filter[] = "sbi.expired_date <= ".ms($defined_date);		}elseif($view_type == 2){ // is after how many days 			$defined_date = date("Y-m-d",strtotime("+$days days", $times));			$filter[] = "sbi.expired_date >= ".ms($defined_date);		}else{ // is within current date to how many days			$defined_date = date("Y-m-d",strtotime("+$days days", $times));			$filter[] = "sbi.expired_date between ".ms($date)." and ".ms($defined_date);		}				$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';		/*$con_multi = new mysql_multi();		if(!$con_multi){	 		die("Error: Fail to connect report server");		}*/		$sql = "select sbi.sku_item_id, si.sku_item_code, si.description, sbi.batch_no, sbi.expired_date,				sbi.qty as batch_qty, sic.qty as sb_qty, si.location, sbi.expired_date				from `sku_batch_items` sbi				left join `sku_items_cost` sic on sic.sku_item_id = sbi.sku_item_id and sic.branch_id = sbi.branch_id				left join `grn` on grn.id = sbi.grn_id and sbi.branch_id = sbi.branch_id				left join `sku_items` si on si.id = sbi.sku_item_id				left join `sku` on sku.id = si.sku_id				where ".join(" and ", $filter)." and grn.batch_status = 1				order by si.sku_item_code";//print "$sql<br /><br />";//xx		$sb = $con_multi->sql_query($sql);		$curr_time = strtotime(date("Y-m-d"));		while($r = $con_multi->sql_fetchrow($sb)){			$this->table[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];			$this->table[$r['sku_item_id']]['description'] = $r['description'];			$this->table[$r['sku_item_id']]['location'] = $r['location'];			$this->table[$r['sku_item_id']]['batch_no'] = $r['batch_no'];			$this->table[$r['sku_item_id']]['expired_date'] = $r['expired_date'];			$this->table[$r['sku_item_id']]['batch_qty'] += $r['batch_qty'];			$this->table[$r['sku_item_id']]['sb_qty'] += $r['sb_qty'];			if($r['expired_date']){				$expired_time = strtotime($r['expired_date']);				if($view_type == 1){					$days_remain = mi(($curr_time-$expired_time)/86400);				}else $days_remain = mi(($expired_time-$curr_time)/86400);				if($days_remain == 0) $days_remain = "Today";				$this->table[$r['sku_item_id']]['days_remain'] = $days_remain;			}		}		$con_multi->sql_freeresult($sb);		//$con_multi->close_connection();	}    function generate_report(){		global $con, $smarty, $con_multi;		$branch_group = $this->branch_group;		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);			$get_branch_group_code = $con_multi->sql_query("select branch_group_items.branch_id, branch_group.code													  from branch_group 													  join branch_group_items on branch_group.id = branch_group_items.branch_group_id 													  where id = $bg_id");			while($bg = $con_multi->sql_fetchrow($get_branch_group_code)){				$bid[] = $bg['branch_id'];				$bg_code = $bg['code'];			}			$con_multi->sql_freeresult($get_branch_group_code);			$report_title[] = "Branch Group: ".$bg_code;			$this->run_report(join(",",$bid));		}else{            $bid  = get_request_branch(true);			if (BRANCH_CODE != 'HQ'){	// is a particular branch	            $this->run_report($bid);	            $branch_code = BRANCH_CODE;			}else{	// from HQ user				if($bid==0){	// is all the branches	                $report_title[] = 'Branch: All';					$this->run_report('');				}else{	// is a particular branch		            $this->run_report($bid);					$branch_code = get_branch_code($bid);					$report_title[] = "Branch: ".$branch_code;				}			}		}		if($this->status == 2) $status_type = "Unconfirmed";		elseif($this->status == 1) $status_type = "Confirmed";		else $status_type = "All";		if($this->view_type == 1) $view_type = "More Than";		elseif($this->view_type == 2) $view_type = "After";		else $view_type = "Within";		if($this->days == 0) $days = "or Equal Today";		else $days = $this->days." Day(s)";		$report_title[] = "Status: Expired ".$view_type." ".$days;		        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));		$smarty->assign('table', $this->table);	}		function process_form(){	    global $con, $smarty;        $this->bid  = get_request_branch();        $this->vendors = $_REQUEST['vendors'];        //$this->use_grn = $_REQUEST['use_grn'];        $this->category_id = $_REQUEST['category_id'];        $this->date = $_REQUEST['date'];	    $this->view_type = $_REQUEST['view_type'];        $this->days = $_REQUEST['days'];		// call parent		parent::process_form();	}}$Batch_No = new Batch_No('Batch No Report');?>