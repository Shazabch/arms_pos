<?php
/*
08/01/2016 10:46 Kee Kee
- Enable InterApplication(IA) accounting software in accounting software where "include_special_in_accounting_software_list" config is enabled

09/21/2016 13:39 Kee Kee
- GAF move to first selection

09/28/2016 15:17 PM Kee Kee
-Show a regenerate message when generate accounting export document and found the same date, same export type and same data type already exported

2017-01-04 13:54 PM Kee Kee
- Added "JOB as Branch Code" settings checking

2017-03-17 10:37 Qiu Ying
- Enhanced to block exporting sales if got pos sales not yet finalise

6/20/2017 09:35 AM Qiu Ying
- Bug fixed on warning message shown when "This company is under GST registered" is unticked

11/9/2017 4:56 PM Andy
- Fixed Save Other Accounting Settings bugs.
*/
$path="./export_account/";

function load_account_file($path="",&$accountings,$skip=array()){
	global $config;

	$gst=check_gst_status();
	$Directory = new RecursiveDirectoryIterator($path);
	$Iterator = new RecursiveIteratorIterator($Directory,RecursiveDirectoryIterator::SKIP_DOTS);
	$Regex = new RegexIterator($Iterator, '/(.*)\/(.*)\.php$/i', RecursiveRegexIterator::GET_MATCH);
	
	foreach($Regex as $name => $object){	
		$classname=$object[2];		
		include_once($name);		
		if(defined("$classname::NAME"))
		{			
			$ExportAcc = new $classname('pos');
			$n=$ExportAcc->get_name();
			if(!$gst && $n=="GST Audit File (GAF)") continue;
						
			if(in_array($n,$skip)){
				if(isset($config['include_special_in_accounting_software_list']) && is_array($config['include_special_in_accounting_software_list'])){
					if(!in_array($n, $config['include_special_in_accounting_software_list']))	continue;
				}else{
					continue;
				}				
			}
			$accountings[$n]=$ExportAcc->get_property('pos');
			
			//load_setting($accountings,$n);
		}
	}	
	
	unset($ExportAcc);
	ksort($accountings);
	if(isset($accountings['GST Audit File (GAF)']))
	{
		$arrTemp['GST Audit File (GAF)'] = $accountings['GST Audit File (GAF)'];
		unset($accountings['GST Audit File (GAF)']);
	}
	if($arrTemp)
		$accountings = array_merge($arrTemp,$accountings);
	unset($arrTemp);
}

function load_setting(&$accountings,$FormatType="",$branch_id=1){
	global $con;

	if($branch_id!=1){
		$ret=$con->sql_query("select count(*) from ExportAccSettings where format=".ms($FormatType)." and branch_id=".mi($branch_id));
		$count=$con->sql_fetchfield(0);

		if($count>0) $accountings[$FormatType]['use_own_settings']=1;
		else $branch_id=1;
	}

	$ret=$con->sql_query("select * from ExportAccSettings where format=".ms($FormatType)." and branch_id=".mi($branch_id));

	while($r=$con->sql_fetchassoc($ret)){
		if($r['key']=='financial_date'){
			$accountings[$FormatType]['settings'][$r['key']]['date']=$r['account_code'];
		}
		elseif($r['key']=='job_as_branch_code'){
			$accountings[$FormatType]['settings'][$r['key']]['name']=$r['name'];
			$accountings[$FormatType]['settings'][$r['key']]['data']=$r['setting_value'];
		}
		else{
			if(isset($r['is_other']) && $r['is_other']){
				// other
				$accountings[$FormatType]['settings'][$r['key']]['key']=$r['key'];
				$accountings[$FormatType]['settings'][$r['key']]['data']=$r['setting_value'];
				
			}else{
				// is acount or gst
				$accountings[$FormatType]['settings'][$r['key']]['account']['account_code']=$r['account_code'];
				$accountings[$FormatType]['settings'][$r['key']]['account']['account_name']=$r['account_name'];
				$accountings[$FormatType]['settings'][$r['key']]['custom']=$r['custom'];
				$accountings[$FormatType]['settings'][$r['key']]['key']=$r['key'];
				$accountings[$FormatType]['settings'][$r['key']]['gst']=$r['is_gst'];
			}
		}
	}
}

function search_export_schedule($form){
	global $con;

	$con->sql_query("select * from ExportAccSchedule
					where active=1
					and branch_id=".ms($form['branch_id'])."
					and ((".ms($form['date_from'])." between date_from and date_to) or (".ms($form['date_to'])." between date_from and date_to))
					and export_type=".ms($form['export_type'])."
					and groupby=".ms($form['groupby'])."
					and data_type=".ms($form['data_type']));
 
	return $con->sql_fetchassoc();
}

function create_export_schedule($form){
	global $con;

	$upd=array();
	$upd['user_id']=$form['user_id'];
	$upd['branch_id']=$form['branch_id'];
	$upd['date_from']=$form['date_from'];
	$upd['date_to']=$form['date_to'];
	$upd['export_type']=$form['export_type'];
	$upd['groupby']=$form['groupby'];
	$upd['data_type']=$form['data_type'];
	$upd['status']="Pending";
	$upd['active']=1;

	$con->sql_query("insert into ExportAccSchedule".mysql_insert_by_field($upd));
	$id=$con->sql_nextid();

	update_export_schedule($id,"batchno",generate_batchno($id, $form));

	return $id;
}

function update_export_schedule($id,$field,$value=null){
	global $con, $error_file;

	if(is_array($field)){
		$upd=$field;
	}
	else{
		$upd=array();
		$upd[$field]=$value;
	}
	$ret=$con->sql_query("update ExportAccSchedule set ".mysql_update_by_field($upd)." where id=".mi($id),true,false);
}

function load_export_schedule($id){
	global $con;

	$con->sql_query("select * from ExportAccSchedule where id=".mi($id));

	return $con->sql_fetchassoc();
}

function generate_batchno($id, $form){
	global $con;

	$branch_id=sprintf("%03s",$form['branch_id']);
	$number=sprintf("%05s",$id);
	$batchno=$branch_id."".$number;
	return $batchno;
}

function get_batchno($form){
	global $con;

	$result=array();

	$sql="select * from ExportAccSchedule
		where active=1
		and branch_id=".ms($form['branch_id'])."
		and export_type=".ms($form['export_type'])."
		and groupby=".ms($form['groupby'])."
		and data_type=".ms($form['data_type'])."
		and completed=1
		and ((".ms($form['date_from'])." BETWEEN date_from AND date_to) or (".ms($form['date_to'])." BETWEEN date_from AND date_to))
		order by end_time";
	$ret=$con->sql_query($sql);

	while($r=$con->sql_fetchassoc($ret)){
		$result[]=array('date_from'=>$r['date_from'],'date_to'=>$r['date_to'],'batchno'=>$r['batchno']);
	}

	$result[]=array('date_from'=>$form['date_from'],'date_to'=>$form['date_to'],'batchno'=>$form['batchno']);
	return $result;
}

function check_finalised_sales($branch_id, $date_from, $date_to){
	global $con;
	
	$is_finalised = true;
	$sql=$con->sql_query("select count(*) as num from pos
		left join pos_finalized pf on pos.branch_id = pf.branch_id and pos.date = pf.date
		where pos.branch_id = " . mi($branch_id) . " and pos.date >= " . ms($date_from) . " and pos.date <= " . ms($date_to) . "
		and finalized = 0 and cancel_status = 0");
	$count = $con->sql_fetchrow($sql);
	$con->sql_freeresult($sql);
	if($count["num"] > 0){
		$is_finalised = false;
	}
	unset($count);
	return $is_finalised;
}

?>
