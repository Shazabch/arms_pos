<?php
/*
8/6/2013 3:39 PM Justin
- Enhanced to show card no list before confirm to insert new member.

8/21/2013 5:35 PM Justin
- Bug fixed on system that unable to auto add "0" while constructing running no.

07/21/2016 16:00 Edwin
- Rename file from 'admin.import_members' to 'admin.preactivate_member_cards'.

1/6/2020 10:33 AM William
- Enhanced to insert "membership_guid" field for membership_history and membership table.

1/30/2020 5:17 Andy
- Increased maintenance checking to v438.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999) header("Location: /");
$maintenance->check(438);

class PREACTIVATE_MEMBER_CARDS extends Module{
	var $branches = array();
	
    function __construct($title){
		global $con, $smarty;

		parent::__construct($title);
	}
	
	function _default(){
	    global $con, $smarty;
	    
	    $this->display();
	}
	
	function view_sample_members(){
		global $con, $smarty, $config;
						
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_member_points.csv");
		print "\"NRIC\",\"Card No\",\"Name\",\"Points\"\n\r";
		print "\"671012564764\",\"202098760201\",\"Teh Leng Hock\",\"120\"";
	}
	
	function import_members(){
		global $con, $config, $smarty, $sessioninfo, $appCore;

		$form = $_REQUEST;
		$err = array();
		$err['top'] = $this->validate_data($card_no_list);
		
		if(!$err && !$card_no_list) $err['top'][] = "Nothing to insert";

	    if($err['top']){   // got error found
			$smarty->assign('err', $err);
			$smarty->assign('form', $form);
			$this->display();
			exit;
		}

		$ttl_row = $ttl_imported_row = 0;		
		foreach($card_no_list as $r=>$card_no){
			$card_no = trim($card_no);
			if(!$card_no) continue;

			$ttl_row++;
			$q1 = $con->sql_query("select * from membership_history where card_no = ".ms($card_no));
			
			if($con->sql_numrows($q1) > 0){
				$err['warning'][] = $card_no." existed in database";
				continue;
			}
			$con->sql_freeresult($q1);

			// insert card as new member
			$m_ins = array();
			$membership_guid = $appCore->newGUID();
			$m_ins['membership_guid'] = $membership_guid;
			$m_ins['nric'] = $card_no;
			$m_ins['card_no'] = $card_no;
			$m_ins['name'] = "NEW MEMBER";
			$m_ins['member_type'] = "member1";
			$m_ins['issue_date'] = $form['issue_date'];
			$m_ins['next_expiry_date'] = $form['expiry_date'];

			$q1 = $con->sql_query("insert into `membership` ".mysql_insert_by_field($m_ins));

			if($con->sql_affectedrows($q1) > 0){
				$mh_ins = array();
				$mh_ins['membership_guid'] = $m_ins['membership_guid'];
				$mh_ins['nric'] = $m_ins['nric'];
				$mh_ins['card_no'] = $m_ins['card_no'];
				$mh_ins['branch_id'] = $sessioninfo['branch_id'];
				$mh_ins['card_type'] = "G";
				$mh_ins['issue_date'] = $form['issue_date'];
				$mh_ins['expiry_date'] = $form['expiry_date'];
				$mh_ins['remark'] = "N";
				$mh_ins['added'] = "CURRENT_TIMESTAMP";
	
				$con->sql_query("insert into `membership_history` ".mysql_insert_by_field($mh_ins));
				
				$ttl_imported_row++;
			}
		}

		$smarty->assign('err', $err);
		$smarty->assign('ttl_imported_row', $ttl_imported_row);
		$smarty->assign('ttl_row', $ttl_row);
		$this->display();
	}
	
	function validate_data(&$card_no_list){
		global $con, $config, $smarty;
		
		$form = $_REQUEST;
		$err = array();
		
		$empty_card_no = false;
		if($form['import_type'] == "by_range"){
			if(!trim($form['card_prefix']) || trim(!$form['card_range_to'])){
				$err[] = "The Card Prefix, Cards Range From & To cannot left empty.";
				$empty_card_no = true;
			}elseif($form['card_range_from'] > $form['card_range_to']){
				$err[] = "Invalid Card range.";
				$empty_card_no = true;
			}
		}elseif($form['import_type'] == "by_cn_list" && !trim($form['card_no_list'])){
			$err[] = "The list of Card No is empty.";
			$empty_card_no = true;
		}

		if(!$form['issue_date'] || !$form['expiry_date']) $err[] = "Please assign issue and expiry date";
		elseif(strtotime($form['issue_date']) >= strtotime($form['expiry_date'])) $err[] = "Issue date should not equal or older than Expiry Date";

		if(!$err){
			if($form['import_type'] == "by_cn_list"){
				$card_no_list = explode("\n", trim($form['card_no_list']));
			}else{
				$card_length = $config['membership_length'] - strlen($form['card_prefix']);
				if($card_length <= 0){
					if(strlen($form['card_range_from']) < strlen($form['card_range_to'])) $card_length = strlen($form['card_range_to']);
					else $card_length = strlen($form['card_range_from']);
				}
				for($i=$form['card_range_from']; $i<=$form['card_range_to']; $i++){
					$pre_card_no = str_pad($i, $card_length, "0", STR_PAD_LEFT);
					$card_no = strval($form['card_prefix']).strval($pre_card_no);
					//$card_no = trim($form['card_prefix']).$i;
					$card_no_list[]  = $card_no;
				}
			}
		}

		if(!$empty_card_no && !$form['skip_duplicate']){
			if($card_no_list){
				foreach($card_no_list as $r=>$card_no){
					if(!trim($card_no)) continue;
					$tmp_err = $this->validate_card($card_no, $card_type);
					if($tmp_err){
						$err = array_merge($err, $tmp_err);
						break;
					}
				}
			}
		}
		
		return $err;
	}
	
	function validate_card($card_no, &$card_type = ''){
		global $config, $con, $LANG;
		
		$errmsg = array();
		if (!preg_match($config['membership_valid_cardno'], $card_no)){
			$errmsg[] = $card_no." - ".$LANG['MEMBERSHIP_INVALID_CARD_NO'];
		}

		if(!$errmsg){
			// perform change card
			$card_type = '';
			foreach($config['membership_cardtype'] as $type=>$ct){
				if (preg_match($ct['pattern'], $card_no)){
					$card_type = $type;
					break;
				}
			}

			if (!$card_type) $errmsg[] = $card_no." - ".$LANG['MEMBERSHIP_INVALID_CARD_NO'];
		}
		
		// search if card existed in database
		$con->sql_query("select nric from membership_history where card_no = ".ms($card_no)." and remark <> 'CB' limit 1");
		if ($con->sql_numrows() > 0){ // if found
			$errmsg[] = $card_no." - ".str_replace("\\n", " ", $LANG['MEMBERSHIP_CARD_IN_DATABASE']);
		}
		
		return $errmsg;	
	}
	
	function ajax_load_card_list_by_range(){
		global $con, $config;
		
		$form = $_REQUEST;
		if($form['import_type'] != "by_range") return;
		
		$this->validate_data($tmp_card_no_list);
		
		$card_length = $config['membership_length'] - strlen($form['card_prefix']);
		if($card_length <= 0){
			if(strlen($form['card_range_from']) < strlen($form['card_range_to'])) $card_length = strlen($form['card_range_to']);
			else $card_length = strlen($form['card_range_from']);
		}
		for($i=$form['card_range_from']; $i<=$form['card_range_to']; $i++){
			$pre_card_no = str_pad($i, $card_length, "0", STR_PAD_LEFT);
			$card_no = strval($form['card_prefix']).strval($pre_card_no);
			$card_no_list[$card_no] = true;
		}
		
		if($card_no_list){
			print "<br /><b>Cards Confirmation List:</b><br />";
			print "<ul>";
			foreach($card_no_list as $card_no=>$r){
				if(!trim($card_no)) continue;
				print "<li>";
				$tmp_err = $this->validate_card($card_no, $card_type);
				if($tmp_err){
					foreach($tmp_err as $err){
						print $err."<br />";
						break;
					}
				}else{
					print $card_no." - OK";
				}
				print "</li>";
			}
			print "</ul>";
		}
	}
}

$PREACTIVATE_MEMBER_CARDS = new PREACTIVATE_MEMBER_CARDS('Pre-activate Member Cards');
?>
