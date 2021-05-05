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

if(!defined("SQLITE_LAYER"))
{

define("SQLITE_LAYER","sqlite");


class sqlite_base 
{
	var $db = false;
	var $dbname;
	var $results = array();
	var $rows = array();
	var $query_result;
	var $db_connect_id = false;
	var $die_on_error = true;

	// create our own SQLITE functions
	function init_functions()
	{
	    //print "creating cunctions\n";
	    if (get_class($this->db) == 'PDO')
	    {
	     	$this->db->sqliteCreateFunction('concat', array($this, 'f_concat'));
	     	$this->db->sqliteCreateFunction('char', 'chr', 1);
		    $this->db->sqliteCreateFunction('replace', array($this, 'f_replace'),3);
	    }
	    else
	    {
	     	$this->db->createFunction('concat', array($this, 'f_concat'));
	     	$this->db->createFunction('char', 'chr', 1);
		    $this->db->createFunction('replace', array($this, 'f_replace'),3);
	    }
	}

 	//function __destruct() { print get_class($this)." dead\n"; }

	function f_concat()
	{
		$a = func_get_args();
  		return implode('', $a);
	}
	
	function f_replace($str,$from,$to)
	{
		return str_replace($from,$to,$str);
	}

	function sql_error($query_id = 0)
	{
		return $this->last_error;
	}

   function reset()
	{
		unset($this->rows);
	    unset($this->results);
	    unset($this->fields);
	    unset($this->query_result);
	    unset($this->last_statement);
	    unset($this->row_index);
	    $this->query_count = 0;
	}
	
	function sql_close()
	{
	    $this->reset();
	    unset($this->db);
	}
	
	function _die($msg, $query = '')
	{
	    if (defined('ARMS_LITE')) return;
	    
		global $LANG;
		if ($query) print "<li> $query<br />";
		//die($msg);
		$ff = fopen("sqlite_error.log", "a+");
		fputs($ff, date("[H:i:s m.d.y]")."  ----------\n\n$query\nDatabase: {$this->dbname}\nError: $msg\n\n\n");
		fclose($ff);
		
		if (function_exists('error'))
			error($LANG['DB_SQLITE_QUERY_ERROR']."\n\nDatabase: {$this->dbname}\nError: $msg\n");
		else
			die($msg);
		return;
		//exit;
	}
	
	function cleanup_dot($array)
	{
	    if (!is_array($array)) return $array;
	    foreach($array as $k=>$v)
	    {
			if (preg_match("/^.*\.(.*)$/", $k, $matches))
			{
				if (!isset($array[$matches[1]])) $array[$matches[1]] = $v;
			    unset($array[$k]);
			}
		}
		return $array;
	}
}


class sqlite_db extends sqlite_base
{
	var $fetch_type = PDO::FETCH_BOTH;
	var $prepare_index = 0;
	function __construct($database)
	{
		$this->dbname = $database;
		try {
			$this->db = new PDO("sqlite:".$this->dbname);
		} catch (PDOException $e) {
			if ($this->die_on_error) $this->_die($e->getMessage());
		}
		
		if (!$this->db)
		{
			$this->db_connect_id = false; 	
		}
		else
		{
			$this->db_connect_id = true;
			$this->init_functions();
		}
		$this->query_count = 0;
	}
	// advance PDO statement feature
	function prepare($sql, $options = NULL)
	{
	    if (is_null($options)) 
	    	$stm = $this->db->prepare($sql);
	    else
			$stm = $this->db->prepare($sql, $options);
	    $this->last_statement = $stm;
	    return $stm;
	}
	function execute($data, $stm)
	{
		$stm->execute($data);
		return $stm->rowCount();
	}
	
	function sql_query($query = '')
	{
		$this->query_result = $this->db->query($query);
		$this->last_error = $this->db->errorCode(); //array_pop($this->db->errorInfo());
		
        if ($this->last_error!='00000')
                $this->last_error = array_pop($this->db->errorInfo());
		
		// die on error
		if ($this->last_error!=='00000' && $this->die_on_error)
			$this->_die(array_pop($this->db->errorInfo()), $query);

		if(!preg_match('/^select/i', $query))   return;
		
		$this->query_count++;
		$this->results[$this->query_count] = $this->query_result;
		if ($this->query_result)
		{ 
			$rows = $this->query_result->fetchAll($this->fetch_type);
			//print "<li> got data:".$query;
			foreach($rows as $r)
			{
				$this->rows[$this->query_count][] = $this->cleanup_dot($r); 
			}
			if (isset($this->rows[$this->query_count]) && is_array($this->rows[$this->query_count]))
			{
				foreach(array_keys($this->rows[$this->query_count][0]) as $f)
				{ 
					if (!is_numeric($f)) $this->fields[$this->query_count][] = $f;
				}
			}
			$this->row_index[$this->query_count] = 0;
		}
		
		if ($this->last_error!=='00000') return false; 
		return $this->query_count;
	}
	
	function sql_affectedrows()
	{
		if ($this->query_result)
			return $this->query_result->rowCount();
		else
			return 0;
	}
	
	function sql_numrows($query_id=0)
	{
		if (!$query_id) $query_id = $this->query_count;
		if (!isset($this->rows[$query_id]))  return 0;
		return count($this->rows[$query_id]);
	}
	
	function sql_numfields($query_id=0)
	{
		if (!$query_id) $query_id = $this->query_count;
		if (!isset($this->fields[$query_id]))  return 0;
		return count($this->fields[$query_id]);
	}
	
	function sql_fieldname($offset, $query_id = 0)
	{
		if (!$query_id) $query_id = $this->query_count;
		return $this->fields[$query_id][$offset];
	}

	function sql_fetchrow($query_id=0)
	{
		if (!$query_id) $query_id = $this->query_count;
		
		$idx = $this->row_index[$query_id];
		if (!isset($this->rows[$query_id][$idx])) return false;
		$this->row_index[$query_id]++;
		return $this->rows[$query_id][$idx];
	}
	
	function sql_fetchrowset($query_id=0)
	{
		if (!$query_id) $query_id = $this->query_count;
		
		$this->row_index[$query_id] = 0;
		return $this->rows[$query_id];
	}
	
	function sql_rowseek($rownum, $query_id=0)
	{
		if (!$query_id) $query_id = $this->query_count;
		
		$this->row_index[$query_id] = $rownum;
		return true;
	}
	
	function sql_fetchfield($field, $rownum = -1, $query_id = 0)
	{
		if (!$query_id) $query_id = $this->query_count;
		if ($rownum < 0) $rownum = $this->row_index[$query_id];
		return $this->rows[$query_id][$rownum][$field];
	}	


	function sql_fieldtype($offset, $query_id = 0)
	{
		//if (!$query_id) $query_id = $this->query_count;
		//$x = $this->results[$query_id]->getColumnMeta($offset);
		return '-not implemented-'; //$x['pdo_type'];
	}

	function sql_freeresult($query_id = 0){
		if (!$query_id) $query_id = $this->query_count;
		unset($this->results[$query_id]);
		unset($this->rows[$query_id]);
		unset($this->row_index[$query_id]);
		unset($this->fields[$query_id]);
	    unset($this->query_result);
	    unset($this->last_statement);
	}
	
	function sql_nextid()
	{
		return $this->db->lastInsertId();
	}	
	
	function sql_begin_transaction()
	{
		return $this->db->beginTransaction();
	}
	
	function sql_commit()
	{
		return $this->db->commit();
	}
	
	function sql_rollback()
	{
		return $this->db->rollBack();
	}

 

}
/*
class sqlite2_db extends sqlite_base 
{
	var $fetch_type = SQLITE_BOTH;
	//
	// Constructor
	//
	function __construct($database)
	{
		$this->dbname = $database; 
		
		$this->db = new SQLiteDatabase($this->dbname, 0666, $init_error); // persistent connection
		$this->last_error = $init_error;
		
		if (!$this->db)
		{
			$this->db_connect_id = false; 	
		}
		else
		{
			$this->db_connect_id = true;
			$this->init_functions();
		}
	}


	//
	// Base query method
	//
	function sql_query($query = "", $transaction = FALSE)
	{
		$this->num_queries++;
		$this->query_result = $this->db->query($query, $this->fetch_type, $this->last_error);
		if (!$this->query_result && $this->die_on_error) die("<font color=red>$this->last_error</font><br />$query");
		$this->results[$this->num_queries] = $this->query_result;
		
		return ($this->num_queries);
	}

	//
	// Other query methods
	//
	function sql_numrows($query_id = 0)
	{
		if(!$query_id)
		{
			return $this->query_result->numRows();
		}
		if($query_id)
		{
			return $this->results[$query_id]->numRows();
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
			$result = $this->db->changes();
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
			return $this->query_result->numFields();
		}
		if($query_id)
		{
			return $this->results[$query_id]->numFields();
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
			return $this->query_result->fieldName($offset);
		}
		if($query_id)
		{
			return $this->results[$query_id]->fieldName($offset);
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
			$arr = $this->query_result->fetchColumnTypes();
			return $arr[$offset];
		}
		if($query_id)
		{
			$arr = $this->results[$query_id]->fetchColumnTypes();
			return $arr[$offset];
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
			$this->row[$this->num_queries] = $this->cleanup_dot($this->query_result->fetch());
			return $this->row[$this->num_queries];
		}
		if($query_id)
		{
			$this->row[$query_id] = $this->cleanup_dot($this->results[$query_id]->fetch());
			return $this->row[$query_id]; 
		}
		else
		{
			return false;
		}
	}
	function sql_fetchrowset($query_id = 0)
	{
		$result = array();
		if(!$query_id)
		{
			foreach ($this->query_result->fetchAll($this->fetch_type) as $row)
			{
			    $ret[] = $this->cleanup_dot($row);
			}
			return $ret;
		}
		if($query_id)
		{
			foreach ($this->results[$query_id]->fetchAll($this->fetch_type) as $row)
			{
			    $ret[] = $this->cleanup_dot($row);
			}
			return $ret;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchfield($field, $rownum = -1, $query_id = 0)
	{
		die("dont use this function la....");
	}
	function sql_rowseek($rownum, $query_id = 0){
		if(!$query_id)
		{
			return $this->query_result->seek($rownum);
		}
		if($query_id)
		{
			return $this->results[$query_id]->seek($rownum);
		}
		else
		{
			return false;
		}
	}
	function sql_nextid(){
		if($this->db_connect_id)
		{
			$result = $this->db->lastInsertRowid();
//			$result = @mysql_insert_id($this->db_connect_id);
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
			unset($this->query_result);
			unset($this->results[$this->num_queries]);
			unset($this->row[$this->num_queries]);
		}

		if ( $query_id )
		{
			unset($this->results[$query_id]);
			unset($this->row[$query_id]);
		}
		else
		{
			return false;
		}
	}
} // class sql_db
*/

} // if ... define

?>
