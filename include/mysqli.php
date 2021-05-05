<?php
/*
7/15/2016 11:02 AM Andy
- Enhanced to record dbname.

8/1/2016 10:09 AM Andy
- Fix mysql gone away auto reconnect bug.

5/30/2018 1:45 PM Andy
- Fixed mysqli class redeclare problem.

6/14/2018 2:38 PM Andy
- Add new mysql function sql_begin_transaction(), sql_commit() and sql_rollback().

7/25/2019 3:03 PM Andy
- Enhanced to check alternative_port for sock2. (fixes for mysql 5.7)
- replace $sqlserver from localhost to 127.0.0.1. (fixes for mysql 5.7)

1/31/2020 5:41 PM Andy
- Changed mysql_multi to connect using config.report_server

2/3/2021 5:32 PM Andy
- Added params $skip_error when connect db.
*/
ini_set("display_errors", 1);
// use mysqli if we have
if (!function_exists('mysql_error')){
	function mysql_error()
	{
		global $con;			
		return $con->error;
	}
}


if(!defined("SQL_LAYER"))
{
	define("SQL_LAYER","mysqli");
	
	class sql_db extends mysqli
	{

		var $query_result;
		var $num_queries = 0;
		var $db_connect_id = 1;
		var $error_log = array();
		var $have_second_chance = true;
		
		var $server;
		var $user;
		var $password;
		var $dbname;
		
		//
		// Constructor
		//
		function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $skip_error = true)
		{
			$this->init();
			$sqlport = null;
			$socket = null;
			$dbname = null;
			$this->server = $sqlserver;
			$this->user = $sqluser;
			$this->password = $sqlpassword;
			
			if(strpos($sqlserver, "mysql.sock") > 0){
				$socket = $sqlserver;
				//$sqlserver = "localhost";
				$sqlserver = "127.0.0.1";
				list($dummy, $socket, $alternative_port) = @explode(":", $socket);
				//$socket = "/tmp/mysql.sock2";
				if($alternative_port)	$sqlport = $alternative_port;
			}else{
				if(strpos($sqlserver,":")>0){
					list($sqlserver,$sqlport) = explode(":", $sqlserver);
				}
				if (!$sqlport) $sqlport = 3306;
			}
			
			$success = $this->real_connect($sqlserver, $sqluser, $sqlpassword, $database, $sqlport, $socket, MYSQLI_CLIENT_COMPRESS);
			
			if (!$success) {
				if(!$skip_error){
					printf("Connect failed: %s\n", mysqli_connect_error());
				}				
				$this->db_connect_id=0;
				return false;
			}
			$this->db_connect_id = $this->thread_id;
			$this->dbname = $database;
			
			return true;
		}

		//
		// Other base methods
		//
		function sql_close()
		{
			$this->close();
		}

		//
		// Base query method
		//
		function sql_query_skip_logbin($query = "", $transaction = FALSE, $die_if_error = TRUE)
		{
			@$this->query("set sql_log_bin=0");
			$ret = $this->sql_query($query, $transaction, $die_if_error);
			@$this->query("set sql_log_bin=1");
			return $ret;
		}
		
		//
		// Base query method
		//
		function sql_query($query = "", $transaction = FALSE, $die_if_error = TRUE)
		{
			global $query_count, $query_time;
			$query_count++;
			
			unset($this->query_result);
			if($query != "")
			{
				// additional fix on create table statement
				if ((preg_match('/^create table/i', $query) || preg_match('/^create temporary table/i', $query)) && !preg_match('/COLLATE=latin1_general_ci/i', $query) && !preg_match('/select /i', $query) && !preg_match('/ like /i', $query)){
					$query .= " ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
				}
				
				$this->num_queries++;
				if (defined('TERMINAL')){
					$this->query_result = @$this->query($query);
				}else{
					if (function_exists('getmicrotime')) $time_start = getmicrotime();
					$this->query_result = @$this->query($query);
					if (function_exists('getmicrotime')) {
						$time_end = getmicrotime();
						global $sessioninfo;
						if ($sessioninfo['level']>=9999 || $_SESSION['admin_session']) $query_time .= ($time_end - $time_start) . " - $query\n";
						if (!defined('TERMINAL') && $time_end - $time_start > 5)
						{
								$ff = fopen("$_SERVER[DOCUMENT_ROOT]/sql_explain.log", "a+");
								fputs($ff, date("H:i:s"). "@$this->server $_SERVER[REMOTE_ADDR] (".($time_end - $time_start)."s) $_SERVER[REQUEST_URI]\n$query\n");
								fputs($ff, "\n\n");
								fclose($ff);
						}
					}
				}
				
			}
			if($this->query_result)
			{
				$this->have_second_chance = true;
				return $this->query_result;
			}
			else
			{
				$error_msg = $this->error;
				// check have second chances
				if($this->have_second_chance){
					if(strpos($error_msg, 'MySQL server has gone away')!==false){
						$this->have_second_chance = false;  // must mark no more second chance to reconnect, else will cause infinity loop
						// close the connection first, if no close cannot reconnect
						$this->sql_close();
						// reconnect
						$this->sql_db($this->server, $this->user, $this->password, $this->dbname);
						return $this->sql_query($query, $transaction, $die_if_error);
						
					}
				}
				
				if ($die_if_error)
				{
					//print("<script>alert('".mysql_error()."')</script>");
					die("<li> Error: $query<br>" . $error_msg);
				}
				else
				{
					//print("<!-- Error: $query<br>" . mysql_error($this->db_connect_id) ."-->");
				}
				return ( $transaction == END_TRANSACTION ) ? true : false;
			}
		}

		//
		// Other query methods
		//
		function sql_numrows($result = 0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				return $result->num_rows;
			}
			else
			{
				return false;
			}
		}
		function sql_affectedrows()
		{
			return $this->affected_rows;
		}
		function sql_numfields($result= 0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				return $result->field_count;
			}
			else
			{
				return false;
			}
		}
		function sql_fieldname($offset, $result = 0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				return $result->fetch_field_direct($offset)->name;
			}
			else
			{
				return false;
			}
		}
		function sql_fieldtype($offset, $result = 0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				return $result->fetch_field_direct($offset)->type;
			}
			else
			{
				return false;
			}
		}
		function sql_fetchrow($result = 0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				return @$result->fetch_array();
			}
			else
			{
				return false;
			}
		}
		
		function sql_fetchfield($field, $rownum = -1, $result = 0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				if($rownum > -1)
				{
					$result->data_seek($rownum);
					$row = @$this->sql_fetchrow($result);
					return $row[$field];
				}
				else
				{
					// always look for the first row only
					$result->data_seek(0);
					$row = @$this->sql_fetchrow($result);
					return $row[$field];
				}
			}
			else
			{
				return false;
			}
		}

		function sql_fetchrowset($result=0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				$ret = array();
				while($r = @$result->fetch_array())
				{
					$ret[] = $r;
				}
				return $ret;
			}
			else
			{
				return false;
			}
		}

		function sql_rowseek($rownum, $result = 0){
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				return $result->data_seek($rownum);
			}
			else
			{
				return false;
			}
		}
		
		function sql_nextid(){
			return $this->insert_id;
		}
		
		function sql_freeresult($result = 0){
			if(!$result)
			{
				$result = $this->query_result;
			}

			if (is_object($result))
			{
				@$result->free();
				return true;
			}
			else
			{
				return false;
			}
		}
		function sql_error($query_id = 0)
		{
			$result["message"] = $this->error;
			$result["code"] = $this->errno;

			return $result;
		}
		
		function sql_query_false($query = "", $show_error_and_stop = false){
			$query_id = $this->sql_query($query,false,false);
			if(!$query_id){
				$err = $this->sql_error();
				$this->error_log[] = "MySQL Error Code ".$err['code'].": ".$err['message'];
				
				if($show_error_and_stop)  $this->display_error(); // force stop and display error
			}
			return $query_id;
		}
		
		function sql_fetchassoc($result = 0)
		{
			if(!$result)
			{
				$result = $this->query_result;
			}
			if($result)
			{
				return $result->fetch_assoc();
			}
			else
			{
				return false;
			}
		}
		
		function display_error(){
			global $smarty;
			
			if(!$this->error_log)   return false;   // nothing to display
			$smarty->assign('PAGE_TITLE', 'MySQL Error');
			$smarty->display('header.tpl');
			print join("<br>" , $this->error_log);
			$smarty->display('footer.tpl');
			exit;
		}

		function sql_begin_transaction()
		{
			return $this->sql_query("BEGIN");
		}
		
		function sql_commit()
		{
			return $this->sql_query("COMMIT");
		}
		
		function sql_rollback()
		{
			return $this->sql_query("ROLLBACK");
		}
	} // class sql_db
			
	/*
	auto select report server to use
	*/
	class mysql_multi extends sql_db {
		
		var $branch_id;
		var $server_name;
		
		function mysql_multi() {
			global $con, $config; 
			$result = false;
			
			if(isset($config['report_server']) && is_array($config['report_server']) && $config['report_server']){
				// Got New Report Server
				$host = trim($config['report_server']['host']);
				$u = trim($config['report_server']['u']);
				$p = trim($config['report_server']['p']);
				$db = trim($config['report_server']['db']);
				
				$result=$this->sql_db($host, $u, $p, $db, false, MYSQL_CLIENT_COMPRESS);
				
				if($result){
					$this->branch_id =1;
					$this->server_name = $host;
					//print "Connected to $host";
				}
			}
			/*$con->sql_query("CREATE TABLE IF NOT EXISTS `report_server` ( `server_name` char(50) NOT NULL, `branch_id` int(11) default NULL, `last_access` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, `in_use` tinyint(1) default '0', `u` char(20) default 'arms_slave', `p` char(20) default 'arms_slave', `db` char(20) default 'armshq', UNIQUE KEY `server_name` (`server_name`) ) ",false,false);
			
			
			$con->sql_query("select report_server.* from report_server left join branch on branch_id = branch.id where branch.code = '".BRANCH_CODE."' order by in_use, last_access",false,false);
			
			while ($r=$con->sql_fetchrow()){
				//echo "start with = $r[server_name] $r[in_use] $r[last_access]\n";
				$result=$this->sql_db($r['server_name'], $r['u'], $r['p'], $r['db'],false, MYSQL_CLIENT_COMPRESS);
				
				if(!$result){
					//echo "<!-- $r[server_name]=$result (Reason:".mysql_error().") -->";
					//echo "$r[server_name]=$result (Reason:".mysql_error().")";
					continue;
				}
				else {
					$this->branch_id = $r['branch_id'];
					$this->server_name = $r['server_name'];
					// echo "$r[server_name]=$result<br>";
					$con->sql_query("update report_server set in_use=1, last_access=now() where server_name='$r[server_name]'",false,false);
					break;				
				}				
			}*/
			
			if (!$result)
			{ 
				global $db_default_connection;
				// must use non persistent otherwise the new connection become invalid
				$this->sql_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3], false, MYSQL_CLIENT_COMPRESS);
			}
		}
		
		function close_connection(){
			// update use=0
			//global $con;
			//$con->sql_query("update report_server set in_use=0 where server_name=".ms($this->server_name)." and branch_id = ".ms($this->branch_id));
			//$this->sql_close(); // terminate connection with report server
		}
		
	}// close the mysql_multi if
}
?>