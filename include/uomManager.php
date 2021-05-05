<?php
/*
11/7/2017 4:18 PM Justin
- Enhanced to have a function to select default UOM for EACH.

11/15/2018 4:46 PM Andy
- Added new uomManager function "getUOMbyCode".
*/

class uomManager{
	// public var
	
	// private var
	private $uomForEACH = array();
	
	function __construct(){
		global $smarty, $con, $appCore;

		
	}
	
	// function to get uom list
	// return array $uomList
	public function getUOMList($params = array()){
		global $con, $appCore;
		
		$filter = array();
		if(isset($params['active']))	$filter[] = "uom.active=".mi($params['active']);
		$filter[] = "uom.fraction>0";
		$filter = "where ".join(' and ', $filter);
		
		$uomList = array();
		
		$q1 = $con->sql_query("select * from uom $filter order by code");
		while($r = $con->sql_fetchassoc($q1)){
			$uomList[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $uomList;
	}
	
	// function to get UOM ID for EACH
	// return array
	public function getUOMForEach(){
		global $con;
		
		if($this->uomForEACH)	return $this->uomForEACH;
		
		$con->sql_query("select id,code,fraction from uom where active=1 and code='EACH' and fraction=1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp){
			$this->uomForEACH = $tmp;
			return $this->uomForEACH;
		}
		
		$con->sql_query("select id,code,fraction from uom where active=1 and fraction=1 order by id limit 1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp && mi($tmp['id'])>0){
			$this->uomForEACH = $tmp;
			return $this->uomForEACH;
		}
		
		$tmp = array();
		$tmp['id'] = 1;
		$tmp['code'] = "EACH";
		$tmp['fraction'] = 1;
		$this->uomForEACH = $tmp;
		
		return $this->uomForEACH;
	}
	
	// function to get UOM ID by UOM Code
	// return array
	public function getUOMbyCode($uomCode){
		global $con;
		
		$uomCode = trim($uomCode);
		if(!$uomCode)	return array();
		
		$con->sql_query("select * from uom where code=".ms($uomCode)." and active=1");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}
}
?>
