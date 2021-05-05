<?
/*
REVISION HISTORY
================
12/27/2007 11:00:17 AM gary
- fix the copy from last year december records to new year. 
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if (!privilege('SHIFT_RECORD_EDIT') && !privilege('SHIFT_RECORD_VIEW')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SHIFT_RECORD_EDIT or SHIFT_RECORD_VIEW', BRANCH_CODE), "/index.php");

//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'SHIFT_RECORD_%'");
while ($r = $con->sql_fetchrow()){
	$shift_record_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("shift_record_privilege", $shift_record_privilege);

$smarty->assign("PAGE_TITLE", "Shift Record");

if(isset($_REQUEST['a'])){
	switch ($_REQUEST['a']){
	
        case 'ajax_edit_item' :
            $month=intval($_REQUEST['month']);
	        $year=intval($_REQUEST['year']);
	        $branch=ms($_REQUEST['branch']);
	        $id=ms($_REQUEST['user_id']);
	        $original_id=ms($_REQUEST['original_id']);
	        $name=ms($_REQUEST['user_name']);

	        if (!$shift_record_privilege['SHIFT_RECORD_EDIT'][$_REQUEST['branch']]){
				print '<script>alert("'.sprintf($LANG['SR_NO_EDIT_PERMISSION']).'")</script>';
	            break;
			}

			if($_REQUEST['department']!='%%'){$v_department=ms($_REQUEST['department']);}
			else {$v_department=ms($_REQUEST['selected_dept']);}
	        
			if($v_department=="'Promoter (Hard line)'" or $v_department=="'Promoter (Soft line)'" or $v_department=="'Promoter (Supermarket line)'"){
				$brand=ms($_REQUEST['user_brand']);
			}
			else{
				$brand="''";
			}
			
			$Edays = ""; $days = "";
			for ($d=1;$d<=31;$d++) $Edays .= " estimate_day_record_$d=".ms($_REQUEST[user_Eday][$d]).", ";
			for ($d=1;$d<=31;$d++) $days .= " day_record_$d=".ms($_REQUEST[user_day][$d]).", ";

           	$query="update shift_record set employee_name=$name,employee_id=$id,department=$v_department,$Edays $days brand=$brand where month='$month' and year='$year' and branch_id=$branch and employee_id=$original_id";
			$con->sql_query($query);

            $query="select * from shift_record where month='$month' and year='$year' and branch_id=$branch and employee_id=$id";
            $con->sql_query($query);
			$smarty->assign("curr_list",$con->sql_fetchrow());

   			$smarty->assign("i",days_of_month($month,$year)+1);
			$smarty->assign("month", $month);
			$smarty->assign("year", $year);
			$smarty->assign("n",  $_REQUEST['no']);
			$smarty->assign("branch", $_REQUEST['branch']);
			$smarty->assign("department",$_REQUEST['department']);
			$smarty->display("shift_record.edit.user.tpl");
			exit;

	
        case 'ajax_del_item' :
            $month=intval($_REQUEST['month']);
	        $year=intval($_REQUEST['year']);
	        $branch=ms($_REQUEST['branch']);
	        $id=ms($_REQUEST['id']);
	        
        	if (!$shift_record_privilege['SHIFT_RECORD_EDIT'][$_REQUEST['branch']])
	        {
				print '<script>alert("'.sprintf($LANG['SR_NO_EDIT_PERMISSION']).'")</script>';
	            break;
			}
            $query="delete from shift_record where month='$month' and year='$year' and branch_id=$branch and employee_id=$id";
			$con->sql_query($query);
			$smarty->assign("no",  $_REQUEST['no']);
			exit;
			

        case 'ajax_call_user' :
            $month=intval($_REQUEST['month']);
	        $year=intval($_REQUEST['year']);
	        $branch=ms($_REQUEST['branch']);
	        $id=ms($_REQUEST['id']);

        	if (!$shift_record_privilege['SHIFT_RECORD_EDIT'][$_REQUEST['branch']])
	        {
				print '<script>alert("'.sprintf($LANG['SR_NO_EDIT_PERMISSION']).'")</script>';
	            break;
			}
            $query="select * from shift_record where month='$month' and year='$year' and branch_id=$branch and employee_id=$id";
            $con->sql_query($query);
			//while($list=$con->sql_query($query))
			$smarty->assign("curr_list",$con->sql_fetchrow());
			//echo "<pre>";print_r($list);echo"</pre>";
   			$smarty->assign("i",days_of_month($month,$year)+1);
			for($i=1;$i<=31;$i++) {
				$tempday=date("D", mktime(0, 0, 0, $month, $i, $year));
				$showday[$i] = $tempday;
			}
			$con->sql_query("select id, code from branch where active order by id");
$smarty->assign("BranchArray", $con->sql_fetchrowset());
			$smarty->assign('showday',$showday);
			$smarty->assign("month", $month);
			$smarty->assign("year", $year);
			$smarty->assign("no",  $_REQUEST['no']);
			$smarty->assign("branch", $_REQUEST['branch']);
			$smarty->assign("original_id",$_REQUEST['id']);
			$smarty->assign("department",$_REQUEST['department']);
			$smarty->display("shift_record.edit.user.tpl");
			exit;
			
			
        case 'ajax_refresh_items':
			get_request_items();
			$smarty->display("shift_record.items.tpl");
			exit;
			

    	case 'update':
	        $update=intval($_REQUEST['update']);
	        $month=intval($_REQUEST['month']);
	        $year=intval($_REQUEST['year']);
	        $branch=ms($_REQUEST['branch']);

    		if (!$shift_record_privilege['SHIFT_RECORD_EDIT'][$_REQUEST['branch']]){
				print '<script>alert("'.sprintf($LANG['SR_NO_EDIT_PERMISSION']).'")</script>';
	    		break;
			}

			$field1="estimate_day_record_$update";
			$field2="day_record_$update";

			//loop to update records
			foreach(array_keys($_REQUEST['id']) as $i=>$dummy){

				$value1=ms($_REQUEST['Eday'][$i+1][$update]);
				$value2=ms($_REQUEST['day'][$i+1][$update]);

				//$value2=ms($_REQUEST[$field2]);

				$id=ms($_REQUEST['id'][$i+1]);
				$query="update shift_record set $field1=$value1,$field2=$value2 where employee_id=$id and month='$month' and year='$year' and branch_id=$branch ";
				$con->sql_query($query);
			}
			break;
			
	    
		case 'copy':
	        $month=intval($_REQUEST['month']);
	        $year=intval($_REQUEST['year']);
	        $branch=ms($_REQUEST['branch']);

    		if (!$shift_record_privilege['SHIFT_RECORD_EDIT'][$_REQUEST['branch']]){
				print '<script>alert("'.sprintf($LANG['SR_NO_EDIT_PERMISSION']).'")</script>';
	    		break;
			}

			if($month=='1'){
				$m=12;
				$y=$year-1;	
			}
			else{
				$m=$month-1;
				$y=$year;	
			}
			//echo "$m==$y//$month==$year<br>";

			$con->sql_query("replace into shift_record (branch_id,department,brand,month,year,employee_name,employee_id) select $branch,department,brand,'$month','$year',employee_name, employee_id from shift_record where month='$m' and year='$y' and branch_id=$branch");
			break;

	    case 'save':
    		if (!$shift_record_privilege['SHIFT_RECORD_EDIT'][$_REQUEST['branch']]){
				print '<script>alert("'.sprintf($LANG['SR_NO_EDIT_PERMISSION']).'")</script>';
	    		break;
			}
			if(isset($_REQUEST['name'])){

				foreach(array_keys($_REQUEST['name']) as $i=>$dummy){

					if(($_REQUEST['name'][$i] != "")&&($_REQUEST['id'][$i] != "")){

				    	if($_REQUEST['department']!='%%'){$var_department[$i]=ms($_REQUEST['department']);}
						else {$var_department[$i]=ms($_REQUEST['array_department'][$i]);}

						$fields = ""; $values = "";
						for ($d=1;$d<=31;$d++) $fields .= "estimate_day_record_$d,day_record_$d,";
						for ($d=1;$d<=31;$d++) $values .= ms($_REQUEST['Eday'][$i][$d]).",".ms($_REQUEST['day'][$i][$d]).",";

						//"estimate_day_record_$i,day_record_$i,"
						$query="replace into shift_record (branch_id,department,brand,month,year,employee_name,$fields employee_id) values (".ms($_REQUEST['branch'])." ,$var_department[$i],".ms($_REQUEST['brand'][$i]).", ".ms($_REQUEST['month']).",".ms($_REQUEST['year']).", ".ms($_REQUEST['name'][$i]).",$values ".ms($_REQUEST['id'][$i]).")";
						$con->sql_query($query);
					}
					elseif ($i){
						//append error message
				    	$errm[] = sprintf($LANG['SR_NAME_OR_ID_EMPTY'], $i);
					}
				}
			}

			$smarty->assign("errm", $errm);
			break;
			
		case 'print_selected':			
		case 'refresh' :
 		case 'print_submit' :
		case 'edit_user':
 	    case 'edit_day':
			break;
		    
		default:
		    print "<h1>Unhandled Request</h1>";
			print_r($_REQUEST);
		    exit;
	}
}
//get branch list for drapdown
$con->sql_query("select id, code from branch where active order by id");
$smarty->assign("BranchArray", $con->sql_fetchrowset());

get_request_items();
if ($_REQUEST['a']=='print_submit' || $_REQUEST['a']=='print_selected'){
	$totalpage = ceil(count($list)/10);
	for ($i=0,$page=1;$i<count($list);$i+=10,$page++){
        $smarty->assign("page", "Page $page of $totalpage");
        $smarty->assign("start_counter", $i);
        $smarty->assign("list", array_slice($list,$i,10));
		$smarty->display("shift_record.print.tpl");
		$smarty->assign("skip_header",1);
	}
}
else{
	$smarty->assign("list", $list);
	$smarty->display("shift_record.tpl");
}



function get_request_items(){
	global $con, $smarty,$sessioninfo,$list;
	$where = array();
	$whr = array();
	$val = array();
	//set default value for record list
	if ($_REQUEST['department'] != ''){
	    if($_REQUEST['print_select']){
	    	foreach ($_REQUEST['print_select'] as $k=>$v){
	    	    $val[]=$v;
	    	    if(count($_REQUEST['print_select'])==1){
					$whr[] = "department=".ms($v);
				}
				else{
		    	    if($k==0)
						$whr[] = "(department=".ms($v);
					elseif($k==(count($_REQUEST['print_select'])-1))
						$whr[] = "department=".ms($v).")";
					else
						$whr[] = "department=".ms($v);
				}

			}
  			$where[] = join(" or ", $whr);
			$dept=join(", ", $val);
			//echo "<pre>";print_r($whr);echo"</pre>";
  			//$where[] = join(" or ", $whr);
		}
		else{
    		$department="$_REQUEST[department]";
			$where[] = "department like ".ms($department);
		}

	}
	if($department==""){$department="%%";}
	
	if($_REQUEST['a']=='print_selected'){
		if($_REQUEST['print_list']){
			$where[] = "employee_id in (" . join(",", array_keys($_REQUEST['print_list'])) . ")";
		}
	}	
	
	if ($_REQUEST['branch']){
    	$branch=$_REQUEST['branch'];
	}
	else{
    	$branch=$sessioninfo['branch_id'];
	}

	$where[] = "branch_id = " .ms($branch);

	if ($_REQUEST['month']){
		$month=$_REQUEST['month'];
		$where[] = "month = '" .mi($month)."'";
	}
	else{
    	$month=date("n");
    	$where[] = "month = '" .mi($month)."'";
	}

	if ($_REQUEST['year']){
    	$year=$_REQUEST['year'];
    	$where[] = "year = '" .mi($year)."'";
	}
	else{
		$year=date("Y");
		$where[] = "year = '" .mi($year)."'";
	}

	if ($_REQUEST['s_field']){
    	$s_field=$_REQUEST['s_field'];
	}
	else{
		$s_field="employee_id";
	}

	if ($_REQUEST['s_arrow']){
    	$s_arrow=$_REQUEST['s_arrow'];
		}
	else{
		$s_arrow="ASC";
	}

	$smarty->assign("s_field", $s_field);
	$smarty->assign("s_arrow", $s_arrow);
	$smarty->assign("month", $month);
	$smarty->assign("year", $year);
	$smarty->assign("branch", $branch);
	$smarty->assign("department", $department);
	$smarty->assign("dept", $dept);

	$where = join(" and ", $where);
	//echo "<pre>";print_r($where);echo"</pre>";
	
	if ($_REQUEST['a']!='print_submit'){
		$con->sql_query("select * from shift_record where $where order by $s_field $s_arrow");
		echo "<script>var currlist = new Array();</script>";
		$p=0;
		while($r=$con->sql_fetchrow()){
	    	$p++;
			echo "<script>currlist[$p]='$r[employee_id]';</script>";
		}
	}
	$r=$con->sql_query("select * from shift_record where $where order by $s_field $s_arrow");
	$list=$con->sql_fetchrowset($r);
	$num_row=$con->sql_numrows($r);
	$smarty->assign("num_row", $num_row);
	
	if($month=='1'){
		$m=12;
		$y=$year-1;	
	}
	else{
		$m=$month-1;
		$y=$year;
	}

	$con->sql_query("select * from shift_record where month='$m' and year='$y' and branch_id='$branch'");
	//get last month num_records (for copying purpose)
	$num_row_last=$con->sql_numrows();
	$smarty->assign("num_row_last", $num_row_last);
	
	$smarty->assign("i",days_of_month($month,$year)+1);
	for($i=1;$i<=31;$i++) {
		$tempday=date("D", mktime(0, 0, 0, $month, $i, $year));
		$showday[$i] = $tempday;
	}
	//echo "<pre>";print_r($showday);echo"</pre>";
	$smarty->assign('showday',$showday);

}


?>
