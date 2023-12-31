<?
/*
1/12/2011 6:03:16 PM Andy
- change column lastlogin, retry to use from table user_status.

6/24/2011 3:04:50 PM Andy
- Make all branch default sort by sequence, code.

3/13/2013 3:44 PM Justin
- Changed the report name from "Inactive" become "No-Activity" User Report.
- Enhanced to show Note for the meaning of status.
- Changed the status "Inactive" into "No-Activity".

4/8/2013 5:15 PM Andy
- Fix the report name not same with title name.

2/9/2017 11:51 AM Andy
- Enhanced to skip check ARMS user.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999) js_redirect('Access Denied', "/index.php");
$maintenance->check(45);

$page_title = 'No-Activity User Report';
$smarty->assign('PAGE_TITLE', $page_title);
$smarty->display("header.tpl");
?>

<script>
function do_submit(){
	if (empty(document.f_a.day, "You must enter all the fields")){
	    return false;
	}
	else if(document.f_a.day.value<1){
		alert('Please provide valid value.');
	    return false;
	}
	document.f_a.submit();
}
</script>
<div class="container">
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary"><?php print $page_title; ?></h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	<div class="card mx-3">
		<div class="card-body">
<p>
<form name=f_a class="stdframe" style="background:#fff">
<input type=hidden name=load value=1>
<?
	if (BRANCH_CODE=='HQ'){
		$con->sql_query("select id as value, branch.code as title from branch order by sequence, code");
		print "<label>Branch  </label>";
		sel($con->sql_fetchrowset(), "branch_id");
	}

?>
<br>
<div class="form-label">More Than</div>
<input type="text" class="r form-control" name="day" value="<?=$_REQUEST['day']?>" id="day" size=6 onclick="this.select();mi(this);">
 <small><b>(Days)</b></small>
<br>
<br>
<input class="btn btn-primary text-center" value='Show Report' onclick="do_submit();">

<p>
<div class="mt-3">
<b class="">Note:</b>
</div>
<div class="form-label">*<span class="text-dark"><b> No-Activity:</b></span>Did not login from the past stated day(s)</div>
 
<div class="form-label">*<span class="text-danger"> <b>Locked:</b></span> Failed to login for 3 times.</div>
</p>

</form>
</p>
		</div>
	</div>
</div>
<?
	if ($_REQUEST['load']){
		$day=mi($_REQUEST['day']);
		if(BRANCH_CODE=='HQ'){
			if($_REQUEST['branch_id']){
				$branch_id=mi($_REQUEST['branch_id']);
				$where="branch.id=$branch_id and ";				
			}
		}
		else{
			$where="branch.id=$sessioninfo[branch_id] and ";				
		}

		$q1=$con->sql_query("select user.id as user_id, u, us.lastlogin, branch.code as b_code, user.position, us.retry, user.fullname as name
from user 
left join user_status us on us.user_id=user.id
left join branch on default_branch_id=branch.id	
where $where template=0 AND us.lastlogin< DATE_SUB(CURDATE(), INTERVAL $day day) and is_arms_user=0");
		//user.active AND 
		while ($r1=$con->sql_fetchrow($q1)){
			if($r1['retry']<3){
				$r1['status']='No-Activity';	
			}
			else{
				$r1['status']='Locked';
			}
			$users[]=$r1;
		}
		//$users=$con->sql_fetchrowset($q1);
		//echo"<pre>";print_r($users);echo"</pre>";
		$smarty->assign("list",$users);
		$smarty->display("admin.inactive_user.tpl");
	}
	$smarty->display("footer.tpl");
?>
