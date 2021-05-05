<?php
/*
4/5/2017 3:55PM Zhi Kai 
-altering default year and month when first entering to current year and month.

5/18/2017 11:13AM Zhi Kai
- adding get_pie_data function to load pie chart of third level category sales.

4:47 PM 5/18/2017 Justin
- Bug fixed on piechart sales is not tally with the original chart.

5/19/2017 9:46 AM Justin
- Bug fixed on piechart sales have wrongly grouped.
- Bug fixed on age chart having group issue.
- Bug fixed on various charts that having calculation issue.

5/22/2017 11:28 AM Justin
- Bug fixed on sales not tally issue.

5/24/2017 10:35 AM Justin
- Bug fixed on the description showing wrongly for "Others" during piechart callout.

6/12/2017 4:49 PM Zhi Kai
- adding get_table_data function to show sku sales table when clicking on dialog's pie chart.

6/29/2017 4:35 PM Zhi Kai
- alter sql to show level 2 category id & description whenever there are no level 3 id for that particular category (instead of 'Others'). 

7/3/2017 3:57 PM Justin
- Bug fixed on sales not tally when comparing piechart sales by category with sales from SKU items.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_OVERVIEW')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_OVERVIEW', BRANCH_CODE), "/index.php");


class MembershipOverviewSales extends Module{
	var $gender_list = array("F"=>"Female", "M"=>"Male", "U"=>"Unassigned");
	var $race_list = array("M"=>"Malay", "C"=>"Chinese", "I"=>"Indian", "O"=>"Others");
	var $age_list = array(1=> "Below 30" , 2=> "31 to 44", 3=>"45 to 59" , 4=>"Above 60" , 5=>"Unassigned");
 
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		
		$this->branch_list = array();
		$q2 = $con->sql_query("select id,code from branch order by sequence,code");
		while($r= $con->sql_fetchassoc($q2)){
			$this->branch_list[$r['id']] = $r['code'];    // Array id => code
		}
		$con->sql_freeresult($q2);
	
		$smarty->assign("brn", $this->branch_list);
		$smarty->assign("gen", $this->gender_list);
		$smarty->assign("rc", $this->race_list);
		$smarty->assign("age", $this->age_list);	
		
		
		parent::__construct($title);
	}
	
	function _default(){
		global $con, $smarty;
		
		$q1 = $con->sql_query("select year(min(date)) as min_year, year(max(date)) as max_year from pos where date>0");

		while ($r = $con->sql_fetchassoc($q1)){    //get the min and max year available in pos sales
            $min_year = $r['min_year'];
            $max_year = $r['max_year'];
		}
		$con->sql_freeresult($q1);
		
		$count_year = $max_year - $min_year;
		
		for($i=0; $i<=$count_year; $i++){ 
			$tmp_year = $min_year+$i;      //load from min year. eg 2015-2017
			$year[$tmp_year] = $tmp_year;
		}
		$smarty->assign("yea", $year);
		
		//////////
		$month = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
		$smarty->assign("mon", $month);
		
		if(!$_REQUEST['year']) $_REQUEST['year']=date('Y');
		if(!$_REQUEST['month']) $_REQUEST['month']=date('m');
		
		$smarty->assign("year", $_REQUEST['year']);
		$smarty->assign("month", $_REQUEST['month']);
		
		$this->display();
	}
	
	function ajax_get_sales_data(){
		global $con, $smarty, $sessioninfo;
		
		$this->year = $_REQUEST['year']; 
		$this->month = $_REQUEST['month'];
		$bid = $_REQUEST['branch'];
		$this->chart_type = $_REQUEST['chart_type'];
		$this->ret = array();
		
		if(!$this->year || !$this->month) die("Invalid Month or Year.");
		if(!$this->chart_type) die("Invalid Chart Type.");
		
		$branch_list = array();
		if(BRANCH_CODE == "HQ"){ //if selected branch is not All
			if($bid > 0) $branch_list[$bid] = true;
			else $branch_list = $this->branch_list;
		}else $branch_list[$sessioninfo['branch_id']] = true;;
	
		$this->total_days = date("t", strtotime($this->year."-".$this->month."-1"));
		
		foreach($branch_list as $curr_bid=>$tmp){
			if($this->chart_type == "race_sales"){
				$this->get_race_data($curr_bid);
			}elseif($this->chart_type == "gender_sales"){
				$this->get_gender_data($curr_bid);
			}elseif($this->chart_type == "age_sales"){
				$this->get_age_data($curr_bid);
			}
		}
		
		if($this->chart_type == "race_sales"){
			foreach($this->race_list as $race_type => $race_desc){
				$this->ret['race_list'][$race_type]['race_name'] = $race_desc;
			}
			ksort($this->ret['race_list']);
		}elseif($this->chart_type == "gender_sales"){
			foreach($this->gender_list as $gender_type=>$gender_desc){
				$this->ret['gender_list'][$gender_type]['gender_name'] = $gender_desc;
			}
			ksort($this->ret['gender_list']);
		}elseif($this->chart_type == "age_sales"){
			foreach($this->age_list as $age_group => $age_desc){
				$this->ret['age_list'][$age_group]['age_name'] = $age_desc;	
			}
			ksort($this->ret['age_list']);
		}
		
		$this->ret['ok'] = 1;
		print json_encode($this->ret);
	}
	
	function get_race_data($bid){
		global $con, $smarty, $sessioninfo;

		$filter = "tbl.month = ".mi($this->month)." and tbl.year = ".mi($this->year)." and tbl.card_no != '' and tbl.card_no is not null";
		
		$tbl = 'member_sales_cache_b'.$bid;
	    $q3 = $con->sql_query("select tbl.date, tbl.year, tbl.month, tbl.day, (tbl.amount) as amount,
							   ifnull((select if(m.race is not null and m.race !='' , m.race , 'Others')
							   from membership_history mh
							   left join membership m on m.nric = mh.nric
							   where mh.card_no = tbl.card_no limit 1), 'Others') as race
							   from $tbl tbl
							   where $filter
							   order by tbl.date asc");

		while($r = $con->sql_fetchassoc($q3)){
			$r['race'] = array_search($r['race'], $this->race_list); // have to get CIMO
			$this->ret['member_sales'][$r['race']][$r['date']]['gross_amount'] += round($r['amount'],2);    //round off the decimal
			$this->ret['member_sales'][$r['race']][$r['date']]['year'] = $r['year'];  //get year
			$this->ret['member_sales'][$r['race']][$r['date']]['month'] = $r['month']; //get month
			$this->ret['member_sales'][$r['race']][$r['date']]['day'] = $r['day'];   //get day
		}
		$con->sql_freeresult($q3);   //check if there is result
		
		if($this->ret['member_sales']){
			foreach($this->ret['member_sales'] as $race=>$date_list){
				for($d=1; $d<=$this->total_days; $d++){        //run thru every single day
					$tmp_date = date("Y-m-d", strtotime($this->year."-".$this->month."-".$d));
					
					if(!isset($this->ret['member_sales'][$race][$tmp_date])){    //if no sales, point result to 0
						$this->ret['member_sales'][$race][$tmp_date]['gross_amount'] += 0;
						$this->ret['member_sales'][$race][$tmp_date]['year'] = date("Y", strtotime($tmp_date));  //get year
						$this->ret['member_sales'][$race][$tmp_date]['month'] = date("m", strtotime($tmp_date)); //get month
						$this->ret['member_sales'][$race][$tmp_date]['day'] = date("d", strtotime($tmp_date));   //get day
					}
				}
				ksort($this->ret['member_sales'][$race]); //ascending sorting of key
			}
		}

		/*print "<pre>";
		print_r ($this->ret);
		print "</pre>";*/
	}
	
	
	function get_gender_data($bid){
		global $con, $smarty, $sessioninfo;
		
		$filter = "tbl.month = ".mi($this->month)." and tbl.year = ".mi($this->year)." and tbl.card_no != '' and tbl.card_no is not null";
		
		$tbl = 'member_sales_cache_b'.$bid;
	    $q5 = $con->sql_query("select tbl.date, tbl.year, tbl.month, tbl.day, (tbl.amount) as amount,
							   ifnull((select if(m.gender is not null and m.gender != '', m.gender, 'U')
										 from membership_history mh
										 left join membership m on m.nric = mh.nric
										 where mh.card_no=tbl.card_no limit 1), 'U') as gender
							   from $tbl tbl
							   where $filter
							   order by tbl.date asc");
								
		while($r = $con->sql_fetchassoc($q5)){
			$this->ret['member_sales'][$r['gender']][$r['date']]['gross_amount'] += round($r['amount'],2);    //round off the decimal
			$this->ret['member_sales'][$r['gender']][$r['date']]['year'] = $r['year'];  //get year
			$this->ret['member_sales'][$r['gender']][$r['date']]['month'] = $r['month']; //get month
			$this->ret['member_sales'][$r['gender']][$r['date']]['day'] = $r['day'];   //get day
		}
		$con->sql_freeresult($q5);   //check if there is result
		
		if($this->ret['member_sales']){
			foreach($this->ret['member_sales'] as $gender=>$date_list){
				for($d=1; $d<=$this->total_days; $d++){        //run thru every single day
					$tmp_date = date("Y-m-d", strtotime($this->year."-".$this->month."-".$d));
					
					if(!isset($this->ret['member_sales'][$gender][$tmp_date])){    //if no sales, point result to 0
						$this->ret['member_sales'][$gender][$tmp_date]['gross_amount'] += 0;                       
						$this->ret['member_sales'][$gender][$tmp_date]['year'] = date("Y", strtotime($tmp_date));  //get year
						$this->ret['member_sales'][$gender][$tmp_date]['month'] = date("m", strtotime($tmp_date)); //get month
						$this->ret['member_sales'][$gender][$tmp_date]['day'] = date("d", strtotime($tmp_date));   //get day
					}
				}
				ksort($this->ret['member_sales'][$gender]); //ascending sorting of key
			}
		}
		
		/*print "<pre>";
		print_r ($this->ret);
		print "</pre>";*/
	}
	
	
	
	function get_age_data($bid){
		global $con, $smarty, $sessioninfo;
		
		$filter = "tbl.month = ".mi($this->month)." and tbl.year = ".mi($this->year)." and tbl.card_no != '' and tbl.card_no is not null";
		
		$tbl = 'member_sales_cache_b'.$bid;
	    $q5 = $con->sql_query("select tbl.date, tbl.year, tbl.month, tbl.day, (tbl.amount) as amount,
							   (select if(m.dob is not null and m.dob != '', m.dob, 'Unassigned')
										 from membership_history mh
										 left join membership m on m.nric = mh.nric
										 where mh.card_no=tbl.card_no limit 1) as dob
							   from $tbl tbl
							   where $filter
							   order by tbl.date asc");

		while($r = $con->sql_fetchassoc($q6)){
			$r['dob']=substr($r['dob'],0,4);
			$r['age']= date('Y') - $r['dob'];
				
			if( $r['age'] < 31 && $r['age'] >0){  
				$r['age_group']=1;	        		
			}					
			elseif( $r['age'] >30 && $r['age'] <45) { 
				$r['age_group']=2;	
			}
			elseif( $r['age'] >44 && $r['age'] <60){
				$r['age_group']=3;	
			}
			elseif( $r['age'] >59 && $r['age']<date("Y")){
				$r['age_group']=4;	
			}
			elseif($r['age'] == date("Y")){          //for unassigned dob
				$r['age_group']=5;
			}
				
			$this->ret['member_sales'][$r['age_group']][$r['date']]['gross_amount'] += round($r['amount'],2) ;    //round off the decimal
			$this->ret['member_sales'][$r['age_group']][$r['date']]['year'] = $r['year'];  //get year
			$this->ret['member_sales'][$r['age_group']][$r['date']]['month'] = $r['month']; //get month
			$this->ret['member_sales'][$r['age_group']][$r['date']]['day'] = $r['day'];   //get day					
		}
		$con->sql_freeresult($q6);   //check if there is result
		
		if($this->ret['member_sales']){
			ksort($this->ret['member_sales']);
			foreach($this->ret['member_sales'] as $age_group=>$date_list){
				for($d=1; $d<=$this->total_days; $d++){        //run thru every single day
					$tmp_date = date("Y-m-d", strtotime($this->year."-".$this->month."-".$d));
					
					if(!isset($this->ret['member_sales'][$age_group][$tmp_date])){    //if no sales, point result to 0
						$this->ret['member_sales'][$age_group][$tmp_date]['gross_amount'] +=0;
						$this->ret['member_sales'][$age_group][$tmp_date]['year'] = date("Y", strtotime($tmp_date));  //get year
						$this->ret['member_sales'][$age_group][$tmp_date]['month'] = date("m", strtotime($tmp_date)); //get month
						$this->ret['member_sales'][$age_group][$tmp_date]['day'] = date("d", strtotime($tmp_date));   //get day
					}
				}
				ksort($this->ret['member_sales'][$age_group]); //ascending sorting of key
			}
		}
		
		/*print "<pre>";
		print_r ($ret);
		print "</pre>";*/
	}
	
	function get_pie_data(){
		global $con, $smarty, $sessioninfo;
		$filter = $having = array();
		
		$this->datefrom = $_REQUEST['datefrom'];
		$this->dateto = $_REQUEST['dateto'];
		$this->c_value = $_REQUEST['c_value'];
		$this->c_type = $_REQUEST['c_type'];
		$this->bid = $_REQUEST['bid'];
		
		$this->data_validate($_REQUEST);
		
		if(BRANCH_CODE == "HQ"){
			if($this->bid > 0) $filter[] ="p.branch_id = ".mi($this->bid); //means it is not All branches
		}else $filter[] ="p.branch_id = ".mi($sessioninfo['branch_id']); // means sub branch
		
		if($this->c_type == "gender"){
			if($this->c_value == 'Male'){
				$this->c_value = 'M';
			}elseif($this->c_value == 'Female'){
				$this->c_value = 'F';
			}elseif($this->c_value == 'Unassigned'){
				$this->c_value = 'U';
			}
			$sub_q ="ifnull((select if(m.gender is not null and m.gender != '', m.gender, 'U')
					from membership_history mh
					left join membership m on m.nric = mh.nric
					where mh.card_no=p.member_no limit 1), 'U')"; 
					
			if($this->c_value != '')			        //not --All--
				$having[] = " filter_val=".ms($this->c_value);	
		}elseif($this->c_type == "race"){
			$sub_q="ifnull((select if(m.race is not null and m.race !='' , m.race , 'Others')
					from membership_history mh
					left join membership m on m.nric = mh.nric
					where mh.card_no = p.member_no limit 1),'Others')";	
			
			if($this->c_value != '') $having[] = " filter_val=".ms($this->c_value);
		}elseif($this->c_type == "age"){
			$sub_q = "ifnull((select if(m.dob > 0 and substring(m.dob,1,4) is not null and substring(m.dob,1,4) != '', YEAR(CURDATE()) - substring(m.dob,1,4), 'Unassigned')
										 from membership_history mh
										 left join membership m on m.nric = mh.nric
										 where mh.card_no=p.member_no limit 1), 'Unassigned') ";

			if ($this->c_value !=''){	
				if($this->c_value == 'Below 30'){
					$having[] = " filter_val between 1 and 30";
				}elseif($this->c_value == '31 to 44'){
					$having[] = " filter_val between 31 and 44";
				}elseif($this->c_value == '45 to 59'){
					$having[] = " filter_val between 45 and 59";
				}elseif($this->c_value == 'Above 60'){
					$having[] = " filter_val > 59";
				}else{
					$having[] = " filter_val = 'Unassigned'";
				}
			}
		}
		
		$filter[] = "p.member_no !='' and p.member_no is not null and p.cancel_status=0 and p.date between ".ms($this->datefrom)." and ".ms($this->dateto)." and pf.finalized=1";
		
		$having[] = "ttl_gross_amt != 0";
		
		$q6 = $con->sql_query($sql="select (ifnull(pi.price,0)-ifnull(pi.discount,0)-ifnull(pi.discount2,0)-ifnull(pi.tax_amount, 0)) as ttl_gross_amt, 
							if(cc.p3 = 0 or cc.p3 is null, c2.description, c3.description) as lvl_cat_desc, if(cc.p3=0 or cc.p3 is null,cc.p2,cc.p3) as lvl_cat_id,
							$sub_q as filter_val
							from pos p
							left join pos_items pi on pi.pos_id = p.id and pi.branch_id = p.branch_id and pi.date = p.date and pi.counter_id = p.counter_id
							left join sku_items si on si.id = pi.sku_item_id  
							left join sku s on s.id = si.sku_id
							left join category c on c.id = s.category_id
							left join category_cache cc on cc.category_id = c.id 
							left join category c3 on c3.id= cc.p3
							left join category c2 on c2.id= cc.p2
							left join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date
							where ".join(" and ", $filter)."
							having ".join(" and ", $having)."
							order by ttl_gross_amt DESC");

		while($r = $con->sql_fetchassoc($q6)){
			if(mb_strlen($r['lvl_cat_desc']) > 20) $cat_name = mb_substr($r['lvl_cat_desc'], 0, 17)."...";
			else $cat_name = $r['lvl_cat_desc'];

			$this->ret['cat_sales'][$r['lvl_cat_id']]['cat_name'] = $cat_name;
			$this->ret['cat_sales'][$r['lvl_cat_id']]['category_id'] = $r['lvl_cat_id'];
			$this->ret['cat_sales'][$r['lvl_cat_id']]['cat_full_name'] = $r['lvl_cat_desc'];
			$this->ret['cat_sales'][$r['lvl_cat_id']]['total'] += round($r['ttl_gross_amt'], 2);
		}
		$con->sql_freeresult($q6);
		
		$count = $key = 0;
		if($this->ret['cat_sales']){
			usort($this->ret['cat_sales'], array($this,"reverse_sort"));
			
			for($i=0; $i<=count($this->ret['cat_sales']); $i++){
				$total_amt += $this->ret['cat_sales'][$i]['total'];
				if($i>=9){
					$key=9; // always 9 since it is the last
					$this->ret['cat_sales'][$key]['cat_name'] = $this->ret['cat_sales'][$key]['cat_full_name'] = "Others";
					$this->ret['cat_sales'][$key]['category_id'] = 'OtherGroup';
					if($i != $key){
						$this->ret['cat_sales'][$key]['total'] += $this->ret['cat_sales'][$i]['total'];
					}
					if(!isset($this->ret['cat_sales'][$key]['top9_cat_id'])){
						$this->ret['cat_sales'][$key]['top9_cat_id'] = join(",", $top9_cat_list);
					}
				}else{
					$top9_cat_list[] = $this->ret['cat_sales'][$i]['category_id'];
				}
			}
		}
		
		$this->ret['ok'] = 1;
		print json_encode($this->ret);
		/*print "<pre>";
		print_r ($this->ret);
		print "</pre>";*/
	}
	
	function get_table_data(){
		global $con, $smarty, $sessioninfo;
		$filter = $cat_list = $having = $items = array();
		
		$this->datefrom = $_REQUEST['datefrom'];
		$this->dateto = $_REQUEST['dateto'];
		$this->c_value = $_REQUEST['c_value'];
		$this->c_type = $_REQUEST['c_type'];
		$this->bid = $_REQUEST['bid'];
		$this->cid = $_REQUEST['cid'];            //from category id

		$this->data_validate($_REQUEST);
		
		if(BRANCH_CODE == "HQ"){
			if($this->bid > 0) $filter[] ="p.branch_id = ".mi($this->bid); //means it is not All branches
		}else $filter[] ="p.branch_id = ".mi($sessioninfo['branch_id']); // means sub branch
		
		if($this->c_type == "gender"){
			if($this->c_value == 'Male'){
				$this->c_value = 'M';
			}elseif($this->c_value == 'Female'){
				$this->c_value = 'F';
			}elseif($this->c_value == 'Unassigned'){
				$this->c_value = 'U';
			}
			$sub_q ="ifnull((select if(m.gender is not null and m.gender != '', m.gender, 'U')
					from membership_history mh
					left join membership m on m.nric = mh.nric
					where mh.card_no=p.member_no limit 1), 'U')"; 
					
			if($this->c_value != '')			        //not --All--
				$having[] = " filter_val=".ms($this->c_value);	
		}elseif($this->c_type == "race"){
			$sub_q="ifnull((select if(m.race is not null and m.race !='' , m.race , 'Others')
					from membership_history mh
					left join membership m on m.nric = mh.nric
					where mh.card_no = p.member_no limit 1),'Others')";	
			if($this->c_value != '')			          
				$having[] = " filter_val=".ms($this->c_value);
		}elseif($this->c_type == "age"){
			$sub_q = "ifnull((select if(m.dob > 0 and substring(m.dob,1,4) is not null and substring(m.dob,1,4) != '', YEAR(CURDATE()) - substring(m.dob,1,4), 'Unassigned')
										 from membership_history mh
										 left join membership m on m.nric = mh.nric
										 where mh.card_no=p.member_no limit 1), 'Unassigned') ";
			if ($this->c_value !=''){	
				if($this->c_value == 'Below 30'){
					$having[] = " filter_val between 1 and 30 ";
				}elseif($this->c_value == '31 to 44'){
					$having[] = " filter_val between 31 and 44 ";
				}elseif($this->c_value == '45 to 59'){
					$having[] = " filter_val between 45 and 59 ";
				}elseif($this->c_value == 'Above 60'){
					$having[] = " filter_val > 59 ";
				}else{
					$having[] = " filter_val = 'Unassigned' ";
				}
			}
		}
		
		$filter[] = "p.member_no !='' and p.member_no is not null and p.cancel_status=0 and p.date between ".ms($this->datefrom)." and ".ms($this->dateto)." and pf.finalized=1";
		
		$having[] = " ttl_gross_amt != 0";
		
		// get category list
		$q7 = $con->sql_query($sql=" 
			select (ifnull(pi.price,0)-ifnull(pi.discount,0)-ifnull(pi.discount2,0)-ifnull(pi.tax_amount, 0)) as ttl_gross_amt,
			if(cc.p3 = 0 or cc.p3 is null, c2.description, c3.description) as lvl_cat_desc, if(cc.p3=0 or cc.p3 is null,cc.p2,cc.p3) as lvl_cat_id, 
			$sub_q as filter_val
			from pos p 
			left join pos_items pi on pi.pos_id = p.id and pi.branch_id = p.branch_id and pi.date = p.date and pi.counter_id = p.counter_id
			left join sku_items si on si.id = pi.sku_item_id  
			left join sku s on s.id = si.sku_id
			left join category c on c.id = s.category_id
			left join category_cache cc on cc.category_id = c.id 
			left join category c3 on c3.id= cc.p3
			left join category c2 on c2.id= cc.p2
			left join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date
			where ".join(" and ", $filter)." 
			having ".join(" and ", $having)."
			order by ttl_gross_amt DESC
			");
			
		while($r= $con->sql_fetchassoc($q7)){
			$cat_list[$r['lvl_cat_id']]['total'] += round($r['ttl_gross_amt'],2);
			$cat_list[$r['lvl_cat_id']]['lvl_cat_id'] = $r['lvl_cat_id'];
			$cat_list[$r['lvl_cat_id']]['description'] = $r['lvl_cat_desc'];
		}
		$con->sql_freeresult($q7);
		
		$count = $key = 0;
		if($cat_list){
			usort($cat_list, array($this,"reverse_sort"));

			foreach($cat_list as $cat_arr=>$t){
				$count++;
				if($count > 9){                            
					$other_cat_list[$t['lvl_cat_id']]['description']= $t['description'];
				}else{
					$top9_cat_list[$t['lvl_cat_id']]['description']= $t['description'];
				}
			}
		}
		unset($cat_list);

		if($this->cid != ''){                                           //not --All--
			if($this->cid == 'OtherGroup'){	                 //when select --Others--
				$having[] = " lvl_cat_id not in (".join(",", array_keys($top9_cat_list)).")";
			}else
				$having[] = " lvl_cat_id=".ms($this->cid);                // filter by category
		}
		
		$q6 = $con->sql_query($sql="select (ifnull(pi.price,0)-ifnull(pi.discount,0)-ifnull(pi.discount2,0)-ifnull(pi.tax_amount, 0)) as ttl_gross_amt, 
							if(cc.p3 = 0 or cc.p3 is null, c2.description, c3.description) as lvl_cat_desc, if(cc.p3=0 or cc.p3 is null,cc.p2,cc.p3) as lvl_cat_id, 
							si.sku_item_code , si.mcode , si.artno , si.description as sku_description, pi.qty , $sub_q as filter_val, si.id as sku_item_id
							from pos p
							left join pos_items pi on pi.pos_id = p.id and pi.branch_id = p.branch_id and pi.date = p.date and pi.counter_id = p.counter_id
							left join sku_items si on si.id = pi.sku_item_id  
							left join sku s on s.id = si.sku_id
							left join category c on c.id = s.category_id
							left join category_cache cc on cc.category_id = c.id 
							left join category c3 on c3.id= cc.p3
							left join category c2 on c2.id= cc.p2
							left join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date
							where ".join(" and ", $filter)." 
							having ".join(" and ", $having)."
							order by ttl_gross_amt DESC");
		
		while($r = $con->sql_fetchassoc($q6)){
			$items[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
			$items[$r['sku_item_id']]['mcode'] = $r['mcode'];
			$items[$r['sku_item_id']]['artno'] = $r['artno'];
			
			$items[$r['sku_item_id']]['sku_description'] = $r['sku_description'];
			$items[$r['sku_item_id']]['quantity'] += mf($r['qty']);
			$items[$r['sku_item_id']]['total'] += round($r['ttl_gross_amt'], 2);
		}
		$con->sql_freeresult($q6);	
		
	/*	print "<pre>";
		print_r ($items);
		print "</pre>"; */
		
		$smarty->assign("items", $items);
		$smarty->assign("cat_list", $top9_cat_list);
		$smarty->assign("other_cat_list", $other_cat_list);
		$smarty->assign("c_type", $this->c_type);    //pass to sku table
		$smarty->assign("cid", $this->cid);    //pass to sku table
		
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch("membership.overview.sales.sku_table.tpl");
		print json_encode($ret);
	}
	
	function reverse_sort($a,$b){
	    if($a['total']==$b['total']) return 0;
	    else return ($a['total']>$b['total']) ? -1 : 1;
        
	}
	
	function data_validate($form){
		if(preg_match("/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{2})$/" ,$form['datefrom']))   //dateformat checking
			$datefromChecking = true;
		if(preg_match("/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{2})$/" ,$form['dateto']))
			$datetoChecking = true;
		if($form['datefrom'] == "" || $form['dateto'] == "")
			die('Please enter date');
		elseif($datefromChecking != true || $datetoChecking != true)
			die('Invalid date format');
		elseif(strtotime($form['datefrom']) > strtotime($form['dateto']))
			die('Date from cannot be newer than Date to.');
	}
	
}
$m = new MembershipOverviewSales('Membership Sales');
?>