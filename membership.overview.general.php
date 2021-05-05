<?php
/*
4/6/2017 11:54 AM Justin
- Bug fixed on race will have "CIMO" wording instead of full sentence.
- Bug fixed on race will have capital wordings that causing the array to capture in 2 different groups.

4/7/2017 1:13PM Zhi Kai
- Altering the 'Unassigned' data in race information to be included as the 'Others' group in the by race chart 

6/28/2017 4:45PM Zhi Kai
- Adding showing brand sales feature.

7/10/2017 9:43AM Zhi Kai
- Adding code for determining the minimum and maximum of y-axis for brand sales.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_OVERVIEW')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_OVERVIEW', BRANCH_CODE), "/index.php");

class MembershipOverviewGeneral extends Module{
	
	var $gender_list = array("F"=>"Female", "M"=>"Male", "U"=>"Unassigned");
	var $race_list = array("M"=>"Malay", "C"=>"Chinese", "I"=>"Indian", "O"=>"Others");
	var $age_list = array(1=> "Below 30" , 2=> "31 to 44", 3=>"45 to 59" , 4=>"Above 60" , 5=>"Unassigned");
	
	function _default(){
		global $con, $smarty;
		
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
		$this->display();	
	}
	
	function get_gender_data(){
		global $con;
		
		$q2 = $con->sql_query("select case when gender = 'F' then 'Female' when gender = 'M' then 'Male' else 'Unassigned' end as gender from membership  ");
		
		$gender_info = array();
		$ret=array();
		
		
		while($r = $con->sql_fetchassoc($q2)){  // reading the row info
			//if($gender_list[$r['gender']]) $r['gender'] = $gender_list[$r['gender']];
			
			$ret['gender_info'][$r['gender']]['gender_name'] = $r['gender'];
			$ret['gender_info'][$r['gender']]['count']++;
		}
		
		$gender_list = array("F"=>"Female", "M"=>"Male", "U"=>"Unassigned");
		foreach($gender_list as $gender_type=>$gender_desc){
			if(!isset($ret['gender_info'][$gender_desc])){    //for empty data
				$ret['gender_info'][$gender_desc]['gender_name'] = $gender_desc;
				$ret['gender_info'][$gender_desc]['count'] = 0;
			}
		}
		if($ret['gender_list']) ksort($ret['gender_list']);
		
		
		$con->sql_freeresult($q2);   //check if there is result
		if($ret['gender_info']) ksort($ret['gender_info']);
		
		$ret['ok'] = 1;
		print json_encode($ret);
		/*print "<pre>";
		print_r ($ret);
		print "</pre>";*/
	}
	
	function get_race_data(){
		global $con;

		$race_list = array("M"=>"Malay", "C"=>"Chinese", "I"=>"Indian", "O"=>"Others");
		$q3 = $con->sql_query("select if(race is not null and race != '', race, 'Others') as race from membership");
		
		$race_info = array();
		$ret=array();
		while($r = $con->sql_fetchassoc($q3)){  // reading the row info
			$r['race'] = ucwords(strtolower($r['race']));
			
			if($race_list[$r['race']]) $r['race'] = $race_list[$r['race']];
			
			$ret['race_info'][$r['race']]['race'] = $r['race'];
			$ret['race_info'][$r['race']]['count']++;
		}
		//$ret['race_info'][$r['race']]['count'] = number_format($ret['race_info'][$r['race']]['count'], 0, '.' , ',');
		$con->sql_freeresult($q3);   //check if there is result
		ksort($ret['race_info']);    //sort the legend according to the number placed for race
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function get_age_data(){
		global $con;
		
		$q4 = $con->sql_query("select if(dob is not null and dob != '', dob, 'U') as dob from membership");
		
		$age_info=array();
		$ret=array();
		
		while($r = $con->sql_fetchassoc($q4)){  // reading the row info
			if($r['dob']>0){                     //to get the age of dob other than those with 0
				$r['dob']=substr($r['dob'],0,4);  //sub string to get the first 4 digit
				$r['age']= date('Y') - $r['dob'];            //getting the age of each individual
				
				if($r['age'] < 31 && $r['age'] >0){ 
					$ret['age_info'][1]['age_name'] = "Below 30";              //assigning the name
					$ret['age_info'][1]['count']++;
				}
				elseif($r['age'] >30 && $r['age'] <45) {
					$ret['age_info'][2]['age_name'] = "31 to 44 ";
					$ret['age_info'][2]['count']++;
				}
				elseif($r['age'] >44 && $r['age'] <60){
					$ret['age_info'][3]['age_name'] = "45 to 59 ";
					$ret['age_info'][3]['count']++;
				}
				elseif($r['age'] >59 ){
					$ret['age_info'][4]['age_name'] = "Above 60";
					$ret['age_info'][4]['count']++;
				}
			}
			else{                       //those unassigned dob and dob with 0 value
				$ret['age_info']["Unassigned"]['age_name'] = "Unassigned";
				$ret['age_info']["Unassigned"]['count']++;
			}
		}
		$con->sql_freeresult($q4);   //check if there is result
		ksort($ret['age_info']);     //sorting the name(legend)
		
		$ret['ok'] = 1;
		print json_encode($ret);		
	}
	
	
	function load_column_chart(){
		global $con, $smarty, $sessioninfo;
		
		$filter = $having = $ret = array();
		
		$this->bid = $_REQUEST['bid'];
		$this->b_value = $_REQUEST['b_value'];
		$this->b_type = $_REQUEST['b_type'];
		$this->datefrom = $_REQUEST['datefrom'];
		$this->dateto = $_REQUEST['dateto'];
		
		$this->data_validate($_REQUEST);
			
		if(BRANCH_CODE == "HQ"){
			if($this->bid > 0) $filter[] ="p.branch_id = ".mi($this->bid); //means it is not All branches
		}else $filter[] ="p.branch_id = ".mi($sessioninfo['branch_id']); // means sub branch
		
		if($this->b_type == "gender"){
			if($this->b_value == 'Male'){
				$this->b_value = 'M';
			}elseif($this->b_value == 'Female'){
				$this->b_value = 'F';
			}elseif($this->b_value == 'Unassigned'){
				$this->b_value = 'U';
			}
			$sub_q ="ifnull((select if(m.gender is not null and m.gender != '', m.gender, 'U')
					from membership_history mh
					left join membership m on m.nric = mh.nric
					where mh.card_no=p.member_no limit 1), 'U')"; 
					
			if($this->b_value != '')			        //not --All--
				$having[] = " filter_val=".ms($this->b_value);	
		}elseif($this->b_type == "race"){
			$sub_q="ifnull((select if(m.race is not null and m.race !='' , m.race , 'Others')
					from membership_history mh
					left join membership m on m.nric = mh.nric
					where mh.card_no = p.member_no limit 1),'Others')";	
			if($this->b_value != '')			          
				$having[] = " filter_val=".ms($this->b_value);
		}elseif($this->b_type == "age"){
			$sub_q = "ifnull((select if(m.dob > 0 and substring(m.dob,1,4) is not null and substring(m.dob,1,4) != '', YEAR(CURDATE()) - substring(m.dob,1,4), 'Unassigned')
										 from membership_history mh
										 left join membership m on m.nric = mh.nric
										 where mh.card_no=p.member_no limit 1), 'Unassigned') ";
			if ($this->b_value !=''){	
				if($this->b_value == 'Below 30'){
					$having[] = " filter_val between 1 and 30 ";
				}elseif($this->b_value == '31 to 44'){
					$having[] = " filter_val between 31 and 44 ";
				}elseif($this->b_value == '45 to 59'){
					$having[] = " filter_val between 45 and 59 ";
				}elseif($this->b_value == 'Above 60'){
					$having[] = " filter_val > 59 ";
				}else{
					$having[] = " filter_val = 'Unassigned' ";
				}
			}
		}
		
		$filter[] = "p.member_no !='' and p.member_no is not null and p.cancel_status=0 and p.date between ".ms($this->datefrom)." and ".ms($this->dateto)." and pf.finalized=1";
		$having[] = "ttl_gross_amt != 0";
	
		$q1=$con->sql_query($sql="select (ifnull(pi.price,0)-ifnull(pi.discount,0)-ifnull(pi.discount2,0)-ifnull(pi.tax_amount,0)) as ttl_gross_amt,
					ifnull(if(b.description is null , 'UN-BRANDED' ,b.description),'UN-BRANDED') as brand_desc , s.brand_id as brand_id, $sub_q as filter_val
					from pos p
					left join pos_items pi on pi.pos_id = p.id and pi.branch_id=p.branch_id and pi.date=p.date and pi.counter_id=p.counter_id
					left join sku_items si on si.id = pi.sku_item_id
					left join sku s on s.id = si.sku_id
					left join brand b on b.id = s.brand_id
					left join pos_finalized pf on pf.branch_id=p.branch_id and pf.date=p.date
					where ".join(" and ", $filter)."
					having ".join(" and ", $having)."
					order by ttl_gross_amt DESC
		");
//	print $sql;	
		while($r=$con->sql_fetchassoc($q1)){
			$this->ret['brand_sales'][$r['brand_id']]['brand_desc'] = $r['brand_desc'];
			$this->ret['brand_sales'][$r['brand_id']]['brand_id'] = $r['brand_id'];
			$this->ret['brand_sales'][$r['brand_id']]['total'] += round($r['ttl_gross_amt'],2);
		}
		$con->sql_freeresult($q1);
	
		$count = $key = 0;
		if($this->ret['brand_sales']){
			usort($this->ret['brand_sales'], array($this,"reverse_sort"));

			for($i=0; $i<=count($this->ret['brand_sales']); $i++){
				if($i>=9){
					$key=9; // always 9 since it is the last
					$this->ret['brand_sales'][$key]['brand_desc'] = "Others";
					$this->ret['brand_sales'][$key]['brand_id'] = "Others";
					if($i != $key){
						$this->ret['brand_sales'][$key]['total'] += $this->ret['brand_sales'][$i]['total'] ; 
					}
				}
			}
		// set minimum/maximum for y-axis
			$totalarr = array();
			foreach ($this->ret['brand_sales'] as $key => $value)   //take only 'total' into new array for selecting min/max value later
				$totalarr[] = $value['total'];
			
			if(min($totalarr) >=0)        //smallest data is positive /all positive
				$this->ret['min_value']=0;
			elseif (min($totalarr) <=0 && max($totalarr)<=0){       // ALL data is negative
				$this->ret['max_value']=0;
			//	$this->ret['min_value']= min($totalarr) ;    
			}
		}
		

/*		print "<pre>";
		print_r ($this->ret);
		print "</pre>";*/
		$this->ret['ok'] = 1;
		print json_encode($this->ret);
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
$m = new MembershipOverviewGeneral('Membership Composition');
?>