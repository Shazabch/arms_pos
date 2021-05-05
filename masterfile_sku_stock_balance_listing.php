<?php
/*
9/28/2016 11:40 AM Qiu Ying
- Fix date error

9/28/2016 16:18 Qiu Ying
- Add log when download report

10/10/2016 10:31 Qiu Ying
- Fix record show wrong when select category 

2/20/2017 1:40 PM Andy
- Change to have sample data in php class.
- Enhance to able to load multiple branch stock.
- Fixed Active/Inactive filter bug.

7/27/2018 5:19 PM Andy
- Fixed a bug when branch code contain character "/" will caused system to export empty data.

2/25/2020 2:40 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

class SKU_Stock_Balance_Listing extends Module{
	var $file_folder = "tmp/SKU_Stock_Balance_Listing";
	var $sample_header = array("Branch Code","ARMS Code","Mcode","Art No","Description","Stock Balance");
	var $sample_list = array(array('HQ','280187210000','4902506071002','PL75A','FERNLEAF',10));
	
	function __construct($title)
	{
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(!is_dir($this->file_folder))	check_and_create_dir($this->file_folder);
		
		$files = scandir($this->file_folder);
		for($i=2; $i<count($files); $i++) {
			if(strtotime("-1 week") > filemtime($this->file_folder."/".$files[$i])) {
				unlink($this->file_folder."/".$files[$i]);
			}
		}
		
		//branches
		$con_multi->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		} 
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		//vendors
		$vendors = array();
		$con_multi->sql_query("select id,description from vendor where active=1 order by description");
		while ($r = $con_multi->sql_fetchassoc()) $vendors[$r['id']] =$r;
		$con_multi->sql_freeresult();
		$smarty->assign('vendors',$vendors);
		$this->vendors = $vendors;
		
		// sku type
		$con_multi->sql_query("select * from sku_type");
		$smarty->assign("sku_type", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
		// brand
		$con_multi->sql_query("select id, description from brand where active=1 order by description");
		$smarty->assign("brand", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
		$this->load_branch_group();
		
		$smarty->assign('sample_header', $this->sample_header);
		$smarty->assign('sample_list', $this->sample_list);
		parent::__construct($title);
	}

	function _default()
	{
		if(isset($_REQUEST['download_report'])){
			$this->download_report();
		}
		$this->display('masterfile_sku_stock_balance_listing.tpl');
	}

	private function load_branch_group(){
		global $con,$smarty,$con_multi;
		
	    if($this->branch_group)  return $this->branch_group;
		$this->branch_group = array();
		
		// load header
		$con_multi->sql_query("select * from branch_group");
		while($r = $con_multi->sql_fetchrow()){
            $this->branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();		

		// load items
		$con_multi->sql_query("select bgi.*,branch.code,branch.description 
			from branch_group_items bgi 
			left join branch on bgi.branch_id=branch.id 
			where branch.active=1 order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc()){
	        $this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $this->branch_group['have_group'][$r['branch_id']] = $r;
		}
		$con_multi->sql_freeresult();

		//print_r($this->branch_group);
		$smarty->assign('branch_group',$this->branch_group);
	}
	
	function download_report()
	{
		global $con, $smarty, $config, $sessioninfo, $con_multi;
		
		$form = $_REQUEST;
		//print_r($form);exit;
		$err = array();
		$branch_id_list = $form['branch_id_list'];
		$category_id = $form['category_id'];
		
		if(!$branch_id_list || !is_array($branch_id_list)){
			$err[] = "Please select at least one branch.";
		}
		if(!$form['all_category']){
			if(!$category_id){
				$err[] = "Please select Category.";
			}
		}
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$filter = array();
		
		if ($form['vendor_id'])	$filter[] = "sku.vendor_id = ". mi($form['vendor_id']);
		if ($form['brand_id'])	$filter[] = "sku.brand_id = ". mi($form['brand_id']);
		if ($form['sku_type'])	$filter[] = "sku.sku_type = ". ms($form['sku_type']);
		if (is_numeric($form['active']))	$filter[] = "si.active = ". mi($form['active']);
		
		if($category_id){
			$con_multi->sql_query("select * from category where id=$category_id") or die(mysql_error());
			$selected_category = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();

			$c_level = $selected_category['level'];

			$filter[] = "cc.p".mi($selected_category['level'])."=$category_id";
		}
		
		if ($filter){
			$filter_str = join(' and ',$filter);
			$where_str = "where ";
		}
		
		$got_data = false;
		$times = time();
		$extra_data = array();
		$file_num = 0;
		$file_name_prefix = "Stock_Balance_Listing_".$times."_";
		
		// loop branch
		foreach($branch_id_list as $bid){
			$branch_code = $this->branches[$bid]['code'];
			
			// get total record count
			$sql = "select count(*) as total_record
			from sku_items si
			left join sku on sku.id = si.sku_id
			left join sku_items_cost sic on si.id = sic.sku_item_id and sic.branch_id=$bid
			left join category_cache cc on cc.category_id = sku.category_id
			$where_str $filter_str";
			
			$con_multi->sql_query($sql);
			$total_record = $con_multi->sql_fetchfield("total_record");
			$con_multi->sql_freeresult();
			
			if($total_record<=0)	continue;
			
			$got_data = true;
			$num = ceil($total_record / 30000);
			$tmp_extra_data = array();
			
			// Get Last GRN Date
			$sql_grn = "select max(rcv_date) as latest_date
			from grn
			left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
			where grn.active = 1 and grn.status = 1 and grn.approved = 1 and grn.branch_id = $bid";
			$con_multi->sql_query($sql_grn);
			$last_grn = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			$last_grn_date = $last_grn["latest_date"] ? $last_grn["latest_date"] : '-';
			$tmp_extra_data[] = array("Last GRN Date", $last_grn_date);
			
			// Get Last GRA Date
			$sql_gra = "select date(max(return_timestamp)) as latest_date 
			from gra
			where status = 0 and returned = 1 and approved = 1 and branch_id = $bid";
			$con_multi->sql_query($sql_gra);
			$last_gra = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			$last_gra_date = $last_gra["latest_date"] ? $last_gra["latest_date"] : '-';
			$tmp_extra_data[] = array("Last GRA Date", $last_gra_date);
			
			// Get Last POS date
			$sql_pos = "select max(date) as latest_date from sku_items_sales_cache_b$bid";
			$con_multi->sql_query($sql_pos);
			$last_pos = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			$last_pos_date = $last_pos["latest_date"] ? $last_pos["latest_date"] : '-';
			$tmp_extra_data[] = array("Last Finalised POS Date", $last_pos_date);
			
			// Get Last DO Date
			$sql_do = "select max(do_date) as latest_date from do 
			where status = 1 and approved = 1 and active = 1 and checkout = 1 and branch_id = $bid";
			$con_multi->sql_query($sql_do);
			$last_do = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			$last_do_date = $last_do["latest_date"] ? $last_do["latest_date"] : '-';
			$tmp_extra_data[] = array("Last DO Date", $last_do_date);
			
			// Get Last Adjustment Date
			$sql_adj = "select max(adjustment_date) as latest_date from adjustment
			where status = 1 and approved = 1 and active = 1 and branch_id = $bid";
			$con_multi->sql_query($sql_adj);
			$last_adj = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			
			$last_adj_date = $last_adj["latest_date"] ? $last_adj["latest_date"] : '-';
			$tmp_extra_data[] = array("Last Adjustment Date", $last_adj_date);
			
			// store into extra data
			$extra_data[$bid] = $tmp_extra_data;
			
			// Start Get SKU
			$last_si_id = 0;
			$filter_str2 = "";
			if ($filter_str) $filter_str2 = " and " . $filter_str;
			for ($i = 0; $i < $num; $i++){
				$q1 = $con_multi->sql_query("select si.sku_item_code, si.mcode, si.artno, si.description, sic.qty as stock_balance, si.id as sid
				from sku_items si
				left join sku on sku.id = si.sku_id
				left join sku_items_cost sic on si.id = sic.sku_item_id and sic.branch_id=$bid
				left join category_cache cc on cc.category_id=sku.category_id
				where si.id > $last_si_id $filter_str2
				order by si.id
				limit 30000");
							
				$file_num++;
				$file_name = $file_name_prefix.$file_num."_".str_replace('/', '', $branch_code).".csv";
				$fp = fopen($this->file_folder. "/".$file_name, 'w');
				fputcsv($fp, array_values($this->sample_header));
				
				while($r = $con_multi->sql_fetchassoc($q1)){
					$last_si_id = $r["sid"];
					fputcsv($fp, array($branch_code, $r["sku_item_code"], $r["mcode"], $r["artno"], $r["description"], $r["stock_balance"]));
				}
				$con_multi->sql_freeresult($q1);
				fclose($fp);
				chmod($this->file_folder. "/".$file_name, 0777);
			}
		}
		
		if($got_data){
			// add the last file
			$file_num++;
			$file_name = $file_name_prefix.$file_num.".csv";
			$fp = fopen($this->file_folder. "/".$file_name, 'a');
			fputcsv($fp, array("",""));
			fputcsv($fp, array("Download Date", date("Y-m-d H:i:s A")));
			
			foreach($extra_data as $bid =>$tmp_extra_data){
				fputcsv($fp, array($this->branches[$bid]['code']));
				foreach($tmp_extra_data as $item){
					fputcsv($fp, $item);
				}
			}
			fclose($fp);
			
			
			$parent_zip = "Stock_Balance_Listing_".$times;
			exec("cd " . $this->file_folder."; zip -9 $parent_zip.zip $file_name_prefix*.csv");
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=$parent_zip.zip");
			readfile($this->file_folder."/$parent_zip.zip");
			log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export SKU Stock Balance Listing Report to ZIP File Format");
			exit;
		}else{
			$smarty->assign("data", "No Result.");
		}
	}
}
$SKU_Stock_Balance_Listing = new SKU_Stock_Balance_Listing('SKU Stock Balance Listing (Download)');
