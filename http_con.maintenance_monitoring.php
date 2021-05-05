<?php
/*
*/

define('TERMINAL',1);
include_once('config.php');
include_once('include/db.php');
include_once('include/functions.php');

ini_set('memory_limit', '512M');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);

$MAINTENANCE_MONITORING = new MAINTENANCE_MONITORING();

switch($_REQUEST['a']){
	case 'load_config_list':
		$MAINTENANCE_MONITORING->load_config_list();
		break;
	case 'load_hdd_space':
		$MAINTENANCE_MONITORING->load_hdd_space();
		break;
	case 'load_db_backup_list':
		$MAINTENANCE_MONITORING->load_db_backup_list();
		break;
	case 'load_branch_list':
		$MAINTENANCE_MONITORING->load_branch_list();
		break;
	case 'load_counter_list':
		$MAINTENANCE_MONITORING->load_counter_list();
		break;
	case 'load_slave_list':
		$MAINTENANCE_MONITORING->load_slave_list();
		break;
	case 'load_server_list':
		$MAINTENANCE_MONITORING->load_server_list();
		break;
	case 'load_server_info':
		$MAINTENANCE_MONITORING->load_server_info();
		break;
	case 'load_counters_info':
		$MAINTENANCE_MONITORING->load_counters_info();
		break;
	case 'load_main_server_list':
		$MAINTENANCE_MONITORING->load_main_server_list();
		break;
}

class MAINTENANCE_MONITORING {

	var $customer_branch = array();
	var $gpm_sku_list = array();
	var $check_date_from = '';
	
	function __construct()
	{
		global $con, $config, $smarty;
		
		$this->slave_server_list = $this->branch_list = array();
	}
	
	// load config list
	function load_config_list(){
		global $config;
		
		$ret = array();
		$ret['single_server_mode'] = $config['single_server_mode'];
		
		print serialize($ret);
	}
	
	// get db backup status
	function load_db_backup_list(){
		print "<pre>";
		passthru("ls -lG /backup;sql");
		print "</pre>";
	}
	
	// get disk space status
	function load_hdd_space(){
		$hdd_info = $hdd_list = array();
        foreach(preg_split('/\n/',`df | grep "^/dev"`) as $line){
			if($line[0]){
				$hdd_info = preg_split('/\s+/', $line);
				$hdd_list[$line[0]] = $hdd_info;
			}
        }
		
		print json_encode($hdd_list);
		unset($hdd_info, $hdd_list);
		//$smarty->assign("hdd_info", $hdd_info);
	}
	
	// branch list
	function load_branch_list($skip_http_con=false){
		global $con, $smarty;
		
		$bid = $_REQUEST['bid'];
		if($bid) $filter = "and id = ".mi($bid);
		
		$q1 = $con->sql_query("select id,code from branch where active=1 $filter order by sequence,code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);	
		
		if(!$skip_http_con){
			$ret = array();
			$ret = $this->branch_list;
			
			print serialize($ret);
		}
	}
	
	// counter list
	function load_counter_list($skip_http_con=false){
		global $con, $smarty;
		
		$this->load_branch_list(true);
		
		// get counter list
		$con->sql_query("select branch_id,id,network_name from counter_settings where active=1 order by branch_id, network_name");
		while($r = $con->sql_fetchassoc()){
			if($this->branch_list[$r['branch_id']])	$this->branch_list[$r['branch_id']]['counter_list'][$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		if(!$skip_http_con){
			$ret = array();
			$ret = $this->branch_list;
			
			print serialize($ret);
		}
	}
	
	function load_main_server_list(){
		global $config;
		
		// main server list
		if(!$config['single_server_mode']){
			$this->load_branch_list(true);
			$main_server_list = array();
			if($config['replica_branch_sequence'] && is_array($config['replica_branch_sequence'])){
				foreach($config['replica_branch_sequence'] as $bcode){
					$bid = $this->get_bid($bcode);
					if(!$bid)	continue;
					
					$main_server_list[$bid] = $this->branch_list[$bid];
				}
			}
			
			if($main_server_list){
				foreach($this->branch_list as $bid => $r){
					if(!$main_server_list[$bid]){
						$main_server_list[$bid] = $r;
					}
				}
			}else{
				$main_server_list = $this->branch_list;
			}

			$ret = array();
			$ret = $main_server_list;
			
			print serialize($ret);
		}else $this->load_branch_list();
	}
	
	// sock / slave list
	function load_slave_list(){
		global $con, $smarty, $config;
		
		$server_key = $_REQUEST['server_key'];
		if($server_key) $filter = " where name = ".ms($server_key);
		
		$con->sql_query("select ss.*, b.code as branch_code 
							from sql_slaves ss
							left join branch b on b.id = ss.branch_id
							$filter");
		while($r = $con->sql_fetchassoc()){
			$this->slave_server_list[$r['name']] = $r;
		}
		$con->sql_freeresult();
		
		$ret = array();
		$ret = $this->slave_server_list;
		
		print serialize($ret);
	}
	
	// get server list
	function load_server_info(){
		global $con, $smarty, $config, $db_default_connection;
		
		$ret = array();
		$server_type = trim($_REQUEST['server_type']);
		$server_key = trim($_REQUEST['server_key']);
		
		if(!$server_type || !$server_key) $ret['error'] = "Invalid Server";
		
		if($server_type == 'main'){
			$bid = mi($server_key);
			$bcode = get_branch_code($server_key);
			if(!$bcode)	die("Invalid Branch");
			
			// get host url		
			if ($bcode != BRANCH_CODE && !$config['single_server_mode'])
		    {
				$con->sql_query("select us.ip
				from branch_status us
				where branch_id=$bid");
				$r = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				//replace HTTP port to MYSQL port
				$r['ip'] = str_replace(":80", ":4001", $r['ip']);
				$r['ip'] = str_replace(":4000", ":4001", $r['ip']);
		    }
		    else{
				if(!$config['single_server_mode'] && defined('HQ_MYSQL')) $r['ip'] = HQ_MYSQL;
		        else $r['ip'] = 'localhost';
		    }
		}elseif($server_type == 'slave'){
			$r['ip'] = $server_key;
		}else{
			//die("Invalid Server Type");
			if(!$ret['error']) $ret['error'] = "Invalid Server Type";
		}
		
		if($ret['error']){
			print serialize($ret);
			exit;
		}
		
	    
	    // connect using arms_slave
	    $con2 = new sql_db($r['ip'], 'arms_slave', 'arms_slave', $db_default_connection[3], false);
    	if(!$con2->db_connect_id) die("Failed to connect $r[ip]:" . mysql_error());
    	
    	$server_info = array();
    	
    	// get master status
    	$con2->sql_query("show master status");
    	$server_info['master'] = $con2->sql_fetchassoc();
    	$con2->sql_freeresult();
    	
    	// get slave status
    	$con2->sql_query("show slave status");
    	$server_info['slave'] = $con2->sql_fetchassoc();
    	$con2->sql_freeresult();
    	
    	// slave running
    	if($server_info['slave']){
    		$server_info['slave']['running'] = 1;
	    	if($server_info['slave']['Slave_IO_Running'] == 'No' || $server_info['slave']['Slave_SQL_Running'] == 'No'){
	    		$server_info['slave']['running'] = 0;
	    		
	    		if($server_info['slave']['Slave_IO_Running'] == 'No'){
	    			$server_info['slave']['not_running_reason'][] = "Slave IO is not running.";
	    		}
	    		if($server_info['slave']['Slave_SQL_Running'] == 'No'){
	    			$server_info['slave']['not_running_reason'][] = "Slave SQL is not running.";
	    		}
	    	}
    	}
    	
    	// status   	
		$status = 'ok';
		if($server_info['slave']){
			if(!$server_info['slave']['running']){
				$status = 'err';
			}
		}
		
		$server_info['status'] = $status;
		$ret['server_info'] = $server_info;
    	
    	if($server_type == 'main'){
			$ret['server'] = $this->branch_list[$bid];
    	}else{
			$ret['server'] = $this->slave_server_list[$server_key];
    	}
		
		// setup config to be pass back to where the function called
		$ret['single_server_mode'] = $config['single_server_mode'];
		
		print serialize($ret);
	}
	
	function load_counters_info(){
		global $con, $smarty;
		
		$this->load_counter_list(true);
		
		$q1 = $con->sql_query("select cs.*, user.u
		from counter_status cs
		left join user on user.id=cs.user_id");

		while($r = $con->sql_fetchassoc($q1)){
			if($this->branch_list[$r['branch_id']]['counter_list'][$r['id']]){
			
				// calculate last ping duration
				if($r['lastping']){
					$r['total_sec'] = time() - strtotime($r['lastping']);
					$r['lastping_duration'] = calculate_duration_by_second($r['total_sec']);
				}

				$this->branch_list[$r['branch_id']]['counter_list'][$r['id']]['info'] = $r;
			}
		}
		$con->sql_freeresult($q1);
		
		foreach($this->branch_list as $bid => $b){
			$active_count = 0;
			$err_count = 0;
			$in_use_count = 0;
			
			if($b['counter_list']){
				foreach($b['counter_list'] as $cid => $c){
					if(!$c['info']){	// no counter status
						$c['syncstatus'] = 'nvr_online';
					}else{
						// check pos tracking error
						$err_list = array();
						$q1 = $con->sql_query("select * from pos_counter_collection_tracking where branch_id=$bid and counter_id=$cid and finalized=0 and error<>'' order by date desc");
						while($r = $con->sql_fetchassoc($q1)){
							$err_list[] = $r;
						}
						$con->sql_freeresult($q1);
						
						// got error
						if($err_list || $c['info']['lasterr'] || $c['info']['sync_error']){
							$c['err_list'] = $err_list;
							$c['syncstatus'] = 'sync_error';
							$err_count++;
						}else{
							// check last ping
							if(!$c['info']['lastping']){	// lastping zero
								$c['syncstatus'] = 'ping_error';
							}else{
								if($c['info']['total_sec']<300){
									$c['syncstatus'] = 'connected';
									$active_count++;
									
									if($c['info']['user_id']>0)	$in_use_count++;
								}else{
									$c['syncstatus'] = 'disconnected';
								}
							}
						}						
					}
					
					$this->branch_list[$bid]['counter_list'][$cid] = $c;					
				}
				$this->branch_list[$bid]['active_count'] = $active_count;
				$this->branch_list[$bid]['err_count'] = $err_count;
				$this->branch_list[$bid]['in_use_count'] = $in_use_count;
			}
		}

		print serialize($this->branch_list);
	}
	
	private function get_bid($bcode){
		if(!$bcode)	return 0;
		
		foreach($this->branch_list as $bid => $r){
			if($r['code'] == $bcode)	return $bid;
		}
	}
}
?>
