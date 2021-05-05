<?
require("include/common.php");
@include_once('xtra/server_list.php');

// security checking
define('SECURITY_CODE', 585858);
if ($_REQUEST['uid']!=SECURITY_CODE) // no uid cannot
{
	if ($_SERVER['SERVER_NAME'] != 'maximus' || $sessioninfo['level']<9999)
	{
		header("Location: /");
		exit;
	}
}


class COMPARE_TBL extends Module{
	var $all_tbl = array();
	var $grp_tbl = array();
	var $is_maximus = false;
	var $group_tbl = array('stock_balance_b', 'category_sales_cache_b', 'dept_trans_cache_b', 'member_sales_cache_b', 'pwp_sales_cache_b', 'sales_target_b', 'sku_items_sales_cache_b', 'sku_monitoring_2_report_cache_b');
	
	function __construct($title){
		global $con, $smarty, $server_list;

		if($_SERVER['SERVER_NAME'] == 'maximus')	$this->is_maximus = true;

		$smarty->assign('server_list', $server_list);
		$smarty->assign('group_tbl', $this->group_tbl);
		
		parent::__construct($title);
	}
	
	function _default(){
		global $con, $smarty, $sessioninfo;
			
		if($this->is_maximus && $sessioninfo){
			$this->load_tables();
			$this->display();
		}
	}
	
	private function load_tables(){
		global $con, $smarty, $sessioninfo;
		$this->all_tbl = $this->grp_tbl = array();
		
		$q1 = $con->sql_query("show tables");
		while($r = $con->sql_fetchrow($q1)){
			if(preg_match("/(".join('|', $this->group_tbl).")/", $r[0])){
				$this->grp_tbl[$r[0]] = $r[0];
			}else{
				$this->all_tbl[$r[0]] = $r[0];
			}
		}
		$con->sql_freeresult($q1);
		$smarty->assign('all_tbl', $this->all_tbl);
	}
	
	function check_server(){
		global $con, $smarty, $sessioninfo, $server_list;
		
		if(!$this->is_maximus)	die();
		
		//print_r($_REQUEST);
		
		$selected_tbl_list = $_REQUEST['selected_tbl_list'];
		$show_only_problem_table = mi($_REQUEST['show_only_problem_table']);
		if(!$selected_tbl_list || !is_array($selected_tbl_list))	die('Invalid table list')
;
		$server = trim($_REQUEST['server']);
		$server_info = $server_list[$server];
		if(!$server_info)	die('Invalid Server');
				
		$port = 4000;
		$url= "http://$server.dyndns.org:$port";
		if($server_info['url']){
            $url = $server_info['url'];
            if($server_info['http_port']){
				$url .= ":".$server_info['http_port'];
			}
		}
		
		$url .= "/compare_tbl.php?a=get_tbl_info&uid=".SECURITY_CODE;
		if(count($selected_tbl_list)<20){
			foreach($selected_tbl_list as $tbl_name){
				$url .= "&t[]=$tbl_name";
			}
		}
		
		$str = file_get_contents($url);
		
		$ret = @unserialize($str);
		if(!$ret)	die($str);
		
		//print_r($ret);
		$own_data = array();
		foreach($selected_tbl_list as $tbl_name){
			$chk_tbl = '';
			
			if(preg_match("/^grp_tbl-/", $tbl_name)){	// group table
				$tbl_prefix = str_replace("grp_tbl-", "", $tbl_name);
				$chk_tbl = $tbl_prefix.'1';	// use HQ as reference
				
				if($tbl_prefix=='stock_balance_b'){
					$chk_tbl .= '_'.date('Y');
				}
			}else	$chk_tbl = $tbl_name;
			
			$q_result = $con->sql_query("explain $chk_tbl",false,false);
			if(!$q_result){
				$data['table_info'][$tbl_name] = 'No such table';
			}else{
				$tmp = array();
				while($r = $con->sql_fetchassoc($q_result)){
					$tmp[$r['Field']] = $r;
				}
				$own_data['table_info'][$tbl_name] = $tmp;
			}
			$con->sql_freeresult($q_result);
		}
		
		$compare_result = array();
		foreach($own_data['table_info'] as $tbl_name => $own_tbl_data){
			$client_tbl_list = array();
			if(preg_match("/^grp_tbl-/", $tbl_name)){	// group table
				$tbl_prefix = str_replace("grp_tbl-", "", $tbl_name);
				
				foreach($ret['table_info'] as $real_tbl_name=>$tbl_info){
					if(preg_match("/^$tbl_prefix/", $real_tbl_name))	$client_tbl_list[$real_tbl_name]=$tbl_info;
				}
			}else{	// normal table
				if($ret['table_info'][$tbl_name]){
					$client_tbl_list = array($tbl_name=>$ret['table_info'][$tbl_name]);
				}
			}
			
			if(!$client_tbl_list)	$compare_result[$tbl_name]['error']['msg'] = 'client dont have this table';
			
			foreach($client_tbl_list as $real_tbl_name => $client_tbl_data){
				foreach($own_tbl_data as $colname=>$own_col_details){					
					$client_col_details = $client_tbl_data[$colname];
					if(!$client_col_details){
						// client dont have this column
						$compare_result[$real_tbl_name]['error']['col'][$colname] = 'No Column';
						continue;	// check next column
					}	
					foreach($own_col_details as $col_field=>$col_value){
						if($col_value != $client_col_details[$col_field]){
							$compare_result[$real_tbl_name]['error']['col'][$colname] = $col_field;
						}
					}
				}
				
				if(!$compare_result[$real_tbl_name]['error']){
					if($show_only_problem_table){
						unset($compare_result[$real_tbl_name]);
						continue;
					}
					$compare_result[$real_tbl_name]['ok'] = true;
				}	
				else{
					$compare_result[$real_tbl_name]['own'] = $own_tbl_data;
					$compare_result[$real_tbl_name]['client'] = $client_tbl_data;
				}
			}
		}
		
		//print_r($compare_result);
		
		$smarty->assign('compare_result', $compare_result);
		$this->display('compare_tbl.table_info.tpl');
	}
	
	function get_tbl_info(){
		global $con;
		
		$ret = array();
		// load all tables
		$this->load_tables();
			
		if(isset($_REQUEST['t'])){
			$t = $_REQUEST['t'];
			if(!$t)	die('Error');
			
			foreach($t as $tbl){
				if(preg_match("/^grp_tbl-/", $tbl)){	// group table
					$tbl_prefix = str_replace("grp_tbl-", "", $tbl);
					foreach($this->grp_tbl as $grp_tbl_name){
						if(preg_match("/^$tbl_prefix/", $grp_tbl_name))	$selected_tbl_list[] = $grp_tbl_name;
					}
				}else{	// normal table
					$selected_tbl_list[] = $tbl;
				}
			}
		}else{			
			// just join all tables
			$selected_tbl_list = array_merge($this->all_tbl, $this->grp_tbl);
		}
		
		if(!$selected_tbl_list)	die('No table');
		
		foreach($selected_tbl_list as $tbl_name){
			$q_result = $con->sql_query("explain $tbl_name",false,false);
			if(!$q_result){
				$ret['table_info'][$tbl_name] = 'No such table';
			}else{
				$tmp = array();
				while($r = $con->sql_fetchassoc($q_result)){
					$tmp[$r['Field']] = $r;
				}
				$ret['table_info'][$tbl_name] = $tmp;
			}
			$con->sql_freeresult($q_result);
		}
		
		print serialize($ret);
	}
}

$COMPARE_TBL = new COMPARE_TBL('COMPARE DATABASE');
?>
