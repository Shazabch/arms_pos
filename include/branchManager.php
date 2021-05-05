<?php
/*
9/28/2018 11:14 AM Andy
- Enhanced branchManager function getBranchInfo to able to accept branch code.

12/9/2020 7:55 PM Shane
- Enhanced getBranchGroupList to return with "with_no_group" option.
- Added function getBranchGroupId
*/
class branchManager{
	// public variable
	
	// private variable

	function __construct(){
		
	}

	// getter
	// function to get branches list
	// return array branchesList
	public function getBranchesList($params = array()){
		global $con;

		$filter = array();
		if(isset($params['active']))	$filter[] = "b.active=".mi($params['active']);

		if($filter)	$str_filter = 'where '.join(' and ', $filter);

		$branchesList = array();
		$q1 = $con->sql_query("select b.id,b.code,b.description from branch b $str_filter order by sequence, code");
	
		while($r = $con->sql_fetchassoc($q1)){
			$branchesList[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		return $branchesList;
	}

	// function to get branch group
	// return array branchGroupList
	public function getBranchGroupList($with_no_group = false){
		global $con;

		$branchGroupList = array();

		// load header
		$q1 = $con->sql_query("select * from branch_group order by description");
		while($r = $con->sql_fetchassoc($q1)){
            $branchGroupList['group'][$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		// load item			
		$q2 = $con->sql_query("select bgi.*,branch.code,branch.description 
			from branch_group_items bgi left join branch on bgi.branch_id=branch.id 
			where branch.active=1 order by branch.sequence, branch.code");
		while($r = $con->sql_fetchassoc($q2)){
			$branchGroupList['group'][$r['branch_group_id']]['itemList'][$r['branch_id']] = $r;
			$branchGroupList['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con->sql_freeresult($q2);
		
		if($with_no_group){
			//Other Group - Branch without Branch Group
			$rs = $con->sql_query("select 0 as branch_group_id,branch.id as branch_id,branch.code,branch.description 
			from branch left join branch_group_items bgi on branch.id=bgi.branch_id
			where bgi.branch_group_id is null and branch.active=1 order by branch.sequence, branch.code");
			while($r = $con->sql_fetchassoc($rs)){
				$branchGroupList['group'][$r['branch_group_id']]['itemList'][$r['branch_id']] = $r;
				$branchGroupList['have_no_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con->sql_freeresult($rs);

			if(isset($branchGroupList['group'][0])){
				$branchGroupList['group'][0]['id'] = 0;
				$branchGroupList['group'][0]['code'] = '#OG#';
				$branchGroupList['group'][0]['description'] = 'Other Group';
			}
		}

		return $branchGroupList;
	}

	// function to get all branch id in a branch group
	// return array $bidList
	function getBranchIDListOfBranchGroup($branchGroupID){
		$bidList = array();
		$branchGroupID = mi($branchGroupID);
		if($branchGroupID > 0){
			$branchGroupList = $this->getBranchGroupList();
			if(isset($branchGroupList['group'][$branchGroupID]['itemList'])){
				$bidList = array_keys($branchGroupList['group'][$branchGroupID]['itemList']);
			}
		}

		return $bidList;
	}

	// function to check the branch id string and return what branch id selected
	// return array bidList
	function checkAndReturnBranchIDList($strBranchID){
		$strBranchID = trim($strBranchID);

		$bidList = array();
		if(preg_match("/^bg,/", $strBranchID)){
			// got branch group
			$branchGroupID = mi(str_replace("bg,", "", $strBranchID));
			if($branchGroupID > 0){
				$bidList = $this->getBranchIDListOfBranchGroup($branchGroupID);
			}
		}else{
			// single branch
			$bid = mi($strBranchID);
			if($bid > 0){
				$bidList[] = $bid;
			}
		}

		return $bidList;
	}

	// function to get branch info
	// return array branch
	public function getBranchInfo($bid, $bcode = ''){
		global $con;

		$bid = mi($bid);
		$str_filter = '';
		if($bid > 0)	$str_filter = "b.id=$bid";	// Filter by Branch ID
		if(!$str_filter && $bcode)	$str_filter = "b.code=".ms($bcode);	// Filter by Branch Code
		
		if(!$str_filter)	return false;
		
		$con->sql_query("select b.* from branch b where $str_filter");
		$branchInfo = $con->sql_fetchassoc();
		$con->sql_freeresult();

		return $branchInfo;
	}

	// function to get branch group info
	// return array branchGroup
	public function getBranchGroupInfo($bgid){
		global $con;

		$con->sql_query("select bg.* from branch_group bg where bg.id=".mi($bgid));
		$branchGroupInfo = $con->sql_fetchassoc();
		$con->sql_freeresult();

		return $branchGroupInfo;	
	}

	// function to get branch_group_id by branch_id
	// return integer branch_group_id, 0 if branch dont have group
	function getBranchGroupId($bid){
		global $con;

		$con->sql_query("select * from branch_group_items");
		while($r=$con->sql_fetchrow()){
			$branch_group_list_by_branch[$r['branch_id']] = $r['branch_group_id'];
		}
		$con->sql_freeresult();
		
		return ($branch_group_list_by_branch[$bid]?$branch_group_list_by_branch[$bid]:0);
	}
}
?>
