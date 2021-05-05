<?php
/*
10/21/2015 5:19 PM Andy
- Add maintenance version check 279.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module
*/
include("include/common.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

if($config['consignment_modules']){	// this module is not for consignment customer
	header("Location: home.php");
	exit;
}

if (!privilege('CN_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CN_APPROVAL', BRANCH_CODE), "/index.php");	

$maintenance->check(279);

class ARMS_CN_APPROVAL extends Module{
	
	function __construct(){
		global $con, $smarty, $appCore;
		
		parent::__construct($appCore->cnoteManager->moduleName.' Approval');
	}
	
	function _default(){
		$this->load_approval_list();
	    $this->display();
	}
	
	function load_approval_list(){
		global $sessioninfo, $appCore, $smarty;
		
		$data = $appCore->cnoteManager->loadApprovalCNoteList($sessioninfo['branch_id'], $sessioninfo['id']);
		$smarty->assign('cn_list', $data['cnList']);
	}
	
	function ajax_load_cn(){
		global $sessioninfo, $appCore, $smarty, $LANG;
		
		$bid = mi($_REQUEST['branch_id']);
		$cn_id = mi($_REQUEST['id']);
		$params = array();
		$params['loadItems'] = 1;
		$params['user_id'] = $sessioninfo['id'];
		$data = $appCore->cnoteManager->loadCNote($bid, $cn_id, $params);
		
		$ret = array();
		if($data){
			$data['header']['approval_screen'] = 1;
			$smarty->assign('form', $data['header']);
			$smarty->assign('items_list', $data['items_list']);
			
			// load items required data
			$appCore->cnoteManager->loadItemsRequiredData($data['header']['is_under_gst'], $data['items_list']);
			
			$ret['ok'] = 1;
			$ret['html'] = $smarty->fetch('cnote.open.tpl');
		}else{
			$ret['failed_reason'] = $LANG['CNOTE_INVALID_FORM'];
		}	
		
		print json_encode($ret);
	}
	
	function ajax_save_cn_approval(){
		global $sessioninfo, $appCore, $smarty, $LANG;
		
		$form = $_REQUEST;
		$type = trim($form['type']);
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		
		$ret = array();
		
		switch($type){
			case 'approve':
				$data = $appCore->cnoteManager->approveCNote($form['branch_id'], $form['id'], $params);
				break;
			case 'reject':
				$params['reason'] = $form['reason'];
				$data = $appCore->cnoteManager->rejectCNote($form['branch_id'], $form['id'], $params);
				break;
			case 'cancel':
				$params['reason'] = $form['reason'];
				$data = $appCore->cnoteManager->cancelCNote($form['branch_id'], $form['id'], $params);
				break;
			default:
				$data['err'] = 'Invalid Action';
				break;
		}
		
		if($data['ok']){
			$ret['ok'] = 1;
		}else{
			$ret['failed_reason'] = $data['err'];
		}
		print json_encode($ret);
	}
}

$ARMS_CN_APPROVAL = new ARMS_CN_APPROVAL();
?>