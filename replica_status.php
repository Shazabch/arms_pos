<?php
/*
Revision History
================
4/20/07 3:31:00 PM   yinsee
- added sequence column in branch (use for sort order)

1/14/2009 4:15:04 PM yinsee
- add sync_slaves table
- skip single-branch

1/14/2011 6:35:56 PM Andy
- Change IP to use from table branch_status.

3/1/2011 10:02:32 AM Andy
- Show MySQL Version.

7/6/2011 12:15:34 PM Andy
- Change split() to use explode()

9/27/2011 12:16:16 PM Andy
- Add branch list will follow config, else will follow sequence.

3/14/2013 5:26 PM Justin
- Added define "TERMINAL" to avoid showing smarty errors.

1/17/2014 4:55 PM Justin
- Enhanced to show branch code if found have assign it on sql_slaves table.

6/20/2014 3:47 PM Justin
- Enhanced to show current time from slave servers.

6/22/2015 9:56 AM Andy
- Remove purge.
- Change use localhost to use db_default_connection.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

11/3/2017 10:16 AM Andy
- Fixed php open tag.

2/1/2019 10:50 AM Andy
- Enhanced to show server remark.

8/30/2019 4:35 PM Andy
- Enhanced multi server to load only config.replica_branch_sequence branch if got set config.

12/9/2019 9:47 AM Andy
- Hide Restart and Skip button.

12/17/2019 11:43 AM Andy
- Enhanced to hide php warning and notice.

2/4/2020 3:05 PM Andy
- Enhanced to show IO Error.
*/
define("TERMINAL",1);
include('config.php');
if (defined('E_FATAL')) error_reporting(E_FATAL | E_ERROR);
else error_reporting (E_ERROR);
	
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}

if (!function_exists('ms'))
{
	function ms($str)
	{
		$str = str_replace("'", "''", $str);
		return "'" . (trim($str)) . "'";
	}
}

$con = new sql_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3],false);

if(!$con->db_connect_id) die(mysql_error());

if (isset($_REQUEST['s'])) status($_REQUEST['s']);

function sql2arr(&$con, $sql)
{
	$con->sql_query($sql);
	$ret = array();
	/*while ($r=$con->sql_fetchrow())
	{
	    for ($i=0; $i<=$con->sql_numfields(); $i++)
			$ret[strtolower($con->sql_fieldname($i))] = $r[$i];
	}*/
	//print_r($ret);exit;
	$tmp = $con->sql_fetchassoc();
	if($tmp){
		foreach($tmp as $k => $v){
			$ret[strtolower($k)] = $v;
		}
	}
	
	//print_r($ret);
	return $ret;
}

function status($location)
{
	global $con, $config, $db_default_connection;
	list($type,$s) = explode(":", $location, 2);
		
    if ($type == 'report')
	{
	    $r['ip'] = $s;
	}
	else if ($type == 'slave')
	{
	    $r['ip'] = $s;

		// get branch code from sql slves
		$q1 = $con->sql_query("select ss.*, b.code as branch_code from sql_slaves ss
							   left join branch b on b.id = ss.branch_id
							   where ss.name = ".ms($r['ip']));

		$slave_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
	}
	else if ($s != BRANCH_CODE && !$config['single_server_mode'])
    {
    
    	$con->sql_query("select us.ip
		from branch_status us
		left join branch on branch.id=us.branch_id
		where branch.code = ".ms($s));
		$r = $con->sql_fetchrow();

        //replace HTTP port to MYSQL port
        $r['ip'] = str_replace(":80", ":4001", $r['ip']);
        $r['ip'] = str_replace(":4000", ":4001", $r['ip']);
		
		if($s == 'TMERAH'){
			$r['ip'] = str_replace(":4001", ":5001", $r['ip']);
		}
    }
    else
    {
        //$r['ip'] = 'localhost';
        $r['ip'] = $db_default_connection[0];
    }
	
	$con = new sql_db($r['ip'], 'arms_slave', 'arms_slave', $db_default_connection[3], false);

	/*$con->sql_query("select ss.* from sql_slaves ss where ss.name=".ms($r['ip']));
	$ss = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($ss){
		$con = new sql_db($r['ip'], $ss['u'], $ss['p'], $db_default_connection[3],false);
	}else{
		$con = new sql_db($r['ip'], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3],false);
		//$con = new sql_db($r['ip'], 'arms_slave', 'arms_slave', $db_default_connection[3], false);
	}*/
	

	if($con->db_connect_id){
		// get server time
		$mysql_time = (sql2arr($con, "select CURRENT_TIMESTAMP as slave_time"));
		
		$time_diff = abs(strtotime($mysql_time['slave_time'])-time()) / 60;
		if($time_diff >=5) $r['slave_time'] = "<font color='red'>".$mysql_time['slave_time']."</font>";
		else $r['slave_time'] = $mysql_time['slave_time'];
	}

	if($r['slave_time']){
		print "<div style='float:right;'><h3>".$r['slave_time']."</h3></div>\n";
	}

	print "<h1>";
	
	if(isset($slave_info['branch_code']) && $slave_info['branch_code']){
		print $slave_info['branch_code']."<br />";
	}
	
	print "$s<sup>$type</sup> <br /></h1>\n";
	
	if($slave_info['remark']){
		print "<div style='font-weight:bold;'>";
		print $slave_info['remark'];
		print "</div><br />";
	}
	
	
	print "$r[ip] [<a href=\"javascript:void(load('$location'))\">reload</a>]<br>";
	if(!$con->db_connect_id) print("Failed to connect $r[ip]:" . mysql_error());
	else
	{
	    $mysql_v = (sql2arr($con, "select version() as v"));
	    print "MySQL Version: ".$mysql_v['v'];
		//------------------------------
		print "<h3>Master Status</h3>";
		$m = sql2arr($con, "show master status");
		if ($m)
		{
			if (isset($_REQUEST['purge']) && intval($_REQUEST['purge'])==1)
			{
			    // get master file
			    $mm = preg_split("/\./", $m['file']);
			    if ($mm[1] > 1)
			    {
					// do purge
			    	$purge_name = sprintf("%s.%06d", $mm[0], $mm[1]--);
					$con->sql_query("PURGE MASTER LOGS TO '$purge_name';") or die(mysql_error());
				}
				$m = sql2arr($con, "show master status");
			}
			elseif (isset($_REQUEST['restart']) && intval($_REQUEST['restart'])==1)
			{
			    // get master file
				$con->sql_query("STOP SLAVE") or die(mysql_error());
				$con->sql_query("START SLAVE") or die(mysql_error());
				$m = sql2arr($con, "show master status");
			}
			elseif (isset($_REQUEST['skiperr']) && intval($_REQUEST['skiperr'])==1)
			{
			    // get master file
				$con->sql_query("STOP SLAVE") or die(mysql_error());
				$con->sql_query("SET GLOBAL SQL_SLAVE_SKIP_COUNTER=1") or die(mysql_error());
				$con->sql_query("START SLAVE") or die(mysql_error());
				$m = sql2arr($con, "show master status");
			}
		
			print "$m[file], $m[position]<br>";//<a href=\"javascript:purge('$location')\">[Purge]</a>";
		}
		else
		{
			print "Not SQL MASTER";
		}
		
		//------------------------------
		print "<h3>Slave Status</h3>";
		$m = sql2arr($con, "show slave status");
		if ($m)
		{
			//print "<a href=\"javascript:restart('$location')\">[Restart]</a><br>";
			// Last Error
			$last_error = trim($m['last_error']);
			
			// IO Error
			if(!$last_error)	$last_error = trim($m['last_io_error']);
			
			// SQL Error
			if(!$last_error)	$last_error = trim($m['last_sql_error']);
			
			if ($last_error)
			{
				print "<p><font color=red>";
				print "Last Error: $last_error<br>";
				print "</font></p>";
				//print "</font><br><a href=\"javascript:skip('$location')\">[Skip]</a></p>";
			}
		
			print "Exec Master: $m[exec_master_log_pos] ($m[relay_master_log_file])<br>";
			print "Read Master: $m[read_master_log_pos] ($m[master_log_file])<br>";
			print "Master Host: $m[master_host]<br>";
			print "Slave IO State: $m[slave_io_state]<br>";
			if ($m['slave_io_running'] == 'No' or $m['slave_sql_running'] == 'No') print "<font color=red>";
			print "Slave IO Running: $m[slave_io_running]<br>";
			print "Slave SQL Running: $m[slave_sql_running]<br>";
			if ($m['slave_io_running'] == 'No' or $m['slave_sql_running'] == 'No') print "</font>";
			
			print "Skip Counter: $m[skip_counter]<br>";
			if ($m['seconds_behind_master']>60) print "<font color=red>";
			print "Seconds Behind Master: $m[seconds_behind_master]";
			if ($m['seconds_behind_master']>60) print "</font>";
		}
		else
		{
			print "Not SQL SLAVE";
		}
	}
	
	// show disk status
	/*exec("df | grep '^/dev'",$ret);
	print "<h3>Disk Usage:</h3>";
	foreach($ret as $line)
	{
		//print $line;	
		$a = preg_split("/\s+/",$line);
		$a[3]=round($a[3]/1024/1024,1);
		print "<li ".(intval($a[4])>80 ? 'style="color:red"':'')."> $a[0] => $a[5] $a[3]G ($a[4])";
	}
	*/
	exit;
}

?>

<style>
.s {
	width:20%;
	font:10px MS Sans Serif;
	float:left;
	overflow: hidden;
	border:1px solid #999;
	padding:10px;
	margin:5px;
}
</style>

<?php
	if (!$config['single_server_mode'])
	{
		$used_tmp_code = array();
		$server_to_load = array();
		if(isset($config['replica_branch_sequence']) && $config['replica_branch_sequence'] && is_array($config['replica_branch_sequence'])){
			foreach($config['replica_branch_sequence'] as $tmp_code){
				$used_tmp_code[] = ms($tmp_code);
				$r1 = $con->sql_query("select concat('server:',code) as code from branch where code=".ms($tmp_code));
				$r = $con->sql_fetchrow($r1);
				$con->sql_freeresult();
				
				if($r){
					print "<div class=s id=\"$r[code]\">x</div>\n";
					$server_to_load[] = $r['code'];
				}			
			}
		}else{
			$filter = array();
			$filter[] = "branch.active=1";
			if($filter)	$filter = "where ".join(' and ', $filter);
			else	$filter = '';
			
			$r1 = $con->sql_query("select concat('server:',code) as code from branch $filter order by sequence");
			while($r=$con->sql_fetchrow())
			{
				print "<div class=s id=\"$r[code]\">x</div>\n";
				$server_to_load[] = $r['code'];
			}
		}
	}
	else
	{
		print "<div class=s id=\"server:HQ\">x</div>\n";
	}
	$r2 = $con->sql_query("select concat('report:',server_name) as code from report_server where branch_id = (select id from branch where code = ".ms(BRANCH_CODE).")");
	while($r=$con->sql_fetchrow())
	{
		print "<div class=s id=\"$r[code]\">x</div>\n";
	}
	$r3 = $con->sql_query("select concat('slave:',name) as code from sql_slaves");
	while($r=$con->sql_fetchrow())
	{
		print "<div class=s id=\"$r[code]\">x</div>\n";
	} 
?>

<script src=/js/prototype.js> </script>
<script>
function purge(v)
{
	$(v).innerHTML = '<h1><img src=/ui/clock.gif> Purge ' + v + '</h1>';
	new Ajax.Updater(v, "replica_status.php", {parameters:"purge=1&s="+v});
}
function restart(v)
{
	$(v).innerHTML = '<h1><img src=/ui/clock.gif> Resetting Slave ' + v + '</h1>';
	new Ajax.Updater(v, "replica_status.php", {parameters:"restart=1&s="+v});
}
function skip(v)
{
	if (!confirm('WARNING: Skipping may cause data out of sync. Are you sure?')) return;
	$(v).innerHTML = '<h1><img src=/ui/clock.gif> Skip Error and Resetting Slave ' + v + '</h1>';
	new Ajax.Updater(v, "replica_status.php", {parameters:"skiperr=1&s="+v});
}
function load(v)
{
	$(v).innerHTML = '<h1><img src=/ui/clock.gif> ' + v + '</h1>';
	new Ajax.PeriodicalUpdater(v, "replica_status.php", {frequency:30, parameters:"s="+v});

}
<?php
	if (!$config['single_server_mode'])
	{
		/*$con->sql_rowseek(0,$r1);
		while($r=$con->sql_fetchrow($r1))
		{
			print "load('$r[code]');\n";
		}*/
		foreach($server_to_load as $server_code){
			print "load('$server_code');\n";
		}
	}
	else
	{
		print "load('server:HQ');\n";
	}
	$con->sql_rowseek(0,$r2);
	while($r=$con->sql_fetchrow($r2))
	{
		print "load('$r[code]');\n";
	}
	$con->sql_rowseek(0,$r3);
	while($r=$con->sql_fetchrow($r3))
	{
		print "load('$r[code]');\n";
	}
?>
</script>
