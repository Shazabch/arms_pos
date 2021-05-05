<?
ini_set('memory_limit','256M');
ini_set('display_errors',1);
set_time_limit(0);

include("../include/mysql.php");

$con = new sql_db("localhost", "arms", "793505", "armshq") or die(mysql_error());
if ($_SERVER[argv][1] != '-p' &&  $_SERVER[argv][1] != '-y') die('Invalid option, -p or -y only\n');
$r1=$con->sql_query($_SERVER[argv][2]) or die(mysql_error());
$n=0;
while($r=$con->sql_fetchrow($r1))
{
        $t = xr($r);
        if ($_SERVER[argv][1] == '-p') print_r($t);
        if ($_SERVER[argv][1] == '-y') $con->sql_query("replace into {$_SERVER[argv][3]} values (".join(",",$t).")") or die(mysql_error());
        $n++;
}
print "$n rows updated\n";

function xr($r)
{
        $ret = array();
        for($i=0;$i<count($r)/2;$i++)
        {
                $ret[$i] = ms($r[$i]);
        }
        return $ret;
}

function ms($str,$null_if_empty=0)
{
        if ($str == '' && $null_if_empty) return "null";
        settype($str, 'string');
        $str = str_replace("'", "''", $str);
        return "'" . (trim($str)) . "'";
}
?>
