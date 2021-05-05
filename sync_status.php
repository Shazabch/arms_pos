<?php
/*
5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/
require_once("include/smarty.php");
include('config.php');
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}
include('include/functions.php');

//include('include/common.php');

/* framework class */
abstract class Module
{
	var $template;
	var $title;

	abstract function _default();

	function display($tpl='')
	{
		global $smarty;

		if ($tpl=='')
			$smarty->display($this->template);
		else
			$smarty->display($tpl);
	}

	function __construct($title, $template='')
	{
		global $smarty;
		$this->title = $title;
		$smarty->assign("PAGE_TITLE", $title);
		if ($template=='')
		{
			$template = str_replace(".php", ".tpl", basename($_SERVER['PHP_SELF']));
		}
		$this->template = $template;

		if (isset($_REQUEST['a']))
		{
			$a = $_REQUEST['a'];
			$this->$a();
			exit;
		}
		$this->_default();
	}

	protected function update_title($new_title){
	    global $smarty;
        $this->title = $new_title;
        $smarty->assign("PAGE_TITLE", $this->title);
	}
}

$con = new sql_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3],false);

class SYNC_STATUS extends Module {
	var $branch_list = array();
	var $slave_server_list = array();
	
	function __construct($title, $template=''){
		global $con, $smarty, $config;
		
		// branch list
		$q1 = $con->sql_query("select id,code from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		// get counter list
		$con->sql_query("select branch_id,id,network_name from counter_settings where active=1 order by branch_id, network_name");
		while($r = $con->sql_fetchassoc()){
			if($this->branch_list[$r['branch_id']])	$this->branch_list[$r['branch_id']]['counter_list'][$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign('branch_list', $this->branch_list);
		
		// sock / slave list
		$this->slave_server_list = array();
		$con->sql_query("select ss.*, b.code as branch_code 
							from sql_slaves ss
							left join branch b on b.id = ss.branch_id");
		while($r = $con->sql_fetchassoc()){
			$this->slave_server_list[$r['name']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('slave_server_list', $this->slave_server_list);
		
		$smarty->assign("config", $config);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $config, $con, $smarty;
		
		// main server list
		if(!$config['single_server_mode']){
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

			$smarty->assign('main_server_list', $main_server_list);
		}
		
		$this->load_counter_status();
		
		$this->display();
	}
	
	private function get_bid($bcode){
		if(!$bcode)	return 0;
		
		foreach($this->branch_list as $bid => $r){
			if($r['code'] == $bcode)	return $bid;
		}
	}
	
	function load_server_info(){
		global $con, $smarty, $config, $db_default_connection;
		
		$server_type = trim($_REQUEST['server_type']);
		$server_key = trim($_REQUEST['server_key']);
		
		if(!$server_type || !$server_key)	die("Invalid Server");
		
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
		    else
		    {
		        $r['ip'] = 'localhost';
		    }
		}elseif($server_type == 'slave'){
			$r['ip'] = $server_key;
		}else{
			die("Invalid Server Type");
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
    	
    	
    	//sleep(20);

    	//print_r($server_info);
    	
    	$smarty->assign('server_type', $server_type);
    	$smarty->assign('server_key', $server_key);
    	$smarty->assign('server_info', $server_info);
    	
    	if($server_type == 'main'){
    		$smarty->assign('server', $this->branch_list[$bid]);
    	}else{
    		$smarty->assign('server', $this->slave_server_list[$server_key]);
    	}
    	
    	
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('sync_status.server.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function load_counter_status(){
		global $con, $smarty;
				
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
						$q1 = $con->sql_query("select * from pos_counter_collection_tracking where branch_id=$bid and counter_id=$cid and finalized=0 and error<>'' order by date");
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
		// re-assign branch list into smarty
		$smarty->assign('branch_list', $this->branch_list);
		
		if(is_ajax()){
			$ret = array();
			$ret['ok'] = 1;
			$ret['html'] = $smarty->fetch('sync_status.counter.tpl');
			
			$ret = array_map(utf8_encode, $ret);	// must put this before json encode
			print json_encode($ret);
		}
	}
}

$SYNC_STATUS = new SYNC_STATUS('Sync Status');
?>
