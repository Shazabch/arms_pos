<?php
/*
1/20/2011 4:42:19 PM Andy
- Add feature to deliver to "open".

7/8/2011 2:39:16 PM Andy
- Add config checking for transporter module.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!$config['enable_consignment_transport_note'])	js_redirect($LANG['NEED_CONFIG'], "/index.php");

include('consignment.include.php');

class CI_TRANSPORT_NOTE extends Module{
    function __construct($title){
        global $con, $smarty, $sessioninfo;

		$this->branches = load_branch();
		$this->branch_group = load_branch_group();
        parent::__construct($title);
	}

	function _default(){
	    //$this->init_table();
	    $this->init_load();
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty;
		
		$con->sql_query("select * from consignment_transporter where active=1 order by code");
		$smarty->assign('transporters', $con->sql_fetchrowset());
	}
	
	private function init_table(){
		global $con;
		
		/*$con->sql_query("create table if not exists consignment_transporter_history(
			name char(50) primary key,
			added timestamp default 0
		)");*/
	}

	function print_transport_note(){
		global $con, $smarty, $config, $sessioninfo;
		
		$form = array();
		$form['bid'] = mi($_REQUEST['branch_id']);
		$form['do_no'] = trim($_REQUEST['do_no']);
		$form['transporter_id'] = mi($_REQUEST['transporter_id']);
		$form['deliver_type'] = trim($_REQUEST['deliver_type']);
		$form['open'] = $_REQUEST['open'];
		//$form['transporter'] = trim($_REQUEST['transporter']);
		$form['carton_s'] = mi($_REQUEST['carton_s']);
		$form['carton_m'] = mi($_REQUEST['carton_m']);
		$form['carton_l'] = mi($_REQUEST['carton_l']);
		$form['carton_xl'] = mi($_REQUEST['carton_xl']);
		
		if($form['deliver_type']=='branch'){
            if(!$form['bid']){
				print "<script>alert('Invalid Branch');</script>";exit;
			}
		}else{
            if(!$form['open']['name']){
				print "<script>alert('Invalid Open Name');</script>";exit;
			}
			if(!$form['open']['address']){
				print "<script>alert('Invalid Open Address');</script>";exit;
			}
		}
		
		if(!$form['do_no']){
            print "<script>alert('Invalid DO No.');</script>";exit;
		}
		if(!$form['transporter_id']){
            die("<script>alert('Invalid Transporter ID.');</script>");
		}else{
			$con->sql_query("select company_name from consignment_transporter where id=".mi($form['transporter_id']));
			$form['transporter'] = $con->sql_fetchfield(0);
		}
		
		if(!$form['transporter']){
            print "<script>alert('Invalid Transporter Name.');</script>";exit;
		}
		if(!$form['carton_s']&&!$form['carton_m']&&!$form['carton_l']&&!$form['carton_xl']){
            print "<script>alert('Invalid Carton.');</script>";exit;
		}
		
		$con->sql_query("select * from consignment_transporter_history where name=".ms($form['transporter']));
		if($con->sql_numrows()<=0){
		    $upd = array();
		    $upd['name'] = $form['transporter'];
		    $upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("replace into consignment_transporter_history ".mysql_insert_by_field($upd));
		}
		
		$smarty->assign('form', $form);
		$smarty->assign('from_branch', $this->branches[$sessioninfo['branch_id']]);
		$smarty->assign('to_branch', $this->branches[$form['bid']]);
		if($config['consignment_transport_note_alt_print_template'])    $smarty->display($config['consignment_transport_note_alt_print_template']);
		else    $smarty->display('consignment.transport_note.print.tpl');
	}
}

$CI_TRANSPORT_NOTE = new CI_TRANSPORT_NOTE('Consignment Transport Note');
?>
