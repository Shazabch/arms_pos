<?php
/*
06.05.2008 16:18:06 saw
- PO vendor control access code, propose to have numeric only, to avoid vendor confuse.

08.05.2008 12:16:26 saw
- Add 'History' button and 'show_history' function to view all ticket created by the login user.

10/8/2009 11:16:52 AM yinsee
- allow vendor ticket to HQ

3/14/2013 3:04 PM Justin
- Enhanced to have new feature of deactivate access code.
- Enhanced to calculate and capture valid period.

4/18/2013 3:55 PM Fithri
- add to show info of how long is the valid period (from config) of vendor access code
- show code expiry date after generating vendor access code.

12/31/2013 4:11 PM Justin
- Bug fixed on valid period shows in wrong info while config has turn on but never assign value.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

6/22/2017 15:00 Qiu Ying
- Enhanced to select multiple department in vendor PO access

7/4/2017 12:02 PM Andy
- Add maintenance checking v324.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_TICKET')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_TICKET', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

$maintenance->check(324);
require_once("vendor_sku.include.php");

$smarty->assign("PAGE_TITLE", "Vendor PO Request");

// manager and above can see all department
if ($sessioninfo['level'] < 9999){
	if (!$sessioninfo['departments'])
		$depts = "c.id in (0)";
	else
		$depts = "c.id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else{
	$depts = 1;
}

$con->sql_query("select c.id, c.description, c.root_id, r.description as root 
				from category c left join category r on c.root_id = r.id 
				where c.level = 2 and c.active = 1 and $depts order by r.description, c.description");
while($r = $con->sql_fetchassoc()){
	$dept[] = $r;
}
$smarty->assign("dept", $dept);
$con->sql_freeresult();

$con->sql_query("select id, code from branch where active=1 order by id");
while($r2 = $con->sql_fetchassoc()){
	$branches[] = $r2;
}
$smarty->assign("branches", $branches);
$con->sql_freeresult();

$branch_id =get_request_branch();
if(!$branch_id){
	$branch_id=$sessioninfo['branch_id'];
}
$smarty->assign("branch_id", $branch_id);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){	
	
		case 'ajax_load_sku':
			$smarty->assign("show_varieties",1);
			get_vendor_po_request_sku();
			exit;
			
		case 'get_po_owners':
			get_po_owners();
			exit;
			
		case 'ajax_show_history':
			$filter = array("vendor_id = ".mi($_REQUEST['vendor_id']));
			if (BRANCH_CODE != 'HQ') $filter[] = "branch_id = $sessioninfo[branch_id]";
			if ($sessioninfo['level']<9999) $filter[] = "create_by = $sessioninfo[id]";
			$where = "where ".join(" and ", $filter);
			
			
			$q1 = $con->sql_query("select login_tickets.*, (select count(*) from po where login_ticket_ac = login_tickets.ac) as po_count, category.description as dept, user.u, user2.u as u_create, branch.code as branch_code, branch.report_prefix from 
			login_tickets left join category on login_tickets.dept_id = category.id 
			left join user on login_tickets.user_id = user.id
			left join user user2 on login_tickets.create_by = user2.id
			left join branch on branch_id = branch.id
			$where order by added desc");

			$ticket_period = ($config['po_vendor_ticket_expiry'] ? $config['po_vendor_ticket_expiry'] : 1) * 86400;
			while($r = $con->sql_fetchassoc($q1)){
				$create_day = strtotime($r['added']);

				$valid_period=$create_day+$ticket_period;
				$r['valid_period'] = date("Y-m-d H:i:s", $valid_period);
				if($r["multiple_dept_id"]){
					$department = array_keys(unserialize($r["multiple_dept_id"]));
					$str_dept = implode(",", $department);
					
					$q2 = $con->sql_query("select id, description from category where id in ($str_dept)");
					while($r2 = $con->sql_fetchassoc($q2)){
						$r["d_id"][] = $r2["description"];
					}
					$con->sql_freeresult($q2);
				}
				
				$data[] = $r;
			}
			$con->sql_freeresult($q1);
			
			$smarty->assign("row", $data);
			$smarty->display("vendor_po_request.history.tpl");
			exit;
			
		case 'generate_ac':
			if (!privilege('PO_TICKET')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_TICKET', BRANCH_CODE), "/vendor_po_request.php");
			
			if(!$_REQUEST['owner_id']) js_redirect("No Owner Found", "/vendor_po_request.php");
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$ac = sprintf("%02d%02d%02d", rand()%100,rand()%100,rand()%100);
			$q1=$con->sql_query("select ac from login_tickets");			
			$r1= $con->sql_fetchrowset($q1);
			//echo"<pre>";print_r($r1);echo"</pre><br>";
			for($i=0;$i<count($r1);){
				if($r1[$i]['ac']==$ac){
					$ac = sprintf("%02d%02d%02d", rand()%100,rand()%100,rand()%100);
					$i=0;
				}
				else{
					$i++;
				}
			}
			$form['ac']=$ac;
			$form['vendor']=$_REQUEST['vendor'];
			$form['create_by']=$sessioninfo['id'];
			// assign to owner
			$form['user_id']=$_REQUEST['owner_id'];
			$tmp_did = array_keys($_REQUEST['department_ids']);
			$form['dept_id']=mi($tmp_did[0]);
			$form['vendor_id']=mi($_REQUEST['vendor_id']);	
			$form['branch_id']=$branch_id;
			$form['added']='CURRENT_TIMESTAMP()';
			$form['multiple_dept_id'] = serialize($_REQUEST['department_ids']);
			
			$ticket_period = ($config['po_vendor_ticket_expiry'] ? $config['po_vendor_ticket_expiry'] : 1) * 86400;
			$valid_period = time() + $ticket_period;
			
			$con->sql_query("insert into login_tickets ".mysql_insert_by_field($form,array('ac', 'create_by', 'user_id','vendor_id','dept_id','branch_id','added', 'multiple_dept_id')));
			header("Location: /vendor_po_request.php?t=generated&vendor=$form[vendor]&code=$ac&expire=".date("Y-m-d H:i:s", $valid_period));
			exit;
		case 'deactivate_ac':
			$ac = $_REQUEST['ac'];
			$bid = $_REQUEST['bid'];
			$q1 = $con->sql_query("update login_tickets set last_update=last_update, active=0 where ac = ".ms($ac)." and branch_id = ".mi($bid));
			
			if($con->sql_affectedrows($q1) > 0) print "Successfully deactivated ".$ac;
			else print "Failed to update, ".$ac." is deactivated previously";
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;	
	}
}

$smarty->display("vendor_po_request.tpl");
exit;

// return list of users that have Create PO privilege and matches the department/branch 
function get_po_owners()
{
	global $con;
	
	$bid = mi($_REQUEST['branch_id']);
	$vid = mi($_REQUEST['vendor_id']);
	
	if(!$vid){
		print "<script>alert('Please select vendor');</script>";
		exit;
	}
	
	if(!isset($_REQUEST['department_ids'])){
		exit;
	}
	
	print "<td><b>Assign to Owner</b></td>";
	
	$con->sql_query("select id,u,departments,vendors from user_privilege left join user on user_id = user.id where privilege_code = 'PO' and branch_id = $bid and active=1 and user.is_arms_user=0 order by u");
	print "<td><select name=owner_id>";
	while ($r=$con->sql_fetchassoc())
	{
		$skip = false;
		$dept = unserialize($r['departments']);
		$vendors = unserialize($r['vendors']);
		if ($vendors && !isset($vendors[$vid])) continue;
		
		foreach($_REQUEST['department_ids'] as $did => $item){
			if (!isset($dept[$did])){
				$skip = true;
				continue;
			}
		}
		
		if(!$skip)
			print "<option value=\"$r[id]\"> $r[u]</option>";
	}
	print "</select></td>";
}
?>
