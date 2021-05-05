<?php
/*
7/26/2012 10:21:34 AM Justin
- Enhanced to use different keys while found config membership_type contains type=>description.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

1/30/2018 4:45 PM Andy
- Remove value pass by reference to avoid deprecated error in ioncube 10.
*/
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

$arg = $_SERVER['argv'];
array_shift($arg);
while($a = array_shift($arg))
{
	switch ($a)
	{
		case '-date':
			$date = array_shift($arg);
			break;
		case '-branch':
			$branch_code = array_shift($arg);
			break;
		default:
			die("Unknown option: $a\n");

	}
}

$filter = $del_filter = array();
if($branch_code){
	$q1=$con->sql_query("select id from branch where code=".ms($branch_code));
	$r1=$con->sql_fetchrow($q1);
	$con->sql_freeresult($q1);
	$branch_id=$r1['id'];
	$filter[] = "pi.branch_id = ".mi($branch_id);
	$del_filter[] = "branch_id = ".mi($branch_id);
}

if(!$date){
	if($config['masterfile_return_policy_expired_days']) $date_deduction = $config['masterfile_return_policy_expired_days'];
	else $date_deduction = "1 month";
	$date = date("Y-m-d", strtotime("-$date_deduction", strtotime(date("Y-m-d"))));
}

$filter[] = "pi.date >= ".ms($date);
$del_filter[] = "date >= ".ms($date);

// delete previous record
print "deleting previous record starts from $date... \n";

$con->sql_query("delete from return_policy_sales_cache where ".join(" and ", $del_filter));

print "deletion process has been done...\n";

// find pos items that contains return policy
print "Searching POS items that contains return policy that haven't return starts from $date...\n";

$pi = $con->sql_query("select pi.*, p.pos_time
					   from pos_items pi
					   join pos p on p.id = pi.pos_id and p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date = pi.date
					   where pi.is_return_policy = 0 and pi.got_return_policy = 1 and p.cancel_status = 0 and ".join(" and ", $filter));

$curr_count = 0;
$curr_time = strtotime();

while($r=$con->sql_fetchrow($pi)){
	$more_info = array();
	$more_info = unserialize($r['more_info']);
	
	if(!isset($more_info['return_policy_detail'])) continue;
	
	//$expiry_durations = $expiry_type = "";
	$member_type = "";
	// is non member
	if(isset($more_info['return_policy_detail']['rp_member_type']['non_member'])){
		//$expiry_durations = $more_info['return_policy_detail']['non_member']['expiry_durations'];
		//$expiry_type = $more_info['return_policy_detail']['non_member']['expiry_type'];
	}else{ // is member
		foreach($config['membership_type'] as $mtype=>$mtype_desc){
			if(is_numeric($mtype)) $mt = $mtype_desc;
			else $mt = $mtype;
			if(isset($more_info['return_policy_detail']['rp_member_type']['member'][$mt]) && $more_info['return_policy_detail']['rp_member_type']['member'][$mt] != "no"){
				$member_type = $mt;
				break;
			}
		}
		if(!$member_type) $member_type = "all";

		//$expiry_durations = $more_info['return_policy_detail']['member'][$member_type]['expiry_durations'];
		//$expiry_type = $more_info['return_policy_detail']['member'][$member_type]['expiry_type'];
	}
	$more_info['item'] = $r;
	
	$rp_info = calculate_rp($more_info, $member_type);
	$curr_count++;

	//if(!$expiry_durations || !$expiry_type) continue; // found if either durations or type is missing, skip the record
	
	if($rp_info['is_expired']){
		$pos_items[$r['branch_id']][$r['date']][$r['sku_item_id']]['expired_count'] += 1;
		$pos_items[$r['branch_id']][$r['date']][$r['sku_item_id']]['charges'] += $rp_info['charges'];
	}else{
		$pos_items[$r['branch_id']][$r['date']][$r['sku_item_id']]['count'] += 1;
		$pos_items[$r['branch_id']][$r['date']][$r['sku_item_id']]['refund'] += $rp_info['refund'];
	}
}

$total_count = count($pos_items);

if($pos_items){
	foreach($pos_items as $bid=>$date_list){
		foreach($date_list as $date=>$si_list){
			print "Processing ".count($pos_items[$bid][$date])." pos items for branch ".get_branch_code($bid)." with return policy from date ".$date."...\n";
			foreach($si_list as $sid=>$f){
				$ins = array();
				$ins['sku_item_id'] = $sid;
				$ins['branch_id'] = $bid;
				$ins['date'] = $date;
				$ins['count'] = $f['count'];
				$ins['refund'] = $f['refund'];
				$ins['expired_count'] = $f['expired_count'];
				$ins['charges'] = $f['charges'];
				$con->sql_query("replace into return_policy_sales_cache ".mysql_insert_by_field($ins));
			}
		}
	}
	print "Done\n";
}else{
	print "Nothing to update!\n";
}

function calculate_rp($more_info, $mt){
	global $config;
	//checking transaction in by member or non member
	if($mt){
		if(isset($more_info['return_policy_detail']['member'][$mt]) && $more_info['return_policy_detail']['member'][$mt]!="no"){
			$rp = $more_info['return_policy_detail']['member'][$mt];
			$more_info['rp_detail']['rp_member_type']['member'] = $mt;
		}
		elseif(isset($more_info['return_policy_detail']['member']["all"]) && $more_info['return_policy_detail']['member']["all"] != "no"){
			$rp = $more_info['return_policy_detail']['member']["all"];
			$more_info['rp_detail']['rp_member_type']['member'] = "all";
		}elseif($more_info['return_policy_detail']['non_member']!="no"){
			$rp = $more_info['return_policy_detail']['non_member'];
			$more_info['rp_detail']['rp_member_type']['non_member'] = "all";
		}else{
			unset($more_info['rp_detail']);
			return;
		}
	}else{
		if($more_info['return_policy_detail']['non_member']!="no"){
			$rp = $more_info['return_policy_detail']['non_member'];
			$more_info['rp_detail']['rp_member_type']['non_member'] = "all";
		}else{
			unset($more_info['rp_detail']);
			return;
		}
	}

	//Durations Condition | Charges Condition: 1=more than, 2=every
	//Expiry Type/durations type: 1=day,2=week,3=month
	$type = array("1"=>"day","2"=>"week","3"=>"month");
	$item_price = ($more_info['item']['price']-$more_info['item']['discount'])/$more_info['item']['qty'];
	$item_pos_time = $more_info['item']['pos_time'];
	$expired_time = strtotime("+ ".$rp['expiry_durations']." ".$type[$rp['expiry_type']], strtotime($item_pos_time));
	$today_time = time();

	$expired = false;
	if($today_time>$expired_time){ // found it is expired
		$expired = true;
		$expired_charges = unserialize($rp['charges']);

		$type_diff = array("1"=>(60*60*24),"2"=>(60*60*24*7),"3"=>(60*60*24*30));
		if($rp['charges_condition']==1){
			foreach($expired_charges as $dinfo)
			{
				$str = "+ ".$dinfo['durations']." ".$type[$dinfo['type']];
				$date = strtotime($str,$expired_date);
				$expired_durations_date[$date]['date'] = date("Y-m-d",$date);
				$expired_durations_date[$date]['type'] = $dinfo['type'];
				$expired_durations_date[$date]['rate'] = $dinfo['rate'];
			}
			krsort($expired_durations_date);

			foreach($expired_durations_date as $dd=>$info)
			{
				if(time()>$dd)
				{
					if(substr($info['rate'], -1) == "%")
					{
						$expired_charge = $item_price*($info['rate']/100);
					}
					else
					{
						$expired_charge = $info['rate'];

					}
					$diff_date = ceil((time() - $expired_date)/$type_diff[$info['type']]);
					$expire_str = date("Y-m-d",$expired_date)." - ".$diff_date." ".$type[$info['type']]."s expired";
					break;
				}
			}
		}else{
			$total = 0;
			$info = array_shift($expired_charges);

			if($info) check_every_date($type,$info,$expired_date,$today_date,$total);

			if(substr($info['rate'], -1) == "%"){
				$expired_charge = $item_price*($info['rate']/100);

			}else{
				$expired_charge = $info['rate'];
			}
			$expired_charge = ($expired_charge*$total);
			if($expired_charge>$rp['max_charges']){
				$expired_charge = $rp['max_charges'];
			}
			$diff_date = ceil((time() - $expired_date)/$type_diff[$info['type']]);
			$expire_str = date("Y-m-d",$expired_date)." - ".$diff_date." ".$type[$info['type']]."s expired";
		}
		$ret['charges'] = round($expired_charge, 2);
	}else{
		$expired = false;
		$durations = unserialize($rp['durations']);

		if($rp['duration_condition']==1)
		{
			foreach($durations as $dinfo)
			{
				$str = "+ ".$dinfo['durations']." ".$type[$dinfo['type']];
				$date = strtotime($str,$item_pos_time);
				$durations_date[$date]['date'] = date("Y-m-d",$date);
				$durations_date[$date]['rate'] = $dinfo['rate'];
			}
			krsort($durations_date);

			foreach($durations_date as $dd=>$info)
			{
				if(time()>$dd)
				{
					if(substr($info['rate'], -1) == "%")
					{
						$deduct_rate = $item_price*($info['rate']/100);
					}
					else
					{
						$deduct_rate = $info['rate'];
					}
					$refund = $item_price-$deduct_rate;
					break;
				}
			}
		}else{
			$total = 0;
			$info = array_shift($durations);
			if($info) check_every_date($type,$info,$item_pos_time,$today_date,$total);
			if(substr($info['rate'], -1) == "%"){
				$deduct_rate = $item_price*($info['rate']/100);

			}else{
				$deduct_rate = $info['rate'];
			}
			$refund = $item_price-($deduct_rate*$total);
		}
		//if($refund<=0) $refund = 0;

		$ret['refund'] = round($refund, 2);
	}
	$ret['is_expired'] = $expired;
	
	return $ret;
}

function check_every_date($type,$info,$datetime,$today_date,&$total){
	$dt = strtotime("+ ".$info['durations']." ".$type[$info['type']],$datetime);

	if($today_date<=$dt){
		$total++;
	}else{
		$total++;
		check_every_date($type,$info,$dt,$today_date, $total);
	}
}

?>
