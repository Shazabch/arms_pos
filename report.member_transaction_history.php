<?php
/*
2/24/2010 11:04:11 AM Andy
- add print function

4/15/2010 2:03:54 PM Andy
- Add receipt able to filter by SKU

1/25/2011 5:16:39 PM Alex
- change use report server
- add branch name at report title
- fix date bugs

6/24/2011 6:16:20 PM Andy
- Make all branch default sort by sequence, code.

11/19/2012 10:36 AM Andy
- Fix not to group same payment type.

2/18/2020 10:44 AM William
- Enhanced to change $con connection to use $con_multi.
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_MEMBERSHIP', BRANCH_CODE), "/index.php");

class MemberTranHistory extends Report
{
	function run_report($bid_list)
	{
	    global $con_multi, $smarty;
		
	    $filter = array();
	    $filter[] = "pos.branch_id in (".join(',',$bid_list).")";
	    $filter[] = 'pos.date between '.ms($this->date_from).' and '.ms($this->date_to);
		$filter[] = "pos.member_no=".ms($this->member_no);
		$filter[] = "pos.cancel_status=0";
		$filter = "where ".join(' and ', $filter);
	  
	    $sql = "select pos.*,pi.qty,pi.price,pi.discount,si.sku_item_code,si.description as sku_description,si.mcode,cs.network_name
from pos
left join pos_items pi on pi.branch_id=pos.branch_id and pi.counter_id=pos.counter_id and pi.pos_id=pos.id and pi.date=pos.date
left join sku_items si on si.id=pi.sku_item_id
left join counter_settings cs on cs.branch_id=pos.branch_id and cs.id=pos.counter_id
$filter
order by pos.date,pos.counter_id,pos.id";

    //print $sql;
    
    $q_1 = $con_multi->sql_query($sql);
    $counter_info = array();
    $pos = array();
    $pos_items = array();
    $pos_payment = array();
    
    while($r = $con_multi->sql_fetchrow($q_1)){
      if(!$counter_info[$r['branch_id']][$r['counter_id']]){  // store counter settings
        $counter_info[$r['branch_id']][$r['counter_id']]['network_name'] = $r['network_name'];
      }
      
      if(!$pos[$r['branch_id']][$r['date']][$r['counter_id']][$r['id']]){ // store pos list - just use first item 
        $pos[$r['branch_id']][$r['date']][$r['counter_id']][$r['id']] = $r;     
        $total['selling'] += $r['amount'];
        // get pos payment details
        $con_multi->sql_query("select * from pos_payment where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and pos_id=".mi($r['id'])." order by type");
        while($py = $con_multi->sql_fetchrow()){
        	if($py['type'] == 'Rounding'){
        		$pos_payment[$r['branch_id']][$r['date']][$r['counter_id']][$r['id']]['rounding'] = $py;	
        	}else{
        		$pos_payment[$r['branch_id']][$r['date']][$r['counter_id']][$r['id']]['pp'][] = $py;	
        	}
        }
        $con_multi->sql_freeresult();
      }
      
      $pos_items[$r['branch_id']][$r['date']][$r['counter_id']][$r['id']][] = $r;
    }
    $con_multi->sql_freeresult($q_1);
    
    $smarty->assign('counter_info',$counter_info);
    $smarty->assign('pos',$pos);
    $smarty->assign('pos_items',$pos_items);
    $smarty->assign('pos_payment',$pos_payment);
    $smarty->assign('total',$total);
    
    //print_r($pos_payment);
	}

	function generate_report()
	{
		global $con, $smarty, $con_multi;
		
		$branch_group = $this->branch_group;
		$branch_id = $this->branch_id;
		//print_r($_REQUEST);
		$bid_list = array();
		if($branch_id>0){   // selected single branch
            $bid_list[] = $branch_id;
            $branch_name = get_branch_code($branch_id);
		}else{
			if($branch_id<0){   // negative branch id is branch group
                $bgid = abs($branch_id);
				if(!$branch_group['items'][$bgid])    $err[] = "Invalid Branch.";
				else{
					foreach($branch_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$branch_name = $branch_group['header'][$bgid]['code'];
				}
			}else{  // all branches
			  $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
	
	    		while($r = $con_multi->sql_fetchrow()){
	    			$branches[$r['id']] = $r;
	    		}
				$con_multi->sql_freeresult();
    		
				foreach($branches as $b){
          			$bid_list[] = $b['id'];
				}
				$branch_name = "All";
			}
		}
		
		$rpt_title[] = "Branch: $branch_name";
		$rpt_title[] = "Date Sales: from ".$_REQUEST['date_from']." to ".$_REQUEST['date_to'];
		$rpt_title[] = "Membership No: ".$this->member_no." ".$this->member_info['name'];
		$rpt_title[] = "IC NO: ".$this->member_info['nric'];

		$report_title = join('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $rpt_title);

		if($bid_list)  $this->run_report($bid_list);
		
		if($this->filter_sku){  // only show receipt with selected sku
			$this->filter_pos();
            $smarty->assign("group_item", $this->group_item);
            $report_title .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Filter SKU: Yes";
		}
		$smarty->assign('report_title',$report_title);
		
		if($_REQUEST['is_print']){
	      $this->template  = "report.member_transaction_history.print.tpl";
	    }
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		// call parent
		parent::process_form();

		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];

		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest  || strtotime($this->date_to) < strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		$this->member_no = trim($_REQUEST['member_no']);
		$this->branch_id = get_request_branch(true);
		$this->filter_sku = $_REQUEST['filter_sku'];
		
		if(BRANCH_CODE != 'HQ' && !$this->branch_id)  $this->err[] = "Invalid Branch.";
		if(!$this->member_no) $this->err[] = "Please key in Member No.";
		else{ // check member no
	      $con_multi->sql_query("select * from membership where card_no=".ms($this->member_no)." limit 1");

	      $member_info = $con_multi->sql_fetchrow();
		  $con_multi->sql_freeresult();
	      if(!$member_info) $this->err[] = "Invalid Member No.";
	    }
	    if($this->filter_sku){
            $this->group_item = $this->generate_multiple_item_to_group_item();
            if(!$this->group_item)  $this->err[] = "No SKU to filter, please either add one or more sku or un-check SKU Filter";
		}	
        //print_r($this->group_item);
        
		if($this->err){
		    $smarty->assign("group_item", $this->group_item);
		  	$smarty->assign("err", $this->err);
			$this->display();
			exit;
		}
		$this->member_info = $member_info;
		$smarty->assign('member_info',$member_info);
	}	
	
	private function filter_pos(){
	    global $con, $smarty;
	    $sku_item_code_arr = array();
		foreach($this->group_item as $r){
            $sku_item_code_arr[] = trim($r['sku_item_code']);
		}
		
		$pos = $smarty->get_template_vars('pos');
		$pos_items = $smarty->get_template_vars('pos_items');
		$total = $smarty->get_template_vars('total');
		//print_r($total);
		
		if($pos){
			foreach($pos as $bid=>$b){
				foreach($b as $date=>$c){
					foreach($c as $cid=>$p){
						foreach($p as $pid=>$r){
							$item_found = false;
							$items_arr = $pos_items[$bid][$date][$cid][$pid];
							if($items_arr){
								foreach($items_arr as $item){
									if(in_array($item['sku_item_code'], $sku_item_code_arr)){   // selected sku found in this POS
                                        $item_found = true;
                                        break;
									}
								}
							}
							if(!$item_found){   // sku not found in this pos
							    $total['selling'] -= $r['amount'];  // decrease total amt
								unset($pos[$bid][$date][$cid][$pid]);   // delete the POS
								if(!$pos[$bid][$date][$cid])    unset($pos[$bid][$date][$cid]);
								if(!$pos[$bid][$date])  unset($pos[$bid][$date]);
								if(!$pos[$bid]) unset($pos[$bid]);
							}
						}
					}
				}
			}
		}
		//print_r($pos);
		$smarty->assign('pos',$pos);
    	$smarty->assign('pos_items',$pos_items);
    	$smarty->assign('total',$total);
	}

	function default_values()
	{
	  $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
		$_REQUEST['date_to'] = date("Y-m-d");
	}
}
//$con_multi = new mysql_multi();
$report = new MemberTranHistory('Member Transaction History');
//$con_multi->close_connection();
?>
