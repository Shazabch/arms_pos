<?php
/*
8/30/2013 3:14 PM Justin
- Enhanced to have new function that can set imported points as latest points.

1/3/2020 4:02 PM William
- Enhanced to insert "membership_guid" field for "membership_points" table.

1/30/2020 5:17 Andy
- Increased maintenance checking to v438.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999) header("Location: /");
$maintenance->check(438);

class IMPORT_MEMBER_POINTS extends Module{
	var $branches = array();
	
    function __construct($title){
		global $con, $smarty;

		parent::__construct($title);
	}
	
	function _default(){
	    global $con, $smarty;
	    
	    $this->display();
	}
	
	function view_sample_member_points(){
		global $con, $smarty, $config;
						
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_member_points.csv");
		print "\"NRIC\",\"Card No\",\"Name\",\"Points\"\n\r";
		print "\"671012564764\",\"202098760201\",\"Teh Leng Hock\",\"120\"";
	}
	
	function import_member_points(){
		global $con, $config, $smarty, $sessioninfo;

		$form = $_REQUEST;
		$err = array();
		$err['top'] = $this->validate_data();

	    if($err['top']){   // got error found
			$smarty->assign('err', $err);
			$this->display();
			exit;
		}
		
		
		$f = $_FILES['import_csv'];
		$fp = fopen($f['tmp_name'], "r");
		$header_line = fgetcsv($fp); // get 1st header line
		$ttl_row = $ttl_imported_row = 0;
		
		while(($data = fgetcsv($fp)) !== FALSE){
			$ttl_row++;
			$nric = trim($data[0]);
			$card_no = trim($data[1]);
			$name = trim($data[2]);
			$points = mf($data[3]);
			
	
			$q1 = $con->sql_query("select * from membership_history where nric = ".ms($nric)." and card_no = ".ms($card_no));
			
			if($con->sql_numrows($q1) == 0){
				$err['warning'][] = $name." is not a valid member";
				continue;
			}
			$r = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			
			if(!$points){
				$err['warning'][] = $name." is having 0 points";
				continue;
			}

			$points_info = array();
			if($form['clear_data'] || $form['is_curr_points']){ // need to clear previous data
				$con->sql_query("delete from membership_points where nric = ".ms($nric)." and type = 'ADJUST' and remark = 'Points From Import' and date_format(date, '%Y-%m-%d') = ".ms($form['date']));
			}else{
				$q1 = $con->sql_query("select * from membership_points where nric = ".ms($nric)." and type = 'ADJUST' and remark = 'Points From Import' and date_format(date, '%Y-%m-%d') = ".ms($form['date']));
				$points_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			
			if($form['is_curr_points']){ // import as latest point
				$q1 = $con->sql_query("select sum(points) as ttl_points from membership_points where nric = ".ms($nric));
				$ttl_points_info = $con->sql_fetchassoc($q1);
				$points += ($ttl_points_info['ttl_points']*-1);
				$con->sql_freeresult($q1);
			}
			
			if($points){
				if(!$points_info){
					$ins = array();
					$ins['membership_guid'] = $r['membership_guid'];
					$ins['nric'] = $nric;
					$ins['card_no'] = $card_no;
					$ins['branch_id'] = $sessioninfo['branch_id'];
					$ins['date'] = $form['date'];
					$ins['points'] = $points;
					$ins['remark'] = "Points From Import";
					$ins['type'] = "ADJUST";
					$ins['user_id'] = $sessioninfo['id'];

					$q1 = $con->sql_query("insert into membership_points ".mysql_insert_by_field($ins));
				}else{
					$upd = array();
					$upd['points'] = $points_info['points']+$points;

					$q1 = $con->sql_query("update membership_points set ".mysql_update_by_field($upd)." where nric = ".ms($nric)." and type = 'ADJUST' and remark = 'Points From Import' and date_format(date, '%Y-%m-%d') = ".ms($form['date']));
				}
			}
			
			if ($con->sql_affectedrows($q1) > 0){
				// set membership as points had changed
				$con->sql_query("update membership set points_changed=1 where nric = ".ms($nric));
				
				// mark this member to recalculate the points once points inserted
				$con->sql_query("delete from tmp_membership_points_trigger where card_no in (select card_no from membership_history where nric=".ms($nric).")");
				
			}
			
			$ttl_imported_row++;
		}
		
		fclose($fp);

		$smarty->assign('err', $err);
		$smarty->assign('ttl_imported_row', $ttl_imported_row);
		$smarty->assign('ttl_row', $ttl_row);
		$this->display();
	}
	
	function validate_data(){
		global $con, $config, $smarty;
		
		$form = $_REQUEST;
		$err = array();
		
		// check file
	    $err = check_upload_file('import_csv', 'csv');
		
		$f = $_FILES['import_csv'];
		$fp = fopen($f['tmp_name'], "r");
		$header_line = fgetcsv($fp); // get 1st header line
		
		if(!$header_line) $err[] = "The file contain no data.";   // no data found
		elseif(count($header_line)<4){ // check header line
			$err[] = "Incorrect file format! Must have NRIC, Card No, Name, Points";
		}

		fclose($fp);

		if(!$form['date']) $err[] = "Date is empty.";
		
		return $err;
	}
}

$IMPORT_MEMBER_POINTS = new IMPORT_MEMBER_POINTS('Import Member Points');
?>
