<?php
// mk0 can only be created in HQ, there is no branch_id in primary key
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

if (!privilege('MKT2_VIEW') and !privilege('MKT2_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT2_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Department Target Review');

//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT2_%'");
while ($r = $con->sql_fetchrow()){
	$mkt2_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt2_privilege", $mkt2_privilege);
//print_r($mkt2_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

$branch_id=get_request_branch();

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'view':
		case 'open':
		    $id = intval($_REQUEST['id']);
		    $branch_id = intval($_REQUEST['branch_id']);
		    if (!$id || !$branch_id){
				header("Location: /mkt2.php?branch_id=$branch_id");
				exit;
			}

			$form = array();
			load_header($form);
			//	generate date list
			$startd = strtotime($form['offer_from']);
			$endd = strtotime($form['offer_to']);
			for ($i=$startd;$i<=$endd;$i+=86400){
			
			    $dates[] = date("Y-m-d",$i);
			}
			$smarty->assign("dates", $dates);

			//echo "<pre>";print_r($form);echo "</pre>";
			
			if (!$form['approved'] || $form['m2_status'])
			{
			    $_REQUEST['a'] = 'view';
			}
			
			$b_code=ms('HQ');
			$q2=$con->sql_query("select * from branch where code=$b_code");
			$r2 = $con->sql_fetchrow($q2);
			// get image path
			$hurl = get_branch_file_url($r2['code'], $r2['ip']);
			$smarty->assign("image_path", $hurl);
			
			$smarty->display("mkt2.edit.tpl");
			exit;
			
		case 'confirm':
 			if (!privilege('MKT2_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_EDIT', BRANCH_CODE), "/mkt2.php?branch_id=$branch_id");
		    $is_confirm = 1;
		case 'save':
 			if (!privilege('MKT2_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_EDIT', BRANCH_CODE), "/mkt2.php?branch_id=$branch_id");
			$form = $_REQUEST;
		    $errm = validate_data($form,$is_confirm);

		    if (!$errm)
		    {
		        // make mkt2 actual
			    if ($is_confirm) $form['status'] = 1;

			    $form['user_id'] = $sessioninfo['id'];
		        $form['mkt0_id'] = $form['id'];
		        $form['added'] = 'CURRENT_TIMESTAMP()';

			    $depts = array_keys($_REQUEST['deptname']);
				if($depts){
					foreach ($depts as $dept){
					    $form['dept_id'] = $dept;
						$form['normal_target'] = $_REQUEST['target']['normal'][$dept];
						$form['sales_target'] = $_REQUEST['target']['promo'][$dept];
						// extract the department sales and targets for each date, and the sum
						$arr_n = array(); $arr_s = array();
						if($_REQUEST['normal']){
							foreach (array_keys($_REQUEST['normal']) as $k)
							{
							    $arr_n[$k] = $_REQUEST['normal'][$k][$dept];
							    $arr_s[$k] = $_REQUEST['sales'][$k][$dept];
							}
						}
						$form['normal'] = serialize($arr_n);
						$form['sales'] = serialize($arr_s);
						$con->sql_query("replace into mkt2 ".mysql_insert_by_field($form,array('mkt0_id','branch_id','dept_id','user_id','normal_target','sales_target','normal','sales','added','status'))) or die(mysql_error());
			        }
		        }
		        
			    // after save , return to front page
			    if ($is_confirm)
				{
					log_br($sessioninfo['id'], 'MKT', $form['id'], "MKT2 Confirmed (ID#form[$id], Branch #$branch_id, Dept: ".join(",",$depts).")");
			    	header("Location: /mkt2.php?t=confirm&id=$form[id]&branch_id=$branch_id");
			    }
			    else
			    	header("Location: /mkt2.php?t=save&id=$form[id]&branch_id=$branch_id");
		    }
		    else
		    {
				load_header($form);

				// copy department promo-target
				$smarty->assign("dept_sales", $form['target']);

				// copy date list
			    $dates = array_keys($form['normal']);
			    // remove "total" row
				array_pop($dates);
				$smarty->assign("dates", $dates);

		        $smarty->assign("form", $form);
		        $smarty->assign("errm", $errm);
				$smarty->display("mkt2.edit.tpl");

			}
			exit;

		case 'ajax_load_mkt_list':
		    load_mkt_list();
		    $smarty->display("mkt2.home.list.tpl");
		    exit;


		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display('mkt2.home.tpl');

function load_mkt_list()
{
	global $con, $sessioninfo, $smarty, $depts;
	$branch_id=get_request_branch();
	$smarty->assign("branch_id", $branch_id);
	if (!$t) $t = intval($_REQUEST['t']);
	
	$owner_check = "";
	//REAL STATEMENT :
	$owner_check = "((mkt1.user_id = $sessioninfo[id] and mkt1.branch_id = $branch_id) or (mkt0.user_id=$sessioninfo[id]) or (mkt2.user_id=$sessioninfo[id] or mkt2.user_id is null)) and ";

    $where ="mkt0.active and mkt0.status and mkt1.approved=1 and mkt1.status and mkt0.branches like '%$branch_id%' and mkt1.branch_id = $branch_id";
	switch ($t)
	{
	    case 0:
	        $where .= ' and (mkt0.id = ' . mi($_REQUEST['s']) . ' or mkt0.title like ' . ms('%'.$_REQUEST['s'].'%') . ')';
	        $_REQUEST['s'] = '';
	        break;

	}
	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
		
	$con->sql_query("select count(DISTINCT(mkt0.id))
from mkt1
left join mkt0 on mkt1.mkt0_id = mkt0.id
left join mkt2 on mkt2.mkt0_id = mkt1.mkt0_id and mkt2.branch_id = mkt1.branch_id
where $owner_check $where
order by mkt1.last_update desc, mkt0.id");
	$r = $con->sql_fetchrow();
	$total = $r[0];
	if ($total > $sz)
	{
	    if ($start > $total) $start = 0;
		// create pagination
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
		for ($i=0,$p=1;$i<$total;$i+=$sz,$p++)
		{
			$pg .= "<option value=$i";
			if ($i == $start)
			{
				$pg .= " selected";
			}
			$pg .= ">$p</option>";
		}
		$pg .= "</select>";
		$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
	}

	$con->sql_query("select distinct mkt0.*,mkt0.user_id as m0_id, mkt1.user_id as m1_id,mkt1.branch_id as m1_branch,mkt2.user_id as m2_id, mkt2.status,user.u, user2.u as create_u
from mkt1
left join mkt0 on mkt1.mkt0_id = mkt0.id
left join mkt2 on mkt2.mkt0_id = mkt1.mkt0_id and mkt2.branch_id = mkt1.branch_id
left join user on mkt1.user_id = user.id
left join user user2 on mkt0.user_id = user2.id
where $owner_check $where
order by mkt1.last_update desc, mkt0.id limit $start, $sz");

while($r=$con->sql_fetchrow()){
	$list[]=$r;
}
$smarty->assign("list", $list);
//echo"<pre>";print_r($list);echo"</pre>";
//$smarty->assign("list", $con->sql_fetchrowset());

}

function validate_data(&$form, $is_confirm){
	global $LANG;
	$errm = array();

	// todo: add checking code here
	return $errm;
}

function load_header(&$form)
{
	global $con, $LANG, $sessioninfo, $smarty;
	
	$branch_id=get_request_branch();
	$smarty->assign("branch_id", $branch_id);

	$id = intval($_REQUEST['id']);
	$c1=$con->sql_query("select mkt1.*, mkt2.status as m2_status, branch.code as current_branch
from mkt1
left join branch on branch.id=mkt1.branch_id
left join mkt2 on mkt2.mkt0_id = mkt1.mkt0_id and mkt2.branch_id = mkt1.branch_id
where mkt1.mkt0_id = $id and mkt1.branch_id = $branch_id");
    $header = $con->sql_fetchrow($c1);
	/// invalid id
	if (!$header)
		show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));

    $c2=$con->sql_query("select *,
YEAR(offer_from) as from_year,MONTH(offer_from) as from_month,
YEAR(offer_to) as to_year,MONTH(offer_to) as to_month
from mkt0 where id = $id");
	$mkt0 = $con->sql_fetchrow($c2);
	
	/// invalid id
	if (!$mkt0)
		show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));

	$mkt0['attachments'] = unserialize($mkt0['attachments']);
	$mkt0['publish_dates'] = unserialize($mkt0['publish_dates']);
	if ($mkt0['attachments']['file']){
		foreach ($mkt0['attachments']['file'] as $idx=>$fn){
			if ($fn) $mkt0['filepath'][$idx] = "/mkt_attachments/$mkt0[id]/$fn";
		}
	}
	$mkt0['branches'] = unserialize($mkt0['branches']);
	if ($mkt0['branches']){
		foreach ($mkt0['branches'] as $idx=>$fn){
			if ($fn) $mkt0['branches'][] =$fn ;
		}
	}
	$lines=array();
	$list=array();

	if($sessioninfo['departments']!=0){
		$where="and category_id in (".join(",",array_keys($sessioninfo[departments])).")";
	}
	else{
		$where ="";
	}

	$c5=$con->sql_query("select * from category where root_id=0 order by id");
	while($cat=$con->sql_fetchrow($c5)){
	    $c6=$con->sql_query("select *, DAY(date) as day,category.description
from mkt_review_contribute
left join category on category.id=mkt_review_contribute.category_id
where branch_id=$branch_id and ((date>='$mkt0[offer_from]' and date<='$mkt0[offer_to]')or (DAY(date)='0')) and cat_root_id='$cat[id]' and total_contribute $where group by category.id order by day");
		while($dept=$con->sql_fetchrow($c6)){
			$list[] = $dept;
			$lines[$cat['description']]['id'] =$cat['id'];
			$lines[$cat['description']]['dept'][]=array('description'=>$dept['description'], 'id'=>$dept['category_id']);
  		}
	}
	
    $temp=array();
	$mkt_r=$con->sql_query("select * from mkt_review where (month>='$mkt0[from_month]' and month<='$mkt0[to_month]') and (year>='$mkt0[from_year]' and year<='$mkt0[to_year]') and branch_id=$branch_id");
	while($mr=$con->sql_fetchrow($mkt_r)){
	    $lc = unserialize($mr['line_contribute']);
	    if($lc){
		    foreach($lc as $k=>$v){
				$mkt_ri=$con->sql_query("select *,DAY(date) as day,normal_forecast,sales_target from mkt_review_items where branch_id=$branch_id and YEAR(date)=$mr[year] and MONTH(date)=$mr[month] order by date");
				while($mri=$con->sql_fetchrow($mkt_ri)){
				    $temp[$mri['date']]['normal']=$mri['normal_forecast'];
				    $temp[$mri['date']]['sales']=$mri['sales_target'];
				}

				$mkt_rc=$con->sql_query("select *,DAY(date) as day from mkt_review_contribute where branch_id=$branch_id and YEAR(date)=$mr[year] and MONTH(date)=$mr[month] and cat_root_id=$k order by category_id, date");
				while($mrc=$con->sql_fetchrow($mkt_rc)){
				    //$list[]=$mrc;
					$day=$mrc['day'];
					$val=$mrc['total_contribute'];
					$cat_id=$mrc['category_id'];
					if($day==0){
	    				$form['zero']['con'][$cat_id]=$val;
					}
					if($temp){
						foreach($temp as $k1=>$v1){
							$form[$k1]['normal'][$cat_id]=$temp[$k1]['normal'];
		    				$form[$k1]['sales'][$cat_id]=$temp[$k1]['sales'];
		    				$form[$mrc['date']]['con'][$cat_id]=$val;
		    				$form[$k1]['total'][$cat_id]=$v;
						}
					}
				}

			}
		}
	}

	$header = array_merge($mkt0,$header);
	$form = array_merge($header, $form);
 	//echo"<pre>";print_r($form);echo"</pre>";
	$smarty->assign("departments", $list);
	$smarty->assign("lines", $lines);
	$smarty->assign('form', $form);
}
?>
