<?php
/*
4/1/2015 5:47 PM Justin
- Bug fixed on GRA return mysql errors.

4/7/2015 12:10 PM Justin
- Bug fixed on final amount for GRN does not deduct D/N amount.
- Bug fixed on calculation will gone wrong if GRN items having fraction more than 1.

4/9/2015 4:14 PM Justin
- Bug fixed on GRA that always return user as non-gst.

4/13/2015 3:16 PM Justin
- Enhanced to pick up invoice qty for GRN while found got set.
- Bug fixed on GRN not to filter out those negative amount which we overbill vendor.
- Bug fixed on did not update return ctn from GRA while insert DN.

4/14/2015 4:13 PM Justin
- Bug fixed on fraction cause calculation error for GRN.
- Bug fixed on GST information is taken from output_tax not input_tax.

4/15/2015 10:39 AM Justin
- Enhanced to take off the print last page config.

4/17/2015 4:16 PM Justin
- Enhanced to always pickup latest GST indicator.
- Enhanced to pickup more info from GRA.

4/24/2015 11:53 AM Justin
- Enhanced to split out the generate DN feature and put into include file. 

11:30 AM 5/8/2015 Justin
- Enhanced to pickup document date.

8/3/2015 11:38 AM Joo Chia
- Add in new module Debit Note.

8/5/2015 11:43 AM Andy
- Add checking to only allow access from retail.

8/10/2015 2:10 PM Andy
- Enhanced to load branch code when load DN list.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

1/11/2016 3:00 PM Qiu Ying
- Fixed is_last_page not show in document printing

13-Jan-2016 10:20 Edwin
- Change vendor address to follow alternative branch contact.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

9/14/2018 2:48 PM Justin
- Bug fixed on showing wrong unit price when printing D/N from Consignment GRA.

6/4/2019 11:46 AM William
-Pick up report_prefix for GRA,GRN.
*/
include("include/common.php");
include("dnote.include.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

if($config['consignment_modules']){	// this module is not for consignment customer
	header("Location: home.php");
	exit;
}

if($_REQUEST['a']!='generate_dn' && $_REQUEST['a']!='print_dn'){
	if (!privilege('DN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DN', BRANCH_CODE), "/index.php");	
}


class ARMS_DN extends Module{
	
	var $dn_list_size = 20;
	
	function __construct($title){
		global $con, $smarty;
		
		parent::__construct($title);
	}
	
	function _default(){
	    $this->display();
	}
	
	function generate_dn(){
		$from_module = trim($_REQUEST['from_module']);
		switch($from_module){
			case 'gra':
				generate_dn_from_gra($_REQUEST['branch_id'], $_REQUEST['id'], $_REQUEST['need_print']);
				break;
			case 'grn':
				$prms = array();
				$prms['grn_id'] = $_REQUEST['id'];
				$prms['branch_id'] = $_REQUEST['branch_id'];
				$prms['need_print'] = $_REQUEST['need_print'];
				generate_dn_from_grn($prms);
				break;
			default:
				die("Unknown Module: $from_module");
		}
	}
	
	function print_dn(){
		global $con, $smarty, $config;
		
		$f = $_REQUEST;
		
		$filter = "";
		if($f['dn_no']){ // if found got parameter of dn no
			$filter = "dn_no = ".ms($f['dn_no']);
		}elseif($f['id'] && $f['branch_id']){ // if found got parameters of id and branch id
			$filter = "id = ".mi($f['id'])." and branch_id = ".mi($f['branch_id']);
		}else{ // show errors and redirect user to main menu if do not found anything
			js_redirect("D/N not found.", "/index.php");
		}
		
		// trigger dn information
		$q1 = $con->sql_query("select * from dnote where ".$filter);
		$form = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// retrieve SKU Type from GRA
		if($form['ref_table'] == "gra"){
			$q2 = $con->sql_query("select sku_type from gra where id = ".mi($form['ref_id'])." and branch_id = ".mi($form['branch_id']));
			$gra_info = $con->sql_fetchassoc($q2); 
			$con->sql_freeresult($q2);
			$form['sku_type'] = $gra_info['sku_type'];
		}
		
		// get gst list for GRA item not in ARMS
		$q1 = $con->sql_query("select * from gst order by id");
		
		while($r = $con->sql_fetchassoc($q1)){
			$tmp_gst_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		// trigger dn items information
		$q1 = $con->sql_query("select di.*, si.description,si.additional_description
							   from dnote_items di
							   left join sku_items si on si.id = di.sku_item_id
							   where di.dnote_id = ".mi($form['id'])." and di.branch_id = ".mi($form['branch_id'])."
							   order by di.id");
		
		while($r = $con->sql_fetchassoc($q1)){
			$more_info = unserialize($r['more_info']); // get custom info
			$r['doc_no'] = $more_info['doc_no'];
			$r['doc_date'] = $more_info['doc_date'];
			
			// found if it is invalid sku item, get information from custom info
			if(!$r['sku_item_id']){
				$r['code'] = $more_info['code'];
				$r['description'] = $more_info['description'];
			}
			
			// get latest price indicator
			if($r['gst_id']) $r['gst_indicator'] = $tmp_gst_list[$r['gst_id']]['indicator_receipt'];

			$items[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("form", $form);
		
		// load branch info
		$q1 = $con->sql_query("select * from branch where id=".mi($form['branch_id']));
		$smarty->assign("branch", $con->sql_fetchassoc($q1));
		$con->sql_freeresult($q1);
		
		// load vendor info
		$q1 = $con->sql_query("select address from branch_vendor where vendor_id=".mi($form['vendor_id'])." and branch_id=".mi($form['branch_id']));
		$branch_vendor = $con->sql_fetchassoc($q1);
		$smarty->assign("branch_vendor", $branch_vendor);
		$con->sql_freeresult($q1);
		
		$q2 = $con->sql_query("select * from vendor where id=".mi($form['vendor_id']));		
		$smarty->assign("vendor", $con->sql_fetchassoc($q2));
		$con->sql_freeresult($q2);
		
		// calculate total page
		$item_per_page= $config['gst_dn_report_print_item_per_page']?$config['gst_dn_report_print_item_per_page']:15;
		$item_per_lastpage = $item_per_page-5;
		$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);
		
		// start print dnote
		$item_index = -1;
		$item_no = -1;
		$page = 1;
		
		$page_item_list = array();
		$page_item_info = array();
		
		foreach($items as $r){	// loop for each item
			if($item_index+1>=$item_per_page){
				$page++;
				$item_index = -1;
			}
			
			$item_no++;
			$item_index++;
			$r['item_no'] = $item_no;
			
			$page_item_list[$page][$item_index] = $r;	// add item to this page
			
			if($config['sku_enable_additional_description'] && $r['additional_description']){
				$r['additional_description'] = unserialize($r['additional_description']);
				foreach($r['additional_description'] as $desc){
					if($item_index+1>=$item_per_page){
						$page++;
						$item_index = -1;
					}
			
					$item_index++;
					$desc_row = array();
					$desc_row['description'] = $desc;
					$page_item_list[$page][$item_index] = $desc_row;
					$page_item_info[$page][$item_index]['not_item'] = 1;
				}
			}
		}
	
		// fix last page
		if(count($page_item_list[$page]) > $item_per_lastpage){	// last page item too many
			//print "page = $page, count=".count($page_item_list[$page]).", item_per_lastpage = $item_per_lastpage, add last page";
			// add one more page and put no item in last page
			$page++;
			$page_item_list[$page] = array();
		}
		$totalpage = count($page_item_list);
		
		// load custom template
		if($config['gst_dn_print_template']) $tpl = $config['gst_dn_print_template'];
		else $tpl = "dnote.print.tpl";
		
		foreach($page_item_list as $page => $item_list){
			$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
			
			$smarty->assign("PAGE_SIZE", $this_page_num);
			$smarty->assign("is_last_page", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter",$item_list[0]['item_no']);
			$smarty->assign("items", $item_list);
			$smarty->assign("page_item_info", $page_item_info[$page]);
			$smarty->display($tpl);
			$smarty->assign("skip_header",1);
		}
	}
	
	function ajax_list_sel(){
        global $con, $sessioninfo, $smarty, $LANG;
        
        $t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);
		$size = $this->dn_list_size;
		$start = $p*$size;
		
		$filter = array();
		
		switch($t){
			case 1:	// active dn
				$filter[] = "dn.active=1";
				break;
			case 2: // cancelled dn
				$filter[] = "dn.active=0";
				break;
			case 3: // search dn
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				$filter_or[] = "dn.inv_no=".strtoupper(ms($str));
				$filter_or[] = "dn.dn_no=".strtoupper(ms($str));
				$filter_or[] = "concat(dn.ref_table, lpad(dn.ref_id,5,'0'))=".strtolower(ms($str));
				$filter[] = "(".join(' or ',$filter_or).")";
				break;
			default:
				die('Invalid Page');
		}
		
		if(BRANCH_CODE!='HQ')	$filter[] = "dn.branch_id=$sessioninfo[branch_id]";
		$filter = "where ".join(' and ',$filter);
		
		$con->sql_query("select count(*) from dnote dn
						left join branch on branch.id = dn.branch_id
						left join vendor on vendor.id = dn.vendor_id
						$filter") or die(mysql_error());
		$total_rows = $con->sql_fetchfield(0);

		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		$order = "order by dn.last_update desc";

		$total_page = ceil($total_rows/$size);

		$sql = "select dn.*, vendor.code as vendor_code, vendor.description as vendor_description,branch.code as branch_code,branch.report_prefix
				from dnote dn
				left join branch on branch.id = dn.branch_id
				left join vendor on vendor.id = dn.vendor_id
				$filter $order $limit";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$dn_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			
			$dn_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		//print_r($dn_list);
		$smarty->assign('dn_list', $dn_list);
		$smarty->assign('total_page', $total_page);
		$smarty->display("dnote.list.tpl");
		
	}
}

$ARMS_DN = new ARMS_DN('Debit Note');
?>