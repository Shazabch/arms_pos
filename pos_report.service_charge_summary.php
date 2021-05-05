<?php
/*
2/25/2020 9:59 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Service_Charge_Summary extends Report
{
    function __construct($title){
		global $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
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
		
        switch($this->filter['group_by']){
            case 'daily':
                $sql = "select date, (amount - service_charges_gst_amt) as 'total_sales', 
				(service_charges - service_charges_gst_amt) as 'total_sc', 
				service_charges_gst_amt as 'gst_on_sc' , service_charges as 'sc_included_gst'
				from pos 
				where cancel_status=0 and date >= ". ms($this->filter['date_from']) . " and date <= ".ms($this->filter["date_to"]." 23:59:59") ." 
				and branch_id = ". mi($this->filter['branch_id']) ."
				order by date";
            break;
            case 'monthly':
                $sql = "select monthname(date) as 'date', sum(amount - service_charges_gst_amt) as 'total_sales', 
				sum(service_charges - service_charges_gst_amt) as 'total_sc', 
				sum(service_charges_gst_amt) as 'gst_on_sc' , 
				sum(service_charges) as 'sc_included_gst'
				from pos 
				where cancel_status=0 and date >= ". ms($this->filter['date_from']) . " and date <= ".ms($this->filter["date_to"]." 23:59:59") ." 
				and branch_id = ". mi($this->filter['branch_id']) ."
				group by month(date) 
				order by month(date)";
            break;
        }
		
		$report_header[] = "Branch: ".$branches[$this->filter['branch_id']]['code'];
		$report_title = join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header);
		$q1 = $con_multi->sql_query($sql) or die(mysql_error());
		$this->table = $con_multi->sql_fetchrowset($q1);
		$con_multi->sql_freeresult($q1);
		$smarty->assign('report_title',$report_title);
		$smarty->assign('table',$this->table);
	}

	function process_form()
	{
        global $smarty,$con;
		
        $form=$_REQUEST;
        $date_from=$form['date_from'];
        $date_to=$form['date_to'];

        $smarty->assign("form", $form);
	    $this->filter = $form;
	}

	function default_values(){
        global $smarty,$con;

        $form['date_to']=date('Y-m-d');
        $form['date_from']=date('Y-m-d',strtotime("- 3 month"));

        $smarty->assign("form", $form);
	}
}

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$SCSummary = new Service_Charge_Summary('Service Charge Summary');
//$con_multi->close_connection();
?>
