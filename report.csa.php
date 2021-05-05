<?php
/*
ANEKA ONLY

12/7/2010 10:17:33 AM Alex
- fix view mode only if no confirm and finalize privilege
- hide confirm button if all had confirmed

12/7/2010 5:55:42 PM Alex
- fix unable to save data if too large

12/8/2010 12:47:52 PM Alex
- add approve icon
- break each line by category root row

12/8/2010 5:55:49 PM Alex
- add ajax_load_vendor to search for vendor
- user can edit, confirm and finalize with 3 full privilege

12/21/2010 3:27:09 PM Alex
- add review privilege to review data
- after review, data cannot be edit

12/22/2010 12:32:00 PM Alex
- can unfinalize 1 motnh before only

12/28/2010 4:21:43 PM Alex
- add checking on review privilege 

12/29/2010 3:44:11 PM Alex
- clear 'Form' word
- Change word "Unreviewed" and "Reviewed" on a button
- After fully review, no unfinalize button
- When unfinalized, only set finalize status to 0, not confirm status to 0
- set closing stock to opening stock after finalized only

1/6/2011 12:28:36 PM Alex
- add ajax_check_departments function to check tick or untick departments

1/10/2011 2:00:03 PM Alex
- fix REVIEW user unable to edit
- fix show and hide buttons under different conditions

1/19/2011 10:52:36 AM Alex
- fix branch checking for subbranch with no $_REQUEST['branch_id']

2/16/2011 6:20:43 PM Alex
- Export Excel with Vendor / Without Vendor
- Search Vendor -> show the column input field allow use to key in necessary data & add filtering SKU Type
- Put no grn vendor to 'Other'
- Copy System Opening Stock to Actual Opening Stock with config control
- 3 of 5 confirmed departments can be editable by finalize user.
- When confirm, check data of untick privilege departments in current Line. if empty, auto confirm it.
- exclude department config change use department id, not description

2/22/2011 2:31:18 PM Alex
- change use year of the file

2/24/2011 12:06:52 PM Alex
- change checking on empty data of all departments instead of a signle LINE
- no checking excluded departments while confirming and finalized

3/1/2011 10:38:02 AM Alex
- fix bugs on missing branch_id

3/3/2011 3:10:56 PM Alex
- add vendor from previous month closing stock and disable its input

3/4/2011 9:38:23 AM Alex
- resave closing stock while confirming and finalizing

3/7/2011 12:44:34 PM Alex
- reconstruct privilege checking on different situation

3/15/2011 10:41:04 AM Alex
- fix vocabulary error
- sort the vendor description according to alphabet

6/24/2011 11:12:23 AM Alex
- auto confirm and finalize the excluded department

6/24/2011 6:03:10 PM Andy
- Make all branch default sort by sequence, code.

6/29/2011 4:19:35 PM Alex
- auto confirm and finalize the excluded department even not under user privilge

7/1/2011 2:16:39 PM Alex
- auto confirm and finalize the excluded department when show report (for new category added)

7/15/2011 11:49:48 AM Alex
- add excel mode for export

7/27/2011 10:46:30 AM Alex
- exclude "Rebate" calculation

8/3/2011 4:05:28 PM Alex
- sort the vendor from A->Z

11/16/2011 3:00:05 PM Alex
- if got stock check, the vendor that not included in stock check list should become 0 at actual opening stock

11/22/2011 9:44:46 AM Alex
- assign each department closing to opening if found finalize
- add trigger file to regenerate

11/30/2011 11:36:27 AM Alex
- while finalize, check config starting date. if same date, no need check previous month => check_previous()

12/7/2011 4:27:25 PM Alex
- add show previous month finalize status
- add flag while finalize or unfinalize to regenerate next month report

2/9/2012 11:13:52 AM Alex
- add missing vendor "Other" if carry over month

3/5/2012 3:46:57 PM Alex
- add stock check column data

3/7/2012 11:49:33 AM Alex
- fix missing data of idt and return stock

3/7/2012 11:49:33 AM Alex
- fix missing data of consignment actual sales gp

4/6/2012 11:03:40 AM Alex
- show item list
- add checking for error message

4/12/2012 10:42:27 AM Alex
- add a REQUEST to call tmp file

4/16/2012 4:03:19 PM Alex
- add get closing stock from tmp database

4/19/2012 2:23:10 PM Alex
- change can unfinalized csa report from latest finalized csa
- change create flag when finalize csa, hide the create flag features under unfinalize csa 
- when tmp mode any changes will goto tmp_csa_report table
- change can unfinalize previous month only cannot unfinalize more than 2 month

4/23/2012 10:26:28 AM Alex
- add checking on missing vendor

4/23/2012 6:12:20 PM Alex
- based on latest finalize report: once finalized, previous month will no longer unfinalize or unreview
- add note not allow unfinalize and unreview

4/24/2012 6:13:50 PM Alex
- fix missing checking department in check_previous()

4/25/2012 3:27:08 PM Alex
- fix missing checking department in finalize_form()

4/26/2012 12:09:22 PM Alex
- add last_user and activity_timestamp to get record while finalize csa

3/25/2014 1:31 PM Justin
- Modified the wording from "Finalize" to "Finalise".

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

*Things to be clear
== create urself a tmp_csa_report table if want use for temporarily data only
== this report only available for stock check scan and auto zero for no scanned items
== currently for Aneka nia.

*/
include("include/common.php");

//set_int("display error")
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");
if (!$sessioninfo['privilege']['REPORTS_CSA']) js_redirect(sprintf($LANG['NO_PRIVILEGE'],'REPORTS_CSA',BRANCH_CODE), "/index.php");
if (!$config['enable_csa_report']) js_redirect($LANG['REPORT_CONFIG_NOT_FOUND'], "/index.php");
$maintenance->check(127);

class csa extends Module
{
	var $months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');

   	var	$full_months = array(1=>'January',	2=>'February',	3=>'March',	4=>'April',	5=>'May',	6=>'June',	7=>'July',	8=>'August',	9=>'September',	10=>'October',	11=>'November',	12=>'December');

    function __construct($title){
        global $con, $smarty, $sessioninfo, $config, $LANG;
		$this->resave_closing=false;
	    $this->bid  = get_request_branch();
		$this->confirm = $sessioninfo['privilege']['REPORTS_CSA_CONFIRM'];
		$this->finalize = $sessioninfo['privilege']['REPORTS_CSA_FINALIZE'];
 		$this->review = $sessioninfo['privilege']['REPORTS_CSA_REVIEW'];

		$smarty->assign('privilege', $sessioninfo['privilege']);

		$con->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}

		$smarty->assign("branches", $branches);

		$smarty->assign("months", $this->months);

        //check cache files
        if ($_REQUEST['tmp'])  	$this->tmp_file = "tmp_";

		$ret = $this->file_exists_2("csa_report_cache/".$this->tmp_file."rpt_csa_b*");
		$directory=explode("\n",$ret);

		list($conf_y,$conf_m,$conf_d)=explode('-',$config['csa_start_opening']);

		foreach ($directory as $dir){
		    if (!$dir) continue;
		    $y=substr($dir,-6,4);

		    if ($y>=$conf_y)	$year[$y]['year']=$y;
		}

		ksort($year);

		$smarty->assign("years", $year);
		if ($this->bid && $_REQUEST['year'] && $_REQUEST['month']){
			//set file name
	        $this->file="csa_report_cache/".$this->tmp_file."rpt_csa_b".$this->bid."_".$_REQUEST['year'].str_pad($_REQUEST['month'], 2, "0", STR_PAD_LEFT);
			if (!$_REQUEST['ajax'])	$this->report_settings();
		}
		
    	parent::__construct($title);
    }

	function _default(){
		$this->display();
		exit;
	}

	function file_exists_2($pattern){
	   $ret = shell_exec("ls ".$pattern);
	   return ($ret);
	}

	function report_settings(){
        global $con, $smarty, $sessioninfo, $config, $LANG;
		$form=$_REQUEST;

		if (!is_dir('csa_report_cache'))	mkdir('csa_report_cache',0777);

		if ($config['csa_exclude_dept']){
			foreach ($config['csa_exclude_dept'] as $e_dept){
				$e_dept=trim($e_dept);

				//add into checking department while save,confirm,finalize
				$this->arr_exc_dept[$e_dept]=$e_dept;
			}

			$exclude_dept=" and c.department_id not in (".join(',',$this->arr_exc_dept).")";

            $this->exclude_dept=$exclude_dept;

			if ($this->arr_exc_dept){
				$exc_rid=$con->sql_query("select * from category where level=2 and id in (".join(',',$this->arr_exc_dept).")");
				if ($con->sql_numrows($exc_rid)){
					while ($exc=$con->sql_fetchassoc($exc_rid)){
						//$this->c_dept[$exc['root_id']][$exc['id']]['c_descrip']=$exc['description'];
						$excluded_dept[$exc['root_id']][$exc['id']]=$exc['id'];
						$root_arr[$exc['root_id']]=$exc['root_id'];
					}
				}
				
				$con->sql_freeresult($exc_rid);
				
				$this->auto_confirm_finalize_dept($form,$root_arr,$excluded_dept);
				unset($this->arr_exc_dept,$exc,$excluded_dept,$root_arr);
			}
		}

		//check privilege view only : Cannot view unconfirmed data within sessioninfo departments ids
		if (!$this->confirm && !$this->finalize && !$this->review){

			//change to view mode only
	    	$smarty->assign('open_mode','view');

			$sess_deptids = explode(',',$sessioninfo['department_ids']);
			foreach ($sess_deptids as $dept_ids){
				$new_sess_deptids[$dept_ids] = 1;
			}

			$conf_sql="select confirmed from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]";

			$conf_q=$con->sql_query($conf_sql);
			$conf=$con->sql_fetchassoc($conf_q);
			$con->sql_freeresult($conf_q);
			$conf_list=unserialize($conf['confirmed']);
			if ($conf_list){
				foreach ($conf_list as $rid => $cd){
					foreach ($cd as $cid => $had_confirmed){
						if (!$had_confirmed)    continue;
						if ($new_sess_deptids[$cid])	$dept_list[]=$cid;
					}
				}

				if ($dept_list){
					$dept_ids = join(',', $dept_list);
				}else{
					$err[]=$LANG['CSA_NOT_CONFIRM'];
			        $smarty->assign('err',$err);
   			        $smarty->assign('PAGE_TITLE',$title);
			        $smarty->display('report.csa.tpl');
			        exit;
				}
			}else{
				$err[]=$LANG['CSA_NOT_CONFIRM'];
		        $smarty->assign('err',$err);
		        $smarty->assign('PAGE_TITLE',$title);
		        $smarty->display('report.csa.tpl');
		        exit;
			}

		}elseif($this->confirm || $this->finalize || $this->review){
			$dept_ids = $sessioninfo['department_ids'];
		}
		
		//review user cannot edit
		if ($this->review && !$this->finalize && !$this->confirm) $smarty->assign('open_mode','view');

		$dept_sql="Select c.id, c.root_id,c.description as c_descrip,
				c2.description as r_descrip,
				cc.is_fresh_market as is_fresh
				from category c
				left join category c2 on c2.id=c.root_id
				left join category_cache cc on cc.category_id=c.id
				where c.id in ($dept_ids) $exclude_dept";

		$dept_rid=$con->sql_query($dept_sql);
		while ($dept=$con->sql_fetchassoc($dept_rid)){

		    $this->get_rootid[$dept['id']]=$dept['root_id'];

			$r_departments[$dept['root_id']]['r_descrip']=$dept['r_descrip'];
			$c_departments[$dept['root_id']][$dept['id']]['c_descrip']=$dept['c_descrip'];
			$this->c_dept[$dept['root_id']][$dept['id']]['c_descrip']=$dept['c_descrip'];
			
			if ($dept['is_fresh'] == 'yes'){
				$r_closing[$dept['root_id']]['r_descrip']=$dept['r_descrip'];
				$c_closing[$dept['root_id']][$dept['id']]['c_descrip']=$dept['c_descrip'];
			}
		}
		$con->sql_freeresult($dept_rid);

		$smarty->assign('r_dept',$r_departments);
		$smarty->assign('c_dept',$c_departments);
		$smarty->assign('r_closing',$r_closing);
		$smarty->assign('c_closing',$c_closing);

		$_REQUEST['branch_id']=$this->bid;

       	$smarty->assign('form',$_REQUEST);
	}

/*
	function check_form(){
		global $smarty;


		$form=$_REQUEST;
		$empty=false;
		$not_empty=false;

		foreach ($form['actual'] as $rid => $actual ){
			if ($not_empty) break;

			foreach ($actual as $cid => $p ){
                if ($not_empty) break;

				if (empty($p) && 
					empty($form['rebate'][$rid][$cid]) &&
					empty($form['closing'][$rid][$cid]) &&
					empty($form['other'][$rid][$cid]))
				    $empty=true;
				else{

				    $not_empty=true;
				    $empty=false;
				}
			}
		}
		
		if ($empty){
			$err[]="Empty Form";

			$smarty->assign('err',$err);

			$this->display();
			exit;
		}
	}
*/
/*
	function edit_form(){
		global $con, $smarty;

	    $form=$_REQUEST;
		$branch_code = get_branch_code($this->bid);
		$form_title = "Branch: $branch_code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						Year: ".$form['year']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						Month: ".$this->full_months[$form['month']]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		$smarty->assign('form_title',$form_title);
		
		//Get data from database
	    $con->sql_query("select * from csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]");

		while ($get=$con->sql_fetchrow()){
	    	$form['actual']=unserialize($get['actual']);
	    	$form['grn']=unserialize($get['grn']);
//	    	$form['rebate']=unserialize($get['rebate']);
		    $form['idt']=unserialize($get['idt']);
		    $form['other']=unserialize($get['other']);
		    $form['confirmed']=unserialize($get['confirmed']);
		    $form['lastupdate']=$get['lastupdate_timestamp'];
		}

		$smarty->assign('form', $form);
		$this->display();
	}

	function view_form(){
		global $con, $smarty;

   		$months = array(1=>'January',	2=>'Febrary',	3=>'March',	4=>'April',	5=>'May',	6=>'June',	7=>'July',	8=>'August',	9=>'September',	10=>'October',	11=>'November',	12=>'December');

	    $form=$_REQUEST;
		$branch_code = get_branch_code($this->bid);
		$form_title = "Branch: $branch_code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						Year: ".$form['year']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						Month: ".$this->full_months[$form['month']]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		$smarty->assign('form_title',$form_title);

		//Get data from database
	    $con->sql_query("select * from csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]");

		while ($get=$con->sql_fetchrow()){
	    	$form['actual']=unserialize($get['actual']);
	    	$form['grn']=unserialize($get['grn']);
	    	$form['rebate']=unserialize($get['rebate']);
		    $form['idt']=unserialize($get['idt']);
		    $form['other']=unserialize($get['other']);
		    $form['confirmed']=unserialize($get['confirmed']);
		    $form['lastupdate']=$get['lastupdate_timestamp'];
		}

    	$smarty->assign('open_mode','view');

		$smarty->assign('form', $form);
		$this->display();
	}
*/
	function save_form(){
		global $con,$LANG,$smarty;
		
		$form=$_REQUEST;

		$this->save_data();

/*		//check confirm data
	    $con->sql_query("select confirmed,reviewed from csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]");
		$c=$con->sql_fetchrow();

    	$conf['confirmed']=unserialize($c['confirmed']);
       	$conf['reviewed']=unserialize($c['reviewed']);

   		foreach ($this->c_dept as $rid => $cfm ){
			foreach ($cfm as $cid => $p ){
			    if (!$conf['reviewed'][$rid][$cid])
	                $conf['confirmed'][$rid][$cid]=0;
			}
		}

		$data['confirmed']=ms(serialize($conf['confirmed']));

		$con->sql_query("update csa_report set confirmed=$data[confirmed] where branch_id=$this->bid and year=$form[year] and month=$form[month]");
*/
		$scc[]=$LANG['CSA_SAVE'];
        $smarty->assign('scc',$scc);

		log_br($sessioninfo['id'], 'CSA_REPORT', $this->bid, "Save Report, Branch:".get_branch_code($this->bid).", Year:$form[year], Month:$form[month]");

		$this->show_report();
	}
	
	function confirm_form(){
		global $LANG,$smarty,$con,$sessioninfo;

		$form=$_REQUEST;
		$this->resave_closing=true;
		$this->save_data();

		$file_data=file($this->file);

		$time_created=$file_data[0]; //generated timestamp

		$data_cat=unserialize($file_data[1]);    //department data
		$data_ven=unserialize($file_data[2]);    //vendor data
		$data_fre=unserialize($file_data[3]);   //Is fresh department
		//$got_data=$file_data[4];    //indentify got data
		unset($file_data);
		//-------------------------->declare the variable same as generate report (easy to compare later)
		//system opening stock
		$vos_arr=$data_ven['vos'];
		$fos_arr=$data_fre['fos'];

		//stock receive
		$vsr_arr=$data_ven['vsr'];
		$fsr_arr=$data_fre['fsr'];

		//adjustment
		$vadj_arr=$data_ven['vadj'];
		$fadj_arr=$data_fre['fadj'];

		//return stock
		$vrs_arr=$data_ven['vrs'];
		$frs_arr=$data_fre['frs'];

		//promotion, price change and actual sales
		$vpa_arr=$data_ven['vpa'];
		$fpa_arr=$data_fre['fpa'];

		$vpca_arr=$data_ven['vpca'];
		$fpca_arr=$data_fre['fpca'];

		$vas_arr=$data_ven['vacs'];
		$fas_arr=$data_fre['facs'];

		unset($data_ven);
		unset($data_fre);

	    $con->sql_query("select confirmed from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]");
		$c=$con->sql_fetchassoc();

    	$conf['confirmed']=unserialize($c['confirmed']);

   		foreach ($this->c_dept as $rid => $cfm ){
			foreach ($cfm as $cid => $p ){
                $conf['confirmed'][$rid][$cid]=1;
			}
		}
//====================auto confirm when no data
/*      //control by LINE
		//get root id
		$sql_root="select * from category c where c.id in ($sessioninfo[department_ids])  group by c.root_id";
		$sql_rr =$con->sql_query($sql_root);

		while ($r=$con->sql_fetchassoc($sql_rr)){
			$root_id[$r['root_id']]=$r['root_id'];
		}
*/
		//get department_id
		$sql_dept="select * from category c where c.level=2 and c.id not in ($sessioninfo[department_ids]) group by c.department_id";

		$con->sql_query($sql_dept);

		while ($d=$con->sql_fetchassoc()){
			//check if got empty data, auto confirm it
			$rid=$d['root_id'];
			$cid=$d['id'];

			if ((!$vos_arr[$cid] && !$vsr_arr[$cid] &&!$vadj_arr[$cid] &&!$vrs_arr[$cid] &&!$vpa_arr[$cid] &&!$vpca_arr[$cid] &&!$vas_arr[$cid]) && (!$fos_arr[$cid] && !$fsr_arr[$cid] &&!$fadj_arr[$cid] &&!$frs_arr[$cid] &&!$fpa_arr[$cid] &&!$fpca_arr[$cid] &&!$fas_arr[$cid])){
                $conf['confirmed'][$rid][$cid]=1;
			}
		}
		
		unset($vos_arr,$vsr_arr,$vadj_arr,$vrs_arr,$vpa_arr,$vpca_arrm,$vas_arr,$fos_arr,$fsr_arr,$fadj_arr,$frs_arr,$fpa_arr,$fpca_arr,$fas_arr);
		
		$con->sql_freeresult();
//====================End

		$data['confirmed']=ms(serialize($conf['confirmed']));

		$con->sql_query("update ".$this->tmp_file."csa_report set confirmed=$data[confirmed] where branch_id=$this->bid and year=$form[year] and month=$form[month]");
		
		log_br($sessioninfo['id'], 'CSA_REPORT', $this->bid, "Confirmed Report, Branch:".get_branch_code($this->bid).", Year:$form[year], Month:$form[month]");

		$scc[]=$LANG['CSA_CONFIRM'];
        $smarty->assign('scc',$scc);

		$this->show_report();
	}

	function finalize_form(){
		global $smarty,$con,$LANG,$config,$sessioninfo;
		
		$form=$_REQUEST;
		$count_conf=0;
		$this->resave_closing=true;
		$this->save_data();

		if (!$this->check_can_unfinalize($form)){
			//next month had finalize, cannot unfinalize or unreview anymore
 			$err[]=$LANG['CSA_INVALID_FINALIZE'];
		    $smarty->assign('err',$err);
			$this->show_report();
			return;
		}

		
/*		if ($config['csa_exclude_dept']){
            $exclude_dept=$this->exclude_dept;
		}
*/
		foreach ($this->c_dept as $rid=> $cfm){
			$root_id_arr[]=$rid;
		}
		
		if ($root_id_arr)   $root_id=" and c2.id in (".join(',',$root_id_arr).")";
		
		$tt_dept=$con->sql_query("select c.*, c2.level as c2_level, c2.id as c2_id, c2.description as c2_description
								from category c
								left join category c2 on c2.id=c.root_id
								where c.level=2 $root_id $this->exclude_dept group by c.department_id");

		while ($dept_l=$con->sql_fetchassoc($tt_dept)){
		    //store for root description
		    $root_description[$dept_l['c2_id']]=$dept_l['c2_description'];

            //department for each line
            $dept_list[$dept_l['c2_id']][$dept_l['id']]=$dept_l['description'];
            
		}
		
        $con->sql_freeresult($tt_dept);
		
		//check confirmed departments
	    $confirm=$con->sql_query("select confirmed, finalized from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=".mi($form['year'])." and month=".mi($form['month']));
		$c=$con->sql_fetchassoc($confirm);
        $con->sql_freeresult($confirm);

    	$conf['confirmed']=unserialize($c['confirmed']);
    	
		if ($conf['confirmed']){
		
    	    //check departments for each Line
	   		foreach ($dept_list as $rid => $cfm ){
				foreach ($cfm as $cid => $desc){
				    if (!$conf['confirmed'][$rid][$cid]){
                        $diff_dept[$rid][$cid]=$desc;
					}
				}
			}
		}else{
            $diff_dept=$dept_list;
		}

		//ALL confirmed then finalize
		if (!$diff_dept){
	     	$conf['finalized']=unserialize($c['finalized']);
	     	
	     	//set finalize flag for each department
	   		foreach ($dept_list as $rid => $cfm){
				foreach ($cfm as $cid => $p ){
	            	$conf['finalized'][$rid][$cid]=1;
				}
			}

		    $finalized=serialize($conf['finalized']);

			$con->sql_query("update ".$this->tmp_file."csa_report set finalized=".ms($finalized).",last_user=".mi($sessioninfo['id']).", activity_timestamp=CURRENT_TIMESTAMP, had_finalized=1 where branch_id=$this->bid and year=".mi($form['year'])." and month=".mi($form['month']));
			$scc[]=$LANG['CSA_FINALIZE'];
	        $smarty->assign('scc',$scc);
	        
			if ($config['csa_generate_report']){
				if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
				$br_rid=$con->sql_query("select code from branch where id=$this->bid");
				$r=$con->sql_fetchassoc($br_rid);
				$con->sql_freeresult($br_rid);
				$check_file="csa_report_cache/".$r['code'].".".mi($form['year']).".".str_pad(mi($form['month']), 2, "0", STR_PAD_LEFT).".csa_cache";
				file_put_contents($check_file,"r");
				chmod($check_file,0777);
			}

			log_br($sessioninfo['id'], 'CSA_REPORT', $this->bid, "Finalised Report, Branch:".get_branch_code($this->bid).", Year:$form[year], Month:$form[month]");
		}else{
 			$err[]=$LANG['CSA_INVALID_FINALIZE'];
 			
 			foreach ($diff_dept as $rid => $cfm){
 				$err[]=sprintf($LANG['CSA_MISSING_DEPT_CONFIRM'],$root_description[$rid], count($cfm),join(" ,",$cfm));
 			}
 			
	        $smarty->assign('err',$err);

//			log_br($sessioninfo['id'], 'CSA_REPORT', $this->bid, "Err Finalized Report, Branch:".get_branch_code($this->bid).", Year:$form[year], Month:$form[month]");

		}

		$this->show_report();
	}

	function unfinalize_form(){
		global $smarty,$con,$LANG,$config;

		if ($this->review)	$this->review_data();

		$form=$_REQUEST;

	    $finalize=$con->sql_query("select confirmed,finalized,reviewed from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]");

		$f=$con->sql_fetchassoc($finalize);
        $con->sql_freeresult($finalize);

		$final['finalized']=unserialize($f['finalized']);
		$final['reviewed']=unserialize($f['reviewed']);
		$final['confirmed']=unserialize($f['confirmed']);

    	if ($final['finalized']){
    		
	   		foreach ($this->c_dept as $rid => $cfm ){
				foreach ($cfm as $cid => $desc){
				    if (!$final['reviewed'][$rid][$cid]){
                        $final['finalized'][$rid][$cid]=0;
                        $arr_check[$cid]="finalized like '%i:$cid;i:1;%'";
					}
				}
			}
			/*
			if ($arr_check){
			    $filter= " and ".join(" and ", $arr_check);
			    
			    $future_finalize=$con->sql_query("select year, month from ".$this->tmp_file."csa_report
												where branch_id=$this->bid and year>=$form[year] $filter
												order by year desc, month desc limit 1");
				if ($con->sql_numrows($future_finalize) >0 ){
					$fut=$con->sql_fetchrow($future_finalize);

					if ($fut['year'] == $form['year']){
					    $diff_month = $fut['month'] - $form['month'];
					}elseif ($fut['year'] > $form['year']){
					    $diff_month = $fut['month'] + 12 - $form['month'];
					}
					
					//if more than 2 month
					if ($diff_month > 1){
						$err[]=$LANG['CSA_INVALID_UNFINALIZE'];
						$err[]=$LANG['CSA_UNFINALIZE_1_MONTH'];
					}

				//cannot unfinalize if found latest finalized csa more than current selected year and month
					if ($form['year'] != $fut['year'] || $form['month'] < $fut['month']){
						$err[]=$LANG['CSA_INVALID_UNFINALIZE'];
						$err[]=sprintf($LANG['CSA_LATEST_FINALIZE'],$fut['year'],$fut['month']);
					}
				}

		        $con->sql_freeresult($future_finalize);
			}else{
				$err[]=$LANG['CSA_INVALID_UNFINALIZE'];
				$err[]=$LANG['CSA_UNFINALIZE_FULL_REVIEW'];
			}

			if (!$err){
				$update_finalized=ms(serialize($final['finalized']));

				$con->sql_query("update ".$this->tmp_file."csa_report set finalized=$update_finalized where branch_id=$this->bid and year=$form[year] and month=$form[month]");

				$scc[]=$LANG['CSA_UNFINALIZE'];
		        $smarty->assign('scc',$scc);
		        /*
				if ($config['csa_generate_report']){
					if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
					list($year,$month,$day)=explode("-", date("Y-m-d",strtotime("+1 month",strtotime(mi($form['year'])."-".mi($form['month'])."-01"))));
					
					$check_file="csa_report_cache/".BRANCH_CODE.".".mi($form['year']).".".str_pad(mi($form['month']), 2, "0", STR_PAD_LEFT).".csa_cache";
					file_put_contents($check_file,"r");
					chmod($check_file,0777);
				}
			}	*/
			
			//change can unfinalize 1 month before now date only
			if ($this->check_can_unfinalize($form)){
				$update_finalized=ms(serialize($final['finalized']));
				$con->sql_query("update ".$this->tmp_file."csa_report set finalized=$update_finalized where branch_id=$this->bid and year=$form[year] and month=$form[month]");
				log_br($sessioninfo['id'], 'CSA_REPORT', $this->bid, "Unfinalised Report, Branch:".get_branch_code($this->bid).", Year:$form[year], Month:$form[month]");
			}else{
				//less than 2 month
				$err[]=$LANG['CSA_INVALID_UNFINALIZE'];
			}
		}else{
            $err[]="Current report hasn't finalised yet.";
		}

        $smarty->assign('err',$err);

		$this->show_report();
	}

	function review_form(){
		global $smarty,$con,$LANG;

		$this->review_data();

		if ($_REQUEST['review'] || $_REQUEST['unreview_review'])
			$scc[]=$LANG['CSA_REVIEW'];
		else{
		
		    $full_review=true;
			foreach ($this->c_dept as $rid=> $cfm){
				foreach ($cfm as $cid => $desc){
					if (!$_REQUEST['reviewed'][$rid][$cid]){
					    $full_review=false;
					    break;
					}
				}
				if (!$full_review)  break;
			}

			if ($full_review)	$err[]=$LANG['CSA_INVALID_UNREVIEW'];
			else	$scc[]=$LANG['CSA_UNREVIEW'];

		}

		$smarty->assign('err',$err);
		$smarty->assign('scc',$scc);

		log_br($sessioninfo['id'], 'CSA_REPORT', $this->bid, "Reviewed Report, Branch:".get_branch_code($this->bid).", Year:$form[year], Month:$form[month]");

		$this->show_report();
	}
	
	function review_data(){
		global $smarty,$con,$LANG;

		$form=$_REQUEST;

	    $review=$con->sql_query("select reviewed from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]");

		$r=$con->sql_fetchassoc($review);
        $con->sql_freeresult($review);

        $rev['reviewed'] = unserialize($r['reviewed']);

		foreach ($this->c_dept as $rid=> $cfm){
			foreach ($cfm as $cid => $desc){
				if ($form['reviewed'][$rid][$cid]){
					$rev['reviewed'][$rid][$cid]=1;
				}else{
                    $rev['reviewed'][$rid][$cid]=0;
				}
			}
		}

		$reviewed=ms(serialize($rev['reviewed']));

		$con->sql_query("update ".$this->tmp_file."csa_report set reviewed=$reviewed where branch_id=$this->bid and year=$form[year] and month=$form[month]");
	}

	function test_mode(){
		global $smarty;
		$smarty->assign("test",1);
		$this->show_report();
	}

	function check_previous(){
		global $smarty,$con,$config;

		$form=$_REQUEST;

		//compare with starting date. if same date, direct approve
//		list($conf_year, $conf_month, $conf_day)=explode("-",$config['csa_start_opening']);
		if (strtotime($config['csa_start_opening']) == strtotime("$form[year]-$form[month]-01")){
			print "OK";
			return;
		}
		// for aneka BALING branch use only
/*		if ($form['year'] == '2011' && $form['month'] == '10'){
			$con->sql_query("select code from branch where id=".mi($form['branch_id'])." and company_no='489624-V'");
			if ($con->sql_numrows()	> 0){
				$r=$con->sql_fetchassoc();
				if ($r['code']=='BALING'){
					print "GOT";
					return;
				}
			}
			$con->sql_freeresult();
		}
		//===========================
*/

		foreach ($this->c_dept as $rid=> $cfm){
			$root_id_arr[]=$rid;
		}

		if ($root_id_arr)   $root_id=" and c2.id in (".join(',',$root_id_arr).")";

		$tt_dept=$con->sql_query("select c.*, c2.level as c2_level, c2.id as c2_id, c2.description as c2_description
								from category c
								left join category c2 on c2.id=c.root_id
								where c.level=2 $root_id $this->exclude_dept group by c.department_id");

		while ($dept_l=$con->sql_fetchassoc($tt_dept)){
            //department for each line
            $dept_list[$dept_l['c2_id']][$dept_l['id']]=$dept_l['description'];
		}
		unset($dept_l);
		$con->sql_freeresult($tt_dept);

		//check previous month finalized status
		$year = $form['year'];
		$month = $form['month']-1;

		if (!$month){
	        $year-=1;
	        $month=12;
		}
		
		if ($form['type'] == "confirmed"){
			$ext_query="confirmed";
		}else{
			$ext_query="finalized";
		}
		
	    $prev=$con->sql_query("select $ext_query from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$year and month=$month");
		
		$p=$con->sql_fetchassoc($prev);
        $con->sql_freeresult($prev);

		$finaliz=unserialize($p[$ext_query]);

		foreach ($dept_list as $rid=> $cfm){
			foreach ($cfm as $cid => $desc){
				if (!$finaliz[$rid][$cid]){
					$not_final=true;    //haven't finalize
					break;
				}
			}
			if ($not_final) break;
		}

		if ($not_final) print "GOT";
		else    print "OK";
	}

	function show_report(){
		global $smarty,$con,$LANG, $config;
		
		$form=$_REQUEST;

		$st_r = $con->sql_query("select * from sku_type where active=1");
		while($st=$con->sql_fetchassoc()){
			$sku_types[$st['code']]=$st['code'];
		}
		$smarty->assign('sku_types',$sku_types);

		$branch_code = get_branch_code($this->bid);
     	$days=days_of_month($form['month'], $form['year']);
		$smarty->assign('days',$days);
		$report_title = "Branch: $branch_code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Year: ".$form['year']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Month: ".$this->full_months[$form['month']]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Days: $days &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		$smarty->assign('report_title',$report_title);

		if ($this->is_cached($this->file))
  		{
			//check starting date of config
			$request_date = $form['year'].'-'.str_pad($form['month'], 2, "0", STR_PAD_LEFT).'-01';

			list($start_year,$start_month,$start_day)=explode('-',$config['csa_start_opening']);
			$starting_date = $start_year.'-'.$start_month.'-01';

			if ($request_date  < $starting_date){
				$errm[]=$LANG["CSA_NOT_ALLOW_EDIT"];
				$smarty->assign('no_edit',true);
				$smarty->assign('err',$errm);
			}
	
			//check previous month finalize status
			$previous_month=date("Y-m-d",strtotime("-1 month",strtotime("$form[year]-$form[month]-01")));
 		    if (strtotime($previous_month) >= strtotime($starting_date)){
 		    	list($prv_year, $prv_month, $prv_day)=explode("-",$previous_month);
				$cur_check=$con->sql_query("select finalized from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$prv_year and month=$prv_month");
				if ($con->sql_numrows($cur_check)>0){
					$c=$con->sql_fetchassoc($cur_check);

		            $conf['finalized']=unserialize($c['finalized']);
					unset($c);
	
			    	//check finalize for current month
					if ($conf['finalized']){
						//if finallize
					    //recheck finalized departments for each Line
						$prev_fin=true;     //fully finalized
						foreach ($this->c_dept as $rid=> $cfm){
							foreach ($cfm as $cid => $desc){
							    if (!$conf['finalized'][$rid][$cid]){
							    	$prev_fin=false;
							    	break;
								}
							}
							if (!$prev_fin)  break;
						}
	
						if ($prev_fin){
							$smarty->assign('previous_finalized',"F");	//had full finalized
						}else{
							$smarty->assign('previous_finalized',"N");	//not fully finalized
						}
						unset($conf);
					}else{
						$smarty->assign('previous_finalized',"N");	//not fully finalized
					}
				}
		        $con->sql_freeresult($cur_check);
			}

			//if got cache file
 		    $cur_check=$con->sql_query("select csa.confirmed, csa.finalized, csa.reviewed, user.u as user_name , csa.activity_timestamp from ".$this->tmp_file."csa_report csa 
 		    left join user on user.id=csa.last_user
			 where csa.branch_id=$this->bid and csa.year=$form[year] and csa.month=$form[month]");
			$c=$con->sql_fetchassoc($cur_check);
	        $con->sql_freeresult($cur_check);

            $conf['finalized']=unserialize($c['finalized']);
	    	$conf['confirmed']=unserialize($c['confirmed']);
	    	$conf['reviewed']=unserialize($c['reviewed']);

	    	if ($c['user_name']){
	    		$recent_activity_msg = "User <b>".$c['user_name']."</b> had finalised this month report at ".$c['activity_timestamp'];
	    		$smarty->assign('recent_activity_msg',$recent_activity_msg);
			}

	    	unset($c);
	    	
	    	//check finalize for current month
			if ($conf['finalized']){
				//if finallize
				    //recheck finalized departments for each Line
				$ful_fin=true;     //fully finalized
				foreach ($this->c_dept as $rid=> $cfm){
					$main_fin=true;     
					foreach ($cfm as $cid => $desc){
					    if (!$conf['finalized'][$rid][$cid]){
							$ful_fin=false;
							$main_fin=false;
							$unfinalized_dept[$rid][$cid]=true;
						}
					}
					if ($main_fin)  $main_finalized[$rid]=1;
				}

				if ($ful_fin){
				    //had full finalized 
			    	$smarty->assign('open_mode','view');
					$smarty->assign('finalized',true);
				}

				// still haven't finished finalize depts
				$smarty->assign('unfinalized_dept',$unfinalized_dept);
				
				//Top LINE 
				$smarty->assign('main_finalized',$main_finalized);
				
				// finished finalize depts
				$smarty->assign('finaliz',$conf['finalized']);

			}
			$smarty->assign('can_unfinalize',$this->check_can_unfinalize($form));
		    
			//check confirm
			if ($conf['confirmed']){
		   		foreach ($this->c_dept as $rid => $cfm ){
		   		    $ful_conf=true;
					foreach ($cfm as $cid => $p ){
		            	if (!$conf['confirmed'][$rid][$cid]){
							$ful_conf=false;     //fully confirmed
							break;
						}
					}
					if (!$ful_conf)  break;
				}

				if ($ful_conf){
					//all department fully confirmed
					if ($this->confirm && !$this->finalize)	$smarty->assign('open_mode','view');
					$smarty->assign('confirmed',true);
				}
				elseif(!$this->confirm && !$this->finalize){
					$smarty->assign('open_mode','view');
				}
				
			}elseif(!$this->confirm){
				//if haven't confirm, view only
				$smarty->assign('open_mode','view');
			}

			//review part
			//for checkbox at Line

			foreach ($this->c_dept as $rid=> $cfm){
			    $num_rev['dept']=count($this->c_dept[$rid]);
			    $num_rev['review']=0;
				$main_rev=true;
				foreach ($cfm as $cid => $desc){
					if (!$conf['reviewed'][$rid][$cid]){
						$main_rev=false;
					}else{
						$num_rev['review']+=1;
					}
				}
				if ($main_rev) $main_reviewed[$rid]=1;

				if (!$num_rev['review']) $none_review[$rid]='none';
				elseif ($num_rev['dept'] != $num_rev['review']) $half_review[$rid]='half';
				elseif ($num_rev['dept'] == $num_rev['review']) $full_review[$rid]='full';
			}

			if ($half_review)  $ful_review='half';
			elseif ($none_review && $full_review ) $ful_review='half';
			elseif ($none_review && !$full_review ) $ful_review='none';
			elseif (!$none_review && $full_review ) $ful_review='full';

			$smarty->assign('main_reviewed',$main_reviewed);
			$smarty->assign('ful_review',$ful_review);
			$smarty->assign('reviewed',$conf['reviewed']);


			$smarty->assign('dept_confirm',$conf['confirmed']);
			$this->assign_input($form);

			$report_cache = $smarty->fetch('report.csa.data.tpl');
			$smarty->assign('report_cache', $report_cache);

			$smarty->assign('search_vendor',true);
		}
		else
		{
			$form['days']=$days;
			
			$err[]=$LANG['CSA_NOT_GENERATE'];
	        $smarty->assign('err',$err);
		}

		$this->display();
     }

	function regenerate_report(){
		global $con,$smarty;

		$err[]="The generated function is too heavy. Currently is not available to use.";
        $smarty->assign('err',$err);

		$this->display();
		return;


		//test script

		$branch_code = get_branch_code($this->bid);
     	$days=days_of_month($form['month'], $form['year']);
		$smarty->assign('days',$days);


		$report_title = "Branch: $branch_code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Year: ".$form['year']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Month: ".$this->full_months[$form['month']]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Days: $days &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		$smarty->assign('report_title',$report_title);

		$form['bid']=$this->bid;
		$form['days']=$days;
		
		$this->generate_report($form,$this->file);

		$report_cache = $smarty->fetch('report.csa.data.tpl');

		$smarty->assign('report_cache', $report_cache);

		$this->display();

	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$smarty->assign('excel_mode',true);
    	$smarty->assign('open_mode','view');
    	$filename = "csa_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Category Stock Analysis To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();
	    exit;
	}
	
	function output_excel_without_vendor(){
	    global $smarty, $sessioninfo;

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$smarty->assign('excel_without_vendor',true);
    	$smarty->assign('excel_mode',true);
    	$smarty->assign('open_mode','view');
    	$filename = "csa_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Category Stock Analysis To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();
	    exit;
	}

	//other unrelated to form functions
	function save_data(){
		global $con, $sessioninfo;
		/*=============Descrip=====
		    'O' = OUTRIGHT;
		    'C' = CONSIGN;
		    'F' = FRESH;
		    'cp' = cost_price;
		    'sp' = selling_price;
		===========================*/

//		$this->check_form();
	    $form=$_REQUEST;

	    $data['branch_id']=$this->bid;
		$data['year']=$form['year'];
	    $data['month']=$form['month'];
	    
		//get old data
	    $con->sql_query("select * from ".$this->tmp_file."csa_report where branch_id=$data[branch_id] and year=$data[year] and month=$data[month]");
		while ($in=$con->sql_fetchassoc()){
	    	//$save['actual_notf_cons_cost']=unserialize($in['actual_notf_cons_cost']);
	    	$save['actual_notf_cons_gp']=unserialize($in['actual_notf_cons_gp']);
	    	$save['grn_notf_out_cost']=unserialize($in['grn_notf_out_cost']);
	    	$save['grn_notf_out_selling']=unserialize($in['grn_notf_out_selling']);
		    $save['grn_fresh_selling']=unserialize($in['grn_fresh_selling']);
//		    $save['rebate_notf_out_selling']=unserialize($in['rebate_notf_out_selling']);
// 		    $save['rebate_notf_cons_selling']=unserialize($in['rebate_notf_cons_selling']);
//	    	$save['rebate_fresh_selling']=unserialize($in['rebate_fresh_selling']);
	    	$save['idt_notf_out_cost']=unserialize($in['idt_notf_out_cost']);
		    $save['idt_notf_out_selling']=unserialize($in['idt_notf_out_selling']);
		    $save['idt_notf_cons_cost']=unserialize($in['idt_notf_cons_cost']);
   		    $save['idt_notf_cons_selling']=unserialize($in['idt_notf_cons_selling']);
		    $save['idt_fresh_selling']=unserialize($in['idt_fresh_selling']);
		    $save['other_notf_out_selling']=unserialize($in['other_notf_out_selling']);
   		    $save['other_notf_cons_selling']=unserialize($in['other_notf_cons_selling']);
		    $save['other_fresh_selling']=unserialize($in['other_fresh_selling']);
		    $save['closing_notf_out_cost']=unserialize($in['closing_notf_out_cost']);
   		    $save['closing_notf_out_selling']=unserialize($in['closing_notf_out_selling']);
   		    $save['closing_fresh_selling']=unserialize($in['closing_fresh_selling']);
		}

		//add in or replace old data
		//actual------------------------------------------------------>
/*
		if ($form['actual']){
	   		foreach ($form['actual'] as $cid => $actual ){
				foreach ($actual as $vid => $other ){
				    if ($other['CONSIGN']['cost'])
		   		    	$save['actual_notf_cons_cost'][$cid][$vid]['C']['cp']=strval($other['CONSIGN']['cost']);
				}
			}
		}
	    $data['actual_notf_cons_cost']=serialize($save['actual_notf_cons_cost']);
		unset($save['actual_notf_cons_cost']);
*/
		if ($form['actual']){
	   		foreach ($form['actual'] as $cid => $actual ){
				foreach ($actual as $vid => $other ){
				    if ($other['CONSIGN']['gp'])
		   		    	$save['actual_notf_cons_gp'][$cid][$vid]['C']['gp']=strval($other['CONSIGN']['gp']);
				}
			}
		}
	    $data['actual_notf_cons_gp']=serialize($save['actual_notf_cons_gp']);
		unset($save['actual_notf_cons_gp']);

		//grn------------------------------------------------------>
		if ($form['grn']){
			foreach ($form['grn'] as $cid => $grn ){
				foreach ($grn as $vid => $other ){
				    if ($other['OUTRIGHT']['cost'] || $other['OUTRIGHT']['selling']){
			   		    $save['grn_notf_out_cost'][$cid][$vid]['O']['cp']=strval($other['OUTRIGHT']['cost']);
					    $save['grn_notf_out_selling'][$cid][$vid]['O']['sp']=strval($other['OUTRIGHT']['selling']);
				    }
				}
			}
		}
		if ($form['fgrn']){
	   		foreach ($form['fgrn'] as $rid => $fgrn ){
	   		    if ($fgrn['FRESH']['selling'])
				    $save['grn_fresh_selling'][$rid]['F']['sp']=strval($fgrn['FRESH']['selling']);
			}
		}
	    $data['grn_notf_out_cost']=serialize($save['grn_notf_out_cost']);
 	    $data['grn_notf_out_selling']=serialize($save['grn_notf_out_selling']);
  	    $data['grn_fresh_selling']=serialize($save['grn_fresh_selling']);
		unset($save['grn_notf_out_cost']);
		unset($save['grn_notf_out_selling']);
		unset($save['grn_fresh_selling']);

		//rebate------------------------------------------------------>
		/*
        if ($form['rebate']){
	   		foreach ($form['rebate'] as $cid => $rebate ){
				foreach ($rebate as $vid => $other ){
					if ($form['rebate'][$cid][$vid]['OUTRIGHT']['selling'])
					    $save['rebate_notf_out_selling'][$cid][$vid]['O']['sp']=strval($form['rebate'][$cid][$vid]['OUTRIGHT']['selling']);
				    
				    if ($form['rebate'][$cid][$vid]['CONSIGN']['selling'])
					    $save['rebate_notf_cons_selling'][$cid][$vid]['C']['sp']=strval($form['rebate'][$cid][$vid]['CONSIGN']['selling']);
				}
			}
		}
		if ($form['frebate']){
	   		foreach ($form['frebate'] as $rid => $frebate ){
			    if ($frebate['FRESH']['selling'])
				    $save['rebate_fresh_selling'][$rid]['F']['sp']=strval($frebate['FRESH']['selling']);
			}
		}
	    $data['rebate_notf_out_selling']=serialize($save['rebate_notf_out_selling']);
	    $data['rebate_notf_cons_selling']=serialize($save['rebate_notf_cons_selling']);
 	    $data['rebate_fresh_selling']=serialize($save['rebate_fresh_selling']);
		unset($save['rebate_notf_out_selling']);
		unset($save['rebate_notf_cons_selling']);
		unset($save['rebate_fresh_selling']);
		*/

		//idt------------------------------------------------------>
		if ($form['idt']){
			foreach ($form['idt'] as $cid => $idt ){
				foreach ($idt as $vid => $other ){
				    if ($other['OUTRIGHT']['cost'] || $other['OUTRIGHT']['selling']){
					    $save['idt_notf_out_cost'][$cid][$vid]['O']['cp']=strval($other['OUTRIGHT']['cost']);
					    $save['idt_notf_out_selling'][$cid][$vid]['O']['sp']=strval($other['OUTRIGHT']['selling']);
				    }
				    
				    if ($other['CONSIGN']['cost'] || $other['CONSIGN']['selling']){
					    $save['idt_notf_cons_cost'][$cid][$vid]['C']['cp']=strval($other['CONSIGN']['cost']);
				    	$save['idt_notf_cons_selling'][$cid][$vid]['C']['sp']=strval($other['CONSIGN']['selling']);
				    }
				}
			}
		}
		if ($form['fidt']){
			foreach ($form['fidt'] as $rid => $fidt ){
			    if ($fidt['FRESH']['selling'])
			    $save['idt_fresh_selling'][$rid]['F']['sp']=strval($fidt['FRESH']['selling']);
			}
		}
	    $data['idt_notf_out_cost']=serialize($save['idt_notf_out_cost']);
	    $data['idt_notf_out_selling']=serialize($save['idt_notf_out_selling']);
	    $data['idt_notf_cons_cost']=serialize($save['idt_notf_cons_cost']);
	    $data['idt_notf_cons_selling']=serialize($save['idt_notf_cons_selling']);
	    $data['idt_fresh_selling']=serialize($save['idt_fresh_selling']);
		unset($save['idt_notf_out_cost']);
		unset($save['idt_notf_out_selling']);
		unset($save['idt_notf_cons_cost']);
		unset($save['idt_notf_cons_selling']);
		unset($save['idt_fresh_selling']);

		//other income------------------------------------------------------>
		if ($form['other']){
			foreach ($form['other'] as $cid => $others ){
				foreach ($others as $vid => $other ){
				    if ($other['OUTRIGHT']['selling'])
					    $save['other_notf_out_selling'][$cid][$vid]['O']['sp']=strval($other['OUTRIGHT']['selling']);
				    
				    if ($other['CONSIGN']['selling'])
					    $save['other_notf_cons_selling'][$cid][$vid]['C']['sp']=strval($other['CONSIGN']['selling']);
				}
			}
		}

		if ($form['fother']){
			foreach ($form['fother'] as $rid => $fothers ){
			    if ($fothers['FRESH']['selling'])
					$save['other_fresh_selling'][$rid]['F']['sp']=strval($fothers['FRESH']['selling']);
			}
		}
	    $data['other_notf_out_selling']=serialize($save['other_notf_out_selling']);
	    $data['other_notf_cons_selling']=serialize($save['other_notf_cons_selling']);
	    $data['other_fresh_selling']=serialize($save['other_fresh_selling']);
	    unset($save['other_notf_out_selling']);
	    unset($save['other_notf_cons_selling']);
	    unset($save['other_fresh_selling']);

		//closing stock------------------------------------------------------>

		if ($form['closing']){
/*
		if ($sessioninfo['u']=='wsatp'){
			print "FORM<br />";
			print_r($form['closing'][2][0]);
		}
*/
			foreach ($form['closing'] as $cid => $closing ){
			    if ($this->resave_closing){
				    unset($save['closing_notf_out_cost'][$cid]);
				    unset($save['closing_notf_out_selling'][$cid]);
				}
				foreach ($closing as $vid => $other ){
				    if ($other['OUTRIGHT']['cost'] || $other['OUTRIGHT']['selling']){
			   		    $save['closing_notf_out_cost'][$cid][$vid]['O']['cp']=strval($other['OUTRIGHT']['cost']);
					    $save['closing_notf_out_selling'][$cid][$vid]['O']['sp']=strval($other['OUTRIGHT']['selling']);
					}
				}
			}
		}
/*
		if ($sessioninfo['u']=='wsatp'){
			print "<br />SAVED<br />";
			print_r($save['closing_notf_out_selling'][2][0]);
		}
*/
		if ($form['fclosing']){
			foreach ($form['fclosing'] as $rid => $fclosing ){
			    if ($fclosing['FRESH']['selling'])
				    $save['closing_fresh_selling'][$rid]['F']['sp']=strval($fclosing['FRESH']['selling']);
			}
		}
	    $data['closing_notf_out_cost']=serialize($save['closing_notf_out_cost']);
	    $data['closing_notf_out_selling']=serialize($save['closing_notf_out_selling']);
	    $data['closing_fresh_selling']=serialize($save['closing_fresh_selling']);
	    unset($save['closing_notf_out_cost']);
	    unset($save['closing_notf_out_selling']);
	    unset($save['closing_fresh_selling']);

		//Save data to database
		$con->sql_query("insert into ".$this->tmp_file."csa_report " . mysql_insert_by_field($data) ." on duplicate key update
						".mysql_update_by_field($data));

		$con->sql_query("update ".$this->tmp_file."csa_report set lastupdate_timestamp = CURRENT_TIMESTAMP() where branch_id=$data[branch_id] and year=$data[year] and month=$data[month]");

		unset($data);
	}
	
	function is_cached($directory)
	{
	    if (file_exists($directory))	return true;
		else	return false;
	}

	function generate_report($form,$file_name)
	{
	    global $con, $smarty, $sessioninfo;
		return;
		$con_multi=$con;
		
		$bid=$this->bid;
		
		$from_date=ms('2010-06-01');
		$to_date=ms('2010-06-30');

	    $st_sql="select if(s.is_fresh_market='inherit',cc.is_fresh_market,s.is_fresh_market) as is_fresh,
		(select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=si.id and vsh.branch_id=$bid and vsh.added <= sc.date order by vsh.added desc limit 1) as vsh_vendor_id  ,s.vendor_id as s_vendor_id ,si.id, sc.date,
		c.department_id, s.sku_type, si.selling_price as si_selling_price, sum(sc.qty) as sc_qty, sc.cost as sc_cost_price, sc.selling as sc_selling_price
	from sku_items si
	left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$bid
	left join sku s on si.sku_id=s.id
	left join category c on s.category_id=c.id
	left join category_cache cc on cc.category_id=c.id and cc.category_id=c.id
	where s.sku_type='OUTRIGHT' and sc.date between $from_date and $to_date
	group by si.id,s_vendor_id,vsh_vendor_id";

	    $st_query=$con_multi->sql_query($st_sql);

		if ($con_multi->sql_numrows($st_query)>0){
			$num_rows=$con_multi->sql_numrows($st_query);

			while ($st_db=$con_multi->sql_fetchassoc($st_query)){

	 			if ($st_db['vsh_vendor_id'] >0  )    $st_vendor_id=$st_db['vsh_vendor_id'];
				else{
				    $no_grn_vendor[$st_db['department_id']][$st_db['s_vendor_id']][$st_db['sku_type']]=1;
					$st_vendor_id = 0;
				}
	/*
		    	$get_price="select price from sku_items_price_history
							where branch_id=$bid and sku_item_id=$st_db[id] and added <= ".ms($st_db['date'])."
							order by added desc limit 1";
		    	$st_query2=$con_multi->sql_query($get_price);
		    	$st_db2=$con_multi->sql_fetchrow($st_query2);
	   			$con_multi->sql_freeresult($st_query2);


		    	if (!empty($st_db2['price'])){
					$st_selling_price=($st_db['sc_qty']-$st_db['start_qty'])*$st_db['si_selling_price'];
				}else{
		            $st_selling_price=($st_db['sc_qty']-$st_db['start_qty'])*$st_db2['price'];
				}

				$st_cost_price=($st_db['sc_qty']-$st_db['start_qty'])*$st_db['sc_cost_price'];
	*/


	 			if ($st_db['is_fresh']=='no'){
	//			    	$st_arr[$st_db['department_id']][$st_db['sku_type']]['cost_price']+=$st_cost_price;
	//			    	$st_arr[$st_db['department_id']][$st_db['sku_type']]['selling_price']+=$st_selling_price;
	                $st_arr[$st_db['department_id']][$st_db['sku_type']]=1;
				}elseif ($st_db['is_fresh']=='yes'){
	  			    $fresh[$st_db['department_id']]=1;
	  			    $fresh[$get_rootid[$st_db['department_id']]]=1;
	//			    	$st_arr[$st_db['department_id']]['FRESH']['cost_price']+=$st_cost_price;
	//			    	$st_arr[$st_db['department_id']]['FRESH']['selling_price']+=$st_selling_price;
			    	$fst_arr[$st_db['department_id']]['FRESH']=1;
				}
				
				
				$st_cost_price=($st_db['sc_qty']-$st_db['start_qty'])*$st_db['sc_cost_price'];
					$st_selling_price=($st_db['sc_qty']-$st_db['start_qty'])*$st_db['si_selling_price'];
				
		    	if(!empty($st_cost_price) || !empty($st_selling_price)){
		 			if ($st_db['is_fresh']=='no'){
				    	$vendor[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]['descrip']=1;
	//				    	$vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]['cost_price']+=$st_cost_price;
	//				    	$vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]['selling_price']+=$st_selling_price;
				    	$vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]=1;
					}
	/*					else{
				    	$vendor[$st_db['department_id']][$st_vendor_id]['FRESH']['descrip']=1;
				    	$vst_arr[$st_db['department_id']][$st_vendor_id]['FRESH']['cost_price']+=$st_cost_price;
				    	$vst_arr[$st_db['department_id']][$st_vendor_id]['FRESH']['selling_price']+=$st_selling_price;
					}
	*/			}
		    	$num_rows-=1;
			}
			$got_data=true;

			//print_r($vendor);
		}
	}
	
	function ajax_load_vendor(){
		global $con;
		$file_data=file($this->file);
 		$data_ven=unserialize($file_data[2]);    //vendor data
		$vendor=$data_ven['vendor'];
		$dept_id=$_REQUEST['department_id'];
		$sku_type= $_REQUEST['sku_type'];

		$year = mi($_REQUEST['year']);
	    $cos_month = mi($_REQUEST['month']) - 1;

		//date checking
		if (!$cos_month){
            $cos_year = $year - 1;
            $cos_month = 12;
		}else{
            $cos_year = $year;
		}

		//get more vendor from last month closing stock
		if ($sku_type == 'OUTRIGHT'){
			$cos_sql = "select closing_notf_out_cost from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$cos_year and month=$cos_month";
		    $cos_query=$con->sql_query($cos_sql,false,false);
			if ($con->sql_numrows($cos_query)>0){
				while ($cos_db=$con->sql_fetchassoc($cos_query)){
				    $save['closing_notf_out_cost']=unserialize($cos_db['closing_notf_out_cost']);
				}
			}

			$con->sql_freeresult($cos_query);

		    if ($save['closing_notf_out_cost']){
				foreach ($save['closing_notf_out_cost'][$dept_id] as $vid => $other){
					if (!$vendor[$dept_id][$vid]['OUTRIGHT']['descrip']){
						if ($vid === '')	continue;
						$missing_vendor[$dept_id][$vid]=$vid;
					}
				}
				if ($missing_vendor){
					$get_vendor_id = join(",",$missing_vendor[$dept_id]);
					$get_vendor="select id,description from vendor where id in ($get_vendor_id)";
					$vendor_query=$con->sql_query($get_vendor);

					while ($ven_db=$con->sql_fetchassoc($vendor_query)){
						$vendor[$dept_id][$ven_db['id']]['OUTRIGHT']['descrip']=$ven_db['description'];
				    }
		    		$con->sql_freeresult($vendor_query);
				}
			}
		}

		$option='';

		if ($vendor[$dept_id]){
			foreach ($vendor[$dept_id] as $vid=>$ocdesc){
			    if ($ocdesc[$sku_type]['descrip'])
					$sel_vendor[$vid]=$ocdesc[$sku_type]['descrip'];
			}

			unset($vendor);
			asort($sel_vendor);

		    foreach ($sel_vendor as $vid=>$desc){
            	$option .="<option value='$vid'>";
                $option .= $desc;
				$option .= "</option>";
			}
		}
			
		if (!$option)   $option =  "<option value=''>-No Data-</option>";
		
		print $option;
	}
	
	function ajax_load_form_type(){
	    global $con,$smarty;
	    
		$form_type=$_REQUEST['sku_type'];
		$dept_id=$_REQUEST['department_id'];
		$vendor_id=$_REQUEST['vendor_id'];

		$con->sql_query("select * from category where id=$dept_id");
		$r=$con->sql_fetchassoc();
		$root_id=$r['root_id'];

        $smarty->assign('form_type',$form_type);
        $smarty->assign('root_id',$root_id);
        $smarty->assign('dept_id',$dept_id);
        $smarty->assign('vendor_id',$vendor_id);

		$smarty->display('report.csa.input_form.tpl');
	}
	
	function assign_input($form){
	    global $smarty,$con,$config;
		
		//--------------------> Assign input data
        $bid  = $this->bid;

		$month=$form['month'];
		$year=$form['year'];

		$file_data = file($this->file);

		$time_created=$file_data[0]; //generated timestamp
		unset($file_data[0]);
		
		$data_cat=unserialize(trim($file_data[1]));    //department data
		unset($file_data[1]);

		$data_ven=unserialize(trim($file_data[2]));    //vendor data
		unset($file_data[2]);

		$data_fre=unserialize(trim($file_data[3]));   //Is fresh department
		unset($file_data[3]);

		$got_data=trim($file_data[4]);    //indentify got data
		unset($file_data[4]);

		$stc_date=trim($file_data[6]);    //indentify got data
		unset($file_data[6]);

		//default true, seems no use of checking this
		$got_data=true;
		$smarty->assign('stc_date',$stc_date);
		
		$opening_date = date("Y-m-d",strtotime($year."-".$month."-01"));
		$smarty->assign('opening_date',$opening_date);

		//-------------------------->declare the variable same as generate report (easy to compare later)
		//system opening stock
		$os_arr=$data_cat['os'];
		$vos_arr=$data_ven['vos'];
		$fos_arr=$data_fre['fos'];

		//stock receive
//		$sr_arr=$data_cat['sr'];
//		$vsr_arr=$data_ven['vsr'];
//		$fsr_arr=$data_fre['fsr'];
		$smarty->assign('sr',$data_cat['sr']);
		$smarty->assign('vsr',$data_ven['vsr']);
		$smarty->assign('fsr',$data_fre['fsr']);
		unset($data_cat['sr'],$data_ven['vsr'],$data_fre['fsr']);
		
		//adjustment
//		$adj_arr=$data_cat['adj'];
//		$vadj_arr=$data_ven['vadj'];
//		$fadj_arr=$data_fre['fadj'];
		$smarty->assign('adj',$data_cat['adj']);
		$smarty->assign('vadj',$data_ven['vadj']);
		$smarty->assign('fadj',$data_fre['fadj']);
		unset($data_cat['adj'],$data_ven['vadj'],$data_fre['fadj']);

		//stock take variance
		$this->st_arr=$data_cat['stv'];
		$this->vst_arr=$data_ven['vstv'];
		$this->fst_arr=$data_fre['fstv'];

		//return stock
		$smarty->assign('rs',$data_cat['rs']);
 		$smarty->assign('vrs',$data_ven['vrs']);
 		$smarty->assign('frs',$data_fre['frs']);
 		unset($data_cat['rs'],$data_ven['vrs'],$data_fre['frs']);

		//promotion, price change and actual sales
//		$pa_arr=$data_cat['pa'];
//		$vpa_arr=$data_ven['vpa'];
//		$fpa_arr=$data_fre['fpa'];
		$smarty->assign('pa',$data_cat['pa']);
		$smarty->assign('vpa',$data_ven['vpa']);
		$smarty->assign('fpa',$data_fre['fpa']);
		unset($data_cat['pa'],$data_ven['vpa'],$data_fre['fpa']);
		
//		$pca_arr=$data_cat['pca'];
//		$vpca_arr=$data_ven['vpca'];
//		$fpca_arr=$data_fre['fpca'];
		$smarty->assign('pca',$data_cat['pca']);
		$smarty->assign('vpca',$data_ven['vpca']);
		$smarty->assign('fpca',$data_fre['fpca']);
		unset($data_cat['pca'],$data_ven['pca'],$data_fre['pca']);
	
		//actual sales	
//		$as_arr=$data_cat['acs'];
		$vas_arr=$data_ven['vacs'];
//		$fas_arr=$data_fre['facs'];
		$smarty->assign('acs',$data_cat['acs']);
		//$smarty->assign('vacs',$data_ven['vacs']);
		$smarty->assign('facs',$data_fre['facs']);
		unset($data_cat['acs'],$data_ven['vacs'],$data_fre['facs']);
		
		//total csa opening variance
		$smarty->assign('cov',$data_cat['stc_opening_variance']);
		$smarty->assign('vcov',$data_ven['stc_opening_variance']);
		$smarty->assign('fcov',$data_fre['stc_opening_variance']);
		unset($data_cat['stc_starting_opening'], $data_ven['stc_starting_opening'], $data_fre['stc_starting_opening']);
				
		//vendor
  		$this->vendor=$data_ven['vendor'];
  		
  		//sort vendor  		
  		foreach ($this->vendor as $cid => $vother){  		
  			uasort($this->vendor[$cid], array('csa','sort_vendor'));
 		}
   		
		$this->check_vendor=$data_ven['check_vendor'];
		$fresh=$data_fre['fresh'];

	    $input_sql=$con->sql_query("select * from ".$this->tmp_file."csa_report where branch_id=$bid and year=$year and month=$month");
		while ($in=$con->sql_fetchassoc($input_sql)){
	    	//$save['actual_notf_cons_cost']=unserialize($in['actual_notf_cons_cost']);
	    	$save['actual_notf_cons_gp']=unserialize($in['actual_notf_cons_gp']);
	    	$save['grn_notf_out_cost']=unserialize($in['grn_notf_out_cost']);
	    	$save['grn_notf_out_selling']=unserialize($in['grn_notf_out_selling']);
		    $save['grn_fresh_selling']=unserialize($in['grn_fresh_selling']);
//		    $save['rebate_notf_out_selling']=unserialize($in['rebate_notf_out_selling']);
//			$save['rebate_notf_cons_selling']=unserialize($in['rebate_notf_cons_selling']);
//	    	$save['rebate_fresh_selling']=unserialize($in['rebate_fresh_selling']);
	    	$save['idt_notf_out_cost']=unserialize($in['idt_notf_out_cost']);
		    $save['idt_notf_out_selling']=unserialize($in['idt_notf_out_selling']);
		    $save['idt_notf_cons_cost']=unserialize($in['idt_notf_cons_cost']);
   		    $save['idt_notf_cons_selling']=unserialize($in['idt_notf_cons_selling']);
		    $save['idt_fresh_selling']=unserialize($in['idt_fresh_selling']);
		    $save['other_notf_out_selling']=unserialize($in['other_notf_out_selling']);
   		    $save['other_notf_cons_selling']=unserialize($in['other_notf_cons_selling']);
		    $save['other_fresh_selling']=unserialize($in['other_fresh_selling']);

   		    $save['closing_fresh_selling']=unserialize($in['closing_fresh_selling']);
		}
		$con->sql_freeresult($input_sql);

		//actual
/*		if ($save['actual_notf_cons_cost']){
	   		foreach ($save['actual_notf_cons_cost'] as $cid => $actual ){
				foreach ($actual as $vid => $other ){
		   		    $vas_arr[$cid][$vid]['CONSIGN']['cost_price']=mf($other['C']['cp']);
				}
			}

			unset($save['actual_notf_cons_cost']);
		}
*/
		if ($save['actual_notf_cons_gp']){
	   		foreach ($save['actual_notf_cons_gp'] as $cid => $actual ){
				foreach ($actual as $vid => $other ){
		   		    $vas_arr[$cid][$vid]['CONSIGN']['gp']=mf($other['C']['gp']);
				}
			}

			unset($save['actual_notf_cons_gp']);
		}

		//grn
		if ($save['grn_notf_out_cost'] || $save['grn_notf_out_selling'] || $save['grn_fresh_selling']){
		    if ($save['grn_notf_out_cost']){
				foreach ($save['grn_notf_out_cost'] as $cid => $grn ){
					foreach ($grn as $vid => $other ){
					    $data['grn'][$cid][$vid]['OUTRIGHT']['cost_price']=mf($other['O']['cp']);
	   				    $data['grn'][$cid][$vid]['OUTRIGHT']['selling_price']=mf($save['grn_notf_out_selling'][$cid][$vid]['O']['sp']);
					}
				}
			}

            $vgrn_arr=$data['grn'];

			if ($save['grn_fresh_selling']){
		   		foreach ($save['grn_fresh_selling'] as $rid => $fgrn ){
				    $fgrn_arr[$rid]['FRESH']['selling_price']=mf($fgrn['F']['sp']);
				}
			}

			unset($save['grn_notf_out_cost'],$save['grn_notf_out_selling'],$save['grn_fresh_selling']);
		}

		//rebate
		/*
		if ($save['rebate_notf_out_selling'] || $save['rebate_notf_cons_selling'] || $save['rebate_fresh_selling']){

			if ($save['rebate_notf_out_selling']){
				foreach ($save['rebate_notf_out_selling'] as $cid => $rebate ){
					foreach ($rebate as $vid => $other ){
					    $data['rebate'][$cid][$vid]['OUTRIGHT']['selling_price']=mf($other['O']['sp']);
					}
				}
			}
			
			if ($save['rebate_notf_cons_selling']){
				foreach ($save['rebate_notf_cons_selling'] as $cid => $rebate ){
					foreach ($rebate as $vid => $other ){
						$data['rebate'][$cid][$vid]['CONSIGN']['selling_price']=mf($other['C']['sp']);
					}
				}
			}

	        $vr_arr=$data['rebate'];

			if ($save['rebate_fresh_selling']){
		   		foreach ($save['rebate_fresh_selling'] as $rid => $frebate ){
				    $fr_arr[$rid]['FRESH']['selling_price']=mf($frebate['F']['sp']);
				}
			}

	        unset($save['rebate_notf_out_selling']);
	        unset($save['rebate_notf_cons_selling']);
	        unset($save['rebate_fresh_selling']);
		}
		*/
		
		//idt
		if ($save['idt_notf_out_cost'] || $save['idt_notf_out_selling'] || $save['idt_notf_cons_cost'] || $save['idt_notf_cons_selling'] || $save['idt_fresh_selling']){

			if ($save['idt_notf_out_cost']){
				foreach ($save['idt_notf_out_cost'] as $cid => $idt ){
					foreach ($idt as $vid => $other ){
						$data['idt'][$cid][$vid]['OUTRIGHT']['cost_price'] = mf($other['O']['cp']);
	                    $data['idt'][$cid][$vid]['OUTRIGHT']['selling_price']=mf($save['idt_notf_out_selling'][$cid][$vid]['O']['sp']);
					}
				}
			}

			if ($save['idt_notf_cons_cost']){
				foreach ($save['idt_notf_cons_cost'] as $cid => $idt ){
					foreach ($idt as $vid => $other ){
						$data['idt'][$cid][$vid]['CONSIGN']['cost_price']=mf($other['C']['cp']);
						$data['idt'][$cid][$vid]['CONSIGN']['selling_price']=mf($save['idt_notf_cons_selling'][$cid][$vid]['C']['sp']);
					}
				}
			}

			$vidt_arr=$data['idt'];

			if ($save['idt_fresh_selling']){
		   		foreach ($save['idt_fresh_selling'] as $rid => $fidt ){
				    $fidt_arr[$rid]['FRESH']['selling_price']=mf($fidt['F']['sp']);
				}
			}

	        unset($save['idt_notf_out_cost'],$save['idt_notf_out_selling'],$save['idt_notf_cons_selling'],$save['idt_fresh_selling']);
		}
		
		//other income
		if ($save['other_notf_out_selling'] || $save['other_notf_cons_selling'] || $save['other_fresh_selling']){
			if ($save['other_notf_out_selling']){
				foreach ($save['other_notf_out_selling'] as $cid => $others ){
					foreach ($others as $vid => $other ){
					    $data['other'][$cid][$vid]['OUTRIGHT']['selling_price']=mf($other['O']['sp']);
					}
				}
			}
			
			if ($save['other_notf_cons_selling']){
				foreach ($save['other_notf_cons_selling'] as $cid => $others ){
					foreach ($others as $vid => $other ){
						$data['other'][$cid][$vid]['CONSIGN']['selling_price']=mf($other['C']['sp']);
					}
				}
			}

			$voi_arr=$data['other'];

			if ($save['other_fresh_selling']){
		   		foreach ($save['other_fresh_selling'] as $rid => $fothers ){
				    $foi_arr[$rid]['FRESH']['selling_price']=mf($fothers['F']['sp']);
				}
			}
	        unset($save['other_notf_out_selling']);
	        unset($save['other_notf_cons_selling']);
	        unset($save['other_fresh_selling']);
		}

		//closing
		if ($save['closing_fresh_selling']){
	   		foreach ($save['closing_fresh_selling'] as $rid => $fclosing ){
			    $fcs_arr[$rid]['FRESH']['selling_price']=mf($fclosing['F']['sp']);
			}
		}

		unset($save['closing_fresh_selling']);

		//grn adjustment
		$smarty->assign('grn',$grn_arr);
		$smarty->assign('vgrn',$vgrn_arr);
 		$smarty->assign('fgrn',$fgrn_arr);
		unset($grn_arr,$vgrn_arr,$fgrn_arr);

		//rebate
/*
		$smarty->assign('r',$r_arr);
		$smarty->assign('vr',$vr_arr);
		$smarty->assign('fr',$fr_arr);
		unset($r_arr);
		unset($vr_arr);
		unset($fr_arr);
*/
		//idt
//		$smarty->assign('idt',$idt_arr);
		$smarty->assign('vidt',$vidt_arr);
//		$smarty->assign('fidt',$fidt_arr);
		unset($idt_arr,$vidt_arr,$fidt_arr);

		//consignment sales gp 
		$smarty->assign('vacs',$vas_arr);

		//other income
		$smarty->assign('oi',$oi_arr);
		$smarty->assign('voi',$voi_arr);
		$smarty->assign('foi',$foi_arr);
		unset($oi_arr,$voi_arr,$foi_arr);

		//closing
		$smarty->assign('fcs',$fcs_arr);
		unset($fcs_arr);

		//system opening stock
		$smarty->assign('os',$os_arr);
		$smarty->assign('vos',$vos_arr);
		$smarty->assign('fos',$fos_arr);

        //$this->compare_closing_stock($year,$month);
        
		//opening stock
		if ($config['csa_start_opening']){
			list($start_year,$start_month,$start_day)=explode('-',$config['csa_start_opening']);
			if ($start_year==$year && $start_month==$month) 
				$this->save_opening_to_closing($year,$month,$vos_arr,$fos_arr);
		}

		$this->assign_opening_stock($year,$month);
//		$smarty->assign('cos',$os_arr);
//		$smarty->assign('vcos',$vos_arr);
//		$smarty->assign('fcos',$fos_arr);
		unset($os_arr,$vos_arr,$fos_arr);


		$smarty->assign('stv',$this->st_arr);
		$smarty->assign('vstv',$this->vst_arr);
		$smarty->assign('fstv',$this->fst_arr);
		//print_r($this->st_arr);
		unset($this->st_arr,$this->vst_arr,$this->fst_arr);


/*
		$smarty->assign('ts',$ts_arr);
		$smarty->assign('vts',$vts_arr);
		unset($ts_arr);
		unset($vts_arr);
*/



//		$smarty->assign('fm',$fm_arr);
//		unset($fm_arr);

		//reset all data
		unset($data);
		unset($data_cat);
		unset($data_ven);
		unset($data_fre);

		$smarty->assign('table',$got_data);
		$smarty->assign('fresh',$fresh);
		$smarty->assign('vendor',$this->vendor);
		$smarty->assign('check_vendor',$this->check_vendor);
		$smarty->assign('created_timestamp',$time_created);
	}
	
	function sort_vendor($a,$b){
		if ($a['OUTRIGHT']['descrip'] == 'Other' || $a['CONSIGN']['descrip'] == 'Other')	return 1;
	
		if ($a['OUTRIGHT']['descrip'] && $b['OUTRIGHT']['descrip']){
			return ($a['OUTRIGHT']['descrip'] < $b['OUTRIGHT']['descrip']) ? -1 : 1;
		}elseif ($a['CONSIGN']['descrip'] && $b['CONSIGN']['descrip']){
			return ($a['CONSIGN']['descrip'] < $b['CONSIGN']['descrip']) ? -1 : 1;
		}
	}
	
	function save_opening_to_closing($year,$month, $vos_arr, $fos_arr){
		global $con,$smarty,$LANG;
		$cos_month = $month - 1;

		//date checking
		if (!$cos_month){
            $cos_year = $year - 1;
            $cos_month = 12;
		}else{
            $cos_year = $year;
		}

        $data['branch_id']=$this->bid;
        $data['year']=$cos_year;
        $data['month']=$cos_month;

		$cos_sql = "select * from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$cos_year and month=$cos_month and start_opening=1";
	    $cos_query=$con->sql_query($cos_sql,false,false);
		//if it is the starting point, dun replace the data.
		if ($con->sql_numrows()>0)  return;
		
		foreach ($vos_arr as $dept_id => $vs_cs){
		    foreach ($vs_cs as $vendor_id => $other ){
                $save['closing_notf_out_cost'][$dept_id][$vendor_id]['O']['cp'] = strval($other['OUTRIGHT']['cost_price']);
                $save['closing_notf_out_selling'][$dept_id][$vendor_id]['O']['sp'] = strval($other['OUTRIGHT']['selling_price']);
			}
		}

		foreach ($fos_arr as $dept_id => $fclosing){
	        $save['closing_fresh_selling'][$dept_id]['F']['sp'] = strval($fclosing['FRESH']['selling_price']);
		}

		$data['closing_notf_out_cost']=serialize($save['closing_notf_out_cost']);
		$data['closing_notf_out_selling']=serialize($save['closing_notf_out_selling']);
		$data['closing_fresh_selling']=serialize($save['closing_fresh_selling']);

		$con->sql_query("select * from category group by department_id");
		while ($r=$con->sql_fetchassoc()){
			$confirm_all[$r['root_id']][$r['department_id']]=1;
		}

        $ser_conf = serialize($confirm_all);

		$data['confirmed']=$ser_conf;
		$data['finalized']=$ser_conf;
		$data['reviewed']=$ser_conf;
		$data['start_opening']=1;

		$con->sql_query("update ".$this->tmp_file."csa_report set start_opening=0 where start_opening=1 and branch_id=$this->bid");

		$con->sql_query("insert into ".$this->tmp_file."csa_report " . mysql_insert_by_field($data) ." on duplicate key update
						".mysql_update_by_field($data));

		$scc[]=$LANG['CSA_STARTING_OPENING'];
        $smarty->assign('scc',$scc);
	}

	function assign_opening_stock($year,$month){
		global $con,$smarty, $sessioninfo;
	    //------------------->Opening Stock
	    $cos_month = $month - 1;

		//date checking
		if (!$cos_month){
            $cos_year = $year - 1;
            $cos_month = 12;
		}else{
            $cos_year = $year;
		}

 		$cos_sql = "select finalized from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$cos_year and month=$cos_month";

	    $cos_query=$con->sql_query($cos_sql,false,false);
		if ($con->sql_numrows($cos_query)>0){
			while ($cos_db=$con->sql_fetchassoc($cos_query)){
			    $finalize_data=unserialize($cos_db['finalized']);
			}
		}
		$con->sql_freeresult($cos_query);
		
  		foreach ($this->c_dept as $rid => $cfm ){
			foreach ($cfm as $cid => $desc){
                if ($finalize_data[$rid][$cid])	$opening_arr[$cid]=1;
//				$arr_check[$cid]="finalized like '%i:$cid;i:1;%'";
			}
		}

//		if ($arr_check)	$filter= " and ".join(" and ", $arr_check);

		$cos_sql = "select closing_notf_out_cost,closing_notf_out_selling,closing_fresh_selling from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$cos_year and month=$cos_month";

	    $cos_query=$con->sql_query($cos_sql,false,false);
		if ($con->sql_numrows($cos_query)>0){
			while ($cos_db=$con->sql_fetchassoc($cos_query)){
			    $save['closing_notf_out_cost']=unserialize($cos_db['closing_notf_out_cost']);
	   		    $save['closing_notf_out_selling']=unserialize($cos_db['closing_notf_out_selling']);
	   		    $save['closing_fresh_selling']=unserialize($cos_db['closing_fresh_selling']);
			}
		}

		$con->sql_freeresult($cos_query);

		if ($save['closing_notf_out_cost'] || $save['closing_notf_out_selling'] || $save['closing_fresh_selling']){
		    if ($save['closing_notf_out_cost']){
				foreach ($save['closing_notf_out_cost'] as $cid => $closing){
					if (!$opening_arr[$cid])	continue;
					foreach ($closing as $vid => $other ){
					    $data['opening'][$cid][$vid]['OUTRIGHT']['cost_price']=mf($other['O']['cp']);
					    $data['opening'][$cid][$vid]['OUTRIGHT']['selling_price']=mf($save['closing_notf_out_selling'][$cid][$vid]['O']['sp']);

						if (!$this->vendor[$cid][$vid]['OUTRIGHT']['descrip']){
							if ($vid === '')	continue;
						    $miss_vendor[$cid][$vid]['OUTRIGHT']=1;
							$missing_vendor[$cid][$vid]=$vid;
							/*
							if ($this->vst_arr[$cid]){
								//only calculate if got stock take
								$this->vst_arr[$cid][$vid]['OUTRIGHT']['cost_price'] = 0 - mf($other['O']['cp']);
								$this->vst_arr[$cid][$vid]['OUTRIGHT']['selling_price'] = 0 - mf($save['closing_notf_out_selling'][$cid][$vid]['O']['sp']);				
								$this->st_arr[$cid]['OUTRIGHT']['cost_price'] += $this->vst_arr[$cid][$vid]['OUTRIGHT']['cost_price']; 
								$this->st_arr[$cid]['OUTRIGHT']['selling_price'] += $this->vst_arr[$cid][$vid]['OUTRIGHT']['selling_price'];
							}
							*/
						}
					}
				}

				if ($missing_vendor){
					foreach ($missing_vendor as $cid => $vd ){
						$get_vendor_id = join(",",$vd);
						$get_vendor="select id,description from vendor where id in ($get_vendor_id)";
						$vendor_query=$con->sql_query($get_vendor);
						
						while ($ven_db=$con->sql_fetchassoc($vendor_query)){
							$this->vendor[$cid][$ven_db['id']]['OUTRIGHT']['descrip']=$ven_db['description'];
					    }
					    
					    if (isset($vd[0])) $this->vendor[$cid][0]['OUTRIGHT']['descrip']="Other";

					    $this->check_vendor[$cid]['OUTRIGHT']=1;
			    		$con->sql_freeresult($vendor_query);
					}
					
					$smarty->assign('miss_vendor', $miss_vendor);
				}
				
				//print_r($this->vendor);
			}

			$vcos_arr=$data['opening'];
/*
			if ($save['closing_fresh_selling']){
		   		foreach ($save['closing_fresh_selling'] as $cid => $fclosing ){
				    $fcos_arr[$cid]['FRESH']['selling_price']=mf($fclosing['F']['sp']);
				    if ($this->fst_arr) $this->fst_arr[$cid]['FRESH']['selling_price'] = 0 -  mf($fcos_arr[$cid]['FRESH']['selling_price']);
				}
			}
*/
			unset($data);
			unset($save);
		}
		
		if ($vcos_arr){
			foreach ($vcos_arr as $cid => $vo){
			    foreach ($vo as $vid => $other){
					$cos_arr[$cid]['OUTRIGHT']['cost_price']+=mf($other['OUTRIGHT']['cost_price']);
					$cos_arr[$cid]['OUTRIGHT']['selling_price']+=mf($other['OUTRIGHT']['selling_price']);
				}
			}
		}

		$smarty->assign('cos',$cos_arr);
		$smarty->assign('vcos',$vcos_arr);
		$smarty->assign('fcos',$fcos_arr);
		unset($cos_arr);
		unset($vcos_arr);
		unset($fcos_arr);
	}
	
	function ajax_check_departments(){
		global $con;
		
		$sql="select reviewed from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$_REQUEST[year] and month=$_REQUEST[month]";

        $con->sql_query($sql);
        $r=$con->sql_fetchassoc();
        $con->sql_freeresult();
        $dreviewed=unserialize($r['reviewed']);

		//filter departments to viewable and revieweddepartments
		if ($dreviewed){
	        foreach ($dreviewed as $rid=>$ct){
			    foreach ($ct as $cid => $trig){
			        if ($this->c_dept[$rid][$cid] && $trig)
			            $db_reviewed[$rid][$cid]=$trig;
			    }
			}
			unset($dreviewed);
		}
		
        $in_reviewed=$_REQUEST['reviewed'];

		//compare reviewed
		if ($in_reviewed){
			foreach ($in_reviewed as $rid=>$ct){
			    foreach ($ct as $cid => $trig){
					if ($db_reviewed[$rid][$cid]){
						unset($db_reviewed[$rid][$cid]);
						unset($in_reviewed[$rid][$cid]);
					}
				}
				if (!count($db_reviewed[$rid]))   unset($db_reviewed[$rid]);
				if (!count($in_reviewed[$rid]))   unset($in_reviewed[$rid]);
			}
		}
		
		//if no edit return
		if (!$db_reviewed && !$in_reviewed){
			print "OK";
			return;
		}

		if (!is_array($in_reviewed)){
            $in_reviewed=array();
		}

		if (!is_array($db_reviewed)){
            $db_reviewed=array();
		}

		$reviewes=array_merge($in_reviewed,$db_reviewed);
		
		foreach ($reviewes as $no => $ct){
		    foreach ($ct as $cid => $trig){
		        $depts[]=$cid;
		    }
		}
		
		//get departments description
		$dept_sql="select id,description from category where id in (".join(",",$depts).")";
        $con->sql_query($dept_sql);
        while ($d=$con->sql_fetchassoc()){
			$dept_desc[$d['id']]=$d['description'];
		}

		if ($db_reviewed){
			//untick
			foreach ($db_reviewed as $rid=>$ct){
			    foreach ($ct as $cid => $trig){
			        $db_desc[]=$dept_desc[$cid];
				}
			}

			$msg[]="Untick department(s): ".join(', ', $db_desc);
		}
		
		if ($in_reviewed){
			//tick
			foreach ($in_reviewed as $rid=>$ct){
			    foreach ($ct as $cid => $trig){
			        $in_desc[]=$dept_desc[$cid];
				}
			}

			$msg[]="Tick department(s): ".join(', ', $in_desc);
		}

		if($msg)	print join("\n", $msg);
	}
	
	function compare_closing_stock($year,$month){
	    global $con, $smarty, $sessioninfo;
	
		$cos_sql = "select closing_notf_out_cost,closing_notf_out_selling,closing_fresh_selling from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$year and month=$month";

	    $cos_query=$con->sql_query($cos_sql,false,false);
		if ($con->sql_numrows($cos_query)>0){
			while ($cos_db=$con->sql_fetchassoc($cos_query)){
			    $save['closing_notf_out_cost']=unserialize($cos_db['closing_notf_out_cost']);
	   		    $save['closing_notf_out_selling']=unserialize($cos_db['closing_notf_out_selling']);
	   		    $save['closing_fresh_selling']=unserialize($cos_db['closing_fresh_selling']);
			}
		}
		$con->sql_freeresult($cos_query);

		if ($save['closing_notf_out_cost'] || $save['closing_notf_out_selling'] || $save['closing_fresh_selling']){
		    if ($save['closing_notf_out_cost']){
				foreach ($save['closing_notf_out_cost'] as $cid => $closing){
					foreach ($closing as $vid => $other ){
					    $data['check_cs'][$cid][$vid]['OUTRIGHT']['cost_price']=mf($other['O']['cp']);
					    $data['check_cs'][$cid][$vid]['OUTRIGHT']['selling_price']=mf($save['closing_notf_out_selling'][$cid][$vid]['O']['sp']);
					}
				}
			}

			$vcos_arr=$data['check_cs'];

			if ($save['closing_fresh_selling']){
		   		foreach ($save['closing_fresh_selling'] as $cid => $fclosing ){
				    $fcos_arr[$cid]['FRESH']['selling_price']=mf($fclosing['F']['sp']);
				}
			}

			unset($data);
			unset($save);
		}

		if ($vcos_arr){
			foreach ($vcos_arr as $cid => $vo){
			    foreach ($vo as $vid => $other){
					$cos_arr[$cid]['OUTRIGHT']['cost_price']+=mf($other['OUTRIGHT']['cost_price']);
					$cos_arr[$cid]['OUTRIGHT']['selling_price']+=mf($other['OUTRIGHT']['selling_price']);
				}
			}
		}

		$smarty->assign('check_cos',$cos_arr);
		$smarty->assign('check_vcos',$vcos_arr);
		$smarty->assign('check_fcos',$fcos_arr);
		unset($cos_arr);
		unset($vcos_arr);
		unset($fcos_arr);
	}
	
	function auto_confirm_finalize_dept($form,$root_arr,$excluded_dept){
		global $con;
		/*
		//get root id
		$r_id=$con->sql_query("select * from category where level=2 and id in (".join(",",$this->arr_exc_dept).")");
		while ($r=$con->sql_fetchassoc($r_id)){
			$excluded_dept[$r['root_id']][$r['id']]=$r['id'];
			$root_arr[$r['root_id']]=$r['root_id'];
		}
		$con->sql_freeresult($r_id);		
		unset($r_id,$r);
		*/
		$can_update=false;
		$rt_id=$con->sql_query("select * from category where level=2 and root_id in (".join(",",$root_arr).") and id not in (".join(",",$this->arr_exc_dept).")");
		while ($rt=$con->sql_fetchassoc($rt_id)){
			$rt_arr[$rt['root_id']][$rt['id']]=$rt['id'];
		}
		$con->sql_freeresult($rt_id);
		
		//get csa report
	    $csa_id=$con->sql_query("select confirmed, finalized from ".$this->tmp_file."csa_report where branch_id=$this->bid and year=$form[year] and month=$form[month]");
	    if ($con->sql_numrows($csa_id)<=0)	return;
		$csa=$con->sql_fetchassoc($csa_id);
		$csa_confirmed_arr=unserialize($csa['confirmed']);
		$csa_finalized_arr=unserialize($csa['finalized']);
		
		$tmp_crt_arr=$rt_arr;
		$tmp_frt_arr=$rt_arr;

		
		foreach ($excluded_dept as $root_id => $exco){
			//check if other department is fully confirmed or finalized
			if ($csa_confirmed_arr[$root_id]){
				foreach ($csa_confirmed_arr[$root_id] as $ccid => $c){
					if ($c)	unset($tmp_crt_arr[$root_id][$ccid]);
				}
			}			
			if ($csa_finalized_arr[$root_id]){		
				foreach ($csa_finalized_arr[$root_id] as $fcid => $f){
					if ($f)	unset($tmp_frt_arr[$root_id][$fcid]);
				}
			}

			foreach ($exco as $exc_cid => $dummy){
				//if fully confirmed or finalized then auto confirm or finalize the excluded department
				if (!$tmp_crt_arr[$root_id]){
					$csa_confirmed_arr[$root_id][$exc_cid]=1;
					$can_update=true;		
				}
				
				if (!$tmp_frt_arr[$root_id]){
					$csa_finalized_arr[$root_id][$exc_cid]=1;		
					$can_update=true;
				}
			}
		}
		
		if ($can_update){
			$upd['confirmed']=serialize($csa_confirmed_arr);
			$upd['finalized']=serialize($csa_finalized_arr);

			$con->sql_query("update ".$this->tmp_file."csa_report set" .mysql_update_by_field($upd)." where branch_id=$this->bid and year=$form[year] and month=$form[month]");
		}
		$con->sql_freeresult($csa_id);
		unset($csa,$tmp_crt_arr,$tmp_frt_arr,$csa_confirmed_arr,$csa_finalized_arr,$upd);

	}
	
	function ajax_load_item_from_vendor(){
		global $con, $smarty;
		$form = $_REQUEST;
		
		$sku_type=$form['sku_type'];
		
		$smarty->assign("sales_gp",mf($form['sales_gp']));
		$smarty->assign("sku_type",$sku_type);		

		$dept_id=$form['department_id'];
		
		$dept_query=$con->sql_query("select * from category where id = $dept_id");
		$dept_db=$con->sql_fetchassoc($dept_query);
		$smarty->assign("department",$dept_db['description']);
		$con->sql_freeresult($dept_query);

		$file_data = file($this->file);

		if ($sku_type!='FRESH'){		
			//vendor
			$vid=$form['vendor_id'];
			$vendor_query=$con->sql_query("select * from vendor where id = $vid");
			$vendor_db=$con->sql_fetchassoc($vendor_query);
			$smarty->assign("vendor",$vendor_db['description']);
			$con->sql_freeresult($vendor_query);

			$data_item_ven=unserialize(trim($file_data[7]));    //vendor data
			//sku items
			$sku_item_id = $data_item_ven['sku_items'][$dept_id][$vid][$sku_type];
		}else{
			$data_item_ven=unserialize(trim($file_data[8]));    //fresh data
			$sku_item_id = $data_item_ven['fsku_items'][$dept_id][$sku_type];
		}	
		unset($file_data);
		
		if (!$data_item_ven)	$err = "No item list was generated for this month.";

		if ($err){
			$smarty->assign("err",$err);
		}else{
			if ($sku_item_id){					
				$get_items="select id,sku_item_code,receipt_description from sku_items where id in (".implode(',',$sku_item_id).") order by receipt_description";
				$items_query=$con->sql_query($get_items);
				
				while ($sku_db=$con->sql_fetchassoc($items_query)){
					$sku_items[$sku_db['id']]['receipt_description']=$sku_db['receipt_description'];
					$sku_items[$sku_db['id']]['sku_item_code']=$sku_db['sku_item_code'];
			    }
	    		$con->sql_freeresult($items_query);
			}
			
			unset($sku_item_id);
				
			$smarty->assign('sku_items',$sku_items);
	
	
			if ($sku_type=='FRESH'){
				//System Stock Opening
				$smarty->assign('item_vos',$data_item_ven['item_fos'][$dept_id][$sku_type]);
				
				unset($data_item_ven['item_fos']);
				
				//Stock Check
				$smarty->assign('item_vstv',$data_item_ven['item_fstv'][$dept_id][$sku_type]);
				unset($data_item_ven['item_fstv']);
				
				//Stocks Before Stock Check	
	//			$smarty->assign('item_vcov',$data_item_ven['stc_opening_variance'][$dept_id][$vid][$sku_type]);
	//			unset($data_item_ven['stc_opening_variance']);		
				
				//Stock Received (GRN)
				$smarty->assign('item_vsr',$data_item_ven['item_fsr'][$dept_id][$sku_type]);
				unset($data_item_ven['item_fsr']);
				
				//Adjustment(ARMS)
				$smarty->assign('item_vadj',$data_item_ven['item_fadj'][$dept_id][$sku_type]);
				unset($data_item_ven['item_fadj']);
				
				//Return Stock
				$smarty->assign('item_vrs',$data_item_ven['item_frs'][$dept_id][$sku_type]);
				unset($data_item_ven['item_frs']);
						
				//Promotion Amount
				$smarty->assign('item_vpa',$data_item_ven['item_fpa'][$dept_id][$sku_type]);
				unset($data_item_ven['item_fpa']);
				
				//price change amount
				$smarty->assign('item_vpca',$data_item_ven['item_fpca'][$dept_id][$sku_type]);
				unset($data_item_ven['item_fpca']);
				
				//Actual Sales
				$smarty->assign('item_vacs',$data_item_ven['item_facs'][$dept_id][$sku_type]);
				unset($data_item_ven['item_facs']);
			
			}else{
				//System Stock Opening
				$smarty->assign('item_vos',$data_item_ven['item_vos'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vos']);
				
				//Stock Check
				$smarty->assign('item_vstv',$data_item_ven['item_vstv'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vstv']);
				
				//Stocks Before Stock Check
		///		$smarty->assign('item_vstv',$data_item_ven['item_vstv'][$dept_id][$vid][$sku_type]);
		//		print_r($data_item_ven['item_vstv'][$dept_id][$vid][$sku_type]);
		//		unset($data_item_ven['item_vstv']);
				
				//Stock Received (GRN)
				$smarty->assign('item_vsr',$data_item_ven['item_vsr'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vsr']);
				
				//Adjustment(ARMS)
				$smarty->assign('item_vadj',$data_item_ven['item_vadj'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vadj']);
				
				//Return Stock
				$smarty->assign('item_vrs',$data_item_ven['item_vrs'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vrs']);
						
				//Promotion Amount
				$smarty->assign('item_vpa',$data_item_ven['item_vpa'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vpa']);
				
				//price change amount
				$smarty->assign('item_vpca',$data_item_ven['item_vpca'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vpca']);
				
				//Actual Sales
				$smarty->assign('item_vacs',$data_item_ven['item_vacs'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['item_vacs']);
				
				//Stocks Before Stock Check	
				$smarty->assign('item_vcov',$data_item_ven['stc_opening_variance'][$dept_id][$vid][$sku_type]);
				unset($data_item_ven['stc_opening_variance']);
						
			}
			unset($data_item_ven);
		}
		$this->display("report.csa.sku_items.tpl");
	}
	
	function ajax_load_before_stock_check(){
		global $con, $smarty;
		$form = $_REQUEST;
		
		$sku_type=$form['sku_type'];
		$smarty->assign("sku_type",$sku_type);	
		
		$file_data = file($this->file);
		$dept_id=$form['department_id'];
		
		$dept_query=$con->sql_query("select * from category where id = $dept_id");
		$dept_db=$con->sql_fetchassoc($dept_query);
		$smarty->assign("department",$dept_db['description']);
		$con->sql_freeresult($dept_query);

		if ($sku_type!='FRESH'){		
			//vendor
			$vid=$form['vendor_id'];
			$vendor_query=$con->sql_query("select * from vendor where id = $vid");
			$vendor_db=$con->sql_fetchassoc($vendor_query);
			$smarty->assign("vendor",$vendor_db['description']);
			$con->sql_freeresult($vendor_query);

			$data_item_ven=unserialize(trim($file_data[7]));    //vendor data
			//sku items
			$sku_item_id = $data_item_ven['stc_sku_items'][$dept_id][$vid][$sku_type];
		}else{
			$data_item_ven=unserialize(trim($file_data[8]));    //fresh data
			$sku_item_id = $data_item_ven['stc_fsku_items'][$dept_id][$sku_type];
		}	
			//=============qty before stock check=================== 
		if ($sku_item_id){					
			$get_items="select id,sku_item_code,receipt_description from sku_items where id in (".implode(',',$sku_item_id).") order by receipt_description";
			$items_query=$con->sql_query($get_items);
			
			while ($sku_db=$con->sql_fetchassoc($items_query)){
				$sku_items[$sku_db['id']]['receipt_description']=$sku_db['receipt_description'];
				$sku_items[$sku_db['id']]['sku_item_code']=$sku_db['sku_item_code'];
		    }
    		$con->sql_freeresult($items_query);
		}			
		unset($sku_item_id);	
		$smarty->assign('sku_items',$sku_items);
	
		/*
		//stock receive
		$smarty->assign('b_stc_sr',$data_cat['b_stc_sr']);
		$smarty->assign('b_stc_vsr',$data_ven['b_stc_vsr']);
		$smarty->assign('b_stc_fsr',$data_fre['b_stc_fsr']);
		unset($data_cat['b_stc_sr'], $data_ven['b_stc_vsr'], $data_fre['b_stc_fsr']);

		//adjustment
		$smarty->assign('b_stc_adj',$data_cat['b_stc_adj']);
		$smarty->assign('b_stc_vadj',$data_ven['b_stc_vadj']);
		$smarty->assign('b_stc_fadj',$data_fre['b_stc_fadj']);
		unset($data_cat['b_stc_adj'], $data_ven['b_stc_vadj'], $data_fre['b_stc_fadj']);
		
		//return stock
		$smarty->assign('b_stc_rs',$data_cat['b_stc_rs']);
		$smarty->assign('b_stc_vrs',$data_ven['b_stc_vrs']);
		$smarty->assign('b_stc_frs',$data_fre['b_stc_frs']);
		unset($data_cat['b_stc_rs'], $data_ven['b_stc_vrs'], $data_fre['b_stc_frs']);
		
		//promotion
		$smarty->assign('b_stc_pa',$data_cat['b_stc_pa']);
		$smarty->assign('b_stc_vpa',$data_ven['b_stc_vpa']);
		$smarty->assign('b_stc_fpa',$data_fre['b_stc_fpa']);
		unset($data_cat['b_stc_pa'], $data_ven['b_stc_vpa'], $data_fre['b_stc_fpa']);

		//price change
		$smarty->assign('b_stc_pca',$data_cat['b_stc_pca']);
		$smarty->assign('b_stc_vpca',$data_ven['b_stc_vpca']);
		$smarty->assign('b_stc_fpca',$data_fre['b_stc_fpca']);
		unset($data_cat['b_stc_pca'], $data_ven['b_stc_vpca'], $data_fre['b_stc_fpca']);

		//actual sales
		$smarty->assign('b_stc_acs',$data_cat['b_stc_acs']);
		$smarty->assign('b_stc_vacs',$data_ven['b_stc_vacs']);
		$smarty->assign('b_stc_facs',$data_fre['b_stc_facs']);
		unset($data_cat['b_stc_acs'], $data_ven['b_stc_vacs'], $data_fre['b_stc_facs']);
		*/
		if ($sku_type=='FRESH'){
			//Stock Received (GRN)
			$smarty->assign('item_vsr',$data_item_ven['b_stc_item_fsr'][$dept_id][$sku_type]);
			unset($data_item_ven['b_stc_item_fsr']);
			
			//Adjustment(ARMS)
			$smarty->assign('item_vadj',$data_item_ven['b_stc_item_fadj'][$dept_id][$sku_type]);
			unset($data_item_ven['b_stc_item_fadj']);
			
			//Return Stock
			$smarty->assign('item_vrs',$data_item_ven['b_stc_item_frs'][$dept_id][$sku_type]);
			unset($data_item_ven['b_stc_item_frs']);
					
			//Promotion Amount
			$smarty->assign('item_vpa',$data_item_ven['b_stc_item_fpa'][$dept_id][$sku_type]);
			unset($data_item_ven['b_stc_item_fpa']);
			
			//price change amount
			$smarty->assign('item_vpca',$data_item_ven['b_stc_item_fpca'][$dept_id][$sku_type]);
			unset($data_item_ven['b_stc_item_fpca']);
			
			//Actual Sales
			$smarty->assign('item_vacs',$data_item_ven['b_stc_item_facs'][$dept_id][$sku_type]);
			unset($data_item_ven['b_stc_item_facs']);
		}else{
			//Stock Received (GRN)
			$smarty->assign('item_vsr',$data_item_ven['b_stc_item_vsr'][$dept_id][$vid][$sku_type]);
			unset($data_item_ven['b_stc_item_vsr']);
			
			//Adjustment(ARMS)
			$smarty->assign('item_vadj',$data_item_ven['b_stc_item_vadj'][$dept_id][$vid][$sku_type]);
			unset($data_item_ven['b_stc_item_vadj']);
			
			//Return Stock
			$smarty->assign('item_vrs',$data_item_ven['b_stc_item_vrs'][$dept_id][$vid][$sku_type]);
			unset($data_item_ven['b_stc_item_vrs']);
					
			//Promotion Amount
			$smarty->assign('item_vpa',$data_item_ven['b_stc_item_vpa'][$dept_id][$vid][$sku_type]);
			unset($data_item_ven['b_stc_item_vpa']);
			
			//price change amount
			$smarty->assign('item_vpca',$data_item_ven['b_stc_item_vpca'][$dept_id][$vid][$sku_type]);
			unset($data_item_ven['b_stc_item_vpca']);
			
			//Actual Sales
			$smarty->assign('item_vacs',$data_item_ven['b_stc_item_vacs'][$dept_id][$vid][$sku_type]);
			unset($data_item_ven['b_stc_item_vacs']);
		}
		unset($data_item_ven);
		$this->display("report.csa.before_stock_check.tpl");
	}
	
	function check_can_unfinalize($form){
		global $con;
//		$prev_timestamp=strtotime("-1 month",strtotime(date("Y")."-".date("m")."-01"));
			
//		return (strtotime($form['year']."-".$form['month']."-01") >= $prev_timestamp);

		//check later month, got finalize no more finalize again
		$later_rid = $con->sql_query("select had_finalized from ".$this->tmp_file."csa_report where branch_id=$this->bid and ((year=".mi($form['year'])." and month>".mi($form['month']).") or year > ".mi($form['year']).") and had_finalized=1");
		
		if ($con->sql_numrows($later_rid)>0)
			//cannot unfinalize if found future had being finalized
			return false;
		else
			return true;	
	}
}
$report = new csa('Category Stock Analysis');

?>