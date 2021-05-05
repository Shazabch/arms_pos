<?
require("../config.php");
set_time_limit(0);
ini_set("display_errors", 1);
require("../include/smarty.php");
require("../include/mysql.php");

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

$tbl = (isset($_REQUEST['tbl'])) ? stripslashes($_REQUEST['tbl']) : "";
$con = connect_db("maximus", 'arms_slave','arms_slave', 'armshq'); 
$q = $con->sql_query("show tables");
while($r = $con->sql_fetchrow($q)){
	$tb[]=$r[0];
}
//echo"<pre>";print_r($tb);echo"</pre>";
?>
<form method=post name=f>
<b>TABLE : </b>
<select name=tbl id="tbl" onchange="document.f.submit();">
<option value="">== Select Table ==</option>
<? foreach($tb as $k1=>$v1){ ?>
<option value="<? echo $v1; ?>" <? if($tbl==$v1)echo "selected"; ?>><? echo $v1; ?></option>
<? }?>
</select> <input name=btn type=submit><br><br>
</form>
<script>
document.f.tbl.focus();
</script>
<?

if($tbl){
	//wsatp,akadhq,pktpt,hiway,smarthq,citymarthq,cwmhq,ws-hq
	if(preg_match("/^tmp_/", $tbl)){
		$servers="wsatp,akadhq,akadgurun,akadbaling,akaddungun,akadkangar,akadjitra,akadtmerah,hiwayhq,hiwaybs,hiwaybh,cwmhq,pkthq,smarthq,ws-hq,gmark-hq,jwthq"; //citymarthq,
	}
	else{
		$servers="wsatp,akadhq,pkthq,hiwayhq,smarthq,cwmhq,ws-hq,gmark-hq,jwthq"; //citymarthq,
		//$servers="wsatp,cwmhq";
	}
 	echo "<table><tr  valign=top>";
	foreach (split(",", "$servers") as $site){
		$db="armshq";
    	if($site=='wsatp'){
      		$host="maximus"; 
    	}
		else if ($site == 'jwthq')
		{
			$url= "jwt-uni.dyndns.org:4001";
		}
    	else if($site=='akadhq'){
			$host="hq.aneka.com.my:3306";
		}
		else if($site=='hiwayhq'){
			$host="hq.hiway.com.my:3306";
		}
		else if($site=='ws-hq' || $site=='gmark-hq'){
			$host="$site.arms.com.my:4001";
		}
		else if($site=='pkthq'){
			$host="hq.12shoppkt.com:3306";
		}
    	else{
	    	$host="$site.no-ip.org:4001";	    	
		}
	    if($site=='pkthq'){
	   	  	$db="arms_pkt";
	    }
    	$con = connect_db("$host", 'arms_slave','arms_slave', $db);  
	    if(!$con){
	      continue;
	    }
		$query="explain $tbl";
		if(!$r1=$con->sql_query($query,false,false)){
			echo "<font color=red>".strtoupper($site)." ===> ". mysql_error() . "</font><br>";
			continue;
		}
		
  		echo "<td>";			
	  	echo "<h4>Server : " .strtoupper($site);
		echo " (Rows returned: " . $con->sql_numrows($r1) .")</h4>";
		echo "<table border=1 width=500px style='font-size:12px;'><tr valign=top>";
									
		for ($i = 0; $i < $con->sql_numfields($r1); $i++){
			$k1= $con->sql_fieldname($i);				
			$fieldname[$i]= $k1;
			echo "<td><b>$k1</b></td>";
		}
		echo "</tr>";
		
		while($row = $con->sql_fetchrow($r1)){
			$trc  = '';
			if($site=='wsatp'){
				$field_setting[$row['Field']]= serialize($row);
			}
			else
			{
				if ($field_setting[$row['Field']] != serialize($row)) $trc = "bgcolor=red";
			}
			echo "<tr $trc>";
			for ($i = 0; $i < $con->sql_numfields($r1); $i++){
				$v1 = $row[$fieldname[$i]];
				echo "<td>$v1</td>";
			}
			//print "<td>".$field_setting[$row['Field']]."<br>".serialize($row)."</td>";
			echo "</tr>";
		}
		
		$indx=$con->sql_query("show indexes from $tbl");
		echo "<table border=1 width=200px style='font-size:10px;'><tr>";
		for ($i = 0; $i < $con->sql_numfields($indx); $i++){
			$k = $con->sql_fieldname($i);
			$fieldname[$i] = $k;
			echo "<td><b>$k</b></td>";
		}
		echo "</tr>";
		
		while($row = $con->sql_fetchrow($indx)){
			$trc  = '';
			unset($row['Cardinality']);
			unset($row[6]);	
			//echo"<pre>";print_r($row);echo"</pre>";
			if($site=='wsatp'){
				$index_setting[$row['Key_name']][$row['Seq_in_index']] = serialize($row);
			}
			else
			{
				if ($index_setting[$row['Key_name']][$row['Seq_in_index']] != serialize($row)) $trc = "bgcolor=yellow";
			}
			echo "<tr $trc>";
			for ($i = 0; $i < $con->sql_numfields($indx); $i++){
				$v = $row[$fieldname[$i]];
				echo "<td>$v</td>";
			}
			echo "</tr>";
		}
		echo "</table><br>";
		echo "</td>";
		
	}
 	echo "</tr></table>";
}

?>
