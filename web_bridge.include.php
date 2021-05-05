<?php
$maintenance->check(116);

function load_ap_settings(){
	global $con,$sessioninfo;
	
	$ap_settings = array();
	$con->sql_query("select * from web_bridge_ap_settings");
	while($r = $con->sql_fetchassoc()){
		$name = trim($r['name']);
	    $v = trim($r['value']);
		$ap_settings[$name] = $r;
		
	    switch($r['type']){
			case 'radio':
			    $ap_settings[$name]['value'] = mi($v);
			    if(!$ap_settings[$name]['value'])	unset($ap_settings[$name]);
			    break;
			case 'str':
			    $ap_settings[$name]['value'] = trim($v);
			    break;
			case 'select':
				$ap_settings[$name]['value'] = trim($v);
				if(!$ap_settings[$name]['value'])	unset($ap_settings[$name]);
			    break;
		}
	}
	$con->sql_freeresult();
	
	return $ap_settings;
}

function load_ar_settings(){
	global $con,$sessioninfo;
	
	$settings = array();
	$con->sql_query("select * from web_bridge_ar_settings");
	while($r = $con->sql_fetchassoc()){
		$name = trim($r['name']);
	    $v = trim($r['value']);
		$settings[$name] = $r;
		
	    switch($r['type']){
			case 'radio':
			    $settings[$name]['value'] = mi($v);
			    if(!$settings[$name]['value'])	unset($settings[$name]);
			    break;
			case 'str':
			    $settings[$name]['value'] = trim($v);
			    break;
			case 'select':
				$settings[$name]['value'] = trim($v);
				if(!$settings[$name]['value'])	unset($settings[$name]);
			    break;
		}
	}
	$con->sql_freeresult();
	
	return $settings;
}
	
function load_cc_settings(){
	global $con,$sessioninfo;
	
	$settings = array();
	$con->sql_query("select * from web_bridge_cc_settings");
	while($r = $con->sql_fetchassoc()){
		$name = trim($r['name']);
	    $v = trim($r['value']);
		$settings[$name] = $r;
		
	    switch($r['type']){
			case 'radio':
			    $settings[$name]['value'] = mi($v);
			    if(!$settings[$name]['value'])	unset($settings[$name]);
			    break;
			case 'str':
			    $settings[$name]['value'] = trim($v);
			    break;
			case 'select':
				$settings[$name]['value'] = trim($v);
				if(!$settings[$name]['value'])	unset($settings[$name]);
			    break;
		}
	}
	$con->sql_freeresult();
	
	return $settings;
}
?>
