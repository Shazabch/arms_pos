<?php
class SOP_SETTINGS
{
	var $settings = array();    // an array to hold all settings

	function __construct(){
	    global $sessioninfo;

		$this->get();   // load all settings
		if(BRANCH_CODE =='HQ'|| ($_GET['force_maintenance']&&$sessioninfo['level']>=9999))	$this->run();
	}

	function get(){
		global $con;

		// create table if cannot found, set the default as 0 and do all the updates below
		$con->sql_query("create table if not exists ".DATABASE_NAME.".sys_setting(
		                    setting_code char(20) primary key,
		                    value char(20)
						 )");
        // first time execute, insert the version 0
        $con->sql_query("insert into ".DATABASE_NAME.".sys_setting (setting_code, value) values ('version', 0)", false, false);

		// select version from database and return to the executor
		$con->sql_query("select * from ".DATABASE_NAME.".sys_setting");
		$this->settings = array();
		while($r = $con->sql_fetchrow()){
		    if(is_numeric($r['value'])) $r['value'] = mf($r['value']);  // make float if value is numeric, so when use to comparison won't come surprised result
            $this->settings[$r['setting_code']] = $r['value'];  // store into array
		}
		$con->sql_freeresult();
	}

	function update($setting_code, $val){
		global $con;

		//if(!$settings_array)    $this->display_error('System failed to update settings file.');

		$filter = array();
		//$filter[] = "value<".mi($this->get_setting('version'));
		if($setting_code){
            $filter[] = "setting_code=".ms($setting_code);
            // version update: can only update if version is lesser
            if($setting_code == 'version') $filter[] = "value<".mf($val);
		}  
		if($filter)	$filter = "where ".join(' and ', $filter);
		$filter = '';
		
		// update settings
		$con->sql_query_false("update ".DATABASE_NAME.".sys_setting set value=".ms($val)." $filter", true);
		$this->get();   // reload the settings
	}
	
	function get_setting($code){
		return $this->settings[$code];
	}

	function check_version($ver){
		global $smarty;

		// if found is the same version, do nothing and back to its own module
		if ($this->get_setting('version') >= $ver) return;

		$this->display_error("<h1>Please update SOP Maintenance script, required version $ver, current version ".mf($this->get_setting('version'))."</h1>");
	}

	function display_error($msg){
    	global $smarty;
    	
    	$smarty->display('header.tpl');
		print $msg;
		$smarty->display('footer.tpl');
		exit;
	}
	
	function update_version($ver){
		$this->update('version', $ver);
	}
	
	function run()
	{
		global $con, $config;
		
		if($this->get_setting('version') < 1){
			$this->update_version(1);
		}

		/*
		    $con->sql_query("create table if not exists ".DATABASE_NAME.".approval_flow like approval_flow");
			$con->sql_query("create table if not exists ".DATABASE_NAME.".approval_history like approval_history");
			$con->sql_query("create table if not exists ".DATABASE_NAME.".approval_history_items like approval_history_items");
			$con->sql_query("create table if not exists ".DATABASE_NAME.".branch_approval_history like branch_approval_history");
			$con->sql_query("create table if not exists ".DATABASE_NAME.".branch_approval_history_items like branch_approval_history_items");
		
		    $con->sql_query("create table if not exists ".DATABASE_NAME.".marketing_plan (
				id int primary key auto_increment,
				year int not null default 0 unique,
				title char(100),
				date_from date,
				date_to date,
				user_id int,
				remark text,
				approval_history_id int,
				active tinyint not null default 1,
				status tinyint not null default 0,
				approved tinyint not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				last_update_by int,
				calendar_color char(7),
				index last_update(last_update), index active_n_status_n_approved (active, status, approved),
				index calendar_color(calendar_color)
			)",false, false);
			
			$con->sql_query("create table if not exists ".DATABASE_NAME.".marketing_plan_promotion(
			    id int auto_increment,
                marketing_plan_id int,
                title char(100),
                for_branch_id_list text,
                date_from date,
				date_to date,
				created_by_user_id int,
				description text,
				branch_own_info text,
				active int not null default 1,
				added timestamp default 0,
				last_update timestamp default 0,
				calendar_color char(7),
				primary key(id),
				index marketing_plan_id(marketing_plan_id),
				index active (active),
				index calendar_color(calendar_color)
			)", false, false);

			$con->sql_query("create table if not exists ".DATABASE_NAME.".marketing_plan_promotion_activity(
			    branch_id int,
			    id int auto_increment,
			    promotion_plan_id int,
                level tinyint not null default 0,
                root_id int not null,
                title char(100),
                date_from date,
				date_to date,
				owner_user_id int,
				pic_user_id_list text,
				remark text,
				budget double,
				active int not null default 1,
				completed tinyint not null default 0,
				completed_percent double not null default 0,
				tree_str text,
				added timestamp default 0,
				last_update timestamp default 0,
				reference_id char(20) unique,
				primary key(branch_id, id),
				index promo_id_n_branch_id (promotion_plan_id, branch_id),
				index active_n_completed (active, completed),
				index active_n_root_id (active, root_id),
				index (level)
			)", false, false);
			
			$con->sql_query("create table if not exists ".DATABASE_NAME.".marketing_plan_promotion_activity_cache(
                branch_id int,
                activity_id int,
				last_update timestamp default 0,
				primary key(branch_id, activity_id)
			)", false, false);
			
			$con->sql_query("create table if not exists ".DATABASE_NAME.".marketing_plan_promotion_user_activity(
			    id int auto_increment,
			    user_id int not null default 0,
                activity_reference_id char(20),
                added timestamp default 0,
				last_update timestamp default 0,
				primary key(user_id, id),
				unique (activity_reference_id, user_id)
			)", false, false);
			
			$con->sql_query("create table if not exists ".DATABASE_NAME.".reminder(
			    id int auto_increment,
			    user_id int not null default 0,
			    title char(50) not null,
			    remark text,
			    date_from date,
				date_to date,
				ref_task char(120),
				ref_table char(50),
				ref_id char(20),
				ref_info text,
				active tinyint not null default 1,
				added timestamp default 0,
				last_update timestamp default 0,
				primary key (user_id, id),
				index user_id_n_active (user_id, active),
				index user_id_ref_task (user_id, ref_task, ref_id)
			)", false, false);
			
			$con->sql_query("create table if not exists ".DATABASE_NAME.".festival_sheet(
			    year int not null primary key,
			    user_id int,
			    approval_history_id int,
				status tinyint not null default 0,
				approved tinyint not null default 0,
				added timestamp default 0,
				last_update timestamp default 0,
				index status_n_approved (status, approved)
			)", false, false);
			
			$con->sql_query("create table if not exists ".DATABASE_NAME.".festival_date(
			    id int not null primary key auto_increment,
			    year int not null,
			    title char(50) not null,
			    user_id int,
			    active tinyint(1) not null default 1,
				date_from date,
				date_to date,
				calendar_color char(7),
				added timestamp default 0,
				last_update timestamp default 0,
				index year_n_active_n_datefrom_n_dateto (year, active, date_from, date_to),
				unique (year, calendar_color),
			)", false, false);
		*/
	}
}

$SOP_SETTINGS = new SOP_SETTINGS();

?>
