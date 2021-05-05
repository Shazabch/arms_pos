<?php
/*
9/19/2017 5:47 PM Justin
- Enhanced to highlight the field if stock balance is less than min qty.
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

$smarty->assign("sn_rows", 20);

class BROKEN_SIZE_CLR_BY_BRANCH_REPORT extends Module{
   function __construct($title){
		global $con, $smarty;

		$this->init_selection();

    	parent::__construct($title);
    }
	
	function _default(){
		$this->display();
		exit;
	}
	
	private function init_selection(){
		global $con, $smarty;

		// load branches
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();
		
		// age group list
		$this->age_group_list = array(30 => "30 Days", 60 => "60 Days", 90 => "90 Days", 120 => "120 Days");
		$smarty->assign("age_group_list", $this->age_group_list);
		
		// brand
		$this->all_brand_filter = "brand.active=1";
		$con->sql_query("select id,code,description from brand where ".$this->all_brand_filter." order by description");
		while($r = $con->sql_fetchassoc()){
			$this->brand_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('brand_list', $this->brand_list);
		
		// brand group
		$con->sql_query("select * from brgroup order by description");
		while($r = $con->sql_fetchassoc()){
			$this->brand_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		$con->sql_query("select * from brand_brgroup");
		while($r = $con->sql_fetchassoc()){
			if(!$this->brand_list[$r['brand_id']])	continue;
			
			$this->brand_group['items'][$r['brgroup_id']][$r['brand_id']] = $r;
			$this->brand_group['have_group'][$r['brand_id']] = $r['brand_id'];
		}
		$con->sql_freeresult();
		$smarty->assign('brand_group', $this->brand_group);
		
		$this->clr_list = array(0=>"RED");
		//$this->clr_list = get_matrix_color();
		$this->size_list = get_matrix_size();
		$smarty->assign("size_list", $this->size_list);
	}

	function show_report(){
		$this->process_form();
		$this->generate_report();
		$this->display();
	}

	private function run_report($bid){
        global $con, $smarty,$sessioninfo;

		/*$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$sql = "select pisnh.*, si.sku_item_code, si.description as si_description, b.code as current_branch, u.u as user
				from `pos_items_sn_history` pisnh
				left join branch b on b.id = pisnh.branch_id
				left join sku_items si on si.id = pisnh.sku_item_id
				left join sku on sku.id = si.sku_id
				left join category c on c.id = sku.category_id
				left join category_cache cc on cc.category_id = c.id
				left join user u on u.id = pisnh.user_id
				where ".join(" and ", $this->filters)." and pisnh.branch_id = ".mi($bid)."
				order by pisnh.serial_no, pisnh.added";

		$q1 = $con_multi->sql_query($sql);//print "$sql<br /><br />";//xx

		while($r = $con_multi->sql_fetchassoc($q1)){			
			if($r['status'] == "Sold"){
				$q2 = $con->sql_query("select nric, name, address, contact_no, email, warranty_expired from sn_info where pos_id = ".mi($r['pos_id'])." and branch_id = ".mi($r['branch_id'])." and item_id = ".mi($r['item_id'])." and date = ".ms($r['date'])." and sku_item_id = ".mi($r['sku_item_id'])." and serial_no = ".ms($r['serial_no']));
				
				if($con->sql_numrows($q2) > 0){
					$sn_info = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);
					
					$r = array_merge($r, $sn_info);
				}
			}
			$this->table[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();*/
		
		$q1 = $con->sql_query("select * from sku_items where active=1 and is_parent=1 order by sku_item_code desc limit 10");
		
		while($r = $con->sql_fetchassoc($q1)){
			if($this->clr_list && $this->size_list){
				foreach($this->clr_list as $arr1=>$clr){
					foreach($this->size_list as $arr2=>$size){
						$sb_qty = rand(0,3);
						$sales_qty = rand(1,10);
						$stock_age_days = rand(1,30);
						$this->table[$bid][$r['id']]['sku_item_code'] = $r['sku_item_code'];
						$this->table[$bid][$r['id']]['mcode'] = $r['mcode'];
						$this->table[$bid][$r['id']]['description'] = $r['description'];
						$this->table[$bid][$r['id']]['stock_age_days'] = $stock_age_days;
						$this->table[$bid][$r['id']]['sb_qty'][$clr][$size] = $sb_qty;
						$this->table[$bid][$r['id']]['less_than_min_qty'][$clr][$size] = rand(0,1);
						$this->table[$bid][$r['id']]['sales_qty'][$clr][$size] = $sales_qty;
					}
				}
			}
		}
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sn_status_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Serial No Status To Excel($filename)");
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
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		if($this->serial_no) $this->report_title[] = "Serial No: ".$this->serial_no;
		if($this->age_group) $status = $this->age_group;
		else $status = "All";

		$this->report_title[] = "Age Group: ".$this->age_group_list[$this->age_group];
		
		if($this->brand_id) $brand_desc = $this->brand_list[$this->brand_id]['description'];
		else $brand_desc = "All";
		
		$this->report_title[] = "Brand: ".$brand_desc;
		
		if($this->include_nrr) $include_nrr = "Yes";
		else $include_nrr = "No";

		$this->report_title[] = "Including Not Reorder Require: ".$include_nrr;
		
		if(!$this->all_category && $this->category_id){
			$q1 = $con->sql_query("select * from category where id = ".mi($this->category_id));
			$cat_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$cat_desc = $cat_info['description'];
		}else $cat_desc = "All";
		
		$this->report_title[] = "Category: ".$cat_desc;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('category', $this->category);
		$smarty->assign('bid_list', $this->branch_id_list);

	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo;

		$this->category_id = $_REQUEST['category_id'];
		$this->all_category = $_REQUEST['all_category'];
		$this->age_group = $_REQUEST['age_group'];
		$this->brand_id = $_REQUEST['brand_id'];
		$this->include_nrr = $_REQUEST['include_nrr'];

		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			$bgid = explode(",",$_REQUEST['branch_id']);
			if($bgid[1] || $branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$bgid[1]] as $bid=>$b){
						$this->branch_id_list[$bid] = $bid;
					}
				}
				$this->report_title[] = "Branch Group: ".$this->branches_group['header'][$bgid[1]]['code'];
			}elseif($branch_id){  // single branch selected
			    $this->branch_id_list[$branch_id] = $branch_id;
                $this->report_title[] = "Branch: ".get_branch_code($branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->branch_id_list[$bid] = $bid;
				}
				$this->report_title[] = "Branch: All";
			}
		}else{  // Branches mode
            //$branch_id = mi($sessioninfo['branch_id']);
            $this->branch_id_list[$sessioninfo['branch_id']] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		if(!$this->all_category && !$this->category_id){
			$error[] = "Please select a category.";
			$smarty->assign("err", $error);
			$this->display();
			exit;
		}
		
		// construct filters
		$this->filters = array();		
		
		// age group filter, to be continued...
		
		if(!$this->all_category && $this->category_id){
			$q1 = $con->sql_query("select level,description from category where id = ".mi($this->category_id));
			$lv = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$level = $lv['level'];
            $this->filters[] = $this->category_id ? "cc.p$level = ".mi($this->category_id) : 1 ;
		}
		if($this->brand_id) $this->filters[] = "sku.brand_id = ".mi($this->brand_id);
		if($this->exclude_inactive_sku) $this->filters[] = $this->exclude_inactive_sku ? 'si.active=1' : '1';
		
		//parent::process_form();
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
		$q1 = $con->sql_query("select * from branch_group $where",false,false);
		
		if($con->sql_numrows()<=0) return;
		
		while($r = $con->sql_fetchassoc($q1)){
            $branch_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		

		// load items
		$q1 = $con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		
		while($r = $con->sql_fetchassoc($q1)){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con->sql_freeresult($q1);
		
		$this->branch_group = $branch_group;
		$smarty->assign('branch_group',$branch_group);
		return $branch_group;
	}
}

$BROKEN_SIZE_CLR_BY_BRANCH_REPORT = new BROKEN_SIZE_CLR_BY_BRANCH_REPORT('Broken Size & Color by Branch Report');
