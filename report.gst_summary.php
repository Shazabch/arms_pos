<?php
/*
12/14/2015 5:21 PM DingRen
- change date to use grr receive date
- remove to check grn status

06/23/2016 11:00 Edwin
- Enhanced on show total amount of the report

12/19/2017 4:47 PM Andy
- Fixed date filtering bug for POS data.
- Removed Membership Redemption from Sales.
- Enhanced to get Credit Note.

1/2/2018 2:20 PM Justin
- Bug fixed on Credit Sales DO does not appear on report.

2/26/2020 9:27 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_GST', BRANCH_CODE), "/index.php");

class GSTSummary extends Report
{
	var $input_tax=array();
	var $output_tax=array();

    function __construct($title){
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

        $q1 = $con_multi->sql_query("select * from gst where active=1 order by id");

		while($g=$con_multi->sql_fetchassoc($q1)){
			if($g['type']=='purchase') $this->input_tax[$g['id']]=$g;
			elseif($g['type']=='supply') $this->output_tax[$g['id']]=$g;
		}
		$con_multi->sql_freeresult($q1);

        $smarty->assign("input_tax",$this->input_tax);
        $smarty->assign("output_tax",$this->output_tax);

		parent::__construct($title);
	}

    function generate_report()
	{

		global $con, $smarty, $con_multi;

		$con_multi->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con_multi->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();

        switch($this->filter['type']){
            case 'Purchase':
                $this->purchase_gst_summary();
				$tax_code=($this->filter['input_tax']==0)?"All":$this->input_tax[$this->filter['input_tax']]['code'];
            break;
            case 'Sales':
                $this->sales_gst_summary();
				$tax_code=($this->filter['output_tax']==0)?"All":$this->output_tax[$this->filter['output_tax']]['code'];
            break;
        }

		$report_header[] = "Branch: ".$branches[$this->filter['branch_id']]['code'];

		$report_title = join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header);
        $report_title .= "&nbsp;&nbsp;&nbsp;&nbsp;Type: ".ucwords($this->filter['type']).
						 "&nbsp;&nbsp;&nbsp;&nbsp;Tax Code: ".$tax_code.
						 "&nbsp;&nbsp;&nbsp;&nbsp;Date From: ".$this->filter['date_from'].
						 "&nbsp;&nbsp;&nbsp;&nbsp;To: ".$this->filter['date_to'];
		
        $smarty->assign('report_title', $report_title);
		$smarty->assign('total', $this->total);
		$smarty->assign('table', $this->table);
	}

	function process_form()
	{
        global $smarty,$con;

        $form=$_REQUEST;
        $date_from=$form['date_from'];
        $date_to=$form['date_to'];

        $smarty->assign("form", $form);

        if($date_to>date("Y-m-d",strtotime($date_from." + 1 month "))){
            //$this->err[] = "You cannot select more than 3 month.";
        }

        if ($this->err)
		{
            
		    $smarty->assign("err", $this->err);
			$this->display();
			exit;
		}

	    $this->filter = $form;
	}

	function default_values(){
        global $smarty,$con;

        $form['date_to']=date('Y-m-d');
        $form['date_from']=date('Y-m-d',strtotime("- 1 month"));

        $smarty->assign("form", $form);
	}

    private function purchase_gst_summary(){
        global $smarty,$con,$con_multi;

        $where=array();
		$where[] = "p.branch_id=".mi($this->filter['branch_id']);
		$where[] = "p.rcv_date between ".ms($this->filter["date_from"])." and ".ms($this->filter["date_to"]);
        $where[] = "p.active = 1";
		$where[] = "p.is_under_gst=1";

		//if($this->filter['input_tax']>0) $where[] = "p.gst_id=".mi($this->filter['input_tax']);

        if($where) $cond = "where ".join(" and ",$where);

        $sql = "select * from grr p
		$cond order by p.rcv_date";

		//echo $sql;
        $q1=$con_multi->sql_query($sql) or die(sql_error());

        $this->table = $this->total = array();
        while ($r = $con_multi->sql_fetchassoc($q1)){

            //$rInvoice=$this->get_invoice($r);
            //if(!$rInvoice) continue;

            $sql="select * from grr_items
				where branch_id = ".mi($r['branch_id'])."
				and grr_id = ".ms($r['id'])."
				and type in ('INVOICE','DO','OTHER')";

            $q2=$con_multi->sql_query($sql);
            while($row=$con_multi->sql_fetchassoc($q2)){
                if($this->filter['input_tax']>0 && $this->filter['input_tax']!=$row['gst_id']) continue;

				$key=$r['rcv_date'].$row['gst_code'].$row['gst_rate'];

                if(!isset($this->table[$key])){
                    $this->table[$key]=array();
                    $this->table[$key]['date']=$r['rcv_date'];
                    $this->table[$key]['code']=$row['gst_code'];
                    $this->table[$key]['rate']=$row['gst_rate'];
                    $this->table[$key]['gst_amount']=0;
                    $this->table[$key]['amount']=0;
					$this->table[$key]['items']['GRR']=array();
					$this->table[$key]['items']['GRR']['gst_amount']=0;
                    $this->table[$key]['items']['GRR']['amount']=0;
                }

                $this->table[$key]['gst_amount']+=$row["gst_amount"];
                $this->table[$key]['amount']+=$row["amount"]-$row["gst_amount"];

				$this->table[$key]['items']['GRR']['gst_amount']+=$row["gst_amount"];
                $this->table[$key]['items']['GRR']['amount']+=$row["amount"]-$row["gst_amount"];
				
				$this->total['tax_code'][$row['gst_code']]['amount'] += $row["amount"] - $row["gst_amount"]; 
				$this->total['tax_code'][$row['gst_code']]['gst_amount'] += $row["gst_amount"];
				$this->total['total']['amount'] += $row["amount"] - $row["gst_amount"]; 
				$this->total['total']['gst_amount'] += $row["gst_amount"];
            }
            $con_multi->sql_freeresult($q2);
		}
		$con_multi->sql_freeresult($q1);
		
		if($this->total['tax_code'])	ksort($this->total['tax_code']);
    }

    private function get_invoice($result){
        global $con,$con_multi;
        $sql = "select grn.* from grr left join grn on grr.id=grn.grr_id and grr.branch_id=grn.branch_id
        where grr.id = ".mi($result['id'])."
        and grr.branch_id=".ms($result['branch_id'])." limit 1";

        $ret=$con_multi->sql_query($sql);
        $invoice = $con_multi->sql_fetchrow($ret);
        $con_multi->sql_freeresult($ret);

        if($invoice['active'] && $invoice['status'] && $invoice['approved'])
          return $invoice;
        else
          return 0;
	}

    private function sales_gst_summary(){
		global $smarty,$con,$con_multi;

		$tax_code=($this->filter['output_tax']==0)?"All":$this->output_tax[$this->filter['output_tax']]['code'];

		$this->table = $this->total = array();

		$where=array();
		$where[] = "p.branch_id = ".mi($this->filter['branch_id']);
	    $where[] = "p.date between ".ms($this->filter["date_from"])." and ".ms($this->filter["date_to"]);
		$where[] = "cancel_status = 0";

		if($where) $cond = "where ".join(" and ",$where);

		$sql = "select * from pos p $cond order by pos_time, receipt_no";

		$q1=$con_multi->sql_query($sql) or die(sql_error());

		while ($r = $con_multi->sql_fetchassoc($q1)){

			if(isset($r['pos_more_info'])) $r['pos_more_info']=unserialize($r['pos_more_info']);

			if(isset($r['deposit']) && $r['deposit']){
				$sql = "select * from pos_deposit
					where branch_id = ".mi($r['branch_id'])."
					and counter_id = ".mi($r['counter_id'])."
					and pos_id = ".mi($r['id'])."
					and date = ".ms($r['date']);

				$q2=$con_multi->sql_query($sql) or die(sql_error());
				while($row = $con_multi->sql_fetchassoc($q2)){
					$row['gst_info']=unserialize($row['gst_info']);

					if($tax_code!="All" && $tax_code!=$row['gst_info']['code']) continue;

					if($row['gst_info']['code']=="") continue;

					$key=$r['date'].$row['gst_info']['code'].mf($row['gst_info']['rate']);

					if(!isset($this->table[$key])){
						$this->table[$key]=array();
						$this->table[$key]['date']=$r['date'];
						$this->table[$key]['code']=$row['gst_info']['code'];
						$this->table[$key]['rate']=$row['gst_info']['rate'];
						$this->table[$key]['gst_amount']=0;
						$this->table[$key]['amount']=0;
						$this->table[$key]['items']=array();
					}

					if(!isset($this->table[$key]['items']['DEPOSIT'])){
						$this->table[$key]['items']['DEPOSIT']=array();
						$this->table[$key]['items']['DEPOSIT']['gst_amount']=0;
						$this->table[$key]['items']['DEPOSIT']['amount']=0;
					}

					$row['deposit_amount']=$row['deposit_amount']-$row["gst_amount"];

					$this->table[$key]['gst_amount']+=$row["gst_amount"];
					$this->table[$key]['amount']+=round($row['deposit_amount'], 2);

					$this->table[$key]['items']['DEPOSIT']['gst_amount']+=$row["gst_amount"];
					$this->table[$key]['items']['DEPOSIT']['amount']+=round($row['deposit_amount'], 2);
					
					$this->total['tax_code'][$row['gst_info']['code']]['amount'] += round($row['deposit_amount'], 2); 
					$this->total['tax_code'][$row['gst_info']['code']]['gst_amount'] += $row["gst_amount"];
					$this->total['total']['amount'] += round($row['deposit_amount'], 2); 
					$this->total['total']['gst_amount'] += $row["gst_amount"];
				}
				$con_multi->sql_freeresult($q2);
			}
			else{
				$sql="select tax_code, tax_rate, sum(before_tax_price) as amount, sum(tax_amount) as gst_amount
				from pos_items
				where branch_id = ".mi($r['branch_id'])."
				and counter_id = ".mi($r['counter_id'])."
				and pos_id = ".mi($r['id'])."
				and date = ".ms($r['date']);

				if($tax_code!="All") $sql.=" and tax_code=".ms($tax_code);

				$sql.="	group by tax_code, tax_rate";
				
				$q2=$con_multi->sql_query($sql) or die(sql_error());
				while($row = $con_multi->sql_fetchassoc($q2)){
					if($row['tax_code']=="") continue;

					$key=$r['date'].$row['tax_code'].mf($row['tax_rate']);

					if(!isset($this->table[$key])){
						$this->table[$key]=array();
						$this->table[$key]['date']=$r['date'];
						$this->table[$key]['code']=$row['tax_code'];
						$this->table[$key]['rate']=$row['tax_rate'];
						$this->table[$key]['gst_amount']=0;
						$this->table[$key]['amount']=0;
						$this->table[$key]['items']=array();
					}

					if(!isset($this->table[$key]['items']['POS'])){
						$this->table[$key]['items']['POS']=array();
						$this->table[$key]['items']['POS']['gst_amount']=0;
						$this->table[$key]['items']['POS']['amount']=0;
					}

					$this->table[$key]['gst_amount']+=$row["gst_amount"];
					$this->table[$key]['amount']+=$row["amount"];

					$this->table[$key]['items']['POS']['gst_amount']+=$row["gst_amount"];
					$this->table[$key]['items']['POS']['amount']+=$row["amount"];
					
					$this->total['tax_code'][$row['tax_code']]['amount'] += $row["amount"]; 
					$this->total['tax_code'][$row['tax_code']]['gst_amount'] += $row["gst_amount"];
					$this->total['total']['amount'] += $row["amount"]; 
					$this->total['total']['gst_amount'] += $row["gst_amount"];
				}
				$con_multi->sql_freeresult($q2);
			}

			if(isset($r['pos_more_info']['deposit'])){
				foreach($r['pos_more_info']['deposit'] as $deposit){
					if(isset($deposit['gst_info'])) $deposit['gst_info']=unserialize($deposit['gst_info']);

					if($tax_code!="All" && $tax_code!=$deposit['gst_info']['code']) continue;

					if($deposit['gst_info']['code']=="") continue;

					$key=$r['date'].$deposit['gst_info']['code'].mf($deposit['gst_info']['rate']);

					if(!isset($this->table[$key])){
						$this->table[$key]=array();
						$this->table[$key]['date']=$r['date'];
						$this->table[$key]['code']=$deposit['gst_info']['code'];
						$this->table[$key]['rate']=$deposit['gst_info']['rate'];
						$this->table[$key]['gst_amount']=0;
						$this->table[$key]['amount']=0;
						$this->table[$key]['items']=array();
					}

					if(!isset($this->table[$key]['items']['DEPOSIT'])){
						$this->table[$key]['items']['DEPOSIT']=array();
						$this->table[$key]['items']['DEPOSIT']['gst_amount']=0;
						$this->table[$key]['items']['DEPOSIT']['amount']=0;
					}

					$gst_amount=0-$deposit['gst_amount'];
					$amount=0-round($deposit['amount']-$deposit['gst_amount'], 2);

					$this->table[$key]['gst_amount']+=$gst_amount;
					$this->table[$key]['amount']+=$amount;

					$this->table[$key]['items']['DEPOSIT']['gst_amount']+=$gst_amount;
					$this->table[$key]['items']['DEPOSIT']['amount']+=$amount;
					
					$this->total['tax_code'][$deposit['gst_info']['code']]['amount'] += $amount; 
					$this->total['tax_code'][$deposit['gst_info']['code']]['gst_amount'] += $gst_amount;
					$this->total['total']['amount'] += $amount; 
					$this->total['total']['gst_amount'] += $gst_amount;
				}
			}

			if(isset($r['service_charges']) && $r['service_charges']>0){
				$taxCode = $r['pos_more_info']['service_charges']['sc_gst_detail']['code'];
				$taxRate = mf($r['pos_more_info']['service_charges']['sc_gst_detail']['rate']);

				if($tax_code!="All" && $tax_code==$taxCode){
					$key=$r['date'].$taxCode.$taxRate;

					if(!isset($this->table[$key])){
						$this->table[$key]=array();
						$this->table[$key]['date']=$r['date'];
						$this->table[$key]['code']=$taxCode;
						$this->table[$key]['rate']=$taxRate;
						$this->table[$key]['gst_amount']=0;
						$this->table[$key]['amount']=0;
						$this->table[$key]['items']=array();
					}

					if(!isset($this->table[$key]['items']['SERVICE_CHARGES'])){
						$this->table[$key]['items']['SERVICE_CHARGES']=array();
						$this->table[$key]['items']['SERVICE_CHARGES']['gst_amount']=0;
						$this->table[$key]['items']['SERVICE_CHARGES']['amount']=0;
					}

					$gst_amount=$r['service_charges_gst_amt'];
					$amount=$r['service_charges']-$gst_amount;

					$this->table[$key]['gst_amount']+=$gst_amount;
					$this->table[$key]['amount']+=$amount;

					$this->table[$key]['items']['SERVICE_CHARGES']['gst_amount']+=$gst_amount;
					$this->table[$key]['items']['SERVICE_CHARGES']['amount']+=$amount;
					
					$this->total['tax_code'][$taxCode]['amount'] += $amount; 
					$this->total['tax_code'][$taxCode]['gst_amount'] += $gst_amount;
					$this->total['total']['amount'] += $amount; 
					$this->total['total']['gst_amount'] += $gst_amount;
				}
			}
		}
		$con_multi->sql_freeresult($q1);

		/*$where=array();
		$where[] = "branch_id = ".mi($this->filter['branch_id']);
		$where[] = "date >= ".ms($this->filter["date_from"]);
		$where[] = "date <= ".ms($this->filter["date_to"]." 23:59:59");
		$where[] = "active = 1";
		$where[] = "verified = 1";
		$where[] = "status = 0";

		if($where) $cond = "where ".join(" and ",$where);

		$sql = "select * from membership_redemption $cond order by date";

		$q1=$con->sql_query($sql) or die(sql_error());

		while ($r = $con->sql_fetchassoc($q1)){
			$sql = "select * from membership_redemption_items
              where membership_redemption_id = ".mi($r['id'])."
			  and branch_id = ".mi($r['branch_id']);
			  
			if($tax_code!="All") $sql.=" and gst_code=".ms($tax_code);
			  
			$q2=$con->sql_query($sql) or die(sql_error());
			while($row = $con->sql_fetchassoc($q2)){
				$key=$r['date'].$row['gst_code'].mf($row['gst_rate']);

				if(!isset($table[$key])){
					$table[$key]=array();
					$table[$key]['date']=$r['date'];
					$table[$key]['code']=$row['gst_code'];
					$table[$key]['rate']=$row['gst_rate'];
					$table[$key]['gst_amount']=0;
					$table[$key]['amount']=0;
					$table[$key]['items']=array();
				}

				if(!isset($table[$key]['items']['MEMBERSHIP_REDEMPTION'])){
					$table[$key]['items']['MEMBERSHIP_REDEMPTION']=array();
					$table[$key]['items']['MEMBERSHIP_REDEMPTION']['gst_amount']=0;
					$table[$key]['items']['MEMBERSHIP_REDEMPTION']['amount']=0;
				}

				$gst_amount=$row['line_gst_amt'];
				$amount=$row['line_gross_amt'];

				$table[$key]['gst_amount']+=$gst_amount;
				$table[$key]['amount']+=$amount;

				$table[$key]['items']['MEMBERSHIP_REDEMPTION']['gst_amount']+=$gst_amount;
				$table[$key]['items']['MEMBERSHIP_REDEMPTION']['amount']+=$amount;
				
				$total['tax_code'][$row['gst_code']]['amount'] += $amount; 
				$total['tax_code'][$row['gst_code']]['gst_amount'] += $gst_amount;
				$total['total']['amount'] += $amount; 
				$total['total']['gst_amount'] += $gst_amount;
			}
			$con->sql_freeresult($q2);
		}
		$con->sql_freeresult($q1);*/

		$where=array();
		$where[] = "branch_id = ".mi($this->filter['branch_id']);
		$where[] = "do_type in ('open', 'credit_sales')";
		$where[] = "do_date between ".ms($this->filter["date_from"])." and ".ms($this->filter["date_to"]);
		$where[] = "status = 1";
		$where[] = "active = 1";
		$where[] = "approved = 1";
		$where[] = "checkout = 1";
		$where[] = 'inv_no <> ""';

		if($where) $cond = "where ".join(" and ",$where);

		$sql = "select * from do $cond order by do_date";
		$q1=$con_multi->sql_query($sql) or die(sql_error());

		while ($r = $con_multi->sql_fetchassoc($q1)){
			$sql="select doi.*, rcv_uom.fraction as fraction, do.do_type
				from do_items doi
				left join do on do.id = doi.do_id and do.branch_id = doi.branch_id
				left join uom rcv_uom on doi.uom_id = rcv_uom.id
				where doi.branch_id = ".mi($r['branch_id'])."
				and do_id = ".mi($r['id']);

			if($tax_code!="All") $sql.=" and gst_code=".ms($tax_code);

			$q2=$con_multi->sql_query($sql) or die(sql_error());
			while($row = $con_multi->sql_fetchassoc($q2)){
				$key=$r['do_date'].$row['gst_code'].mf($row['gst_rate']);
				if($row['do_type'] == "open") $do_type = "DO Cash Sales";
				else $do_type = "DO Credit Sales";

				if(!isset($this->table[$key])){
					$this->table[$key]=array();
					$this->table[$key]['date']=$r['do_date'];
					$this->table[$key]['code']=$row['gst_code'];
					$this->table[$key]['rate']=$row['gst_rate'];
					$this->table[$key]['gst_amount']=0;
					$this->table[$key]['amount']=0;
					$this->table[$key]['items']=array();
				}

				if(!isset($this->table[$key]['items'][$do_type])){
					$this->table[$key]['items'][$do_type]=array();
					$this->table[$key]['items'][$do_type]['gst_amount']=0;
					$this->table[$key]['items'][$do_type]['amount']=0;
				}

				$gst_amount=round($row["inv_line_gst_amt2"],2);
				$amount=round(($row["inv_line_gross_amt2"]),2);

				$this->table[$key]['gst_amount']+=$gst_amount;
				$this->table[$key]['amount']+=$amount;

				$this->table[$key]['items'][$do_type]['gst_amount']+=$gst_amount;
				$this->table[$key]['items'][$do_type]['amount']+=$amount;
				
				$this->total['tax_code'][$row['gst_code']]['amount'] += $amount; 
				$this->total['tax_code'][$row['gst_code']]['gst_amount'] += $gst_amount;
				$this->total['total']['amount'] += $amount; 
				$this->total['total']['gst_amount'] += $gst_amount;
			}
			$con_multi->sql_freeresult($q2);
		}
		$con_multi->sql_freeresult($q1);
		
		// Get Credit Note
		$where=array();
		$where[] = "c.branch_id = ".mi($this->filter['branch_id']);
		$where[] = "c.cn_date between ".ms($this->filter["date_from"])." and ".ms($this->filter["date_to"]);
		$where[] = "c.status = 1";
		$where[] = "c.active = 1";
		$where[] = "c.approved = 1";
		if($tax_code!="All") $where[] = "ci.gst_code=".ms($tax_code);
		
		$cond = "where ".join(" and ",$where);
		
		$sql = "select c.cn_date, ci.line_gross_amt2, ci.line_gst_amt2, ci.line_amt2, ci.gst_code, ci.gst_rate
		from cnote c 
		join cnote_items ci on ci.branch_id=c.branch_id and ci.cnote_id=c.id
		$cond";
		$q1=$con_multi->sql_query($sql);
		while ($r = $con_multi->sql_fetchassoc($q1)){
			$key=$r['cn_date'].$r['gst_code'].mf($r['gst_rate']);
			
			if(!isset($this->table[$key])){
				$this->table[$key]=array();
				$this->table[$key]['date']=$r['cn_date'];
				$this->table[$key]['code']=$r['gst_code'];
				$this->table[$key]['rate']=$r['gst_rate'];
				$this->table[$key]['gst_amount']=0;
				$this->table[$key]['amount']=0;
				$this->table[$key]['items']=array();
			}
			
			if(!isset($this->table[$key]['items']['CN'])){
				$this->table[$key]['items']['CN']=array();
				$this->table[$key]['items']['CN']['gst_amount']=0;
				$this->table[$key]['items']['CN']['amount']=0;
			}

			$gst_amount = round($r["line_gst_amt2"],2)*-1;
			$amount = round(($r["line_gross_amt2"]),2)*-1;

			$this->table[$key]['gst_amount']+=$gst_amount;
			$this->table[$key]['amount']+=$amount;

			$this->table[$key]['items']['CN']['gst_amount']+=$gst_amount;
			$this->table[$key]['items']['CN']['amount']+=$amount;
			
			$this->total['tax_code'][$r['gst_code']]['amount'] += $amount; 
			$this->total['tax_code'][$r['gst_code']]['gst_amount'] += $gst_amount;
			$this->total['total']['amount'] += $amount; 
			$this->total['total']['gst_amount'] += $gst_amount;
		}
		$con_multi->sql_freeresult($q1);
		
		if($this->total['tax_code'])	ksort($this->total['tax_code']);
		
    }
}

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$DOSummary = new GSTSummary('GST Summary');
//$con_multi->close_connection();
?>
