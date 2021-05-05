<?php
/*
3/25/2010 5:15:35 PM Andy
- Add calculation start date parameter for membership points calculation

11/10/2010 3:09:13 PM Justin
- Fixed the wrong delete of membership points when member have more than 1 card histories.

2/10/2012 5:06:43 PM Justin
- Added to update last purchase branch id onto membership table.

2/27/2012 5:08:43 PM Justin
- Added to exclude cancel bill membership card.

6/25/2012 11:47:23 AM Justin
- Updated to calculate points from sub card sum to principal card.

7/19/2012 4:07:34 PM Justin
- Bug fixed to remove the "use_points" which never existed in db.

8/25/2012 4:15pm yinsee
- add include cron.calc_membership_points.extra.php and call extra_point_process() for segi to run 3x birthday points

9/13/2012 4:38 PM Justin
- Enhanced to update points_changed = 0 while finished to calculate the points.

9/14/2012 11:12 AM Andy
- Fix pricipal card cause wrong points for other members.

1/16/2013 11:31 AM Andy
- Enhance cron of point calculation to include calculate staff quota as well.

3/13/2013 3:00 PM Justin
- Enhanced to do recalculate for those members which having empty card history but having outdated points.

9/10/2013 4:40 PM Andy
- Enhance cron to check server process to prevent multiple script run at the same time.

4/21/2014 2:32 PM Justin
- Bug fixed on members without points will not recorded into points history.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

3/27/2019 5:28 PM Andy
- Fixed php7 error when got staff card.

7/2/2019 11:47 AM Andy
- Enhanced to calculate membership favourite item using cron.

8/8/2019 1:03 PM Andy
- Fixed staff quota balance calculation.
*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '1024M');
set_time_limit(0);


/* do these.
create table if not exists tmp_membership_points_trigger (card_no char(20) primary key)
alter table membership add points_update date
alter table tmp_membership_points_trigger convert to charset latin1 collate latin1_general_ci
alter table  membership_points change transaction_time date date, add type enum('POS','REDEEM','ADJUST') default 'ADJUST' 
alter table membership_points drop primary key, add PRIMARY KEY (`branch_id`,`card_no`,`date`,`type`), add index(nric)

CREATE TRIGGER `membership_points_pos_insert_trigger` 
AFTER INSERT ON `pos` 
FOR EACH ROW 
delete from tmp_membership_points_trigger where card_no = NEW.member_no ;

CREATE TRIGGER `membership_points_pos_update_trigger` 
AFTER UPDATE ON `pos` 
FOR EACH ROW 
delete from tmp_membership_points_trigger where card_no = NEW.member_no or card_no = OLD.member_no ;

CREATE TRIGGER `membership_points_pos_delete_trigger` 
AFTER DELETE ON `pos` 
FOR EACH ROW 
delete from tmp_membership_points_trigger where card_no = OLD.member_no ;


membership_points trigger
-------------------------
1. pos inserted - insert record if cancel=0
2. pos update - insert record if cancel 1=>0, delete record if cancel 0=>1
3. pos delete - delete record if cancel = 0


membership.points trigger
-------------------
1. select sum, max timestamp and update membership
*/

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
	@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps x\n";
}else{
	@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps ax\n";
}

if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

$arg = $_SERVER['argv'];
array_shift($arg);
while($a = array_shift($arg))
{
	switch ($a)
	{
		case '-date':
			$from_date = array_shift($arg);
			break;
		default:
			die("Unknown option: $a\n");

	}
}


$year = mi(date("Y"));
$month = mi(date("m"));

$first_day_of_month = date("Y-m-01");
$first_day_of_month_time = strtotime($first_day_of_month);

$last_day_of_month = date("Y-m-").days_of_month($month, $year);
$m_filter = array();
$m_filter2 = array();
$m_filter[] = "mh.remark != 'CB'";
$m_filter2[] = "mh.card_no not in (select t.card_no from tmp_membership_points_trigger t)";

if($config['membership_enable_staff_card']){
	$staff_quota_list = array();
	$staff_filter_str = array();
		
	// get staff quota info
	$q1 = $con->sql_query("select * from mst_staff_quota");
	while($r = $con->sql_fetchassoc($q1)){
		$staff_quota_list[$r['staff_type']] = $r;
	}
	$con->sql_freeresult($q1);
	
	if($staff_quota_list){
		foreach($staff_quota_list as $r){
			$staff_filter_str[] = ms($r['staff_type']);
			
			if(!$r['changed'])	continue;	// check whether got quota changed
			print "mark staff quota changed: ".$r['staff_type']."\n";
			
			// mark those member need to recal point and quota
			$con->sql_query("delete from tmp_membership_points_trigger where card_no in (select mh.card_no 
	from membership_history mh 
	join membership m on m.nric=mh.nric
	where m.staff_type=".ms($r['staff_type']).")");
			$con->sql_query("update mst_staff_quota set changed=0 where staff_type=".ms($r['staff_type']));
		}
		
		$staff_filter_str = join(',', $staff_filter_str);
		$m_filter2[] = "(m.staff_type in ($staff_filter_str) and m.quota_last_update<".ms($first_day_of_month).")";
	}	
}
$m_filter[] = "(".join(' or ', $m_filter2).")";

// find cards that need update
print "Searching cards for update...\n";

$m_filter = join(' and ', $m_filter);

$s1=$con->sql_query($sql = "select distinct mh.nric,mh.card_no,m.dob,m.staff_type
from membership_history mh
join membership m on mh.nric=m.nric
where $m_filter
UNION ALL
select distinct m.nric,m.card_no,m.dob,m.staff_type
from membership m
left join membership_history mh on mh.nric = m.nric
left join tmp_membership_points_trigger tmpt on tmpt.card_no = m.card_no
where mh.card_no is null and tmpt.card_no is null and m.card_no is not null and m.card_no != ''
order by nric, card_no");
//print $sql."\n";
if($from_date){
	$filter_date = " and date>=".ms($from_date);
}

$total_count = $con->sql_numrows($s1);
$curr_count = 0;
// replace into membership_points table by date

@include("cron.calc_membership_points.extra.php");
while($r=$con->sql_fetchassoc($s1))
{
	$curr_count++;
	$lp_branch_id = "";
	$nric = ms($r['nric']);
	$card_no = ms($r['card_no']);
	
	$s2 = $con->sql_query("select $nric as nric,member_no as card_no, branch_id,date,sum(point) as points, sum(quota_used) as quota_used, 'POS' as type, max(pos_time) as max_pos_time, date_format(date,'%m%d') as pos_date, count(pos.id) as pos_count
						   from pos
						   where cancel_status=0 and member_no=$card_no $filter_date 
						   group by member_no,branch_id,date
						   having pos_count > 0 or quota_used<>0"); // using $filter_pos previously
						   
	// clear points usage
	$con->sql_query("delete from membership_points where nric=$nric and card_no = $card_no and type='POS' $filter_date");
	
	// clear quota usage
	if($config['membership_enable_staff_card']){
		$con->sql_query("delete from staff_quota_used_history where nric=$nric and card_no = $card_no and type='POS' $filter_date");
	}
	
	
	while($mmp=$con->sql_fetchassoc($s2))
	{
		// got points
		if($mmp['pos_count']){
			$upd = array();
			$upd['nric'] = $mmp['nric'];
			$upd['card_no'] = $mmp['card_no'];
			$upd['branch_id'] = $mmp['branch_id'];
			$upd['points'] = $mmp['points'];
			$upd['type'] = $mmp['type'];
			$upd['date'] = $mmp['max_pos_time'];
			
			if (function_exists('extra_point_process')) extra_point_process($r,$mmp,$upd);
			
			$con->sql_query("replace into membership_points ".mysql_insert_by_field($upd));

		}
		
		// got use quota
		if($mmp['quota_used'] && $config['membership_enable_staff_card']){
			$upd = array();
			$upd['nric'] = $mmp['nric'];
			$upd['card_no'] = $mmp['card_no'];
			$upd['branch_id'] = $mmp['branch_id'];
			$upd['quota_date'] = $mmp['date'];
			$upd['date'] = $mmp['max_pos_time'];
			$upd['quota_amount'] = $mmp['quota_used']*-1;	// save as negative
			$upd['type'] = $mmp['type'];
			$upd['user_id'] = -1;
			$upd['added_timestamp'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("replace into staff_quota_used_history ".mysql_insert_by_field($upd));
		}
		
		$lp_branch_id = $mmp['branch_id'];	// last purchase branch id
	}
	$con->sql_freeresult($s2);
	
	// get max point info
	$con->sql_query("select max(date) as max_date,sum(points) as all_points from membership_points where nric=$nric");
	$max_point_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$maxtime = ms($max_point_info['max_date']);
	$points = mf($max_point_info['all_points']);
	
	$last_quota_update_time = '';
	if($config['membership_enable_staff_card']){
		// get max quota used info
		$con->sql_query("select max(date) as max_date from staff_quota_used_history where nric=$nric");
		$max_quota_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$last_quota_update_time = $max_quota_info['max_date'];	// last quota used
	}
	
	// got principal card config
	if($config['membership_data_use_custom_field']['principal_card'] && trim($r['nric']) != ''){
		// check either is sub card
		$q1 = $con->sql_query("select m.parent_nric from membership m where m.nric = ".$nric);
		$member_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$tmp_points = 0;
		
		if($member_info['parent_nric']){ // the card is sub card
			$q2 = $con->sql_query("select sum(mp.points) as points from membership_points mp where mp.nric = ".ms($member_info['parent_nric']));
			$parent_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			$con->sql_query("update membership set points=".mf($points+$parent_info['points']).", points_update = ".$maxtime." where nric = ".ms($member_info['parent_nric']));
			
			$points = 0;
		}else{ // check the card whether is principal card for other
			$q1 = $con->sql_query("select sum(mp.points) as points from membership m left join membership_points mp on mp.nric = m.nric where m.parent_nric = ".$nric." group by m.parent_nric");
			
			while($r2 = $con->sql_fetchassoc($q1)){ // it is a principal card for other
				$tmp_points += $r2['points'];
			}
			$points = $points + $tmp_points;
			$con->sql_freeresult($q1);
		}
	}
	
	// Fav Items
	// Get all card no this member used before
	//$card_no_list = $appCore->memberManager->getMemberCardNoList($form['nric']);
	//if($card_no_list){
		$con->sql_begin_transaction();
		//$str_card_no = join(',', array_map('ms', $card_no_list));
		$str_card_no = $card_no;
		
		// Delete Old
		$con->sql_query("delete from membership_fav_items where card_no in ($str_card_no) $filter_date");
		
		// Get The Sales
		$q_fav = $con->sql_query("select p.branch_id, p.member_no as card_no, pi.sku_item_id, p.date as dt,  sum(qty) as qty, sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amount, pi.discount as disc_amt, pi.discount2 as disc_amt2, pi.tax_amount
			from pos_items pi
			left join pos p  on p.branch_id=pi.branch_id and p.counter_id = pi.counter_id and p.date = pi.date and p.id = pi.pos_id
			where p.member_no in ($str_card_no) and p.cancel_status=0 $filter_date
			group by branch_id, card_no, sku_item_id, dt");
			
		while($r_fav = $con->sql_fetchassoc($q_fav)){
			$upd = array();
			$upd['branch_id'] = $r_fav['branch_id'];
			$upd['card_no'] = $r_fav['card_no'];
			$upd['sku_item_id'] = $r_fav['sku_item_id'];
			$upd['date'] = $r_fav['dt'];
			$upd['qty'] = $r_fav['qty'];
			$upd['amount'] = round($r_fav['amount'], 2);
			$upd['disc_amt'] = round($r_fav['disc_amt'], 2);
			$upd['disc_amt2'] = round($r_fav['disc_amt2'], 2);
			$upd['tax_amt'] = round($r_fav['tax_amount'], 2);
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("replace into membership_fav_items ".mysql_insert_by_field($upd));
		}
		$con->sql_freeresult($q_fav);
		$con->sql_commit();
	//}
	
	$extra_upd = array();
	
	// calculate quota
	if($config['membership_enable_staff_card']){
		$remain_quota = 0;
		
		// check this member type got quota or not
		if(isset($staff_quota_list[$r['staff_type']])){
			// get monthly quota
			$monthly_quota = mf($staff_quota_list[$r['staff_type']]['quota_value']);
			
			// get all used quota in this month
			$con->sql_query("select sum(quota_amount) as quota_adj from staff_quota_used_history where nric=$nric and quota_date between ".ms($first_day_of_month)." and ".ms($last_day_of_month));
			
			$quota_adj = mf($con->sql_fetchfield(0));
			$con->sql_freeresult();
			
			// calculate remaining quota
			$remain_quota = $monthly_quota + $quota_adj;	
			
			// remain quota cannot more then monthly quota
			if($remain_quota > $monthly_quota)	$remain_quota = $monthly_quota;
			
			// mark last quota update time as this month, if last purchase time not this month
			if(strtotime($last_quota_update_time) < $first_day_of_month_time)	$last_quota_update_time = $first_day_of_month;
		}

		$extra_upd['quota_balance'] = $remain_quota;
		$extra_upd['quota_last_update'] = $last_quota_update_time;
		
		//print_r($extra_upd);
	}
	$ext_upd = '';
	print "($curr_count/$total_count)$r[nric] $r[card_no]... ";
	print "last-update: $maxtime     total-points: $points\n";
	if($lp_branch_id) $ext_upd = ", lp_branch_id = ".mi($lp_branch_id);
	//else $ext_upd = ", lp_branch_id = ''";
	$extra_upd['last_recalculate_time'] = 'CURRENT_TIMESTAMP';
	if($extra_upd)	$ext_upd .= ", ".mysql_update_by_field($extra_upd);
	
	$con->sql_query("update membership set points_update=$maxtime, points=$points, points_changed=0 $ext_upd where nric=$nric");
	$con->sql_query("replace into tmp_membership_points_trigger values ($card_no)");
}
$con->sql_freeresult($s1);
print "Done.\n";

// 
//560829025580 AK0010126... last-update: '2009-03-16'     total-points: '30'

?>
