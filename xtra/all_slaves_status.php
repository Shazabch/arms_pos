<?
//require("../config.php");
set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors", 1);
//require("../include/smarty.php");
require("../include/mysql.php");

/*$query="select grn_items.branch_id, grn_id, added, grr_item_id, grn.grr_id, type, doc_no, count(*) 
from grn_items 
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id 
left join grr_items on grr_items.id = grr_item_id and  grn_items.branch_id = grr_items.branch_id
where sku_item_id=0 and date(added)>='2007-12-12' group by grn_items.branch_id, grn_id, added";
*/

$query = "show slave status";


function connect_db($server, $u, $p, $db){
	$con = new sql_db($server, $u, $p, $db,false);
	if(!$con->db_connect_id){
		echo date("[H:i:s m.d.y] ");
		echo("<font color=red>Error: Could not connect to database $db@$server</font>\n");
  		echo "<br>";
		return false;
	}
	return $con;
}

$notify = false;
$str = "<h2>Server Status @ " . date("r") . "</h2>";
$errdomain = array();
foreach (split(",", "akadhq,akadbaling,akadgurun,akaddungun,akadkangar,akadjitra,akadtmerah,cwmsku,pktpt,smopm2,smopp,smokkn,smorp,smojth,smokbm,smokbs,smokkrai,smomcg,smopm1,smopm3,smotm,smowcy,agrofresh-jmt") as $site){
	
	$db="armshq";

	$host = "$site.dyndns.org:4001";
	
	if ($site == 'hiwayhq')
	{
		$host= "hq.hiway.com.my:3306";
	}
	else if ($site == 'akadhq')
	{
		$host= "hq.aneka.com.my:3306";
	}
	else if ($site == 'cwmsku')
	{
		$host= "$site.no-ip.info:4001";
	}
	else if (strstr($site, 'smo'))
	{
		$host = "$site.no-ip.org:3306";
	}
	else if ($site == 'pktpt')
	{
    $db="arms_pkt";
    $host = "$site.no-ip.org:4001";
  }
	
	$con = connect_db("$host", 'arms_slave','arms_slave', $db);  
    if(!$con){
    	$notify = false;
    	$str .= "<font color=red>$site is not responding</font><br />";
	$errdomain[] = $site;
      	continue;
    }
	if(!$r1=$con->sql_query($query,false,false)){
		$notify = true;
		$str .= "<font color=red>".strtoupper($site)." ===> ". mysql_error() . "</font><br>";
		$errdomain[] = $site;
		continue;
	}
	$s[$site] = $con->sql_fetchrow();
}
$str .= "<table border=1 cellspacing=0 cellpadding=4><tr><th>Site</th><th>SQL Running</th><th>IO State</th><th>Last Error</th></tr>";

foreach($s as $p=>$r)
{
	$style = '';
	if ($r['Slave_SQL_Running'] != 'Yes' || $r['Last_Error'] != '')
	{
		$style = 'style="color:red"';
		$notify = true;
		$errdomain[] = $p;
		$str .="<tr $style><td>$p</td><td nowrap>$r[Slave_SQL_Running]</td><td nowrap>$r[Slave_IO_State]</td><td>$r[Last_Error]</td></tr>";
	}
}
$str .="</table>";

if (!$notify) { exit; }

print "Send mail: ";

include("class.phpgmailer.php");
$mail = new PHPGMailer;
$mail->Username = 'yinsee@gmail.com';
$mail->Password = 'meow1234';
$mail->FromName     = 'yinsee';
$mail->From = 'yinsee@gmail.com';
$mail->CharSet = 'utf-8';
$mail->AddAddress('tommy@wsatp.com');
$mail->AddAddress('yinsee@wsatp.com');
$mail->AddAddress('cslo@aneka.com.my');
$mail->AddAddress('hoaykheetan@aneka.com.my');
$mail->AddAddress('sllee@aneka.com.my');
$mail->AddAddress('jeff@wsatp.com');
$mail->AddAddress('edward@wsatp.com');
$mail->AddAddress('fadzilan@wsatp.com');
$mail->AddAddress('chongeng@wsatp.com');
$mail->Body    = $str;
$mail->Subject = "SQL Error (".join(",",$errdomain).")";
$mail->IsHTML(1);
/* and now mail it */
print $mail->Send();

?>
