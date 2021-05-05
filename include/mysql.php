<?php
/***************************************************************************
 *                                 mysql.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: mysql.php,v 1.16 2002/03/19 01:07:36 psotfx Exp $
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/*
9/29/2010 3:49:56 PM Andy
- Add checking if got error "MySQL server has gone away", try to reconnect and send the query again.
- Add SQL persistency mode checking, make it can automatically try another persistency mode if given persistency mode was failed to connect.

4/4/2011 2:30:07 PM Andy
- Add assign PAGE_TITLE in function display_error()

5/23/2011 1:30:50 PM Andy
- Add sql_query_skip_logbin() to skip those query no need to replica.

6/1/2011 12:37 AM Yinsee
- fix missing assign for ... if(!$this->db_connect_id) $this->db_connect_id = ...  

6/1/2011 9:56:16 AM Andy
- Change when call mysql_connect it will always open new link. 

8/3/2012 10:08 AM Andy
- Append "ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci" into create table statement if not found the charset related word.

10/29/2012 9:37 AM Justin
- Added new checking "like" during append "ENGINE=MYISAM...".

8/23/2013 2:21 PM Justin'
- Added new checking to allow "create temporary table" to change the table structure.

2/11/2015 2:22 PM Andy
- Enhance to record and show SQL log when using login as.

4/6/2016 3:30 PM Andy
- Change to cast query_id to integer, fix php 5.5 warning.

6/14/2018 2:38 PM Andy
- Add new mysql function sql_begin_transaction(), sql_commit() and sql_rollback().

1/31/2020 5:41 PM Andy
- Changed mysql_multi to connect using config.report_server
*/
if(!defined("SQL_LAYER"))
{

define("SQL_LAYER","mysql");

class sql_db
{

	var $db_connect_id;
	var $query_result;
	var $row = array();
	var $rowset = array();
	var $num_queries = 0;
	var $error_log = array();
	var $have_second_chance = true;
	
	function __destruct()
	{
	    @mysql_close($this->db_connect_id);
	}
	//
	// Constructor
	//
	function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true, $client_flag = NULL)
	{

		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->password = $sqlpassword;
		$this->server = $sqlserver;
		$this->dbname = $database;
		$this->client_flag = $client_flag;
		
		if($this->persistency)
		{
		    $connect_method1 = "mysql_pconnect";
		    $connect_method2 = "mysql_connect";
			//$this->db_connect_id = @mysql_pconnect($this->server, $this->user, $this->password, NULL, $client_flag);
		}
		else
		{
		    $connect_method1 = "mysql_connect";
		    $connect_method2 = "mysql_pconnect";
			//$this->db_connect_id = @mysql_connect($this->server, $this->user, $this->password, NULL, $client_flag);
		}
		
		$this->db_connect_id = @$connect_method1($this->server, $this->user, $this->password, true, $client_flag);
		// if the passed persistency mode failed to connect, try another mode
		if(!$this->db_connect_id) $this->db_connect_id = @$connect_method2($this->server, $this->user, $this->password, true, $client_flag);
		
		if($this->db_connect_id)
		{
			if($database != "")
			{
				$this->dbname = $database;
				$dbselect = @mysql_select_db($this->dbname);
				if(!$dbselect)
				{
					@mysql_close($this->db_connect_id);
					$this->db_connect_id = $dbselect;
				}
			}
			return $this->db_connect_id;
		}
		else
		{
			return false;
		}
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if($this->db_connect_id)
		{
			if($this->query_result)
			{
				@mysql_free_result($this->query_result);
			}
			$result = @mysql_close($this->db_connect_id);
			return $result;
		}
		else
		{
			return false;
		}
	}

	//
	// Base query method
	//
	function sql_query_skip_logbin($query = "", $transaction = FALSE, $die_if_error = TRUE)
	{
		@mysql_query("set sql_log_bin=0", $this->db_connect_id);
		$ret = $this->sql_query($query, $transaction, $die_if_error);
		@mysql_query("set sql_log_bin=1", $this->db_connect_id);
		return $ret;
	}
	
	function sql_query($query = "", $transaction = FALSE, $die_if_error = TRUE)
	{
		global $query_count, $query_time;
		$query_count++;
		// Remove any pre-existing queries
		/*if (preg_match('/select/i', $query))
		{
			$ff = fopen("sql_explain.log", "a+");
			fputs($ff, "$query\n");
			$k = mysql_fetch_array(mysql_query("explain " . $query, $this->db_connect_id));
			foreach ($k as $h=>$v)
			{
				if (!is_numeric($h)) fputs($ff, "$h: $v\n");
			}
			fputs($ff, "\n\n");
			fclose($ff);
		}*/
		unset($this->query_result);
		if($query != "")
		{
			// additional fix on create table statement
			if ((preg_match('/^create table/i', $query) || preg_match('/^create temporary table/i', $query)) && !preg_match('/COLLATE=latin1_general_ci/i', $query) && !preg_match('/select /i', $query) && !preg_match('/ like /i', $query)){
				$query .= " ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
			}
			
			$this->num_queries++;
			if (defined('TERMINAL'))
				$this->query_result = mysql_query($query, $this->db_connect_id);
			else
			{
				if (function_exists('getmicrotime')) $time_start = getmicrotime();
				$this->query_result = @mysql_query($query, $this->db_connect_id);
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
			unset($this->row[$this->query_result]);
			unset($this->rowset[$this->query_result]);
			return $this->query_result;
		}
		else
		{
			$error_msg = mysql_error($this->db_connect_id);
		    // check have second chances
		    if($this->have_second_chance){
                if(strpos($error_msg, 'MySQL server has gone away')!==false){
                    $this->have_second_chance = false;  // must mark no more second chance to reconnect, else will cause infinity loop
					// close the connection first, if no close cannot reconnect
					@mysql_close($this->db_connect_id);
					// reconnect
                    $this->sql_db($this->server, $this->user, $this->password, $this->dbname, $this->persistency, $this->client_flag);
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
	function sql_numrows($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysql_num_rows($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_affectedrows()
	{
		if($this->db_connect_id)
		{
			$result = @mysql_affected_rows($this->db_connect_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_numfields($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysql_num_fields($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fieldname($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysql_field_name($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fieldtype($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysql_field_type($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchrow($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$qid = (int)$query_id;
			$this->row[$qid] = @mysql_fetch_array($query_id);
			return $this->row[$qid];
		}
		else
		{
			return false;
		}
	}
	function sql_fetchrowset($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$qid = (int)$query_id;
			unset($this->rowset[$qid]);
			unset($this->row[$qid]);
			while($this->rowset[$qid] = @mysql_fetch_array($query_id))
			{
				$result[] = $this->rowset[$qid];
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchfield($field, $rownum = -1, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$qid = (int)$query_id;
			if($rownum > -1)
			{
				$result = @mysql_result($query_id, $rownum, $field);
			}
			else
			{
				if(empty($this->row[$qid]) && empty($this->rowset[$qid]))
				{
					if($this->sql_fetchrow())
					{
						$result = $this->row[$qid][$field];
					}
				}
				else
				{
					if($this->rowset[$qid])
					{
						$result = $this->rowset[$qid][$field];
					}
					else if($this->row[$qid])
					{
						$result = $this->row[$qid][$field];
					}
				}
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_rowseek($rownum, $query_id = 0){
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysql_data_seek($query_id, $rownum);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_nextid(){
		if($this->db_connect_id)
		{
			$result = @mysql_insert_id($this->db_connect_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_freeresult($query_id = 0){
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ( $query_id )
		{
			$qid = (int)$query_id;
			unset($this->row[$qid]);
			unset($this->rowset[$qid]);

			@mysql_free_result($query_id);

			return true;
		}
		else
		{
			return false;
		}
	}
	function sql_error($query_id = 0)
	{
		$result["message"] = @mysql_error($this->db_connect_id);
		$result["code"] = @mysql_errno($this->db_connect_id);

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

	function sql_fetchassoc($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$qid = (int)$query_id;
			$this->row[$qid] = @mysql_fetch_assoc($query_id);
			return $this->row[$qid];
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
	    	global $con;
	    	$con->sql_query("update report_server set in_use=0 where server_name=".ms($this->server_name)." and branch_id = ".ms($this->branch_id));
	    	//$this->sql_close(); // terminate connection with report server
		}
		
	}// close the mysql_multi if

} // close the if ... define

?>
