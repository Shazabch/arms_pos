<?php
/*
*/
include("include/common.php");

// check ticket validity
session_start();
$ssid = session_id();
$sa_id = $sa_session['id'];
$con->sql_query("select * from sa where sa.id=".mi($sa_id));
$r=$con->sql_fetchrow();

if(!$r || !$sa_session || $sa_session['ticket_no'] != $r['ticket_no']){
	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
}

//$maintenance->check(420);

class MASTERFILE_SA_KPI_RATING extends Module{
	function __construct($title){
		global $con, $smarty, $config, $sa_session, $appCore;
		
		if(!$_REQUEST['year']) $_REQUEST['year'] = date("Y");
		
		// select the sales agent list under this leader
		$q1 = $con->sql_query("select sa.*
							   from sa
							   join sa_leader sl on sl.sa_id = sa.id
							   where sl.sa_leader_id = ".mi($sa_session['id'])." and sa.position_id > 0");
		
		while($r = $con->sql_fetchassoc($q1)){
			$sa_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("sa_list", $sa_list);
		
		// load year list (currently only show current and last year)
		$year_list = array();
		$year_list[] = date("Y",strtotime("-1 year"));
		$year_list[] = date("Y");
		$smarty->assign("year_list", $year_list);
		
		// load month list
		$prms = array();
		$prms['year'] = $_REQUEST['year'];
		$mth_list = $appCore->salesAgentManager->getMonthList($prms);
		$smarty->assign("mth_list", $mth_list);

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty;
		
		//$this->load_kpi_list();
	    $this->display();
	    exit;
	}
	
	private function load_kpi_list(){
		global $con, $smarty, $sa_session, $appCore;
		
		$form = $_REQUEST;
		
		// need to assign month and hide the input fields for future months if user is viewing current year
		if($form['year'] == date("Y")){
			$form['curr_mth'] = date("m");
		}
		
		if(!$form['sa_id']) die("Please select a Sales Agent");
		
		// load KPI data
		$kpi_items_list = $prms = array();
		$prms['sa_id'] = $form['sa_id'];
		$kpi_items_list = $appCore->salesAgentManager->getKPIItems($prms);
		
		$filters = array();
		
		// current logged on sales agent filter
		$filters[] = "skr.sa_id = ".mi($form['sa_id'])." and skr.sa_leader_id=".mi($sa_session['id']);
		$filters[] = "skr.year = ".mi($form['year']);
		
		$filter = "";
		if($filters) $filter = "where ".join(' and ', $filters);
		
		// get KPI previous data which save/confirmed by the leader
		$q1 = $con->sql_query($sql="select skr.*
							   from sa_kpi_rating skr
							   $filter
							   order by skr.year, skr.month");

		$sa_kpi_data = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($r['kpi_data']) $r['kpi_data'] = unserialize($r['kpi_data']);
			$sa_kpi_data[$r['month']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("kpi_items_list", $kpi_items_list);
		$smarty->assign("sa_kpi_data", $sa_kpi_data);
		$smarty->assign("form", $form);
		unset($filters, $filter, $sa_kpi_data);
	}
	
	function ajax_reload_sa_kpi_list(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		if(!$form['sa_id']) die("No Sales Agent were selected!");
		
		$this->load_kpi_list();
		
		$ret = array();
		$ret['html'] = $smarty->fetch("sa.kpi_rating.list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
		exit;
	}
	
	function confirm_sa_kpi(){
		$this->save_sa_kpi(true);
	}
	
	function save_sa_kpi($is_confirm=false){
		global $con, $smarty, $config, $sa_session, $appCore;

		$form=$_REQUEST;
		
		$this->tmp_sa_kpi_data = array(); // usage when having error msg
		$err_list = $this->validate();
		
		if($err_list){ // found the list contains errors, redirect user back with error msg
			// load KPI data
			$kpi_items_list = $prms = array();
			$prms['sa_id'] = $form['sa_id'];
			$kpi_items_list = $appCore->salesAgentManager->getKPIItems($prms);
			unset($prms);
			
			// get the date from the first month of selected year
			$prms = array();
			$prms['year'] = $form['year'];
			$mth_list = $appCore->salesAgentManager->getMonthList($prms);
			$smarty->assign("mth_list", $mth_list);
			
			$smarty->assign("form", $form);
			$smarty->assign("kpi_items_list", $kpi_items_list);
			$smarty->assign("sa_kpi_data", $this->tmp_sa_kpi_data);
			$smarty->assign("err_list", $err_list);
			$this->display();
			unset($kpi_items_list, $this->tmp_sa_kpi_data, $err_list);
			exit;
		}
		
		$ins = array();
		$ins['sa_id'] = $form['sa_id'];
		$ins['year'] = $form['year'];
		
		foreach($form['sa_kpi_data'] as $mth=>$kpi_data_list){
			// look if the has existing data
			$q1 = $con->sql_query("select * from sa_kpi_rating 
								   where sa_id = ".mi($form['sa_id'])." and sa_leader_id = ".mi($sa_session['id'])." and year = ".mi($form['year'])." and month = ".mi($mth)
								);
			$has_data = $con->sql_numrows($q1);
			$con->sql_freeresult($q1);
			
			$ins = array();
			$ins['kpi_data'] = serialize($kpi_data_list);
			if($is_confirm) $ins['status'] = 1;
			if($has_data > 0){ // if found data from database, do update
				$ins['last_update'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("update sa_kpi_rating set ".mysql_update_by_field($ins)." where sa_id = ".mi($form['sa_id'])." and sa_leader_id = ".mi($sa_session['id'])." and year = ".mi($form['year'])." and month = ".mi($mth));
			}else{ // otherwise do insertion
				$ins['sa_id'] = $form['sa_id'];
				$ins['sa_leader_id'] = $sa_session['id'];
				$ins['year'] = $form['year'];
				$ins['month'] = $mth;
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
		
				$con->sql_query("insert into sa_kpi_rating ".mysql_insert_by_field($ins));
			}
		}
		
		if($is_confirm) $update_type = "Confirmed";
		else $update_type = "Updated";
		
		log_sa($sa_session['id'], 'SA_KPI_RATING', 0, $update_type." S/A KPI Rating: SA ID#".$form['sa_id'].", SA Leader ID#".$sa_session['id'].", Year#".$form['year']);
	    header("Location: sa.kpi_rating.php");
	}
	
	function validate(){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err_list = array();
		
		$tmp_sa_code = $form['code'];
		$kpi_description = trim($form['description']);
		
		foreach($form['sa_kpi_data'] as $mth=>$kpi_id_list){
			$has_negative_scores = $exceeded_max_scores = false;
			foreach($kpi_id_list as $kpi_id=>$scores){
				$max_scores = $form['kpi_scores'][$kpi_id];
				
				if(!$has_negative_scores && $scores < 0){ // negative scores are not allowed
					$has_negative_scores = true;
					$err_list[$kpi_id][] = $LANG['SA_KPI_RATING_INVALID_SCORES'];
				}elseif(!$exceeded_max_scores && $scores > $max_scores){ // cannot be higher than the max scores
					$exceeded_max_scores = true;
					$err_list[$kpi_id][] = $LANG['SA_KPI_RATING_MAX_SCORES_EXCEEDED'];
				}
				$this->tmp_sa_kpi_data[$mth]['kpi_data'][$kpi_id] = $scores;
			}
			$this->tmp_sa_kpi_data[$mth]['status'] = $form['sa_kpi_month_status'][$mth];
			
			unset($has_negative_scores, $exceeded_max_scores);
		}
		
		return $err_list;
	}
}

$MASTERFILE_SA_KPI_RATING=new MASTERFILE_SA_KPI_RATING("Sales Agent - KPI Rating");

?>
