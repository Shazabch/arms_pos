<?php
define("TERMINAL",1);
include("config.php");
$grab = BRANCH_CODE;
//if ($grab == 'HQ') die("no no i won't run in HQ.");

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("include/mysql.php");

while (1)
{
	$con = connect_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3]);

	if (!$con)
	{
	    sleep(10);
	    continue;
	}
//	$con->sql_query("update membership_history set grab_GURUN=0");

	// check renewal
	print date("[H:i:s m.d.y] ");
	$res1 = $con->sql_query("select branch_id, remark, h.nric, h.card_no, h.issue_date as org_isd, DATE_FORMAT(h.issue_date, '%d/%m/%Y') as issue_date, DATE_FORMAT(h.expiry_date, '%d/%m/%Y') as expiry_date, replace(replace(m.name, ' ', '_'),'@','/') as name, m.name as org_name from membership_history as h left join membership as m on h.nric = m.nric where grab_$grab = 0 order by h.issue_date");
	$n = $con->sql_numrows();
	print $n . " new records\n";
	if ($n > 0)
	{
		// running without option will grab the data frmo server
		if (!file_exists("data.new"))
		{
			$ff = fopen("data.new", "a+");
			fputs($ff, "@\n");
		}
		else
		{
			$ff = fopen("data.new", "a+");
		}
		// open file 2 for ATP
		$ff2 = fopen("data_atp.new", "a+");

		if (!$ff || !$ff2)
		{
			@fclose($ff); @fclose($ff2);
			print "Error creating dump file, try again later...";
			sleep(30);
			continue;
		}

		while($r = $con->sql_fetchrow($res1))
		{
			$con->sql_query("update membership_history set grab_$grab=1 where branch_id=$r[branch_id] and remark='$r[remark]' and card_no='$r[card_no]' and nric=".ms($r['nric'])." and issue_date = ".ms($r['org_isd'])) or die(mysql_error());
			/*if ($con->sql_affectedrows()<=0)
			{
			    print_r($r);
				die("update membership_history set grab_$grab=1 where branch_id=$r[branch_id] and remark='$r[remark]' and card_no='$r[card_no]' and nric=".ms($r['nric'])." and issue_date = ".ms($r['org_isd']));
			}*/

			// if cancel bill retrieve the previous one
			if ($r['remark']=='CB')
			{
			    $con->sql_query("select branch_id, remark, h.nric, h.card_no, DATE_FORMAT(h.issue_date, '%d/%m/%Y') as issue_date, DATE_FORMAT(h.expiry_date, '%d/%m/%Y') as expiry_date, replace(replace(m.name, ' ', '_'),'@','/') as name, m.name as org_name from membership_history as h left join membership as m on h.nric = m.nric where h.nric = '$r[nric]' and h.issue_date <= '$r[org_isd]' and remark <> 'CB' order by h.issue_date desc limit 1");

			    if ($con->sql_numrows()>0)
			    {
					$r = $con->sql_fetchrow();
					$r['remark'] = 'N'; // override remark as "new"
			    }
				else
			    	print "- Cancel bill and Delete >> ";
			}
			// if card no changed, pass a "T" to delete the previous card
			/*
		 	if ($r['remark'] == 'L' || $r['remark'] == 'LR' || $r['remark'] == 'C' || $r['remark'] == 'ER')
			{
				$con->sql_query("select branch_id, remark, h.nric, h.card_no, DATE_FORMAT(h.issue_date, '%d/%m/%Y') as issue_date, DATE_FORMAT(h.expiry_date, '%d/%m/%Y') as expiry_date, replace(replace(m.name, ' ', '_'),'@','/') as name, m.name as org_name from membership_history as h left join membership as m on h.nric = m.nric where h.nric = '$r[nric]' and h.issue_date <= '$r[issue_date]' and  order by h.issue_date desc limit 1");
				$r2=$con->sql_fetchrow();
				fprintf($ff2, "%-4s%-20s%-15s%-15s%-15s%-80s\n", 'T', $r2['nric'], $r2['card_no'], $r2['issue_date'], $r2['expiry_date'], $r2['org_name']); // atp use org_name
			}
			*/
			
			fprintf($ff, "%s %s %s %s %s %s @\n", $r['remark'], $r['nric'], $r['card_no'], $r['issue_date'], $r['expiry_date'], $r['name']);
			fprintf($ff2, "%-4s%-20s%-15s%-15s%-15s%-80s\n", $r['remark'], $r['nric'], $r['card_no'], $r['issue_date'], $r['expiry_date'], $r['org_name']); // atp use org_name
			printf("%s %s %s %s %s %s @\n", $r['remark'], $r['nric'], $r['card_no'], $r['issue_date'], $r['expiry_date'], $r['name']); // multics use name

			
		}
		fclose($ff);
		fclose($ff2);
		
		exit;
	}
	sleep(30);
}

function connect_db($server, $u, $p, $db)
{
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id)
	{
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		return false;
	}
	return $con;
}

function ms($str,$null_if_empty=0)
{
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	return "'" . (trim($str)) . "'";
}
?>
