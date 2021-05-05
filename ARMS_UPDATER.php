<?php
/*
1/6/2014 5:16 PM Justin
- Enhanced to have separator.

5/9/2014 11:29 AM Justin
- Enhanced to have list premium & armsgo servers by type.
- Enhanced to have "Retry" button whenever the file is under "connecting more than 10 seconds" and "failed".

7/16/2015 10:08 AM Andy
- Enhanced to get max sequence and max id to compare to get the new arms_file_update id.

10/23/2015 10:30 AM Andy
- Enhanced to make consignment server preset list.

2/21/2018 5:35 PM Andy
- Enhanced to check svn_type and use ARMS_ENCODED_PHP7.
*/

require_once("include/common.php");

set_time_limit(0);
include_once('xtra/server_list.php');

class ARMS_UPDATER extends Module{

	var $status_list = array(0=>'Active', 1=>'Done');
	var $disallow_file_list = array('config.php', 'Thumbs.db');
	var $source_file_folder = '/www/ARMS_ENCODED';
	var $source_file_folder_php7 = '/www/PHP7/ARMS_ENCODED_PHP7';
	var $source_tmp_file_folder = '/tmp';
	var $upload_file_folder = 'ARMS_UPLOAD_FILE';
	var $priority_status = array('connecting'=>1, 'connected'=>2, 'uploading'=>3, 'done'=>4, 'failed'=>4);
	
	function __construct($title){
		global $con, $smarty, $server_list;
		
	
		$smarty->assign('status_list', $this->status_list);
		$smarty->assign('server_list', $server_list);
		
		parent::__construct($title);
	}
	
	function _default(){
		global $sessioninfo, $smarty, $server_list;
		
		if($sessioninfo['u'] == 'andy'){
			//print_r($_SESSION);
		}
		
		// load premium server by type
		$preset_server_list = array();
		if($server_list){
			foreach($server_list as $server_name=>$server){
				if(!$server['armsgo'] && !$server['consignment'] && ($server['type'] == 'hq' || $server['type'] == 'branch' || $server['type'] == 'test')){
					$svn_server = $server['svn_server'];
					if(!$server['svn_server']) $svn_server = "uncategorized";
					$svn_server = strtoupper($svn_server);
					
					$preset_server_list['premium'][$svn_server][$server_name] = $server;
					continue;
				}
				if($server['armsgo'] && ($server['type'] == 'hq' || $server['type'] == 'branch' || $server['type'] == 'test')){
					$svn_server = $server['svn_server'];
					if(!$server['svn_server']) $svn_server = "uncategorized";
					$svn_server = strtoupper($svn_server);
					
					$preset_server_list['armsgo'][$svn_server][$server_name] = $server;
					continue;
				}
				if(!$server['armsgo'] && $server['consignment'] && ($server['type'] == 'hq' || $server['type'] == 'branch' || $server['type'] == 'test')){
					$svn_server = $server['svn_server'];
					if(!$server['svn_server']) $svn_server = "uncategorized";
					$svn_server = strtoupper($svn_server);
					
					$preset_server_list['consignment'][$svn_server][$server_name] = $server;
					continue;
				}
			}
		}
		
		if($preset_server_list['premium']) ksort($preset_server_list['premium']);
		if($preset_server_list['armsgo']) ksort($preset_server_list['armsgo']);
		if($preset_server_list['consignment']) ksort($preset_server_list['consignment']);

		$smarty->assign("preset_server_list", $preset_server_list);
		
		// clear all uploaded file
		$this->clear_old_tgz_file();
		
		// load update file list
		$this->load_update_file_list();
		
		$this->display();
	}
	
	private function clear_old_tgz_file(){
		$expire_time = time()-604800;	// 1 week

		// ARMS_ENCODED
		foreach(glob($this->source_file_folder."/".$this->upload_file_folder."/*.tgz") as $f){
			if(filemtime($f)<$expire_time){
				unlink($f);
			}
		}
		
		// ARMS_ENCODED_PHP7
		foreach(glob($this->source_file_folder_php7."/".$this->upload_file_folder."/*.tgz") as $f){
			if(filemtime($f)<$expire_time){
				unlink($f);
			}
		}
	}
	
	private function load_update_file_list(){
		global $con, $smarty;
		
		$status = mi($_REQUEST['status']);
		$file_pattern = trim($_REQUEST['file_pattern']);
		$receive_date_from = trim($_REQUEST['receive_date_from']);
		$receive_date_to = trim($_REQUEST['receive_date_to']);
		
		$filter = array();
		$filter[] = "f.status=$status";
		if($receive_date_from)	$filter[] = "f.receive_date>=".ms($receive_date_from);
		if($receive_date_to)	$filter[] = "f.receive_date<=".ms($receive_date_to);
		
		$filter = "where ".join(' and ', $filter);
		
		$file_update_list = array();
		$q1 = $con->sql_query("select * from arms_file_update f $filter order by f.sequence desc");
		while($r = $con->sql_fetchassoc($q1)){
			$tmp = $this->load_and_process_list_info($r['id'], $r);
			
			if($file_pattern){	// need filter file pattern
				if(!$tmp['file_list'])	continue;	// no file
				$pass = false;
				foreach($tmp['file_list'] as $file_id => $file_info){
					if(strpos($file_info['filename'], $file_pattern)!==false){
						$pass = true;
					}
				}
				if(!$pass)	continue;
			}
			$tmp['id'] = $r['id'];
			$file_update_list[$r['sequence']] = $tmp;
			
			if($min_seq > $r['sequence']) $min_seq = $r['sequence'];
			if($max_seq < $r['sequence']) $max_seq = $r['sequence'];
		}
		$con->sql_freeresult($q1);
		
		$separator_filters = array();
		if($receive_date_from || $receive_date_to) $separator_filters[] = "sequence between ".mi($min_seq)." and ".mi($max_seq);
		
		if($separator_filters) $separator_filter = "where ".join(" and ", $separator_filters);
		
		// load separator row
		$q1 = $con->sql_query("select * from arms_file_update_separator f $separator_filter order by f.sequence desc");
		while($r = $con->sql_fetchassoc($q1)){
			$r['is_separator'] = 1;
			$file_update_list[$r['sequence']] = $r;
		}
		$con->sql_freeresult($q1);
		
		krsort($file_update_list);
		
		/*print "<pre>";
		print_r($file_update_list);
		print "</pre>";*/
		
		$smarty->assign('file_update_list', $file_update_list);
	}
	
	function ajax_add_update_row(){
		global $con, $smarty;
		
		// get max sequence
		//$con->sql_query("select max(sequence) from arms_file_update");
		//$new_sequence = mi($con->sql_fetchfield(0))+1;
		//$con->sql_freeresult();
		
		// get max ID
		$con->sql_query("select max(sequence) as max_seq, max(id) as max_id from arms_file_update");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$tmp_new_id1 = $tmp['max_seq'] > $tmp['max_id'] ? $tmp['max_seq'] : $tmp['max_id'];
		
		$con->sql_query("select max(sequence) from arms_file_update_separator");
		$tmp_new_id2 = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();

		if($tmp_new_id1 > $tmp_new_id2){
			$new_id = $tmp_new_id1 + 1;
		}else $new_id = $tmp_new_id2 + 1;
		
		$upd = array();
		$upd['id'] = $new_id;
		$upd['receive_date'] = date("Y-m-d");
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['sequence'] = $new_id;
		
		$con->sql_query("insert into arms_file_update ".mysql_insert_by_field($upd));
		//$new_id = $con->sql_nextid();
		
		//$con->sql_query("update arms_file_update set sequence=$new_id where id=$new_id");	// update sequence = id
		
		$list_info = $this->load_and_process_list_info($new_id);
		$smarty->assign('list_info', $list_info);
		$smarty->assign('list_id', $new_id);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('ARMS_UPDATER.list_row.tpl');
		$ret['list_id'] = $new_id;
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	private function load_and_process_list_info($list_id, $list_info = array()){
		global $con;
		
		if(!$list_id)	return false;
		
		if(!$list_info){
			// load list info
			$con->sql_query("select f.* from arms_file_update f where f.id=".mi($list_id));
			$list_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			
		}
		
		if(!isset($list_info['file_list'])){
			// load file list
			$con->sql_query("select * from arms_file_update_items where file_update_id=$list_id order by filename");
			while($r = $con->sql_fetchassoc()){
				$r['more_info'] = unserialize($r['more_info']);
				$list_info['file_list'][$r['id']] = $r;
			}
			$con->sql_freeresult();
		}
		
		return $list_info;
	}
	
	function ajax_save_form(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		$ret = array();
		
		
		if($form['update_list_id_list']){	// check got thing need to update
			$separator_got_sequence_changes = $got_sequence_changes = false;
			
			foreach($form['update_list_id_list'] as $list_id => $update_timestamp){	// loop for each row which need update
				$ret['update_list_id_list'][$list_id] = $update_timestamp;
				
				// select the row first
				$con->sql_query("select * from arms_file_update where id=$list_id");
				$data = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				
				if($form['update_list'][$list_id]['is_separator']){
					// select the row first
					$con->sql_query("select * from arms_file_update_separator where id=$list_id");
					$data = $con->sql_fetchassoc();
					$con->sql_freeresult();
				
					$upd = array();
					$upd['description'] = $form['update_list'][$list_id]['description'];
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					
					// check sequence changes
					if($data['sequence'] != $form['update_list'][$list_id]['sequence']){
						$upd['sequence'] = $form['update_list'][$list_id]['sequence']*-1;	// mark negative first
						$separator_got_sequence_changes = true;
					}

					$con->sql_query("update arms_file_update_separator set ".mysql_update_by_field($upd)." where id=$list_id");
				}else{
					// select the row first
					$con->sql_query("select * from arms_file_update where id=$list_id");
					$data = $con->sql_fetchassoc();
					$con->sql_freeresult();
				
					$upd = array();
					$upd['title'] = $form['update_list'][$list_id]['title'];
					$upd['changes_log'] = $form['update_list'][$list_id]['changes_log'];
					$upd['extras'] = $form['update_list'][$list_id]['extras'];
					$upd['status'] = $form['update_list'][$list_id]['status'];
					$upd['receive_date'] = $form['update_list'][$list_id]['receive_date'];
					$upd['username'] = $form['update_list'][$list_id]['username'];
					$upd['last_update'] = 'CURRENT_TIMESTAMP';
					
					// check sequence changes
					if($data['sequence'] != $form['update_list'][$list_id]['sequence']){
						$upd['sequence'] = $form['update_list'][$list_id]['sequence']*-1;	// mark negative first
						$got_sequence_changes = true;
					}

					$con->sql_query("update arms_file_update set ".mysql_update_by_field($upd)." where id=$list_id");
				}
				
			}
			
			if($got_sequence_changes){
				$con->sql_query("update arms_file_update set sequence=sequence*-1 where sequence<0");
			}
			
			if($separator_got_sequence_changes){
				$con->sql_query("update arms_file_update_separator set sequence=sequence*-1 where sequence<0");
			}
		}
		$ret['ok'] = 1;
		
		//$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_delete_update_row(){
		global $con, $smarty;
		
		$list_id = mi($_REQUEST['list_id']);
		
		if(!$list_id)	die('Invalid Row ID.');
		
		$con->sql_query("delete from arms_file_update where id=$list_id");
		
		$ret = array();
		$ret['ok'] = 1;
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_load_update_file_list(){
		global $con, $smarty;
		
		$list_id = mi($_REQUEST['list_id']);
		
		if(!$list_id)	die('Invalid Row ID.');
		
		// load list info
		$list_info = $this->load_and_process_list_info($list_id);

		$smarty->assign('list_info', $list_info);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('ARMS_UPDATER.file_list_dialog.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_save_update_file_list(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		
		$list_id = mi($form['list_id']);
		if(!$list_id)	die('Invalid ID');
		
		$file_list = array();
		if($form['file_list']){
			foreach(explode("\n", trim($form['file_list'])) as $f){
				if(!$f = trim($f))	continue;	// empty string
				if(in_array($f, $file_list))	continue;	// duplicate filename
				
				$file_list[] = $f;
			}
		}
		
		$err = array();
		
		if($file_list){
			// check file
			foreach($file_list as $f){
				if(!file_exists($f))	$err[] = "<b>$f</b> not exists.";
				elseif(in_array($f, $this->disallow_file_list))	$err[] = "<b>$f</b> is not allow to upload.";
				
			}
		}
				
		if($err){
			print "Error: <ul>";
			foreach($err as $e)	print "<li>$e</li>";
			print "</ul>";
			exit;
		}
		
		if($file_list){
			$curr_file_list = array();
			$con->sql_query("select * from arms_file_update_items where file_update_id=$list_id");
			while($r = $con->sql_fetchassoc()){
				$curr_file_list[$r['filename']] = $r;	
			}
			$con->sql_freeresult();
			
			$drop_file_list = $curr_file_list;
			
			// check file
			foreach($file_list as $f){
				if(isset($drop_file_list[$f])){	// this file exists
					unset($drop_file_list[$f]);
					continue;
				}
				
				// new file
				$upd = array();
				$upd['file_update_id'] = $list_id;
				$upd['filename'] = $f;
				$con->sql_query("insert into arms_file_update_items ".mysql_insert_by_field($upd));
			}
			
			if($drop_file_list){	// got file need delete
				foreach($drop_file_list as $f => $r){
					$con->sql_query("delete from arms_file_update_items where file_update_id=$list_id and filename=".ms($f));	
				}
			}
		}else{	// all files are clear
			$con->sql_query("delete from arms_file_update_items where file_update_id=$list_id");
		}
		
		// load list info
		$list_info = $this->load_and_process_list_info($list_id);
		$smarty->assign('list_id', $list_id);
		$smarty->assign('list_info', $list_info);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('ARMS_UPDATER.list_row.file_table.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_delete_file_row(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		$file_row_id = mi($form['file_row_id']);
		if(!$file_row_id)	die('Invalid file ID.');
		
		$con->sql_query("delete from arms_file_update_items where id=$file_row_id");
		$affected = $con->sql_affectedrows();
		if(!$affected)	die('Delete failed.');
		
		$ret = array();
		$ret['ok'] = 1;
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_validate_submit_files(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		
		//print_r($form);
		if(!$form['update_list'])	die('No Data');
		
		$file_id_list = array();
		$list_id_list = array();
		$file_name_list = array();
		
		foreach($form['update_list'] as $list_id => $list_info){
			if($list_info['file_list']){
				foreach($list_info['file_list'] as $file_id => $file_info){
					if($file_info['need_upload']){	// got checked need upload
						$file_id_list[$file_id] = array();
						$list_id_list[$list_id]['file_id_list'][$file_id] = $file_id;	// store file id list
					}
				}
			}			
		}
		
		if(!$file_id_list)	die('No file to upload.');
		
		$con->sql_query("select * from arms_file_update_items where id in (".join(',', array_keys($file_id_list)).")");
		while($r = $con->sql_fetchassoc()){
			$file_id_list[$r['id']] = $r;
			if(!in_array($r['filename'], $file_name_list))	$file_name_list[] = $r['filename'];
		}
		$con->sql_freeresult();
		
		//print_r($list_id_list);
		
		$ret = array();
		
		// check whether got select partial upload
		$con->sql_query("select file_update_id, count(*) as total_file from arms_file_update_items where file_update_id in (".join(',', array_keys($list_id_list)).") group by file_update_id");
		while($r = $con->sql_fetchassoc()){
			if($r['total_file'] != count($list_id_list[$r['file_update_id']]['file_id_list'])){	// selected upload count not same as list count
				$ret['error']['partial_upload'][$r['file_update_id']]['file_id_list'] = $list_id_list[$r['file_update_id']]['file_id_list'];
			}
		}
		$con->sql_freeresult();
		
		// check whether got upload overlap file to other update	
		$con->sql_query("select upi.* 
from arms_file_update_items upi
join arms_file_update up on up.id=upi.file_update_id
where up.status=0 and upi.filename in (".join(',', array_map('ms', $file_name_list)).")");
		while($r = $con->sql_fetchassoc()){
			if(!isset($list_id_list[$r['file_update_id']])){	// this list not selected to upload
				$ret['error']['partial_upload'][$r['file_update_id']]['file_id_list'][$r['id']] = $r['id'];
			}
		}
		$con->sql_freeresult();
		
		//print_r($ret);
		
		if($ret['error']){
			print json_encode($ret);
			exit;
		}
		
		$ret['ok'] = 1;
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_svn_confirm_submit_files(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		$svn_type = trim($form['svn_type']);
		
		//print_r($form);
		
		if(!$form['file_id_list'])	die('No File to Upload.');
		
		$file_info_list = array();
		$err = array();
		$list_info = array();
	
		$con->sql_query("select afui.*, afu.changes_log
						from arms_file_update_items afui
						left join arms_file_update afu on afu.id = afui.file_update_id
						where afui.id in (".join(',', $form['file_id_list']).")");
		while($r = $con->sql_fetchassoc()){
			if(!isset($file_info_list[$r['filename']])){
				$file_info_list[$r['filename']] = array();
				$file_info_list[$r['filename']]['changes_log'] = $r['changes_log'];
			}
			
			$list_info[$r['file_update_id']]['file_id_list'][] = $r['id'];
		}
		$con->sql_freeresult();
		
		switch($svn_type){
			case 'php7':	// folder for ARMS_ENCODED_PHP7
				$source_file_folder = $this->source_file_folder_php7;
				$credential = "--username andy --password svn4439";
				break;
			default:	// folder for ARMS_ENCODED
				$source_file_folder = $this->source_file_folder;
				break;
		}
		
		
		if(!file_exists($this->source_tmp_file_folder))	$err[] = "<b>".$this->source_tmp_file_folder."</b> does not exists.";
		else{
			$ci_list = $ci_remarks = array();
			// check whether file exists in ARMS_ENCODED
			foreach($file_info_list as $f => $file_info){
				if(!file_exists($source_file_folder."/".$f)){
					$err[] = "<b>".$source_file_folder."/$f</b> does not exists.";
					continue;
				}
				
				$ci_list[] = $source_file_folder."/".$f;
				$ci_remarks[] = $file_info['changes_log'];
			}
		}
		
		if($err){
			print "<ul>";
			foreach($err as $e){
				print "<li>$e</li>";
			}
			print "</ul>";
			exit;
		}
		
		$ci_remarks_path = "/tmp/ci_remarks.txt";
		$ci_list_path = "/tmp/ci_list.txt";

		if($ci_remarks){
			if(file_exists($ci_remarks_path)) @unlink($ci_remarks_path);
			$ci_remarks = join($ci_remarks, "\n")."\n";
			file_put_contents($ci_remarks_path, $ci_remarks);
			chmod($ci_remarks_path, 0777);
		}
		
		if($ci_list){
			if(file_exists($ci_list_path)) @unlink($ci_list_path);
			$ci_list = join($ci_list, "\n");
			file_put_contents($ci_list_path, $ci_list);
			chmod($ci_list_path, 0777);
		}

		$info = shell_exec("cd ".$source_file_folder."; svn ci $credential -F /tmp/ci_remarks.txt --targets /tmp/ci_list.txt 2>&1");
		
		if(!$info){
			print "No SVN was updated.";
		}else{
			if (strpos($info, 'Committed revision') !== false) {
				$upd = array();
				switch($svn_type){
				case 'php7':	// ARMS_ENCODED_PHP7
					$upd['is_svn_php7'] = 1;
					break;
				default:	// ARMS_ENCODED
					$upd['is_svn'] = 1;
					break;
			}
				$con->sql_query("update arms_file_update_items afui	set ".mysql_update_by_field($upd)." where afui.id in (".join(',', $form['file_id_list']).")");
			}

			print nl2br($info);
		}
		//$ret['html'] = $smarty->fetch('ARMS_UPDATER.confirm_submit_file_popup.tpl');
		
		//$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		//print json_encode($ret);
	}
	
	function ajax_confirm_submit_files(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		
		//print_r($form);
		
		if(!$form['file_id_list'])	die('No File to Upload.');
		if(!$form['server_name'])	die('No Server is Selected.');
		
		$file_info_list = array();
		$err = array();
		$list_info = array();
	
		$con->sql_query("select * from arms_file_update_items where id in (".join(',', $form['file_id_list']).")");
		while($r = $con->sql_fetchassoc()){
			if(!isset($file_info_list[$r['filename']]))	$file_info_list[$r['filename']] = array();
			
			$list_info[$r['file_update_id']]['file_id_list'][] = $r['id'];
		}
		$con->sql_freeresult();
		
		// check whether file exists in ARMS_ENCODED
		foreach($file_info_list as $f => $file_info){
			// ARMS_ENCODED
			if(!file_exists($this->source_file_folder."/".$f))	$err[] = "<b>".$this->source_file_folder."/$f</b> does not exists.";
			
			$file_info_list[$f]['filesize'] = filesize($this->source_file_folder."/".$f);
			$file_info_list[$f]['filemtime'] = filemtime($this->source_file_folder."/".$f);
			
			// ARMS_ENCODED_PHP7
			if(!file_exists($this->source_file_folder_php7."/".$f))	$err[] = "<b>".$this->source_file_folder_php7."/$f</b> does not exists.";
			
			$file_info_list[$f]['filesize_php7'] = filesize($this->source_file_folder_php7."/".$f);
			$file_info_list[$f]['filemtime_php7'] = filemtime($this->source_file_folder_php7."/".$f);
		}
		
		if($err){
			print "<ul>";
			foreach($err as $e){
				print "<li>$e</li>";
			}
			print "</ul>";
			exit;
		}
		
		$time = time();
		do{
			$time++;
			$tgz_filename = 'update_'.$time.'.tgz';
			$final_path = $this->source_file_folder."/".$this->upload_file_folder."/".$tgz_filename;
			$final_path_php7 = $this->source_file_folder_php7."/".$this->upload_file_folder."/".$tgz_filename;
			
		}while(file_exists($final_path) || file_exists($final_path_php7));	// prevent duplicate file, if already exists, create another filename
		
		// ARMS_ENCODED
		$command_str = "cd ".$this->source_file_folder."; tar czf ".$this->upload_file_folder."/$tgz_filename";
		foreach($file_info_list as $f => $file_info){
			$command_str .= " $f";
		}
		exec($command_str);
		
		if(!file_exists($final_path))	die("$final_path failed to create.");
		
		$total_filesize = filesize($final_path);
		
		// ARMS_ENCODED_PHP7
		$command_str = "cd ".$this->source_file_folder_php7."; tar czf ".$this->upload_file_folder."/$tgz_filename";
		foreach($file_info_list as $f => $file_info){
			$command_str .= " $f";
		}
		exec($command_str);
		
		if(!file_exists($final_path_php7))	die("$final_path_php7 failed to create.");
		
		$total_filesize_php7 = filesize($final_path_php7);
		
		// history
		$timestamp = date("Y-m-d H:i:s");
		foreach($form['server_name'] as $server_name => $v){
			$upd = array();
			$upd['tgz_filename'] = $tgz_filename;
			$upd['server_name'] = $server_name;
			
			foreach($list_info as $list_id => $list_data){	// loop for each update list
				$upd['file_update_id'] = $list_id;
				$upd['file_id_list'] = serialize($list_data['file_id_list']);
				$upd['added'] = $upd['last_update'] = $timestamp;
				$con->sql_query("insert into arms_file_update_history ".mysql_insert_by_field($upd));
			}

			// loop each file id			
			foreach($form['file_id_list'] as $file_id){				
				$con->sql_query("select * from arms_file_update_items where id=$file_id");
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$upd2 = array();
				$upd2['more_info'] = unserialize($tmp['more_info']);
				$upd2['more_info']['last_update'][$server_name] = $timestamp;
				$upd2['more_info'] = serialize($upd2['more_info']);
				
				$con->sql_query("update arms_file_update_items set ".mysql_update_by_field($upd2)." where id=$file_id");
			}
		}
		
		
		
		$smarty->assign('file_info_list', $file_info_list);
		$smarty->assign('selected_server_name', $form['server_name']);
		$smarty->assign('tgz_filename', $tgz_filename);
		$smarty->assign('total_filesize', $total_filesize);
		$smarty->assign('total_filesize_php7', $total_filesize_php7);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('ARMS_UPDATER.confirm_submit_file_popup.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_update_server_upload_status(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		if(!$form['tgz_filename'] || !$form['server_name'] || !$form['status'])	exit;
		
		$upd = array();
		$upd['result'] = $form['status'];
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$ret = array();	
		if(isset($_SESSION['arms_upload_status'][$form['tgz_filename']][$form['server_name']])){	// checking not to update backward status
			$last_priority = $this->priority_status[$_SESSION['arms_upload_status'][$form['tgz_filename']][$form['server_name']]['status']];
			$curr_priority = $this->priority_status[$form['status']];
			
			$ret['s'] = "$curr_priority < $last_priority";
			if($curr_priority < $last_priority)	exit;
		}
		
		$con->sql_query($q = "update arms_file_update_history set ".mysql_update_by_field($upd)." where tgz_filename=".ms($form['tgz_filename'])." and server_name=".ms($form['server_name']));
		$_SESSION['arms_upload_status'][$form['tgz_filename']][$form['server_name']]['status'] = $form['status'];
		$_SESSION['arms_upload_status'][$form['tgz_filename']][$form['server_name']]['timestamp'] = time();
		
		
		$ret['ok'] = 1;
		$ret['q'] = $q;
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
		
	}
	
	function view_update_history(){
		global $con, $smarty, $server_list;
		
		$id = mi($_REQUEST['id']);
		if(!$id)	die("Invalid Update ID");
		
		// history by file
		$con->sql_query("select * from arms_file_update_items where file_update_id=$id");
		$file_list = array();
		while($r = $con->sql_fetchassoc()){
			$r['more_info'] = unserialize($r['more_info']);
			$file_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		if($file_list){
			// history by list
			$con->sql_query("select * from arms_file_update_history where file_update_id=$id order by last_update desc");
			while($r = $con->sql_fetchassoc()){
				$file_id_list = unserialize($r['file_id_list']);
				
				foreach($file_id_list as $file_id){	// loop for each file id
					if(isset($file_list[$file_id]) && !isset($file_list[$file_id]['update_result'][$r['server_name']]['result'])){
						$file_list[$file_id]['update_result'][$r['server_name']]['result'] = $r['result'];
						$file_list[$file_id]['update_result'][$r['server_name']]['last_update'] = $r['last_update'];
					}
				}
			}
			$con->sql_freeresult();
			
			$real_server_type_list = array('hq', 'branch', 'test');
			foreach($file_list as $file_id => $r){
				foreach($server_list as $server_name => $server_info){
					if(!in_array($server_info['type'], $real_server_type_list))	continue;
					if($server_info['svn'])	continue;
					
					if(!isset($file_list[$file_id]['update_result'][$server_name])){
						$file_list[$file_id]['remaining_server'][$server_name] = 1;
					}
				}
			}
		}
		//print_r($file_list);
		
		$smarty->assign('file_list', $file_list);
		
		$smarty->display('ARMS_UPDATER.update_history.tpl');
	}
	
	function ajax_add_separator_row(){
		global $smarty, $con;
		
		$form = $_REQUEST;
		
		// get max ID
		$con->sql_query("select max(sequence) from arms_file_update");
		$tmp_new_id1 = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		$con->sql_query("select max(sequence) from arms_file_update_separator");
		$tmp_new_id2 = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();

		if($tmp_new_id1 > $tmp_new_id2){
			$new_id = $tmp_new_id1 + 1;
		}else $new_id = $tmp_new_id2 + 1;
		
		$upd = array();
		$upd['id'] = $new_id;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['sequence'] = $new_id;
		
		$con->sql_query("insert into arms_file_update_separator ".mysql_insert_by_field($upd));
		$new_id = $con->sql_nextid();
		
		//$con->sql_query("update arms_file_update_separator set sequence=$new_id where id=$new_id");	// update sequence = id
		
		//$list_info = $this->load_and_process_list_info($new_id);
		//$smarty->assign('seq_info', $list_info);
		$list_info['id'] = $new_id;
		$list_info['sequence'] = $upd['sequence'];
		$smarty->assign('list_id', $new_id);
		$smarty->assign('list_info', $list_info);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('ARMS_UPDATER.list_separator_row.tpl');
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_delete_separator_row(){
		global $con, $smarty;
		
		$list_id = mi($_REQUEST['list_id']);
		
		if(!$list_id)	die('Invalid Row ID.');
		
		$con->sql_query("delete from arms_file_update_separator where id=$list_id");
		
		$ret = array();
		$ret['ok'] = 1;
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
}

$ARMS_UPDATER = new ARMS_UPDATER('ARMS UPDATER');
?>
