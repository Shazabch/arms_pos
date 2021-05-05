<?
/*
12/28/2010 2:49:49 PM Alex
- change use multiple branch
1/5/2011 5:34:19 PM Alex
- add function can add/remove multiple branch
1/11/2011 5:49:15 PM Alex
- fix to avoid duplicated department, brand or vendor
3/15/2011 5:51:54 PM Alex
- add function ajax_activate_deactivate() to change consignment bearing active status
6/8/2011 5:04:29 PM Alex
- add checking discount at validate_data()

6/24/2011 4:46:34 PM Andy
- Make all branch default sort by sequence, code.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_CONTABLE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CONSIGNMENT BEARING', BRANCH_CODE), "/index.php");
//if (!privilege('MST_BRANCH')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRANCH', BRANCH_CODE), "/index.php");
//$maintenance->check(3);

class Mst_Consignment_Bearing extends Module{

	function __construct($title){
	    global $smarty, $con, $sessioninfo;

		$this->group_id = intval($_REQUEST['group_id']);

		if (BRANCH_CODE == 'HQ') 	$this->bid = intval($_REQUEST['branch_id']);
		else{
			$this->bid = $sessioninfo['branch_id'];
		}

		$con->sql_query("select * from category where id in ($sessioninfo[department_ids]) order by description");
		$smarty->assign("departments", $con->sql_fetchrowset());
		$con->sql_query("select * from branch order by sequence, code");
		$smarty->assign("branches", $con->sql_fetchrowset());

		if (BRANCH_CODE == 'HQ')	$smarty->assign('edit_on',1);
		else    $smarty->assign('read_only',1);

 		parent::__construct($title);
	}

	function _default(){
		unset($_REQUEST);
	    $this->load_consignment_list($this->bid);
	    $this->display();
	    exit;
	}
	
	function new_consignment(){
	    global $smarty;

        if (BRANCH_CODE != 'HQ')
            header("Location: /masterfile_consignment_bearing.php");
		else
        	$smarty->display('masterfile_consignment_bearing.multi_branch_edit.tpl');
	}
	
	function create_new(){
	    global $smarty;
		$errm=$this->create_new_consignment();
		if ($errm){
		    $smarty->assign('errm',$errm);
		}else{
   			$smarty->assign('consignment_exist',1);
		}

        $smarty->display('masterfile_consignment_bearing.multi_branch_edit.tpl');
	}

	function view_items(){
		$this->view_or_edit_mode('view');
	}

	function edit_items(){
		$this->view_or_edit_mode('edit');
	}

	function view_or_edit_mode($mode){
	    global $smarty;
	    if ($mode == 'view')	$smarty->assign('read_only',1);
	    else{
    		if (!privilege('MST_CONTABLE_EDIT'))
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CONSIGNMENT BEARING EDIT', BRANCH_CODE), "/masterfile_consignment_bearing.php");
		}
		$errm=$this->load_consignment_items($this->group_id);
		if ($errm){
		    $smarty->assign('errm',$errm);
		    if (BRANCH_CODE=='HQ')	$this->bid='';
		    $this->_default();
		    exit;
		}
        $smarty->display('masterfile_consignment_bearing.multi_branch_edit.tpl');
	}

	function update_consignment(){
		global $con,$smarty;

		$group_id=$_REQUEST['group_id'];
		//return $br['untick'] or $br['tick']
		$br = $this->ajax_check_cons_branch(true);

		//add new branch
		if ($br['tick']){
			$_REQUEST['branch_id']=$br['tick'];

			$errm=$this->create_new_consignment($_REQUEST['group_id']);

			if ($errm){
			    $smarty->assign('errm',$errm);
			}
		}

		//delete consignment_bearing
		if (!$errm && $br['untick']){
			$del_sql="delete cb ,cbi
				from consignment_bearing cb
				left join consignment_bearing_items cbi on cbi.branch_id=cb.branch_id and cbi.consignment_bearing_id=cb.id
				where cb.branch_id in (".join(',',$br['untick']).") and cb.group_id=$group_id";

			$con->sql_query($del_sql);

			$con->sql_query("select id,code from branch where id in (".join(",",$br['untick']).")");
			while ($b=$con->sql_fetchrow()){
				$delete_branch[$b['id']]=$b['code'];
			}

			log_br($sessioninfo['id'],'CONSIGNMENT_BEARING',$group_id,"Delete Consignment Bearing (Branch Code: ".join(",",$delete_branch).", Department_id: $dept_id, group id: $group_id )");
		}
	
		$this->edit_items();
	}
	
	function update_items(){
		global $con,$smarty,$sessioninfo, $LANG;

		$form=$_REQUEST;
		$group_id=$form['group_id'];


		if ($form['p_type']){
			foreach ($form['p_type'] as $branch_id => $no_code){

				$sql_sel="select * from consignment_bearing cb where group_id=$group_id and branch_id=$branch_id ";

				$query_sel=$con->sql_query($sql_sel);
				$cb=$con->sql_fetchrow($query_sel);

				$bearing['consignment_bearing_id']=$cb['id'];
				$bearing['branch_id']=$branch_id;
				$bearing['added']='CURRENT_TIMESTAMP';

				$sql_del="delete from consignment_bearing_items where consignment_bearing_id=$bearing[consignment_bearing_id] and branch_id=".mi($bearing['branch_id']);
				$con->sql_query($sql_del);

			    foreach ($no_code as $row_id => $price_code){

                    $bearing['trade_discount_type_code']=$price_code;
					$bearing['profit']=$form['profit'][$branch_id][$row_id];
					$bearing['discount']=$form['discount'][$branch_id][$row_id];
					$bearing['use_net']=$form['use_net'][$branch_id][$row_id];
					$bearing['net_bearing']=$form['net_bearing'][$branch_id][$row_id];
					$bearing['sequence_num']=$row_id;

					$bearing['created_by']=$sessioninfo['id'];


					$sql_ins = "insert into consignment_bearing_items ".mysql_insert_by_field($bearing);

					$con->sql_query($sql_ins);

				    $con->sql_query("update consignment_bearing set last_update=CURRENT_TIMESTAMP where id=$bearing[consignment_bearing_id] and branch_id=$bearing[branch_id]");
				    
				}
			    $branches_ids[$branch_id]=$branch_id;
			}
			
			log_br($sessioninfo['id'],'CONSIGNMENT_BEARING',$group_id,"Insert items for Consignment Bearing (Branches ids: ".join(', ',$branches_ids).") ");
			
		}
		
		$msg[]=$LANG['MCB_UPDATE'];
		
        $smarty->assign("msg",$msg);

//		$smarty->assign("bid",$this->bid);
		$this->load_consignment_list($this->bid);
		
	    $this->display();
	}

	function delete_consignment_items(){
	    global $smarty, $con, $LANG;

		$con->sql_query("select * from consignment_bearing where group_id=$this->group_id and branch_id=$this->bid");
		
		$cb=$con->sql_fetchrow();
		$cons_id=$cb['id'];
		
	    $con->sql_query("delete from consignment_bearing where group_id=$this->group_id and branch_id=$this->bid");


		$con->sql_query("delete from consignment_bearing_items where consignment_bearing_id=$cons_id and branch_id=$this->bid");
		log_br($sessioninfo['id'],'CONSIGNMENT_BEARING',$this->group_id,"Consignment Bearing Deleted (Branch id: $this->bid, Consignment Bearing id: $cons_id) ");
        $smarty->assign("msg",$LANG['MCB_DELETE']);
//		$smarty->assign("bid",$this->bid);
		$this->load_consignment_list($this->bid);
		$this->display();
	}
	
	function ajax_load_department(){
		global $con;

		//masterfile_consignment_bearing
		$bid=$this->bid;

		if ($bid)	$filter[] = "cb.branch_id=$bid";
		if ($filter)	$where = "where ".join(' and ', $filter);

		$sql="select category.id, category.description  from consignment_bearing cb
		    left join category on cb.dept_id=category.id
			$where
			group by cb.dept_id
			";

		$con->sql_query($sql);

		$rows=$con->sql_numrows();
		if ($rows>0){
			if ($rows>1)    $out="<option value='All'>All</option>";

			while($r=$con->sql_fetchrow()){
				$selected="";
			    if ($_REQUEST['department_id'] == $r[id] ) $selected=" selected ";

	            $out.="<option value='$r[id]' $selected>$r[description]</option>";
			}
		}else{
            $out="<option value=''>No Data</option>";
		}

		print $out;
	}
	
	function ajax_load_r_type_vendor_brand(){

		global $con;

		//masterfile_consignment_bearing
		$bid=$this->bid;
		$dept_id=$_REQUEST['dept_id'];

		if ($this->bid)	$filter[] = "cb.branch_id=$this->bid";
		if ($dept_id != "All") $filter[]="cb.dept_id=$dept_id";

		if ($filter)		$where = " where ".join(' and ',$filter);

		$sql="select cb.r_type,
			brand.id as brand_id,brand.description as brand_description,
			vendor.id as vendor_id,vendor.description as vendor_description
			from consignment_bearing cb
		    left join brand on cb.brand_id=brand.id
		    left join vendor on cb.vendor_id = vendor.id
			$where";

		$con->sql_query($sql);

		$rows=$con->sql_numrows();
		if ($rows>0){
			while($r=$con->sql_fetchrow()){
				$r_type[$r['r_type']]=$r['r_type'];

				if ($r['brand_id'])		$brand[$r['brand_id']] = $r['brand_description'];

				if ($r['vendor_id'])	$vendor[$r['vendor_id']] = $r['vendor_description'];
			}

			//r_type options
			if ($r_type){
				if (count($r_type)>1)  $out['r_type']="<option value=''>All</option>";
				foreach ($r_type as $type){
	   				$selected_r="";
				    if ($_REQUEST['r_type'] == $type) $selected_r=" selected ";

		            $out['r_type'].="<option value='$type' $selected_r>".ucfirst($type)."</option>";
				}
			}
			
			//brand options
  			if ($brand){
				if (count($brand)>1)  $out['brand']="<option value='All'>All</option>";

				foreach ($brand as $brand_id=> $brand_description){
	   				$selected_r="";
				    if ($_REQUEST['brand_id'] == $brand_id) $selected_r=" selected ";

	                $out['brand'].="<option value='$brand_id' $selected_r>".ucwords($brand_description)."</option>";
				}
			}else{
   			    $out['brand']="<option value=''>No Data</option>";
			}

			//vendor options
			if ($vendor){
				if (count($vendor)>1)  $out['vendor']="<option value='All'>All</option>";

				foreach ($vendor as $vendor_id=> $vendor_description){
	   				$selected_r="";
				    if ($_REQUEST['vendor_id'] == $vendor_id) $selected_r=" selected ";

	                $out['vendor'].="<option value='$vendor_id' $selected_r>$vendor_description</option>";
				}
			}else{
       			$out['vendor']="<option value=''>No Data</option>";
			}
		}else{
            $out['r_type']="<option value=''>No Data</option>";
            $out['brand']="<option value=''>No Data</option>";
            $out['vendor']="<option value=''>No Data</option>";
		}

		print_r(json_encode($out));
	}


	function ajax_load_consignment_list(){
		global $smarty;
		
		$this->load_consignment_list($this->bid);
  		$smarty->display('masterfile_consignment_bearing.list.tpl');
	}

	function ajax_validate_data(){
	    $errm=$this->validate_data();

	    if ($errm){
			foreach ($errm['mid'] as $err_msg){
				print "<li>".$err_msg."</li>";
			}
		}
	    else{
	        print "ok";
		}
	}

	function ajax_check_cons_branch($return_data=false){
	    global $con;
	    $group_id=$_REQUEST['group_id'];

		if (!$_REQUEST['branch_id']){
			print "NO";
			return;
		}
		
	    foreach ($_REQUEST['branch_id'] as $bid){
			$input_branch[$bid]=$bid;
		}

	    $sql_sel="select * from consignment_bearing cb where group_id=$group_id";
		$query_sel=$con->sql_query($sql_sel);
		
        $total_branch=$con->sql_numrows();
		while($cb=$con->sql_fetchrow($query_sel)){
  		    $db_branch[$cb['branch_id']]=$cb['branch_id'];
		}

		//compare branches
		foreach ($input_branch as $bid){
			if ($db_branch[$bid]){
				unset($db_branch[$bid]);
				unset($input_branch[$bid]);
			}
		}
		
		if (!$db_branch) $db_branch=array();
		if (!$input_branch) $input_branch=array();
		 
		$branches=array_merge($input_branch,$db_branch);

		//if no edit return
		if (!$branches){
			if (!$return_data)	print "OK";
			return;
		}

		//get branch code
	    $sql_br="select * from branch where id in (".join(",",$branches).") order by sequence,code";
		$q_br=$con->sql_query($sql_br);
		while ($br=$con->sql_fetchrow($q_br)){
			$branch_codes[$br['id']]=$br['code'];
		}

	    if ($db_branch){
	        //unticked branch
	        $b_result['untick']=$db_branch;

			foreach ($db_branch as $un_bid){
			    $un_branches[]=$branch_codes[$un_bid];
			}
			
			$msg[]="Unticked branches: ".join(", ",$un_branches);
		}

	    if ($input_branch){
	        //new ticked branch
	        $b_result['tick']=$input_branch;
	        
			foreach ($input_branch as $ti_bid){
			    $ti_branches[]=$branch_codes[$ti_bid];
			}
			$msg[]="New ticked branches: ".join(", ",$ti_branches);
		}

		if ($return_data)	return $b_result; //for php use
		else	print join("\n", $msg); //for ajax call use
	}
	
	function ajax_activate_deactivate(){
		global $con,$LANG;

		$group_id=$_REQUEST['group_id'];
		$upd['active']=$_REQUEST['act_deact'];
		$upd['last_update']='CURRENT_TIMESTAMP';

		$con->sql_query("update consignment_bearing set ".mysql_update_by_field($upd)." where group_id=$group_id");

		if ($upd['active']) $msg="Activate";
		else    $msg="Deactivate";
		
		log_br($sessioninfo['id'],'CONSIGNMENT_BEARING',$group_id,"$msg Consignment Bearing group id: $group_id)");

		print "OK";
	}
	
	function load_consignment_list($bid){
		global $con,$smarty,$sessioninfo,$LANG;

		if ($bid)  								$filter[] = "cb.branch_id=$bid";
		if (intval($_REQUEST['department_id']))	$filter[] = "cb.dept_id=$_REQUEST[department_id]";
		else                                    $filter[] = "cb.dept_id in ($sessioninfo[department_ids])";
		
		if ($_REQUEST['r_type'])       			$filter[] = "cb.r_type=".ms($_REQUEST['r_type']);
		if (intval($_REQUEST['vendor_id']))		$filter[] = "cb.vendor_id=$_REQUEST[vendor_id]";
		if (intval($_REQUEST['brand_id']))		$filter[] = "cb.brand_id=$_REQUEST[brand_id]";
		if ($_REQUEST['status'] != '')       $filter[] = "cb.active=$_REQUEST[status]";

		if ($filter)	$where = "where ".join(' and ',$filter);

		// create pagination
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else
			$sz = 25;

		$con->sql_query("select *
						from consignment_bearing cb
 						$where group by cb.group_id");
 					
		$total = $con->sql_numrows();

		if (!$total)    return;
		
		$smarty->assign('total_row', $total);
		if ($total > $sz){
			if ($start > $total) $start = 0;
			// create pagination
			$pg="";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start){
					$pg .= " selected";
				}
				$pg .= ">$p</option>";
			}
			$pg .= "</select>&nbsp;&nbsp;";

			$pg = "<b>Page</b> <select name=s id='s_id' onchange=\"change_consignment_list(this.value);\">".$pg;
			$smarty->assign("pagination", "$pg");
		}

		$limit = "limit $start, $sz";

		//Only HQ can see multiple branch
		if (BRANCH_CODE == "HQ"){
			$sql_get_limit = "select cb.group_id from consignment_bearing cb $where group by cb.group_id $limit";
			$s_get_limit = $con->sql_query($sql_get_limit);
			if ($con->sql_numrows()>0){
				while($gl = $con->sql_fetchrow($s_get_limit)){
				    if (!$gl['group_id'])   continue;
	                $group_ids[$gl['group_id']]=$gl['group_id'];
				}

				if ($group_ids){
					$where = "where cb.group_id in (".join(",",$group_ids).")";
					$limit = "";
				}
			}
		}

		$sql="select cb.id, cb.branch_id ,cb.r_type, cb.added as addded_date, cb.last_update as last_update, cb.group_id, cb.active,category.description as cat_des, vendor.description as vendor_des, brand.description as brand_des, user.u as username, branch.code as branch_code
						from consignment_bearing cb
				        left join vendor on cb.vendor_id=vendor.id
				        left join brand on cb.brand_id=brand.id
				        left join category on cb.dept_id=category.id
				        left join user on cb.created_by=user.id
				        left join branch on cb.branch_id=branch.id
						$where
						order by cb.last_update desc $limit";

		$con->sql_query($sql);

		if (BRANCH_CODE == "HQ"){
		    //display multiple branch on list
			while ($c=$con->sql_fetchrow()){
				$br_arr[$c['group_id']][$c['branch_id']]=$c['branch_code'];

				$consignment[$c['group_id']]=$c;
			}

			foreach ($br_arr as $gid => $bbcode){
	            $consignment[$gid]['branch_code']=join(', ', $bbcode);
			}
		}else{
            $consignment=$con->sql_fetchrowset();
		}

		$smarty->assign("consignment", $consignment);
	}

	function load_consignment_items($group_id){
		global $con,$smarty,$LANG;

		if (BRANCH_CODE != "HQ") $filter=" and cb.branch_id=$this->bid";

		$sql= "select cb.*, vendor.description as vendor, brand.description as brand, category.description as cat_des,
				branch.code as branch_code
				from consignment_bearing cb
				left join consignment_bearing_items cbi on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
		        left join vendor on cb.vendor_id=vendor.id
		        left join brand on cb.brand_id=brand.id
		        left join category on cb.dept_id=category.id
		        left join branch on cb.branch_id=branch.id
				where cb.group_id=$group_id $filter";

		$detail['group_id']=$group_id;

		$cb_q=$con->sql_query($sql);
		if ($con->sql_numrows($cb_q)>0){
			while($r=$con->sql_fetchrow($cb_q)){

				$detail['branch_arr'][$r['branch_id']]=$r['branch_code'];
				$detail['branch_id'][$r['branch_id']]=$r['branch_id'];
				$detail['dept']=$r['cat_des'];
				$detail['dept_id']=$r['dept_id'];
				$detail['vendor_id']=$r['vendor_id'];
				$detail['vendor']=$r['vendor'];
				$detail['brand_id']=$r['brand_id'];
				$detail['brand']=$r['brand'];
				$detail['r_type']=$r['r_type'];

				//get consignment bearing items
				$sql2= "select cbi.*, branch.code as branch_code
						from consignment_bearing_items cbi
						left join branch on cbi.branch_id=branch.id
						where cbi.consignment_bearing_id=$r[id] and cbi.branch_id=$r[branch_id]
						order by trade_discount_type_code,branch.sequence,branch_code,discount,use_net,sequence_num";

				$cbi_q=$con->sql_query($sql2);
				if($con->sql_numrows($cbi_q)>0){
					while($p=$con->sql_fetchrow($cbi_q)){
					    $prices[$p['sequence_num']][$p['branch_id']]['trade_discount_type_code']=$p['trade_discount_type_code'];
						$prices[$p['sequence_num']][$p['branch_id']]['profit']=$p['profit'];
						$prices[$p['sequence_num']][$p['branch_id']]['use_net']=$p['use_net'];
						$prices[$p['sequence_num']][$p['branch_id']]['net_bearing']=$p['net_bearing'];
						$prices[$p['sequence_num']][$p['branch_id']]['discount']=$p['discount'];
					}
				}
			}

			$detail['group_id']=$group_id;

			$this->load_price_type($detail);

			$detail['branch_code']=join(", ",$detail['branch_arr']);
			$smarty->assign('detail',$detail);
			$smarty->assign('prices',$prices);
			$smarty->assign('branch_col',count($detail['branch_arr']));
			$smarty->assign('consignment_exist',1);
		}
		else{
			$errm['top'][]=$LANG['MCB_DATA_NOT_EXIST'];
		}
		return $errm;
	}

	function load_price_type($arr_data){
		global $con, $smarty;

		$branches=join(",",$arr_data['branch_id']);

		if ($arr_data['r_type']=='none'){
			$sql="select code from trade_discount_type order by code";
			$con->sql_query($sql);
			while ($r=$con->sql_fetchrow()){
				$price_type[$r['code']][0]['rate']=0;
			}
			$smarty->assign('price_type',$price_type);
			return;
			
		}elseif ($arr_data['r_type']=='brand')
			$sql="select branch_id, skutype_code as code, rate from brand_commission where branch_id in ($branches) and department_id=$arr_data[dept_id] and rate>0 and brand_id=$arr_data[brand_id] order by skutype_code";
		elseif ($arr_data['r_type']=='vendor')
			$sql="select branch_id, skutype_code as code, rate from vendor_commission where branch_id in ($branches) and department_id=$arr_data[dept_id] and rate>0 and vendor_id=$arr_data[vendor_id] order by skutype_code";

		$con->sql_query($sql);
		while ($r=$con->sql_fetchrow()){
			$price_type[$r['code']][$r['branch_id']]['rate']=$r['rate'];
		}

		$smarty->assign('price_type',$price_type);
	}

	function create_new_consignment($group_id=''){
		global $con, $smarty, $sessioninfo, $LANG;

		$form=$_REQUEST;

		//----------------Checking Data-------------------------------------->

		$missing=array();
		$trig_none=false;
		
		if ($form['branch_id']!=''){
			foreach ($form['branch_id'] as $bid){
				$branch_arr[$bid]=$bid;	
			}
  			$branch_id=join(",",$form['branch_id']);

//			$detail['branch_id']=$form['branch_id'];

			$con->sql_query("select id,code from branch where id in ($branch_id) order by sequence,code");
			while ($b=$con->sql_fetchrow()){
				$detail['branch_arr'][$b['id']]=$b['code'];
			}
			$detail['branch_code']=join(', ',$detail['branch_arr']);
			$smarty->assign('branch_col',count($detail['branch_arr']));
		}
		else
			$missing[]="branch";

		if ($form['dept_id']!=''){
			$dept_id=$form['dept_id'];
			$detail['dept_id']=$dept_id;

			$con->sql_query("select description from category where id=$dept_id");
			$d=$con->sql_fetchrow();
			$detail['dept']=$d['description'];

		}
		else
			$missing[]="department";

		//Check Type
		$r_type=$_REQUEST['r_type'];
		$detail['r_type']=$r_type;

		if ($r_type=='vendor'){
		    if ($form['vendor_id']!=''){
				$vendor_id=$form['vendor_id'];
	            $detail['vendor_id']=$vendor_id;
	            $detail['vendor']=$form['vendor'];
	        	$sql_check_exist="select distinct branch_id, skutype_code as code, rate from vendor_commission where branch_id in ($branch_id) and department_id=$dept_id and rate>0 and vendor_id=$vendor_id";
			}
			else
			    $missing[]="vendor";

		}
		elseif ($r_type=='brand'){
			if ($form['brand_id']!=''){
				$brand_id=$form['brand_id'];
				$detail['brand_id']=$brand_id;
	            $detail['brand']=$form['brand'];
	            $sql_check_exist="select distinct branch_id, skutype_code as code, rate from brand_commission where branch_id in ($branch_id) and department_id=$dept_id and rate>0 and brand_id=$brand_id";
			}
			else
			    $missing[]="brand";

		}
		elseif ($r_type=='none'){
			$trig_none=true;
			$sql_check_exist="";
			$con->sql_query("select code from trade_discount_type");
			while ($r=$con->sql_fetchrow()){
				$price_type[$r['code']][0]['rate']=0;
			}
			$smarty->assign('price_type',$price_type);
		}
		else{
			$missing[]="type";
		}
		//----------------End checking-------------------------------------->

		if ($missing){
			$errm['top'][]=sprintf($LANG['MCB_MISSING_DATA'], join(', ',$missing));
		}else{
			//Check existence in commission table
			if ($sql_check_exist){
				$sce_rid=$con->sql_query($sql_check_exist);
				if($con->sql_numrows($sce_rid)==0){
					$b_codes=$this->get_branches_codes($branch_arr);
					$errm['top'][]=sprintf($LANG['MCB_ZERO_RATE'],join(", ", $b_codes));
				}else{
					while ($r=$con->sql_fetchrow($sce_rid)){
						$price_type[$r['code']][$r['branch_id']]['rate']=$r['rate'];
						unset($branch_arr[$r['branch_id']]);
					}
					
					if ($branch_arr){
						$b_codes=$this->get_branches_codes($branch_arr);
						$errm['top'][]=sprintf($LANG['MCB_ZERO_RATE'],join(", ", $b_codes));
					}
					$smarty->assign('price_type',$price_type);
				}
				$con->sql_freeresult($sce_rid);
			}

			if (!$group_id){
			    //only checking for new created
				$sql_check_duplicate="select c1.group_id from consignment_bearing c1
									where c1.dept_id=$dept_id and c1.vendor_id=".mi($vendor_id)."
									and c1.brand_id=".mi($brand_id)." and c1.r_type=".ms($r_type)."
									group by c1.group_id";
				$dup_rid=$con->sql_query($sql_check_duplicate);

				if ($con->sql_numrows($dup_rid)>0){
					$dup=$con->sql_fetchassoc($dup_rid);
					$errm['top'][]=sprintf($LANG['MCB_DATA_EXIST'], "<a href='masterfile_consignment_bearing.php?a=edit_items&group_id=$dup[group_id]'>here</a>");
				}
				$con->sql_freeresult($dup_rid);
			}

			if (!$errm){
			    //no error
				if (!$group_id){
					//get max group id
					$sql_check_group_id="select max(group_id) as max_group_id from consignment_bearing";

					$con->sql_query($sql_check_group_id);

					$m=$con->sql_fetchrow();

					//set default 1
					$max_group_id=$m['max_group_id'] ? $m['max_group_id']+1: 1;
				}else{
                    $max_group_id=$group_id;
				}
				$detail['group_id']=$max_group_id;
				foreach ($detail['branch_arr'] as $b_id => $b_code){
					$arr_new['branch_id']=$b_id;
					$arr_new['dept_id']=$dept_id;
					$arr_new['vendor_id']=$vendor_id;
					$arr_new['brand_id']=$brand_id;
					$arr_new['r_type']=$r_type;
					$arr_new['added']='CURRENT_TIMESTAMP';
					$arr_new['last_update']='CURRENT_TIMESTAMP';
					$arr_new['created_by']=$sessioninfo['id'];
					$arr_new['group_id']=$max_group_id;

					$con->sql_query("insert into consignment_bearing ".mysql_insert_by_field($arr_new));
				}

				log_br($sessioninfo['id'],'CONSIGNMENT_BEARING',$max_group_id,"Create new Consignment Bearing (Branch Code: ".join(",",$detail['branch_arr']).", Department_id: $dept_id, group id: $max_group_id )");

   			}
		}
		
		$smarty->assign('detail',$detail);

		return $errm;
	}

	function validate_data(){

		global $con, $smarty, $LANG;

		$data=$_REQUEST;
		$num=0;
		$check=array();

		//check duplicate data
		if ($data['p_type']){
			foreach ($data['p_type'] as $branch_id => $no_code){
			    foreach ($no_code as $row_id => $price_code ){
			        //no profit, SKIP
			        if (!$data['profit'][$branch_id][$row_id])  continue;
			    	$msg_str=$price_code." ".get_branch_code($branch_id)." ".$data['profit'][$branch_id][$row_id];
			    	//check 0 discount if not amount type
	    			if ($data['use_net'][$branch_id][$row_id] != "amount"){
						if ($data['discount'][$branch_id][$row_id] <=0){
							$errm['mid'][]=sprintf($LANG['MCB_NO_DISCOUNT'],$msg_str);
						}
					}
			    	
			    	//check duplicate
					if (isset($check[$branch_id][$price_code][$data['profit'][$branch_id][$row_id]][$data['discount'][$branch_id][$row_id]][$data['use_net'][$branch_id][$row_id]][$data['net_bearing'][$branch_id][$row_id]])){
						$errm['mid'][]=sprintf($LANG['MCB_DUPLICATE_DATA'],$msg_str);
					}else{
					    $check[$branch_id][$price_code][$data['profit'][$branch_id][$row_id]][$data['discount'][$branch_id][$row_id]][$data['use_net'][$branch_id][$row_id]][$data['net_bearing'][$branch_id][$row_id]]=1;
					}
					
				}
			}
		}
		return $errm;
	}
	
	function get_branches_codes($branches_id){
		global $con;
		
		$b_rid=$con->sql_query("select * from branch where id in (".join(",",$branches_id).") order by sequence,code");
		while ($r=$con->sql_fetchassoc($b_rid)){
			$branch_codes[$r['id']]=$r['code'];
		}
		$con->sql_freeresult($b_rid);
		
		return $branch_codes;
	}
}

$mst_consignment_bearing = new Mst_Consignment_Bearing("Consignment Bearing Table");

?>
