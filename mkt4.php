<?php
// mkt0 can only be created in HQ, there is no branch_id in primary key
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

if (!privilege('MKT4_VIEW') and !privilege('MKT4_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT4_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Brand And Item Planner');

//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT4_%'");
while ($r = $con->sql_fetchrow()){
	$mkt4_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt4_privilege", $mkt4_privilege);
//print_r($mkt4_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'open':
			if (!privilege('MKT4_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT4_EDIT', BRANCH_CODE), "/mkt4.php?branch_id=$branch_id");
	    case 'view':
			//print "<pre>";print_r($_REQUEST);print "</pre>";
		    $id = intval($_REQUEST['id']);
		    $branch_id = intval($_REQUEST['branch_id']);
		    $dept_id = intval($_REQUEST['dept_id']);
		    
		    if (!$id || !$branch_id)
			{
				header("Location: /mkt3.php");
				exit;
			}
			$form = array();
			load_header($form);
			//print "<pre>";print_r($form['offers']);print "</pre><br>";
			$con->sql_query("select * from mkt4 where mkt0_id = $id and branch_id=$branch_id and dept_id = $dept_id");
			$mkt4 = $con->sql_fetchrow();
			if ($mkt4)
			{
				$mkt4['offers'] = unserialize($mkt4['offers']);
				$mkt4['brands'] = unserialize($mkt4['brands']);

				// rebuild form array for offers and brands table if not empty
				array_swapindex($mkt4['offers']);
				array_swapindex($mkt4['brands']);

		        $form['brands'] = @array_merge($form['brands'], $mkt4['brands']);
		        $form['offers'] = @array_merge($form['offers'], $mkt4['offers']);
			}
			//print "<pre>";print_r($form);print "</pre>";
			$smarty->assign("form", $form);
			
			$b_code=ms('HQ');
			$q2=$con->sql_query("select * from branch where code=$b_code");
			$r2 = $con->sql_fetchrow($q2);
			// get image path
			$hurl = get_branch_file_url($r2['code'], $r2['ip']);
			$smarty->assign("image_path", $hurl);
			
			if (!$form['approved'] || $form['m4_status'] || ($form['m4_user_id']!=$sessioninfo['id'] && $form['m4_user_id']>0))
			{
			    $_REQUEST['a'] = 'view';
			}
	        $smarty->display("mkt4.edit.tpl");
	        exit;

		case 'confirm':
			if (!privilege('MKT4_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT4_EDIT', BRANCH_CODE), "/mkt4.php?branch_id=$branch_id");
		    $is_confirm=1;
		case 'save':
			if (!privilege('MKT4_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT4_EDIT', BRANCH_CODE), "/mkt4.php?branch_id=$branch_id");
		   	$form = $_REQUEST;
		    $errm = validate_data($form,$is_confirm);
//print "*<pre>";print_r($form['brands']);print "</pre>";
//exit;
		    if (!$errm)
		    {
		        // make mkt4 actual
			    if ($is_confirm) $form['status'] = 1;

			    $form['user_id'] = $sessioninfo['id'];
		        $form['mkt0_id'] = $form['id'];
	    		$dept_id = intval($_REQUEST['dept_id']);
	    		$branch_id = intval($_REQUEST['branch_id']);
	    		$mkt0_id = intval($_REQUEST['id']);
		        $form['added'] = 'CURRENT_TIMESTAMP()';

				array_swapindex($form['offers']);
				array_swapindex($form['brands']);
				
				//$call1=$con->sql_query("select id,code from branch where id>1 and id<>$branch_id order by id");
				//while($r=$con->sql_fetchrow($call1)){
				$q1=$con->sql_query("select branches from mkt0 where id=$mkt0_id");
				$mkt0=$con->sql_fetchrow($q1);
   				$mkt0['branches'] = unserialize($mkt0['branches']);
				foreach($mkt0['branches'] as $key=>$r){			
				if($r!=$branch_id){
				    if($form['offer_copy']){
				    	foreach($form['offer_copy'] as $k=>$v){
				    		if($v){   						
								$call22=$con->sql_query("select offers, approved from mkt3 where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								$r22=$con->sql_fetchrow($call22);
								
								$call2=$con->sql_query("select offers, status from mkt4 where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								$r2=$con->sql_fetchrow($call2);
								
								if(!$r22['approved']){//unapproved_branch
	    							$errm['offers'][] = "<font color=blue>{$_REQUEST[offers][description][$k]}</font> cannot copy to $key (unapporved branch).";
								}
								elseif($r2['status']!=0){//confirmed_branch
	    							$errm['offers'][] = "<font color=blue>{$_REQUEST[offers][description][$k]}</font> cannot copy to $key (Confirmed MKT).";
								}
								else{//approved_branch
									if($r2){
									    $offer=array(); 
										$offer = unserialize($r2['offers']);
										$offer[]=$form['offers'][$k];
										$offer = sz($offer);
										$con->sql_query("update mkt4 set offers=$offer where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
									}
									else{
		          						$offer=array(); 
										$o=array();  
										$mkt['mkt0_id']=$mkt0_id;
										$mkt['branch_id']=$r;
										$mkt['dept_id']=$dept_id;										
										$offer = unserialize($r22['offers']);
										//print "1-$k-$v<pre>";print_r($form['offers'][$k]);print "</pre>";				
										$o[]=$form['offers'][$k];
										//print "2-<pre>";print_r($o);print "</pre>";
										$off = @array_merge($offer,$o);
										//print "3-<pre>";print_r($off);print "</pre>";
										//exit;
										$mkt['offers']=serialize($off);	
																											
										$con->sql_query("insert into mkt4 ".mysql_insert_by_field($mkt,array('mkt0_id','branch_id','dept_id','offers','added'))) or die(mysql_error());									
									}	
								}

							}
						}						
					}
				    if($form['brand_copy']){
				    	foreach($form['brand_copy'] as $k1=>$v1){
				    		if($v1){   						
								$call22=$con->sql_query("select brands, approved from mkt3 where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								$r22=$con->sql_fetchrow($call22);
								
								$call2=$con->sql_query("select brands, status from mkt4 where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								$r2=$con->sql_fetchrow($call2);
								
								if(!$r22['approved']){//unapproved_branch
	    							$errm['brands'][] = "<font color=blue>{$_REQUEST[brands][brand][$k1]}</font> cannot copy to $key (unapporved branch).";
								}
								elseif($r2['status']!=0){//confirmed_branch
	    							$errm['brands'][] = "<font color=blue>{$_REQUEST[brands][brand][$k1]}</font> cannot copy to $key (Confirmed MKT).";
								}
								else{//approved_branch
									if($r2){
									    $brand=array(); 
										$brand = unserialize($r2['brands']);
										$brand[]=$form['brands'][$k1];
										$brand = sz($brand);
										$con->sql_query("update mkt4 set brands=$brand where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
									}
									else{
		          						$brand=array(); 
										$b=array();  
										$mkt['mkt0_id']=$mkt0_id;
										$mkt['branch_id']=$r;
										$mkt['dept_id']=$dept_id;										
										$brand = unserialize($r22['brands']);
										//print "1-$k1-$v1<pre>";print_r($form['brands']);print "</pre>";				
										$b[]=$form['brands'][$k1];
										//print "2-<pre>";print_r($b);print "</pre>";
										$bra = @array_merge($brand,$b);
										//print "3-<pre>";print_r($bra);print "</pre>";
										//exit;
										$mkt['brands']=serialize($bra);	
																											
										$con->sql_query("insert into mkt4 ".mysql_insert_by_field($mkt,array('mkt0_id','branch_id','dept_id','brands','added'))) or die(mysql_error());									
									}	
								}

							}
						}						
					}				
				    /*if($form['offer_copy']){
				    	foreach($form['offer_copy'] as $k=>$v){
				    		if($v){
          						$offer=array();
								$call2=$con->sql_query("select * from mkt4 where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								$r2=$con->sql_fetchrow($call2);
								if($r2['offers']){
									$offer = unserialize($r2['offers']);
									$offer[]=$form[offers][$k];
								}
								else{
									//$offer[]=$form[offers][$k];
	    							$errm['offers'][] = "<font color=blue>{$_REQUEST[offers][description][$k]}</font> cannot copy to $key (unapporved branch).";
								}
								$offer = serialize($offer);

								if($r2['status']==0){
									$con->sql_query("update mkt4 set offers='$offer' where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								}
								else{
	    							$errm['offers'][] = "<font color=blue>{$_REQUEST[offers][description][$k]}</font> cannot copy to $key (Confirmed MKT).";
								}
							}
						}
					}
				    if($form['brand_copy']){
				    	foreach($form['brand_copy'] as $k=>$v){
				    		if($v){
          						$brand=array();
								$call2=$con->sql_query("select brands from mkt4 where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								$r2=$con->sql_fetchrow($call2);
								if($r2['brands']){
									$brand = unserialize($r2['brands']);
									$brand[]=$form[brands][$k];
								}
								else{
									//$brand[]=$form[brands][$k];
	    							$errm['brands'][] = "<font color=blue>{$_REQUEST[brands][brand][$k]}</font> cannot copy to $key (unapporved branch).";
								}
								$brand = serialize($brand);
								if($r2['status']==0){
									$con->sql_query("update mkt4 set brands='$brand' where branch_id=$r and dept_id=$dept_id and mkt0_id=$mkt0_id");
								}
								else{
	    							$errm['offers'][] = "<font color=blue>{$_REQUEST[brands][brand][$k]}</font> cannot copy to $key (Confirmed MKT).";
								}
							}
						}
					}*/
				}
				}

				$form['offers'] = serialize($form['offers']);
			    $form['brands'] = serialize($form['brands']);
			    
				$con->sql_query("replace into mkt4 ".mysql_insert_by_field($form,array('mkt0_id','branch_id','dept_id','user_id','offers','brands','added','status'))) or die(mysql_error());

			    // after save , return to front page
			    if ($is_confirm && !$errm)
				{
					log_br($sessioninfo['id'], 'MKT', $form['id'], "MKT4 Confirmed (ID#form[$id], Branch #$form[branch_id], Dept: $form[dept_id])");
			    	header("Location: /mkt4.php?t=confirm&id=$form[id]&branch_id=$branch_id");
			    }
			    elseif (!$errm){
			    	header("Location: /mkt4.php?t=save&id=$form[id]&branch_id=$branch_id");
				}
				else{
					$form_1 = $_REQUEST;
			        load_header($form_1);
       		    	validate_data($form_1,$is_confirm);
			        $smarty->assign("form", $form_1);
			        $smarty->assign("errm", $errm);
					$smarty->display("mkt4.edit.tpl");
				}

		    }
		    else
		    {
				//print "<pre>";print_r($form);print "</pre>";
		        load_header($form);
		        $smarty->assign("form", $form);
		        $smarty->assign("errm", $errm);
				$smarty->display("mkt4.edit.tpl");

			}
			exit;

		case 'ajax_load_mkt_list':
			 //print "<pre>";print_r($_REQUEST);print "</pre>";
		    load_mkt_list();
		    $smarty->display("mkt4.home.list.tpl");
		    exit;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display('mkt4.home.tpl');

function load_mkt_list(){
	global $con, $sessioninfo, $smarty, $depts;
	if($sessioninfo['branch_id']==1){
		if($_REQUEST['branch_id']){
    		$branch_id = intval($_REQUEST['branch_id']);
		}
		else{
    		$branch_id = 2;
		}
	}
	else{
		$branch_id=$sessioninfo['branch_id'];
	}
	$smarty->assign("branch_id", $branch_id);
	
	if (!$t) $t = intval($_REQUEST['t']);
	/*if ($sessioninfo['level']>=9999)
		$owner_check = "";
	else
		$owner_check = "(mkt0.user_id = $sessioninfo[id]) and";
		*/

    $where = "mkt0.active and mkt0.status = 1 and mkt3.approved=1 and mkt0.branches like '%$branch_id%'";
	switch ($t)
	{
	    case 0:
	        $where .= 'and (mkt0.id = ' . mi($_REQUEST['s']) . ' or mkt0.title like ' . ms('%'.$_REQUEST['s'].'%') . ')';
	        $_REQUEST['s'] = '';
	        break;

	}
	
	if (isset($_REQUEST['s']))
		$s = intval($_REQUEST['s']);

	$con->sql_query("select count(*),mkt0.id
from mkt3
left join mkt0 on mkt3.mkt0_id = mkt0.id and mkt3.branch_id = $branch_id
left join mkt4 on mkt3.mkt0_id = mkt4.mkt0_id and mkt3.branch_id = mkt4.branch_id and mkt3.dept_id = mkt4.dept_id
left join user on mkt4.user_id = user.id
left join user user2 on mkt0.user_id = user2.id
left join category on mkt3.dept_id = category.id
where $where
group by mkt0.id
order by mkt3.last_update desc
");

	$i=0;
	$pg = "<b>Select </b> <select onchange=\"list_sel($t,this.value)\">";
	while($r = $con->sql_fetchrow()){
		$pg .= "<option value=$r[1]";
		if ($i==0 && !$s){
		    $s=$r[1];
			$pg .= " selected";
		}
		elseif($s==$r[1]){
			$pg .= " selected";
		}
		$title=sprintf("MKT%05d",$r[1]);
		$pg .= ">$title</option>";
		$i++;
	}
	$pg .= "</select>";
	$smarty->assign("total_mkt", $i);
	$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
	
	// pagination
	/*$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$con->sql_query("select count(*)
from mkt3
left join mkt0 on mkt3.mkt0_id = mkt0.id and mkt3.branch_id = $branch_id
left join mkt4 on mkt3.mkt0_id = mkt4.mkt0_id and mkt3.branch_id = mkt4.branch_id and mkt3.dept_id = mkt4.dept_id
left join user on mkt4.user_id = user.id
left join user user2 on mkt0.user_id = user2.id
left join category on mkt3.dept_id = category.id
where $where");

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
	}*/

	$con->sql_query("select mkt0.*, mkt3.*, mkt4.*, mkt3.dept_id, category.description as dept, user.u, user2.u as create_u
from mkt3
left join mkt0 on mkt3.mkt0_id = mkt0.id and mkt3.branch_id = $branch_id
left join mkt4 on mkt3.mkt0_id = mkt4.mkt0_id and mkt3.branch_id = mkt4.branch_id and mkt3.dept_id = mkt4.dept_id
left join user on mkt4.user_id = user.id
left join user user2 on mkt0.user_id = user2.id
left join category on mkt3.dept_id = category.id
where $where and mkt0.id=".mi($s)."
order by mkt3.last_update desc");

	$smarty->assign("list", $con->sql_fetchrowset());

}

function validate_data(&$form, $is_confirm)
{
	global $LANG;
	$errm = array();

    // exclude blank rows from offer items proposal
	$offers = array();
	foreach($form['offers']['sku_item_id'] as $k=>$v)
	{
	    if ($v!='')
	    {
	        foreach(array_keys($form['offers']) as $kk)
	        {
				$offers[$kk][$k] = $form['offers'][$kk][$k];
			}
		}
	}
	$form['offers'] = $offers;

	if (!$form['offers'] && $form['limit']['min_offer']!=0)
	{
	    $errm['offers'][] = $LANG['MKT3_NO_OFFERS'];
	}
	elseif (count($form['offers']['sku_item_id']) < $form['limit']['min_offer'])
	{
	    $errm['offers'][] = sprintf($LANG['MKT3_NEED_AT_LEST_X_OFFERS'], $form['limit']['min_offer']);
	}

    // exclude blank rows from brand proposal
	$brands = array();
	foreach($form['brands']['brand_id'] as $k=>$v)
	{
	    if ($v!='')
	    {
	        foreach(array_keys($form['brands']) as $kk)
	        {
				$brands[$kk][$k] = $form['brands'][$kk][$k];
			}
		}
	}
	$form['brands'] = $brands;

	if (!$form['brands'] && $form['limit']['min_brand']!=0)
	{
	    $errm['brands'][] = $LANG['MKT3_NO_BRANDS'];
	}
	elseif (count($form['brands']['brand_id']) < $form['limit']['min_brand'])
	{
	    $errm['brands'][] = sprintf($LANG['MKT3_NEED_AT_LEST_X_BRANDS'], $form['limit']['min_brand']);
	}

	return $errm;
}

function load_header(&$form)
{
	global $con, $LANG,$smarty;

    $id = intval($_REQUEST['id']);
	$branch_id = intval($_REQUEST['branch_id']);
    $dept_id = intval($_REQUEST['dept_id']);
	$smarty->assign("branch_id", $branch_id);
	
	$c1=$con->sql_query("select mkt0.*, mkt4.status as m4_status, mkt4.user_id as m4_user_id, branch.code as current_branch
from mkt0
left join branch on branch.id=$branch_id
left join mkt4 on mkt4.mkt0_id = mkt0.id  and mkt4.branch_id = $branch_id and mkt4.dept_id=$dept_id
where mkt0.id =$id");
	$header = $con->sql_fetchrow();
	/// invalid id
	if (!$header)
		show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));
	$header['attachments'] = unserialize($header['attachments']);
	$header['publish_dates'] = unserialize($header['publish_dates']);
	if ($header['attachments']['file'])
	{
		foreach ($header['attachments']['file'] as $idx=>$fn)
		{
			if ($fn) $header['filepath'][$idx] = "mkt_attachments/$header[id]/$fn";
		}
	}

	$con->sql_query("select description from category where id = $dept_id");
	$r = $con->sql_fetchrow();

	$header['branch_id'] = $branch_id;
	$header['dept_id'] = $dept_id;
	$header['department'] = $r['description'];
	$header['branches'] = unserialize($header['branches']);

	foreach($header['branches'] as $k=>$v){
		$q2=$con->sql_query("select approved from mkt3 where branch_id=$v and dept_id=$dept_id and mkt0_id=$id");
		$r2=$con->sql_fetchrow();
		$q22=$con->sql_query("select status from mkt4 where branch_id=$v and dept_id=$dept_id and mkt0_id=$id");
		$r22=$con->sql_fetchrow();
		if(!$r2['approved']){
			$header['unapproved_branch'][$k]=$v;
		}
		elseif($r22['status']!=0){
			$header['confirmed_branch'][$k]=$v;
		}
		else{
			$header['approved_branch'][$k]=$v;		
		}
	}
	

	
	$con->sql_query("select * from mkt2 where mkt0_id = $id and dept_id=$dept_id and branch_id=$branch_id");
	$mkt2 = $con->sql_fetchrow();

	$header['sales_target'] = $mkt2['sales_target'];
	$header['normal_target'] = $mkt2['normal_target'];

	$con->sql_query("select * from mkt3 where mkt0_id = $id and branch_id=$branch_id and dept_id = $dept_id");
	$mkt3 = $con->sql_fetchrow();
	$mkt3['offers'] = unserialize($mkt3['offers']);
	$mkt3['brands'] = unserialize($mkt3['brands']);

    $header = array_merge($mkt3,$header);

	// get minimum number of input needed from mkt settings
	$con->sql_query("select min_offer,max_offer,min_brand,max_brand from mkt_settings where branch_id = $branch_id and dept_id = $dept_id");
	$r = $con->sql_fetchrow();
    $header['limit'] = $r;

	// rebuild form array for offers and brands table if not empty
	array_swapindex($header['offers']);
	array_swapindex($header['brands']);

	$form = array_merge($header,$form);
}
?>
