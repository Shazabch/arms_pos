<?php
/*
9/23/2013 3:20 PM Andy
- Merge with "cron.membership_points_expire_one_month.php".
- Enhance to calculate and point by principle/sub card, deduct point at principle card.

9/23/2013 5:07 PM Andy
- Enhance to deduct point from first day if found the usage date is 2013 Jun and before.

10/16/2013 11:05 AM Andy
- Enhance to truncate all tmp_point_calculations before start calculation.

5/5/2014 4:58 PM Andy
- Enhance to check the flush point which do not make the point become negative.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

1/3/2020 4:10 PM William
- Enhanced to insert "membership_guid" field for "membership_points" table.
*/
// by yinsee@wsatp.com
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

$expiry_after_mth = 12;
$agrs = $_SERVER['argv'];

switch($agrs[1]){
	case 'create_data':
		create_data();
		exit;
	case 'erase_point':
		erase_point();
		exit;
	case 'run':
		create_data();
		erase_point();
		exit;
	default:
		print "Invalid Action\n";
		exit;
}

die("No Action.\n");

// php cron.membership_point_expire.php create_data
function create_data(){
	global $con;
	
	// create table
	$con->sql_query("create table if not exists tmp_point_calculations (nric char(20), y int, m int, a int, s int, r int, index(nric), primary key (nric, y, m))");	
	$con->sql_query("truncate tmp_point_calculations");
	
	$q1 = $con->sql_query("select nric from membership where (nric<>'' and nric is not null) and (parent_nric='' or parent_nric is null) order by nric");
	//$q1 = $con->sql_query("select nric from membership where nric='831110025612'");
	
	$total_row = $con->sql_numrows($q1);
	$curr_row = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$curr_row++;
		print "\r$curr_row / $total_row . . . $r[nric]...";
		
		$nric = trim($r['nric']);
		if(!$nric)	continue;
		
		
		calc_member_point_usage($r['nric']);
	}
	$con->sql_freeresult($q1);
	
	print "\nDone.\n";
}

function calc_member_point_usage($nric)
{
	global $con, $expiry_after_mth;

	/*
	get +ve points sum up by month and -ve points sum up by month
	*/
	// get increment point
	$data = array();
	$adds = array();
	$subs = array();
	
	$nric_list = array();
	$nric_list[] = ms($nric);
	
	// get all sub card
	$q1 = $con->sql_query("select nric from membership where (nric<>'' and nric is not null) and parent_nric=".ms($nric)." order by nric");
	while($r = $con->sql_fetchassoc($q1)){
		if($tmp_nric = trim($r['nric'])){
			$nric_list[] = ms($tmp_nric);
		}
	}
	$con->sql_freeresult($q1);
	
	$q_a…= $con->sql_query("select year(date) as y, month(date) as m, sum(points) as c from membership_points where nric in (".join(',', $nric_list).") and points > 0 and type <> 'EXPIRED' group by y, m order by y, m");
	while($r = $con->sql_fetchassoc($q_add)){
		$adds[] = $r;
	}
	$con->sql_freeresult($q_add);

	// get decrease point
	$q_sub = $con->sql_query("select year(date) as y, month(date) as m, sum(points) as c from membership_points where nric in (".join(',', $nric_list).")  and points < 0 and type <> 'EXPIRED' group by y, m order by y, m");
	while($r = $con->sql_fetchassoc($q_sub)){
		$subs[] = $r;
	}
	$con->sql_freeresult($q_sub);
	
	// loop to construct monthly span
	for($i=0,$len=count($adds); $i<$len; $i++)
	{
		create_monthly_data($data, $adds[$i], $adds[$i+1], 'add');
	}
	for($i=0,$len=count($subs); $i<$len; $i++)
	{
		create_monthly_data($data, $subs[$i], $subs[$i+1], 'sub');
	}
	unset($adds);
	unset($subs);
	
	// put in the initial remainings
	
	
	if($data){
		// sort year/month
		uksort($data, "sort_data");		
		foreach($data as $y => $ydata){
			 uksort($data[$y], "sort_data");
		}
	
		reset($data);
		$first_y = key($data);

		reset($data[$first_y]);
		$first_m = key($data[$first_y]);
		
		//print "First Y: $first_y, First M: $first_m\n";
		foreach ($data as $y=>$ydata)
		{
			foreach ($ydata as $m=>$v)
			{
				$data[$y][$m]['remain'] = $v['add'];
			}
		}
			//print_r($data);
		// now deduct out the usage
		$sub_count = 0;
		foreach ($data as $y=>$ydata)
		{
			foreach ($ydata as $m=>$v)
			{
				if ($v['sub']<0)
				{				
					// here's where we start
					if($y<=2013 && $m <=6){ // 2013 Jun or before
						$dy = $first_y;
						$dm = $first_m;
					}else{
						// deduct point start from selected entry					
						$dy = $y;
						$dm = $m - $expiry_after_mth;
						if ($dm < 1) { 
							$dm += 12;
							$dy --;
						}
					}
					
					
					$points_to_deduct = -1*$v['sub'];
					while($points_to_deduct>0)
					{
						if ($data[$dy][$dm]['remain'] > $points_to_deduct) // can deduct all
						{
							print "deduct $points_to_deduct from $dy-$dm\n";
							$data[$dy][$dm]['remain'] -= $points_to_deduct;
							$points_to_deduct = 0; 
						}
						else if ($data[$dy][$dm]['remain'] > 0) // can deduct partial
						{
							print "deduct {$data[$dy][$dm]['remain']} from $dy-$dm\n";
							$points_to_deduct -= $data[$dy][$dm]['remain']; 
							$data[$dy][$dm]['remain'] = 0;
						}
						// next month
						$dm++;
						if ($dm>12) { $dm=1; $dy++; }
						
						if ($dy*12+$dm > $y*12+$m) break;
					}				
				}
			}
		}
	}
		
	
	// print the result
	//print_r($data);
	//print "<table>";
	$con->sql_query("delete from tmp_point_calculations where nric=".ms($nric));
	if($data){
		foreach ($data as $y=>$ydata)
		{
			foreach ($ydata as $m=>$v)
			{	
		//		print "<tr><td>$y</td><td>$m</td><td>{$v[add]}</td><td>{$v[sub]}</td><td>{$v[remain]}</tr>";
				$upd = array('nric'=>$nric, 'y'=>$y, 'm'=>$m, 'a'=>$v['add'], 's'=>$v['sub'], 'r'=>$v['remain']);	
				$con->sql_query("insert into tmp_point_calculations ".mysql_insert_by_field($upd));
			}
		}
		//print "</table>";
	}
	
	unset($data);
}


function create_monthly_data(&$data, $from, $to, $type){
	$yy = $from['y']; $mm = $from['m']; $pp = $from['c'];
	$dt_to = $to['y'] * 12 + $to['m'];
	do
	{
		// loop till next year and month
		$data[$yy][$mm][$type] = $pp;
		$mm++; if ($mm==13) { $mm=1; $yy++; }
		$dt = $yy * 12 + $mm;
		$pp=0;
	} while($dt < $dt_to);
}

// php cron.membership_point_expire.php erase_point
function erase_point(){
	global $con, $expiry_after_mth;
	
	// select points from 13 months ago = expired (in Aug 2013, July 2012 points are expired)
	list($dy, $dm) = explode(" ", date("Y m", strtotime("-".mi($expiry_after_mth+1)." month")));
	
	$q1 = $con->sql_query("select t.r, t.nric, mem.card_no, mem.points as curr_points, mem.membership_guid
	from tmp_point_calculations t 
	join membership mem on mem.nric=t.nric
	where t.y=".mi($dy)." and t.m=".mi($dm)." and t.r>0 and (t.nric<>'' and t.nric is not null) and (mem.parent_nric='' or mem.parent_nric is null)");
	$total_row = $con->sql_numrows($q1);
	$curr_row = 0;
	$remark = "$dm/$dy";
	$type = 'EXPIRED';
	$point_source = 'system';
	$bid = 1;
	
	while($r = $con->sql_fetchassoc($q1))
	{
		$curr_row++;
		
		if($r['r']>$r['curr_points']){	// the remove point more than current point
			$r['r'] = $r['curr_points'];
		}
		
		print "\r$curr_row / $total_row. . . erase $r[r] points from $r[nric] $r[card_no]";
		
	 	// delete old expired row
	 	$con->sql_query("delete from membership_points where branch_id=$bid and nric=".ms($r['nric'])." and remark=".ms($remark)." and type=".ms($type)." and point_source=".ms($point_source)." and user_id=1");
	 	
	 	if ($r['r']<=0) continue;
	 	
		$upd = array();
		$upd['membership_guid'] = $r['membership_guid'];
		$upd['nric'] = $r['nric'];
		$upd['card_no'] = $r['card_no'];
		$upd['branch_id'] = $bid;
		$upd['date'] = 'CURRENT_TIMESTAMP';
		$upd['points'] = -1 * $r['r'];
		$upd['remark'] = $remark;
		$upd['type'] = $type;
		$upd['user_id'] = 1;
		$upd['point_source'] = $point_source;
		
	 	$con->sql_query("insert into membership_points ".mysql_insert_by_field($upd));
	 	$con->sql_query("update membership set points_changed=1 where nric = ".ms($r['nric']));
	 	$con->sql_query("delete from tmp_membership_points_trigger where card_no = ".ms($r['card_no']));
	}
	$con->sql_freeresult($q1);
	
	print "\nDone.\n";
}

function sort_data($a, $b){
	if($a == $b)	return 0;
	return $a>$b ? 1 : -1;
}

?>
