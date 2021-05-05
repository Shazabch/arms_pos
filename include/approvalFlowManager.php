<?php
class approvalFlowManager{
	// public var
	
	// private var
	
	function __construct(){
		global $smarty, $con, $appCore;

	}
	
	// function to check whether a module have approval flow
	// return boolean
	public function isModuleHaveApprovalFlow($bid, $moduleType, $params = array()){
		global $con;
		$filter = array();
		$filter[] = "active=1";
		
		switch($moduleType){
			case 'CN':
				$filter[] = "branch_id=".mi($bid);
				$filter[] = "type=".ms($moduleType);
				break;
			default:
				// no match approval flow
				return false;
				break;
		}
		
		$filter = "where ".join(' and ', $filter);
		$con->sql_query("select id from approval_flow $filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $tmp ? true : false;
	}
}
?>
