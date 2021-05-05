<?php
/*
auto select report server to use
*/
class mysql_multi extends sql_db {
    
    function mysql_multi() {
    	global $con; 
		$con->sql_query("select * from report_server order by in_use, last_access limit 1",false,false);
		$r=$con->sql_fetchrow();
		if (!$r) { 
			global $db_default_connection;
			$this->sql_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]); 
		}
		else
		{
			// update use = 1
			$con->sql_query("update report_server set in_use=1");
			$this->sql_db($r['server_name'], $r['u'], $r['p'], $r['db']);
		}		
    }
    
    function close_connection()
    {
    	// update use=0
    	global $con;
    	$con->sql_query("update report_server set in_use=0");
	}
	
}

?>
