<?
require("../config.php");
set_time_limit(0);
ini_set("display_errors", 1);
require("../include/smarty.php");
require("../include/mysql.php");

/*$query="select grn_items.branch_id, grn_id, added, grr_item_id, grn.grr_id, type, doc_no, count(*) 
from grn_items 
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id 
left join grr_items on grr_items.id = grr_item_id and  grn_items.branch_id = grr_items.branch_id
where sku_item_id=0 and date(added)>='2007-12-12' group by grn_items.branch_id, grn_id, added";
*/

$query = "show slave status";


function connect_db($server, $u, $p, $db){
	$con = new sql_db($server, $u, $p, $db, true);
	if(!$con->db_connect_id){
		echo date("[H:i:s m.d.y] ");
		echo("<font color=red>Error: Could not connect to database $db@$server</font>\n");
  		echo "<br>";
		return false;
	}
	return $con;
}


foreach (split(",", "akadhq,akadbaling,akadgurun,akaddungun,akadkangar,akadjitra,akadtmerah,hiwayhq,hiwaybs,hiwaybh,cwmhq,pkthq,smarthq,wshq,jwthq,upwell,pktent") as $site){
	$db="armshq";

	$host = "$site.no-ip.org:4001";
	
	if ($site == 'hiwayhq')
	{
		$host= "hq.hiway.com.my:3306";
	}
	else if ($site == 'jwthq')
	{
		$url= "jwt-uni.dyndns.org:4001";
	}
	else if ($site == 'akadhq')
	{
		$host= "hq.aneka.com.my:3306";
	}
	else if($site=='cwmhq'){
		$host= "cwmhq.no-ip.org:4001";
	}
	else if ($site == 'wshq')
	{
		$host= "ws-hq.arms.com.my:4001";
	}
	else if ($site == 'pkthq') {
		$host="hq.12shoppkt.com:3306";
	}
	else if ($site == 'smarthq')
	{
		$host= "smarthq.dyndns.org:4001";
	}
	else if ($site == 'pktent')
	{
		$url= "http://pktent.no-ip.org:4000";
	}
	else if ($site == 'upwell')
	{
		$url= "http://upwell-hq.dyndns.org:4000";
	}
	
	$con = connect_db("$host", 'arms_slave','arms_slave', $db);  
    if(!$con){
      continue;
    }
	if(!$r1=$con->sql_query($query,false,false)){
		echo "<font color=red>".strtoupper($site)." ===> ". mysql_error() . "</font><br>";
		continue;
	}
	
  	echo "<h4>Server : " .strtoupper($site);
	echo " (Rows returned: " . $con->sql_numrows($r1) .")</h4>";
	echo "<table border=1><tr valign=top>";
								
	for ($i = 0; $i < $con->sql_numfields($r1); $i++){
		$k1= $con->sql_fieldname($i);				
		$fieldname[$i]= $k1;
		echo "<td><b>$k1</b></td>";
	}
	echo "</tr>";
	
	while($row = $con->sql_fetchrow($r1)){
		echo "<tr>";
		for ($i = 0; $i < $con->sql_numfields($r1); $i++){
			$v1 = $row[$fieldname[$i]];
			if($i==0){
				$bid=$v1;
			}
			elseif($i==1){
				$grn_id=$v1;
			}
			elseif($i==3){
				$grr_item_id=$v1;
			}
			echo "<td>$v1</td>";
		}

		/*$con->sql_query("update grr_items set grn_used=0 where id=$grr_item_id and branch_id=$bid",false,false);
		$con->sql_query("delete from grn where id=$grn_id and branch_id=$bid",false,false);
		$con->sql_query("delete from grn_items where grn_id=$grn_id and branch_id=$bid",false,false);
		
		echo "<td>update grr_items set grn_used=0 where id=$grr_item_id and branch_id=$bid <br>delete from grn where id=$grn_id and branch_id=$bid<br>delete from grn_items where grn_id=$grn_id and branch_id=$bid</td></tr>";*/
	}
		
	echo "</table>";
}


?>
