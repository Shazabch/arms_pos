<?php
/*
5/29/2018 11:50AM HockLee
- new form: shipper

8/15/2018 2:40 PM Andy
- Increase maintenance version checking to 356.

8/27/2018 4:00PM HockLee
- Bugs fixed: toggle_status_driver().
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!$config['enable_reorder_integration'])	js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MST_TRANSPORTER_v2')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_TRANSPORTER_v2', BRANCH_CODE), "/index.php");

$maintenance->check(356);

class MST_TRANSPORTER_v2 extends Module{
    function __construct($title){
        global $con, $smarty, $sessioninfo;

        parent::__construct($title);
	}

	function _default(){
		$this->display();
	}
	
	// transporter
	function open(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select * from transporter where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		// get transporter type
		$type = array();
		$q_type = $con->sql_query("select id as 'type_id', name, active from transporter_type order by name");
		while($d_type = $con->sql_fetchassoc($q_type)){
			$type[$d_type['type_id']]['name'] = $d_type['name'];
			$type[$d_type['type_id']]['active'] = $d_type['active'];
		}
		$con->sql_freeresult($q_type);

		$smarty->assign('type', $type);
		$smarty->display('masterfile_shipper.open.tpl');
	}
	
	function save(){
        global $con, $smarty, $sessioninfo;

        $upd = array();
        $id = mi($_REQUEST['id']);

        $upd['type_id'] = $_REQUEST['type'];
	    $upd['code'] = trim($_REQUEST['code']);
	    $upd['company_name'] = trim($_REQUEST['company_name']);
	    $upd['address'] = trim($_REQUEST['address']);
	    $upd['phone_1'] = trim($_REQUEST['phone_1']);
	    $upd['phone_2'] = trim($_REQUEST['phone_2']);
	    $upd['fax'] = trim($_REQUEST['fax']);
	    $upd['contact_person'] = trim($_REQUEST['contact_person']);
	    $upd['contact_email'] =trim($_REQUEST['contact_email']);
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for code
        $con->sql_query("select count(*) from transporter where code = ".ms($upd['code'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Transporter Code '$upd[code]' already in used.");
		
        if($id > 0){
			$con->sql_query("update transporter set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter ID #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter ".mysql_insert_by_field($upd));
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Transporter '$upd[code]' added");
		}
		print "OK";
	}
	
	function reload_table($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select t.id, t.code, t.company_name, tt.name as 'type', t.contact_person, t.phone_1, t.contact_email, t.added, t.last_update, t.active 
        	from transporter t 
        	left join transporter_type tt on tt.id =t.type_id 
        	order by t.last_update desc");
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper.table.tpl');
	}
	
	function toggle_status(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter ID #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter set ".mysql_update_by_field($upd)." where id=$id");
        $this->reload_table();
	}

	// vehicle
	function open_vehicle(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select * from transporter_vehicle where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		// get transporter
		$transporter = array();
		$q_transporter = $con->sql_query("select id as 'transporter_id', code, active from transporter order by code");
		while($d_transporter = $con->sql_fetchassoc($q_transporter)){
			$transporter[$d_transporter['transporter_id']]['code'] = $d_transporter['code'];
			$transporter[$d_transporter['transporter_id']]['active'] = $d_transporter['active'];
		}
		$con->sql_freeresult($q_transporter);

		// get vehicle type
		$type = array();
		$q_type = $con->sql_query("select id as 'type_id', name, active from transporter_vehicle_type order by name");
		while($d_type = $con->sql_fetchassoc($q_type)){
			$type[$d_type['type_id']]['name'] = $d_type['name'];
			$type[$d_type['type_id']]['active'] = $d_type['active'];
		}
		$con->sql_freeresult($q_type);

		// get vehicle brand
		$brand = array();
		$q_brand = $con->sql_query("select id as 'brand_id', name, active from transporter_vehicle_brand order by name");
		while($d_brand = $con->sql_fetchassoc($q_brand)){
			$brand[$d_brand['brand_id']]['name'] = $d_brand['name'];
			$brand[$d_brand['brand_id']]['active'] = $d_brand['active'];
		}
		$con->sql_freeresult($q_brand);

		// get route to be assigned
		$route = array();
		$q_route = $con->sql_query("select id as 'route_id', name, active from transporter_route order by name");
		while($d_route = $con->sql_fetchassoc($q_route)){
			$route[$d_route['route_id']]['name'] = $d_route['name'];
			$route[$d_route['route_id']]['active'] = $d_route['active'];
		}
		$con->sql_freeresult($q_route);

		// get vehicle status
		$status = array();
		$q_status = $con->sql_query("select id as 'status_id', name, active from transporter_vehicle_status order by name");
		while($d_status = $con->sql_fetchassoc($q_status)){
			$status[$d_status['status_id']]['name'] = $d_status['name'];
			$status[$d_status['status_id']]['active'] = $d_status['active'];
		}
		$con->sql_freeresult($q_status);

		$smarty->assign('transporter', $transporter);
		$smarty->assign('type', $type);
		$smarty->assign('brand', $brand);
		$smarty->assign('route', $route);
		$smarty->assign('status', $status);
		$smarty->display('masterfile_shipper_vehicle.open.tpl');
	}

	function save_vehicle(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);
        $upd['plate_no'] = trim($_REQUEST['plate_no']);
	    $upd['transporter_id'] = $_REQUEST['transporter'];
	    $upd['type_id'] = $_REQUEST['type'];
	    $upd['brand_id'] = $_REQUEST['brand'];
	    $upd['route_id'] = $_REQUEST['route'];
	    $upd['max_load'] = trim($_REQUEST['max_load']);
	    $upd['status_id'] = $_REQUEST['status'];
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for code
        $con->sql_query("select count(*) from transporter_vehicle where plate_no = ".ms($upd['plate_no'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Transporter Plate No. '$upd[plate_no]' already in used.");
		
        if($id > 0){
			$con->sql_query("update transporter_vehicle set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter vehicle ID #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_vehicle ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Transporter Vehicle '$upd[plate_no]' added");
		}
		print "OK";
	}

	function reload_table_vehicle($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select v.id, v.plate_no, t.code as 'transporter', vt.name as 'type', vb.name as 'brand', r.name as 'route', v.max_load, if(isnull(vs.name), '-', vs.name) as 'status', v.added, v.last_update, v.active 
        	from transporter_vehicle v 
        	left join transporter t on t.id = v.transporter_id 
        	left join transporter_vehicle_type vt on vt.id = v.type_id 
        	left join transporter_vehicle_brand vb on vb.id = v.brand_id 
        	left join transporter_route r on r.id = v.route_id 
        	left join transporter_vehicle_status vs on vs.id = v.status_id 
        	order by v.last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_vehicle.table.tpl');
	}
	
	function toggle_status_vehicle(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter ID #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_vehicle set ".mysql_update_by_field($upd)." where id=$id");
        $this->reload_table_vehicle();
	}

	// driver
	function open_driver(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select * from transporter_driver where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		// get vehicle
		$vehicle = array();
		$q_vehicle = $con->sql_query("select id as 'vehicle_id', plate_no, active from transporter_vehicle order by plate_no");
		while($d_vehicle = $con->sql_fetchassoc($q_vehicle)){
			$vehicle[$d_vehicle['vehicle_id']]['plate_no'] = $d_vehicle['plate_no'];
			$vehicle[$d_vehicle['vehicle_id']]['active'] = $d_vehicle['active'];
		}
		$con->sql_freeresult($q_vehicle);		

		$smarty->assign('vehicle', $vehicle);
		$smarty->display('masterfile_shipper_driver.open.tpl');
	}
	function save_driver(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);        

	    $upd['name'] = trim($_REQUEST['name']);
	    $upd['ic_no'] = trim($_REQUEST['ic_no']);
	    $upd['address'] = trim($_REQUEST['address']);
	    $upd['phone_1'] = trim($_REQUEST['phone_1']);
	    $upd['phone_2'] = trim($_REQUEST['phone_2']);
	    $upd['vehicle_id'] = $_REQUEST['vehicle'];
	        if($_REQUEST['vehicle'] != 0){
	        	$upd['assigned'] = 1;
	        }else{
	        	$upd['assigned'] = 0;
	        }
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for code
        $con->sql_query("select count(*) from transporter_driver where ic_no = ".ms($upd['ic_no'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Same Transporter Driver IC No. '$upd[ic_no]' found in the system.");
		
        if($id > 0){
			$con->sql_query("update transporter_driver set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter driver ID #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_driver ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Transporter Driver '$upd[name]' '$upd[ic_no]' added");
		}
		print "OK";
	}

	function reload_table_driver($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select td.id, td.name, td.ic_no, td.address, td.phone_1, if(isnull(tv.plate_no), '-', tv.plate_no) as 'plate_no', if(td.assigned = 1, 'yes', 'no') as 'assigned', td.added, td.last_update, td.active 
        	from transporter_driver td 
        	left join transporter_vehicle tv on tv.id = td.vehicle_id 
        	order by td.last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_driver.table.tpl');
	}
	
	function toggle_status_driver(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter ID #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_driver set ".mysql_update_by_field($upd)." where id=$id");
        $this->reload_table_driver();
	}

	// route area
	function open_route_area(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select ta.id, ta.route_id, ta.area_id, tr.name as 'route_name', a.name as 'area', ta.sequence, ta.active, ta.added, ta.last_update 
	    	from transporter_route_area ta 
	    	left join transporter_area a on a.id = ta.area_id 
	    	left join transporter_route tr on tr.id = ta.route_id 
	    	where ta.id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
			$smarty->assign('show', 1);
		}

		if(!$id){
			$smarty->assign('new_sequence', 1);
		}

		// get route
		$route = array();
		$q_route = $con->sql_query("select id as 'route_id', name from transporter_route where active = 1 order by name");
		while($d_route = $con->sql_fetchassoc($q_route)){
			$route[$d_route['route_id']] = $d_route['name'];
		}
		$con->sql_freeresult($q_route);

		// get area
		$area = array();
		$q_area = $con->sql_query("select id as 'area_id', name from transporter_area where active = 1 order by name");
		while($d_area = $con->sql_fetchassoc($q_area)){
			$area[$d_area['area_id']] = $d_area['name'];
		}
		$con->sql_freeresult($q_area);

		// get area sequence
		$sequence = array();
		$q_sequence = $con->sql_query("select route_id, sequence from transporter_route_area where active = 1 order by route_id, sequence asc");
		while($d_sequence = $con->sql_fetchassoc($q_sequence)){
			$sequence[$d_sequence['route_id']][] = $d_sequence['sequence'];
		}
		$con->sql_freeresult($q_sequence);

		$smarty->assign('route', $route);
		$smarty->assign('area', $area);
		$smarty->assign('sequence', $sequence);
		$smarty->display('masterfile_shipper_route_area.open.tpl');
	}

	function save_route_area(){
        global $con, $smarty, $sessioninfo;

        $upd = array();
        $id = mi($_REQUEST['id']);
	    $upd['route_id'] = $_REQUEST['route'];
	    $upd['area_id'] = $_REQUEST['area'];
	    $original_route_id = $_REQUEST['original_route_id'];
	    $original_sequence = $_REQUEST['original_sequence'];
	    $upd['sequence'] = $_REQUEST['sequence'];
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';        
		
        if($id > 0){
        	$q_exchange_id = $con->sql_query("select id from transporter_route_area where route_id = $original_route_id and sequence = $upd[sequence]");
        	$exchange_id = $con->sql_fetchassoc($q_exchange_id);

        	// change sequence
        	if(!$exchange_id['id']){
        		// change sequence itself
        		$con->sql_query("update transporter_route_area set user_id = $sessioninfo[id], sequence = $upd[sequence], last_update = CURRENT_TIMESTAMP 
					where id = $id");
        		log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter Route ID #$id sequence updated");        		
        	}else{
        		// exchange sequence in same route
        		$con->sql_query("update transporter_route_area set user_id = $sessioninfo[id], sequence = $original_sequence, last_update = CURRENT_TIMESTAMP 
					where id = $exchange_id[id]");
				$con->sql_query("update transporter_route_area set user_id = $sessioninfo[id], sequence = $upd[sequence], last_update = CURRENT_TIMESTAMP 
					where id = $id");
				log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter Route ID #$id and #$exchange_id[id] sequence updated");
        	}
		}else{
            // checking for same route same area
	        $con->sql_query("select count(*) from transporter_route_area where route_id = $upd[route_id] and area_id = $upd[area_id] and id <> $id");
	        if($con->sql_fetchfield(0)) die("Same Route and Area found in the system.");

	        // checking for same route same sequence
	        $con->sql_query("select count(*) from transporter_route_area where route_id = $upd[route_id] and sequence = $upd[sequence] and id <> $id");
	        if($con->sql_fetchfield(0)) die("Same Route and Sequence found in the system.");
	        
            $upd['user_id'] = $sessioninfo['id'];
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_route_area ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Transporter Route $id added");
		}
		print "OK";
	}

	function reload_table_route_area($sql_only = false){
	    global $con, $smarty;
	    
	    $con->sql_query("select tra.id, tr.name as 'route_name', ta.name as 'area', tra.sequence, tra.active, tra.added, tra.last_update 
	    	from transporter_route_area tra 
	    	left join transporter_area ta on ta.id = tra.area_id 
	    	left join transporter_route tr on tr.id = tra.route_id 
	    	order by tr.name, tra.sequence asc");
	    $smarty->assign('table', $con->sql_fetchrowset());
	    if(!$sql_only)  $smarty->display('masterfile_shipper_route_area.table.tpl');
	}
	
	function toggle_status_route_area(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter Route Area ID #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_route_area set ".mysql_update_by_field($upd)." where id=$id");
        $this->reload_table_route_area();
	}

	// route
	function open_route(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select id, name from transporter_route where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		$smarty->display('masterfile_shipper_route.open.tpl');
	}

	function save_route(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);        

	    $upd['name'] = trim($_REQUEST['name']);	    
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for same route name
        $con->sql_query("select count(*) from transporter_route where name = ".ms($upd['name'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Same Route Name '$upd[name]' found in the system.");
		
        if($id > 0){
			$con->sql_query("update transporter_route set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter Route Name #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_route ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Transporter Route Name '$upd[name]' added");
		}
		print "OK";
	}

	function reload_table_route($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select id, name, active, added, last_update 
        	from transporter_route 
        	order by name asc, last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_route.table.tpl');
	}
	
	function toggle_status_route(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter Route Name #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_route set ".mysql_update_by_field($upd)." where id = $id");
        $this->reload_table_route();
	}

	// transporter type
	function open_type(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select id, name from transporter_type where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		$smarty->display('masterfile_shipper_type.open.tpl');
	}

	function save_type(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);        

	    $upd['name'] = trim($_REQUEST['name']);	    
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for same route name
        $con->sql_query("select count(*) from transporter_type where name = ".ms($upd['name'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Same Type Name '$upd[name]' found in the system.");
		
        if($id > 0){
			$con->sql_query("update transporter_type set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter Type Name #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_type ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Transporter Type '$upd[name]' added");
		}
		print "OK";
	}

	function reload_table_type($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select id, name, active, added, last_update 
        	from transporter_type 
        	order by name asc, last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_type.table.tpl');
	}
	
	function toggle_status_type(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter Type Name #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_type set ".mysql_update_by_field($upd)." where id = $id");
        $this->reload_table_type();
	}

	// transporter vehicle brand
	function open_brand(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select id, name from transporter_vehicle_brand where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		$smarty->display('masterfile_shipper_brand.open.tpl');
	}

	function save_brand(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);        

	    $upd['name'] = trim($_REQUEST['name']);	    
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for same brand name
        $con->sql_query("select count(*) from transporter_vehicle_brand where name = ".ms($upd['name'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Same Brand Name '$upd[name]' found in the system.");
		
        if($id > 0){
			$con->sql_query("update transporter_vehicle_brand set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Vehicle Brand Name #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_vehicle_brand ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Vehicle Brand Name '$upd[name]' added");
		}
		print "OK";
	}

	function reload_table_brand($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select id, name, active, added, last_update 
        	from transporter_vehicle_brand 
        	order by name asc, last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_brand.table.tpl');
	}
	
	function toggle_status_brand(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Vehicle Brand Name #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_vehicle_brand set ".mysql_update_by_field($upd)." where id = $id");
        $this->reload_table_brand();
	}

	// transporter vehicle status
	function open_status(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select id, name from transporter_vehicle_status where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		$smarty->display('masterfile_shipper_status.open.tpl');
	}

	function save_status(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);        

	    $upd['name'] = trim($_REQUEST['name']);	    
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for same status
        $con->sql_query("select count(*) from transporter_vehicle_status where name = ".ms($upd['name'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Same Status '$upd[name]' found in the system.");
		
        if($id > 0){
			$con->sql_query("update transporter_vehicle_status set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Vehicle Status #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_vehicle_status ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Vehicle Status '$upd[name]' added");
		}
		print "OK";
	}

	function reload_table_status($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select id, name, active, added, last_update 
        	from transporter_vehicle_status 
        	order by name asc, last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_status.table.tpl');
	}
	
	function toggle_status_status(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Vehicle Status #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_vehicle_status set ".mysql_update_by_field($upd)." where id = $id");
        $this->reload_table_status();
	}

	// transporter vehicle type
	function open_vehicle_type(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select id, name from transporter_vehicle_type where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		$smarty->display('masterfile_shipper_vehicle_type.open.tpl');
	}

	function save_vehicle_type(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);        

	    $upd['name'] = trim($_REQUEST['name']);	    
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
        
        // checking for same vehicle type
        $con->sql_query("select count(*) from transporter_vehicle_type where name = ".ms($upd['name'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Same Vehicle Type '$upd[name]' found in the system.");
		
        if($id > 0){
			$con->sql_query("update transporter_vehicle_type set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Vehicle Type #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_vehicle_type ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Vehicle Type '$upd[name]' added");
		}
		print "OK";
	}

	function reload_table_vehicle_type($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select id, name, active, added, last_update 
        	from transporter_vehicle_type 
        	order by name asc, last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_vehicle_type.table.tpl');
	}
	
	function toggle_status_vehicle_type(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Vehicle Type #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_vehicle_type set ".mysql_update_by_field($upd)." where id = $id");
        $this->reload_table_vehicle_type();
	}

	// area/region
	function open_area(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select id, name from transporter_area where id = $id");
			$smarty->assign('form', $con->sql_fetchrow());
		}

		$smarty->display('masterfile_shipper_area.open.tpl');
	}

	function save_area(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);        

	    $upd['name'] = trim($_REQUEST['name']);	    
	    $upd['active'] = 1;
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['user_id'] = $sessioninfo['id'];
        
        // checking for same area
        $con->sql_query("select count(*) from transporter_area where name = ".ms($upd['name'])." and id <> $id and active = 1");
        if($con->sql_fetchfield(0)) die("Same Area Name '$upd[name]' found in the system.");
		
        if($id > 0){
			$con->sql_query("update transporter_area set ".mysql_update_by_field($upd)." where id = $id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Area Name #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into transporter_area ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Area Name '$upd[name]' added");
		}
		print "OK";
	}

	function reload_table_area($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select id, name, active, added, last_update 
        	from transporter_area 
        	order by name asc, last_update desc");        
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_shipper_area.table.tpl');
	}
	
	function toggle_status_area(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Area Name #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update transporter_area set ".mysql_update_by_field($upd)." where id = $id");
        $this->reload_table_area();
	}
	
	function transporter(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table(true);
		$smarty->assign('PAGE_TITLE', 'Transporter');
		$smarty->display('masterfile_shipper.tpl');	
	}
					
	function transporter_vehicle(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_vehicle(true);
		$smarty->assign('PAGE_TITLE', 'Vehicle');
		$smarty->display('masterfile_shipper_vehicle.tpl');	
	}
			
	function transporter_driver(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_driver(true);
		$smarty->assign('PAGE_TITLE', 'Driver');
		$smarty->display('masterfile_shipper_driver.tpl');	
	}

	function transporter_route_area(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_route_area(true);
		$smarty->assign('PAGE_TITLE', 'Route Area');
		$smarty->display('masterfile_shipper_route_area.tpl');	
	}

	function transporter_area(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_area(true);
		$smarty->assign('PAGE_TITLE', 'Area');
		$smarty->display('masterfile_shipper_area.tpl');	
	}
			
	function transporter_route(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_route(true);
		$smarty->assign('PAGE_TITLE', 'Route');
		$smarty->display('masterfile_shipper_route.tpl');	
	}
		
	function transporter_type(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_type(true);
		$smarty->assign('PAGE_TITLE', 'Transporter Type');
		$smarty->display('masterfile_shipper_type.tpl');	
	}
			
	function transporter_vehicle_brand(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_brand(true);
		$smarty->assign('PAGE_TITLE', 'Vehicle Brand');
		$smarty->display('masterfile_shipper_brand.tpl');	
	}
		
	function transporter_vehicle_status(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_status(true);
		$smarty->assign('PAGE_TITLE', 'Vehicle Status');
		$smarty->display('masterfile_shipper_status.tpl');	
	}
	
	function transporter_vehicle_type(){
		global $con, $smarty, $sessioninfo;
		
		$this->reload_table_vehicle_type(true);
		$smarty->assign('PAGE_TITLE', 'Vehicle Type');
		$smarty->display('masterfile_shipper_vehicle_type.tpl');	
	}
			
}

$MST_TRANSPORTER_v2 = new MST_TRANSPORTER_v2('Transporter v2 (Shipping)');
?>