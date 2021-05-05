<?php
/*
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_SALES_AGENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SALES_AGENT', BRANCH_CODE), "/index.php");
$maintenance->check(420);

class MASTERFILE_SA_KPI_RESULT extends Module{
	function __construct($title){
		global $con, $smarty, $config, $appCore;
		
		if(!$_REQUEST['year']) $_REQUEST['year'] = date("Y");
							 
		$this->sa_list = array();
		$prms = array();
		$prms['active'] = 1;
		$this->sa_list = $appCore->salesAgentManager->getSAList($prms);
		$smarty->assign("sa_list", $this->sa_list);
		
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
		global $con, $smarty, $appCore;
		
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
		
		// current logged on sales agent filter
		$filters = array();
		$filters[] = "skr.sa_id = ".mi($form['sa_id']);
		$filters[] = "skr.year = ".mi($form['year']);
		
		$filter = "";
		if($filters) $filter = "where ".join(' and ', $filters);
		
		// get KPI previous data which save/confirmed by the leader
		$q1 = $con->sql_query($sql="select skr.*, sal.code as leader_code, sal.name as leader_name
							   from sa_kpi_rating skr
							   left join sa sal on sal.id = skr.sa_leader_id
							   $filter
							   order by skr.year, skr.month");

		$sa_kpi_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($r['kpi_data']) $r['kpi_data'] = unserialize($r['kpi_data']);
			$sa_kpi_list[$r['sa_leader_id']]['leader_code'] = $r['leader_code'];
			$sa_kpi_list[$r['sa_leader_id']]['leader_name'] = $r['leader_name'];
			$sa_kpi_list[$r['sa_leader_id']][$r['month']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("kpi_items_list", $kpi_items_list);
		$smarty->assign("sa_kpi_list", $sa_kpi_list);
		$smarty->assign("form", $form);
		unset($filters, $filter, $sa_kpi_list);
	}
	
	function ajax_reload_sa_kpi_list(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		if(!$form['sa_id']) die("No Sales Agent were selected!");
		
		$this->load_kpi_list();
		
		$ret = array();
		$ret['html'] = $smarty->fetch("masterfile_sa.kpi_result.list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
		exit;
	}
	
	function reset_sa_kpi(){
		global $con, $sessioninfo;
		//$this->save_sa_kpi(true);
		$form = $_REQUEST;
		
		if(!$form['sa_leader_id'] || !$form['sa_id'] || !$form['year']) die("Not enough info to do reset!");
		
		$upd = array();
		$upd['status'] = 0;
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("update sa_kpi_rating set ".mysql_update_by_field($upd)." where sa_id = ".mi($form['sa_id'])." and sa_leader_id = ".mi($form['sa_leader_id'])." and year = ".mi($form['year']));
		
		log_br($sessioninfo['id'], 'SA_KPI_RESULT', 0, "Reset S/A KPI Rating: SA ID#".$form['sa_id'].", SA Leader ID#".$form['sa_leader_id'].", Year#".$form['year']);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function save_sa_kpi(){
		global $con, $smarty, $config, $appCore, $sessioninfo;

		$form=$_REQUEST;
		
		$this->tmp_sa_kpi_list = array(); // usage when having error msg
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
			$smarty->assign("sa_kpi_list", $this->tmp_sa_kpi_list);
			$smarty->assign("err_list", $err_list);
			$this->display();
			unset($kpi_items_list, $this->tmp_sa_kpi_list, $err_list);
			exit;
		}
		
		// got this base on which buton that the user clicked
		$sal_id = $form['selected_sa_leader_id'];
		
		foreach($form['sa_kpi_data'][$sal_id] as $mth=>$kpi_data_list){
			// look if the has existing data
			$q1 = $con->sql_query("select * from sa_kpi_rating 
								   where sa_id = ".mi($form['sa_id'])." and sa_leader_id = ".mi($sal_id)." and year = ".mi($form['year'])." and month = ".mi($mth)
								 );
			$has_data = $con->sql_numrows($q1);
			$con->sql_freeresult($q1);
			
			$ins = array();
			$ins['kpi_data'] = serialize($kpi_data_list);
			if($is_confirm) $ins['status'] = 1;
			if($has_data > 0){ // if found data from database, do update
				$ins['last_update'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("update sa_kpi_rating set ".mysql_update_by_field($ins)." where sa_id = ".mi($form['sa_id'])." and sa_leader_id = ".mi($sal_id)." and year = ".mi($form['year'])." and month = ".mi($mth));
			}else{ // otherwise do insertion
				$ins['sa_id'] = $form['sa_id'];
				$ins['sa_leader_id'] = $sal_id;
				$ins['year'] = $form['year'];
				$ins['month'] = $mth;
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
		
				$con->sql_query("insert into sa_kpi_rating ".mysql_insert_by_field($ins));
			}
		}
		log_br($sessioninfo['id'], 'SA_KPI_RESULT', 0, "Updated S/A KPI Rating: SA ID#".$form['sa_id'].", SA Leader ID#".$sal_id.", Year#".$form['year']);
		
	    header("Location: masterfile_sa.kpi_result.php");
	}
	
	function validate(){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err_list = array();
		
		$tmp_sa_code = $form['code'];
		$kpi_description = trim($form['description']);
		
		foreach($form['sa_leader_id'] as $sal_id){
			foreach($form['sa_kpi_data'][$sal_id] as $mth=>$kpi_data_list){
				$has_negative_scores = $exceeded_max_scores = false;
				foreach($kpi_data_list as $kpi_id=>$scores){
					$max_scores = $form['kpi_scores'][$sal_id][$kpi_id];
					
					if($sal_id == $form['selected_sa_leader_id']){ // do checking base on the user save button clicked only
						if(!$has_negative_scores && $scores < 0){ // negative scores are not allowed
							$has_negative_scores = true;
							$err_list[$sal_id][$kpi_id][] = $LANG['SA_KPI_RATING_INVALID_SCORES'];
						}elseif(!$exceeded_max_scores && $scores > $max_scores){ // cannot be higher than the max scores
							$exceeded_max_scores = true;
							$err_list[$sal_id][$kpi_id][] = $LANG['SA_KPI_RATING_MAX_SCORES_EXCEEDED'];
						}
					}
					$this->tmp_sa_kpi_list[$sal_id]['leader_code'] = $this->sa_list[$sal_id]['code'];
					$this->tmp_sa_kpi_list[$sal_id]['leader_name'] = $this->sa_list[$sal_id]['name'];
					$this->tmp_sa_kpi_list[$sal_id][$mth]['kpi_data'][$kpi_id] = $scores;
				}
				$this->tmp_sa_kpi_list[$sal_id][$mth]['status'] = $form['sa_kpi_month_status'][$sal_id][$mth];
				unset($has_negative_scores, $exceeded_max_scores);
			}
		}
		
		return $err_list;
	}
}

$MASTERFILE_SA_KPI_RESULT=new MASTERFILE_SA_KPI_RESULT("Sales Agent - KPI Result");

?>
