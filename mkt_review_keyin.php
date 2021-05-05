<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT_DAILY_KEYIN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_DAILY_KEYIN', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Daily Sales Keyin');

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


if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
      	case 'search':
      		//echo"<pre>";print_r($_REQUEST);echo"</pre>";
      		$form=$_REQUEST;
			$con->sql_query("select *,MONTH(date) as g_month, YEAR(date) as g_year from mkt_review_items where branch_id=$sessioninfo[branch_id] and date='$form[selected_date]' ");
			$smarty->assign("cat",$con->sql_fetchrow());

      		$smarty->assign("form",$form);
      		break;
      		
      	case 'save_edit':
      		//echo"<pre>";print_r($_REQUEST);echo"</pre>";
       		$form=$_REQUEST;
			$cat_root_id=mi($form['line_id']);
			$category_id=mi($form['dept_id']);
			$sales_amount=$form['value']; 
			$date=ms($form['selected_date']);
					
    		$q1=$con->sql_query("select * from mkt_review_contribute where cat_root_id=$cat_root_id and category_id=$category_id and date=$date and branch_id=$branch_id");
    		$r1 = $con->sql_fetchrow($q1);
    		if($r1){
       			$q2=$con->sql_query("update mkt_review_contribute set sales_amount='$sales_amount' where cat_root_id=$cat_root_id and category_id=$category_id and date=$date and branch_id=$branch_id");
			}
			else{
				$q2=$con->sql_query("insert into mkt_review_contribute values('$branch_id','$cat_root_id','$category_id',$date,'','$sales_amount') ");
			}
	    	
			$q3=$con->sql_query("select sales_amount from mkt_review_contribute where cat_root_id=$cat_root_id and category_id=$category_id and date=$date and branch_id=$branch_id");
	    	$r3 = $con->sql_fetchrow($q3);				  
	        if ($form['value'] == '')
	            print "&nbsp;";
			else
	        	print number_format($r3['sales_amount'],2);   		
      		exit;
      	
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}

$q1=$con->sql_query("select * from category where root_id=0 order by id");
while($r1=$con->sql_fetchrow($q1)){
	$category[]=$r1;
	$q2=$con->sql_query("select * from category where root_id=$r1[id] order by id");
	while($r2=$con->sql_fetchrow($q2)){
		$department[$r1[id]][description][]=$r2['description'];
		$department[$r1[id]][id][]=$r2['id'];
		$q3=$con->sql_query("select sales_amount from mkt_review_contribute where cat_root_id=$r1[id] and category_id=$r2[id] and date=".ms($_REQUEST['selected_date'])." and branch_id=$branch_id order by category_id");
		$r3=$con->sql_fetchrow($q3);
		$department[$r1[id]][sales][]=$r3['sales_amount'];		
	}
}
$dept=$department;
//echo"<pre>";print_r($dept);echo"</pre>";
$smarty->assign("dept",$dept);
$smarty->assign("category",$category);
$smarty->display("mkt_review_keyin.tpl");

?>
