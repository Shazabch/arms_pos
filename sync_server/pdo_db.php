<?php
/*
2/1/2012 12:12:18 PM Alex
- created

9/22/2017 4:389 PM Andy
- Added function exec_sql_error.

6/12/2020 1:26 PM Andy
- Added function "rollback".
*/
class pdo_db{
	var $resource_obj;
	var $rows;
	var $num_rows;
	
	function pdo_db($db, $u, $p){
		$this->resource_obj=new PDO($db, $u, $p);
		return $this->resource_obj;
	}
	
	function query($sql, $transaction = FALSE, $die_if_error = TRUE){
		unset($this->rows);
		
		$this->rows=$this->resource_obj->prepare($sql);
		$this->rows->execute();
		if ($die_if_error)	$this->sql_error($this->rows,$sql);
		return $this->rows;
	}
	
	
	function sql_fetchassoc($obj=''){
		$obj = $obj ? $obj : $this->rows;
		if (!$obj)	return false;
		return $obj->fetch(PDO::FETCH_ASSOC);
	}
	
	function sql_numrows($obj=''){
		$obj = $obj ? $obj : $this->rows;
		return $obj->rowCount();
	}
	
	function sql_freeresult(&$obj=''){
		if (!$obj){
			$this->rows->closeCursor();
			unset($this->rows);
		}else{
			$obj->closeCursor();
			unset($obj);
		}
	}
	
	function exec($sql){
		return $this->resource_obj->exec($sql); 
	}
	
	function sql_error($connection,$sql=''){
		$err=$connection->errorInfo();
		if($err[2])	die("\nError:".$sql.". ".$err[2]."\n");	
	}
	
	function exec_sql_error(){
		$err = $this->resource_obj->errorInfo();
		return "Error: ".$err[2];
	}
	
	function beginTransaction(){
		$success=$this->resource_obj->beginTransaction();
		if (!$success)	die("\nError: Begin transaction\n");
	}
	
	function commit(){
		$success=$this->resource_obj->commit();
		if (!$success)	die("\nError: Commit transaction\n");
	}
	
	function rollback(){
		$success=$this->resource_obj->rollBack();
		if (!$success)	die("\nError: Rollback Failed\n");
	}
/*
	function prepare($sql_patt){
		return $this->resource_obj->prepare($sql_patt);
	}

	function execute($p,$sql_data,$die_if_error = TRUE){
		$p->execute($sql_data);
		if ($die_if_error)	$this->sql_error($p);
		return $rows_effected; 
	}
*/
}

?>
