<?php
/*
1/29/2021 9:00 AM William
- Enhanced to add member no, phone to member table.

5/4/2021 1:25 PM Andy
- Added "Purchase History".
*/
include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
if (!privilege('MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP', BRANCH_CODE), "/pda");
class MEMBER_ENQUIRY_Module extends Scan_Product{
    function __construct($title){
        global $sessioninfo;
		
		parent::__construct($title);
	}

	function init_module(){
	    global $con, $smarty;

		$smarty->assign('PAGE_TITLE','Member Enquiry');
	}

	function default_(){
		global $smarty;
		
		$smarty->display('member_enquiry.tpl');
	}
	
	function check_member(){
		global $con, $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		$err = array();
		if($form['member_no']){
			$filter = array();
			$member_no = trim($form['member_no']);
			
			// search member info
			$q1=$con->sql_query("select membership.name, membership.nric, membership.card_no, membership.phone_3
			from membership
			where (membership.card_no like '%".$member_no."%' or membership.phone_1 like '%".$member_no."%' 
			or membership.phone_2 like '%".$member_no."%' or membership.phone_3 like '%".$member_no."%' 
			or membership.nric like '%".$member_no."%' or membership.name like '%".$member_no."%')");
			$count=$con->sql_numrows($q1);
			
			if($count > 0){
				if($count == 1){
					$mdata=$con->sql_fetchassoc($q1);
					//$this->get_member_info($mdata['nric']);
					header("location:".$_SERVER['PHP_SELF']."?a=get_member_info&nric=".urlencode($mdata['nric']));
					exit;
				}else{
					$member_list = array();
					while($r1=$con->sql_fetchassoc($q1)){
						$member_list[] = $r1;
					}
					$smarty->assign('member_list', $member_list);
				}
			}else{
				$err[] = "Member not found.";
			}
			$con->sql_freeresult($q1);
		}else{
			$err[] = "Please enter member no/name/nric/phone";
		}
		
		$smarty->assign('err', $err);
		$smarty->assign('form', $form);
		$smarty->display('member_enquiry.tpl');
	}
	
	function get_member_info($nric = ""){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$form = $_REQUEST;
		if(!$nric){
			$nric = $_REQUEST['nric'];
		}
		
		if($nric){
			$con->sql_query("select *, DATE_FORMAT(dob, '%Y-%m-%d') as dob
			from membership where nric=".ms($nric));
			$member_data=$con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$member_data['profile_image_url'] = $member_data['profile_image_url'];
			if($member_data['gender'] == "M"){
				$member_data['gender'] = "Male";
			}elseif($member_data['gender'] == "F"){
				$member_data['gender'] = "Female";
			}
			
			// Generate QR Code
			if($member_data['card_no']){
				$qr_img_name = tempnam("/tmp", "pda_member_qr");
				if(file_exists($qr_img_name)){
					rename($qr_img_name, $qr_img_name.'.png');
					$appCore->generateQRCodeImage($qr_img_name, $member_data['card_no']);
					$member_data['member_no_qrcode'] = $qr_img_name;
				}
			}
			// get history info
			$rs = $con->sql_query("select b.code as branch_code, mh.card_type
			from membership_history mh 
			left join branch b on mh.branch_id = b.id 
			where mh.nric=".ms($nric)." 
			order by mh.expiry_date desc, mh.issue_date desc, mh.added desc 
			limit 1");
			$history = $con->sql_fetchassoc($rs);
			$con->sql_freeresult($rs);
			
			// product history
			if($history){
				// Get all card no this member used before
				$member_data['branch_code'] = $history['branch_code'];
				$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
				$str_card_no = join(',', array_map('ms', $card_no_list));
				
				if($card_no_list){
					// Get Data from pregen table
					$q_fav = $con->sql_query( "select tbl.card_no, si.sku_item_code, si.artno, si.mcode, si.link_code, 
					si.receipt_description, sum(tbl.qty) as qty, sum(tbl.amount + tbl.tax_amt) as price, max(tbl.date) as dt 
					from membership_fav_items tbl
					join sku_items si on tbl.sku_item_id = si.id
					where tbl.card_no in ($str_card_no)
					group by tbl.sku_item_id
					order by qty desc, price desc
					limit 100");
					if(!$con->sql_numrows($q_fav)){	// No Data from pregen table
						$con->sql_freeresult($q_fav);
						
						// Direct select from pos_items - Remove in future
						$q_fav = $con->sql_query("select member_no as card_no, sku_item_code, artno, mcode, barcode, link_code, 
						receipt_description,  sum(qty) as qty, sum(price-discount-discount2) as price, max(pi.date) as dt 
						from pos_items pi
						left join pos on pos.branch_id=pi.branch_id and pos.counter_id = pi.counter_id and pos.date = pi.date and pos.id = pi.pos_id
						left join sku_items on sku_item_id = sku_items.id
						where pos.member_no in ($str_card_no) and pos.cancel_status=0 and pos.member_no is not null and pos.member_no != ''
						group by sku_item_id
						order by qty desc, price desc
						limit 100");
					}
					
					$product_history = array();
					while($ph = $con->sql_fetchassoc($q_fav)){
						$product_history[] = $ph;
					}
					$con->sql_freeresult($q_fav);

					$smarty->assign("product_history", $product_history);
				}
			}
			
			$smarty->assign('member_data', $member_data);
			$smarty->display('member_enquiry.member_info.tpl');
		}else{
			$err = array();
			$err[] = "Invalid Nric.";
			$smarty->assign('err', $err);
			$smarty->assign('form', $form);
			$smarty->display('member_enquiry.tpl');
		}
	}
	
	
	private function default_load(){
		global $con,$smarty;
	}
	
	function show_scan_product(){
		global $con, $smarty;

	}

	function add_items(){
		global $con,$config,$sessioninfo;

	}
	
	function view_purchase_history(){
		global $con, $smarty, $appCore;
		
		$nric = trim($_REQUEST['nric']);
		
		if($nric){
			// Get Member Data
			$member_data = $appCore->memberManager->getMember($nric, false);
		}
		
		// Member Not Found
		if(!$member_data){
			$err = array();
			$err[] = "Invalid Nric.";
			$smarty->assign('err', $err);
			$smarty->assign('form', $form);
			$smarty->display('member_enquiry.tpl');
			exit;
		}
		
		$filter = array();
		//$member_data['membership_guid'] = '';
		if($member_data['membership_guid']){
			// If got Member GUID, filter using it will be faster
			$filter[] = "p.membership_guid=".ms($member_data['membership_guid']);
		}else{
			// No Member GUID, filter using member_no
			$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
			if($card_no_list){
				$filter[] = "p.member_no in (".join(",", array_map("ms", $card_no_list)).")";
			}else{
				$filter[] = "0";	// filter by zero will always return no result
			}
		}
		$filter[] = "p.cancel_status=0";
		$str_filter = "where ".join(' and ', $filter);
		
		$member_data['pos_list'] = array();
		
		// Get Purchase History
		$q1 = $con->sql_query($sql = "select p.branch_id,p.counter_id,p.id,p.date,p.receipt_ref_no,amount,
			(select sum(pp.amount) from pos_payment pp where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date and pp.pos_id=p.id and pp.type in ('Discount', 'Mix & Match Total Disc') and pp.adjust=0) as total_disc_amt
			from pos p
			$str_filter
			order by p.date desc, p.id desc
			limit 100");
		//print $sql;
		while($r = $con->sql_fetchassoc($q1)){
			$r['final_amt'] = round($r['amount'] - $r['total_disc_amt'], 2);
			$member_data['pos_list'][] = $r;
		}
		$con->sql_freeresult($q1);
		//print_r($member_data);
		$smarty->assign('member_data', $member_data);
		$smarty->display('member_enquiry.view_purchase_history.tpl');
	}
}
$MEMBER_ENQUIRY_Module = new MEMBER_ENQUIRY_Module('Member Enquiry');
?>
