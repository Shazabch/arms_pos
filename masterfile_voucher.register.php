<?
/*
11/26/2010 5:09:54 PM Alex
- created by me
4/15/2011 6:33:14 PM Alex
- move allow interbranch from register to activate
- add checking for 2 user register at same time
4/21/2011 11:12:44 AM Alex
- clear all number of data after success generate 
6/15/2011 3:17:09 PM Alex
- fix voucher generate float value bugs
6/23/2011 6:29:43 PM Alex
- fix message output voucher value bugs

6/24/2011 4:56:10 PM Andy
- Make all branch default sort by sequence, code.

12/09/2015 4:00 PM Qiu Ying
- get voucher value which set by user

01/16/2016 9:48 AM Kee Kee
- Fixed get wrong maximum voucher code from database

3/9/2017 3:35 PM Justin
- Enhanced to check voucher code which will show error message if using auto generate voucher code and it is exceeded 7 digits.

4/19/2017 10:24 AM Khausalya 
- Enhanced changes from RM to use config setting. 

10/14/2019 5:36 PM William
- Fixed php error show when no code generated.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_VOUCHER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER', BRANCH_CODE), "/index.php");
if (!privilege('MST_VOUCHER_REGISTER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER_REGISTER', BRANCH_CODE), "/index.php");

include("masterfile_voucher.include.php");
//ONLY HQ CAN REGISTER VOUCHER

class Voucher_Register extends Module{

	function __construct($title){
		global $con, $smarty, $config;

		$con->sql_query('select * from branch where active=1 order by sequence,code');
		$branches=$con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('branches',$branches);
		
		$con->sql_query('select * from mst_voucher_setup');
		$voucher=$con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('voucher',$voucher);
		
		$this->voucher_max_length = 7; // currently only accept 7 digits

 		parent::__construct($title);
	}

	function _default(){
	    $this->display();
	    exit;
	}
	
	function register_form(){
		global $con,$sessioninfo,$config,$smarty,$LANG;

		$form=$_REQUEST;
		$codes=$this->validate_data();  

		$time_stamp=date("Y-m-d H:i:s");

		//top ladder table
		$ins['branch_id']=$form['branch_id'];

        $ins['added']=$time_stamp;
		$ins['last_update']=$time_stamp;
		$ins['create_user_id']=$sessioninfo['id'];

		//=========set batch no============
	    $con->sql_query("select max(batch_no) as max_batch from mst_voucher_batch");
        $batch=$con->sql_fetchrow();
        $ins['batch_no'] = $batch['max_batch']+1;
        //============end set==============
 
		//====================set begining and end of loop for code===================
		if ($config['voucher_auto_generate']){						
		    $con->sql_query("select max(LPAD(code,".mi($this->voucher_max_length).",0)) as max_code from mst_voucher");			
			$vi=$con->sql_fetchrow();
			$con->sql_freeresult();
			if (!$vi['max_code'])   $loop_start=1;
			else $loop_start=$vi['max_code']+1;
			
			$loop_end=0;
			
			foreach ($form['no_code'] as $voucher_value => $qty){
				
			    if (!$qty) continue;
				$loop[$voucher_value]['start']=$loop_end ? ($loop_end+1) : $loop_start;
				$loop[$voucher_value]['end']=($loop_end ? ($loop_end+1): $loop_start) + intval($qty) - 1;

				$loop_end = $loop[$voucher_value]['end'];
			}
		}else{
		    foreach ($codes as $voucher_value => $from_to){
		        //for code checking
		        $from_to['from']=str_pad($from_to['from'],$this->voucher_max_length,"0",STR_PAD_LEFT);
		        $from_to['to']=str_pad($from_to['to'],$this->voucher_max_length,"0",STR_PAD_LEFT);

			    $con->sql_query("select * from mst_voucher where code between ".$from_to['from']." and ".$from_to['to']);

				if ($con->sql_numrows()>0){
					$err[] = sprintf($LANG["VOU_CODE_EXIST"], $voucher_value/100);
				}else{
				    $loop[$voucher_value]['start'] = intval($from_to['from']);
	                $loop[$voucher_value]['end'] = intval($from_to['to']);
				}
				$con->sql_freeresult();
			}
		}
		//===================end set=======================

		
		//check duplicate voucher code if 2 user create at sames time
		if ($loop){
			foreach ($loop as $voucher_value => $start_end){				
			    $con->sql_query("select * from mst_voucher where code between ".$start_end['start']." and ".$start_end['end']);
			    if ($con->sql_numrows()>0){
					$err[]=$LANG["VOU_USE_SAME_TIME"];
				}
				$con->sql_freeresult();
			}
		}

		if ($err)	$smarty->assign('err',$err);
		else{
			$con->sql_query("insert into mst_voucher_batch ".mysql_insert_by_field($ins));

			$ins['allow_interbranch'] = 1;    //default open for all

			foreach ($loop as $voucher_value => $start_end){
				$voucher_value=$voucher_value/100;
				$ins['voucher_value'] = $voucher_value;

				for ($i=$start_end['start'];$i<=$start_end['end'];$i++){
	                $ins['code']=STR_PAD($i,$this->voucher_max_length,"0",STR_PAD_LEFT);
			        $con->sql_query("insert into mst_voucher ".mysql_insert_by_field($ins));
				}

		    	log_br($sessioninfo['id'], 'VOUCHER',$sessioninfo['branch_id'], "Batch no: $ins[batch_no], Generate voucher from:$loop_start to:$loop_end , Branch:".get_branch_code($ins['branch_id']).", Value: " . $config["arms_currency"]["symbol"] .$ins['voucher_value']);

				$start_code=str_pad($start_end['start'],$this->voucher_max_length,'0',STR_PAD_LEFT);
				$end_code=str_pad($start_end['end'],$this->voucher_max_length,'0',STR_PAD_LEFT);

				$suc[]=sprintf($LANG['VOU_CODE_CREATED'],$ins['batch_no'],$start_code,$end_code,$voucher_value);
			}
			
			$smarty->assign('suc',$suc);
		}

        $this->display();
	}
	
	function validate_data(){
		global $config,$smarty,$LANG,$con;
		
		$voucher_limit = $config['voucher_generate_limit'];
		$form=$_REQUEST;
		
		if ($config['voucher_auto_generate']){
			if (max($_REQUEST['no_code']) <= 0) $err[] = $LANG["VOU_ERR_NO_CODE"];
			else{
			    foreach($_REQUEST['no_code'] as $voucher_value => $qty){
					if ($qty > $voucher_limit) $err[] = sprintf($LANG["VOU_OVER_LIMIT"],$voucher_value/100);
				}
			}
			
			// voucher number auto generate validation
			$q1 = $con->sql_query("select max(LPAD(code,".mi($this->voucher_max_length).",0)) as max_code from mst_voucher");	
			$vi=$con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			
			if (!$vi['max_code']) $loop_start=1;
			else $loop_start = $vi['max_code']+1;
			
			$loop_end=0;
			
			foreach($form['no_code'] as $voucher_value => $qty){
			    if (!$qty) continue;
				$loop[$voucher_value]['start']=$loop_end ? ($loop_end+1) : $loop_start;
				$loop[$voucher_value]['end']=($loop_end ? ($loop_end+1): $loop_start) + intval($qty) - 1;

				$loop_end = $loop[$voucher_value]['end'];
			}
			if($loop){
				foreach ($loop as $voucher_value => $start_end){
					$error=false;
					for ($i=$start_end['start'];$i<=$start_end['end'];$i++){
						// check if the voucher number is duplicated from system
						$q1 = $con->sql_query("select * from mst_voucher where code = ".ms($i));
						$voucher_existed = $con->sql_numrows($q1);
						$con->sql_freeresult($q1);
						
						if($voucher_existed > 0){ // found it is duplicated from database
							$err[]=sprintf($LANG['VOU_CODE_DUPLICATE'], $voucher_value/100);
							$error=true;
							break;
						}elseif(strlen($i) > $this->voucher_max_length){ // found out the voucher code is exceeded more than 7 digits
							$err[]=$LANG['VOU_CODE_EXCEEDED_MAX_NUMBER'];
							$error=true;
							break;
						}
					}
					if($error) break;
				}
			}
		}else{
		    // change to int for easy checking

		    foreach ($_REQUEST['from_code'] as $voucher_value => $from_code){
			    $duplicate=false;
		        if (!$from_code && !$_REQUEST['to_code'][$voucher_value])   continue;
		    
				if ((!$from_code && $_REQUEST['to_code'][$voucher_value]) || ($from_code && !$_REQUEST['to_code'][$voucher_value]) || $from_code<=0 || $_REQUEST['to_code'][$voucher_value]<=0){
					$err[] = sprintf($LANG["VOU_CODE_INVALID"], $voucher_value/100);
					continue;
				}
				
	            if ($from_code > $_REQUEST['to_code'][$voucher_value]){
					$err[] = sprintf($LANG["VOU_CODE_FROM_TO"],$voucher_value/100);
					continue;
				}
				
				//check limit
	            $code_diff = $_REQUEST['to_code'][$voucher_value] - $from_code;
	            if ($code_diff >= $voucher_limit)   $err[] = sprintf($LANG["VOU_OVER_LIMIT"],$voucher_value/100);
	            
				//check duplicate code
				foreach ($_REQUEST['to_code'] as $vv => $to_code){
					if ($voucher_value == $vv)  continue;
			        if (!$to_code && !$_REQUEST['from_code'][$vv])   continue;
					if ((!$to_code && $_REQUEST['from_code'][$vv]) || ($to_code && !$_REQUEST['from_code'][$vv]) || $to_code<=0 || $_REQUEST['from_code'][$vv]<=0) continue;
		            if ($to_code < $_REQUEST['from_code'][$vv]) continue;

					if ($_REQUEST['from_code'][$vv] <= $from_code && $from_code <= $to_code){
						$err[]=sprintf($LANG['VOU_CODE_DUPLICATE'], $voucher_value/100);
						$duplicate=true;
						break;
					}
				}
				
				if ($duplicate) continue;
				else{
				    $codes[$voucher_value]['from']=$from_code;
				    $codes[$voucher_value]['to']=$_REQUEST['to_code'][$voucher_value];
				}
            }
            
       		if (!$codes)	$err[]=$LANG['VOU_ERR_NO_CODE'];
		}
		
		if ($err){
			$smarty->assign('err',$err);
		    $this->display();
		    exit;
		}else{
			return $codes;
		}
	}
}

function return_voucher_value($in){
	return $in*100;
}


$Voucher = new Voucher_Register("Voucher Registration");

?>