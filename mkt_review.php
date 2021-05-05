<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT_REVIEW_VIEW') and !privilege('MKT_REVIEW_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_REVIEW_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Annual Planner and Review');

$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT_REVIEW_%'");
while ($r = $con->sql_fetchrow()){
	$mkt_review_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt_review_privilege", $mkt_review_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

$branch_id =get_request_branch();
if(!$branch_id){
	$branch_id=$sessioninfo['branch_id'];
}

if ($sessioninfo['level'] < 9999)
{
	if (!$sessioninfo['departments'])
		$depts = "category.id in (0)";
	else
		$depts = "category.id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else
{
	$depts = 1;
}	

if (isset($_REQUEST['a'])){

	switch($_REQUEST['a']){

		case 'update_sales':
			set_time_limit(0);
			ini_set("memory_limit", "64M");
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$month=mi($_REQUEST['month']);
			$year=mi($_REQUEST['year']);
			get_real_sales($branch_id,$year,$month);	
			break;
	    
		case 'ajax_get_category':
 	    	//echo"<pre>";print_r($_REQUEST);echo"</pre>";
        	$id=intval($_REQUEST['id']);
  			get_detail();
  			$con->sql_query("select * from category where id=$id order by id");
    		$smarty->assign("cat",$con->sql_fetchrow());
  			$con->sql_query("select * from category where root_id=$id and $depts order by id");
  			$smarty->assign("subcat",$con->sql_fetchrowset());
  			$smarty->display("mkt_review.sheet.tpl");
	    	exit;
	
	    case 'save_edit':
	    	//echo"<pre>";print_r($_REQUEST);echo"</pre>";
	    	$form=$_REQUEST;
	    	$day=intval($form['day']);
	    	$month=intval($form['month']);
	    	$year=intval($form['year']);
	    	if($day==0){
	    		$day="00";
				$date="$year-$month-$day";
			}
			else{
				$date="$day/$month/$year";
				$date=dmy_to_sqldate($date);
			}
	    	$form['date']=$date;
	    	$form['branch_id']=$branch_id;
     		$form['category_id']=intval($form['cat_id']);
     		$form['cat_root_id']=intval($form['id']);
	    	$field=$form['field'];
	    	$value=$form['value'];
	    	$form[$field]=$value;
	    	
	    	if($field=='total_target'){
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r = $con->sql_fetchrow();
	    		if($r){
           			$con->sql_query("update mkt_review set $field=$value where month='$month' and year='$year' and branch_id=$branch_id");
				}
				else{
					$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id',$field)));
				}
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r2 = $con->sql_fetchrow();
			}
			
			elseif($field=='line_contribute'){

	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r = $con->sql_fetchrow();
	    		if($r){
					$line=unserialize($r['line_contribute']);

					$line[$form['cat_root_id']] = $value;

					$value=sz($line);
           			$con->sql_query("update mkt_review set $field=$value where month='$month' and year='$year' and branch_id=$branch_id");
				}
				else{
				    $form['line_contribute']=serialize($value);
					$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id','line_contribute')));
				}
	    		$r2[$field]="$form[value]";
			}
			
			elseif($field=='total_contribute' or $field=='sales_amount'){
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r = $con->sql_fetchrow();
	    		if($r){
	    			$con->sql_query("select * from mkt_review_contribute where date='$date' and branch_id=$branch_id and category_id=".intval($form['category_id'])." and cat_root_id=".intval($form['cat_root_id'])."");
	    			$r3 = $con->sql_fetchrow();
	    			if(!$r3){
						$con->sql_query("insert into mkt_review_contribute ".mysql_insert_by_field($form,array('date','branch_id','cat_root_id','category_id',$field)));
					}
					else{
						$con->sql_query("update mkt_review_contribute set $field=$value where date='$date' and branch_id=$branch_id and category_id=".intval($form['category_id'])." and cat_root_id=".intval($form['cat_root_id'])."");
					}
				}
				else{
					$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id')));
					$con->sql_query("insert into mkt_review_contribute ".mysql_insert_by_field($form,array('date','branch_id','cat_root_id','category_id',$field)));
				}

	    		$con->sql_query("select * from mkt_review_contribute where date='$date' and branch_id=$branch_id and category_id=".intval($form['category_id'])." and cat_root_id=".intval($form['cat_root_id'])."");
	    		$r2 = $con->sql_fetchrow();
			}
			
			else{
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r = $con->sql_fetchrow();
	    		if($r){
	    			$form['mkt_review_id'] = $r[id];
	    			$q1=$con->sql_query("select * from mkt_review_items where date='$date' and branch_id=$branch_id");
	    			$r3 = $con->sql_fetchrow($q1);
	    			if(!$r3){
						if($field=='normal_forecast'){
						    $field2='sales_target';
	    					$form[$field2]=$value;
							$con->sql_query("insert into mkt_review_items ".mysql_insert_by_field($form,array('date','branch_id',$field,$field2)));
						}
						else{
								$con->sql_query("insert into mkt_review_items ".mysql_insert_by_field($form,array('date','branch_id',$field)));
						}

					}
					else{
						if($field=='normal_forecast'){
						    if(!$r3['sales_target']){
							    $field2='sales_target';
								$con->sql_query("update mkt_review_items set $field=$value,$field2=$value where date='$date' and branch_id='$branch_id'");
							}
							else{
								$con->sql_query("update mkt_review_items set $field=$value where date='$date' and branch_id='$branch_id'");
							}
						}
						else{
							$con->sql_query("update mkt_review_items set $field=$value where date='$date' and branch_id='$branch_id'");
						}
					}
				}
				else{
					$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id')));
					$form['mkt_review_id'] = $con->sql_nextid();
					if($field=='normal_forecast'){
					    $field2='sales_target';
    					$form[$field2]=$value;
						$con->sql_query("insert into mkt_review_items ".mysql_insert_by_field($form,array('date','branch_id',$field,$field2)));
					}
					else{
						$con->sql_query("insert into mkt_review_items ".mysql_insert_by_field($form,array('date','branch_id',$field)));
					}
				}
				
	    		$con->sql_query("select * from mkt_review_items where date='$date' and branch_id=$branch_id");
	    		$r2 = $con->sql_fetchrow();
			}
	        if ($form['value'] == '')
	            print "&nbsp;";
			else
	        	print number_format($r2[$field],2);
	        exit;
	        
		case 'refresh_dept_default_val':
 	    	//echo"<pre>";print_r($_REQUEST);echo"</pre>";
	    	$id=intval($_REQUEST['id']);
	    	$month=intval($_REQUEST['month']);
	    	$year=intval($_REQUEST['year']);
			get_dept_default_val($branch_id,$id,$year,$month);
  			$con->sql_query("select * from category where id=$id order by id");
    		$smarty->assign("cat",$con->sql_fetchrow());
  			$con->sql_query("select * from category where root_id=$id order by id");
  			$smarty->assign("subcat",$con->sql_fetchrowset());
  			$smarty->display("mkt_review.reload.tpl");
			exit;
		
	    case 'search':
			break;
	        
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}

get_detail();
$smarty->assign('PAGE_TITLE', "Annual Planner and Review for ".str_month($month)." $year");
$smarty->display("mkt_review.tpl");

function get_detail(){

	global $smarty,$con,$sessioninfo,$month,$year;
	
	$branch_id =get_request_branch();
	$smarty->assign("branch_id", $branch_id);
 	
	if ($_REQUEST['year'])
	    $year=$_REQUEST['year'];
	else
		$year=date("Y");

	if ($_REQUEST['month'])
	    $month=$_REQUEST['month'];
	else
		$month=date("m");

 	if ($_REQUEST['id'])
	    $id=mi($_REQUEST['id']);
	else
		$id=1;
	
	$smarty->assign("month", $month);
	$smarty->assign("year", $year);

	$smarty->assign("i",days_of_month($month,$year)+1);
	for($i=1;$i<=31;$i++) {
		$tempday=date("l", mktime(0, 0, 0, $month, $i, $year));
		$showday[$i] = $tempday;
	}
	$smarty->assign('showday',$showday);

	$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	$r1 = $con->sql_fetchrow();
	$line=unserialize($r1[line_contribute]);
	$list['total_target']=$r1['total_target'];
	$list['adjustment']=$r1['adjustment'];
	
	$con->sql_query("select *,DATE_FORMAT(date, '%e') as day from mkt_review_items where branch_id=$branch_id and YEAR(date)=$year and MONTH(date)=$month");
	while($r=$con->sql_fetchrow()){
		$list[$r['day']]=$r;
	}
	//echo"<pre>";print_r($tempday);echo"</pre>";
	$smarty->assign("list",$list);
	$smarty->assign("line",$line);

	$con->sql_query("select *,DATE_FORMAT(date, '%e') as day from mkt_review_contribute where branch_id=$branch_id and cat_root_id=$id and YEAR(date)=$year and MONTH(date)=$month order by date");
	while($r=$con->sql_fetchrow()){
		$dept[$r['category_id']][$r['day']]=$r;
	}
	get_dept_default_val($branch_id,$id,$year,$month);

	$smarty->assign("dept",$dept);
	//echo"<pre>";print_r($dept_default);echo"</pre>";
	$con->sql_query("select * from category where root_id=0 order by id");
	$smarty->assign("category",$con->sql_fetchrowset());
	//get_real_sales($branch_id,$year,$month);
}

function get_dept_default_val($branch_id,$id,$year,$month){

	global $smarty,$con,$sessioninfo,$dept_default,$r2;
	
	$q2=$con->sql_query("select *,DATE_FORMAT(date, '%e') as day  from mkt_review_contribute where branch_id=$branch_id and cat_root_id=$id and YEAR(date)=$year and MONTH(date)=$month and DAY(date)=0 order by date");
	while($r2=$con->sql_fetchrow($q2)){
		$dept_default[$r2['category_id']]=$r2;
	}
	//echo"<pre>";print_r($dept_default);echo"</pre>";
	$smarty->assign("dept_default",$dept_default);

}


function get_real_sales($branch_id,$year,$month){

	global $smarty,$con,$sessioninfo,$real_sales;

	$q1=$con->sql_query("select p2 as cat_id, sum(pos_transaction.amount) as amt, date(pos_transaction.timestamp) as dt, pos_transaction.day as day, p1 as root_id
from pos_transaction 
left join sku_items on pos_transaction.sku_item_code = sku_items.sku_item_code 
left join sku on sku_items.sku_id = sku.id 
left join category_cache c on sku.category_id = c.category_id 
where pos_transaction.branch_id =$branch_id and  year=$year and month=$month group by p2,dt order by p2,dt");
	  	
	while($r1 = $con->sql_fetchrow($q1)){
		$date=$r1['dt'];
		$cat_id=$r1['cat_id'];
		$value=$r1['amt'];
		$id=$r1['root_id'];
		
		if($cat_id){
			$real_sales[$cat_id][$r1['day']]['amt']=$value;				

			$form['month']=$month;
			$form['year']=$year;
			$form['branch_id']=$branch_id;
			$form['date']=$date;
			$form['cat_root_id']=$id;
			$form['category_id']=$cat_id;
			$form['sales_amount']=$value;
					
			$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");		
			$r = $con->sql_fetchrow();
			
			if($r){		
				$con->sql_query("select * from mkt_review_contribute where date='$date' and branch_id=$branch_id and category_id=".intval($cat_id)." and cat_root_id=".intval($id)."");
				$r3 = $con->sql_fetchrow();
				if(!$r3){
					$con->sql_query("insert into mkt_review_contribute ".mysql_insert_by_field($form,array('date','branch_id','cat_root_id','category_id','sales_amount')));
				}
				else{
					$con->sql_query("update mkt_review_contribute set sales_amount=$value where date='$date' and branch_id=$branch_id and category_id=".intval($form['category_id'])." and cat_root_id=".intval($form['cat_root_id'])."");
				}			
			}
			
			else{
				
				$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id')));
				
				$con->sql_query("insert into mkt_review_contribute ".mysql_insert_by_field($form,array('date','branch_id','cat_root_id','category_id','sales_amount')));
			}
		}
	}
	$smarty->assign("real_sales",$real_sales);
	//echo"<pre>";print_r($real_sales);echo"</pre>";
}
?>
