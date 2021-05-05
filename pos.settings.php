<?php
/*
1/17/2009 8:18:27 AM yinsee
- update all image size if single-server mode

2/6/2009 3:15:00 PM Jeff
- add in Cash Redemption Have Point

1/26/2011 9:56:46 AM yinsee
- add pos_receipt_image upload (and fix size 200x48)

6/24/2011 5:14:53 PM Andy
- Make all branch default sort by sequence, code.

6/27/2011 3:58:16 PM Alex
- save all data include blank

8/3/2011 4:30:43 PM Andy
- Fix when delete uploaded image should also clear the size stored.

9/7/2011 4:13:43 PM Alex
- add save receipt footer and header into branch table

10/06/2011 4:09:00 Kee Kee
- add print username in receipt who allow to goods return
- add show future promotion in price check

10/06/2011 12:00:26 PM Kee Kee
- Add preset receipt footer function(Customixe receipt footer)
- Print counter version in receipt footer

09/13/2012 2:11 PM Kee Kee
- Add save current time into "last_update" column

7/3/2013 4:56 PM Fithri
- if display images not found, then copy from default images

7/8/2013 10:15 AM Fithri
- no need to create default image for pos_receipt_image

7/29/2013 11:16 AM Kee Kee
- add pos_receipt_image upload with size 588 x 100

8/20/2013 1:48 PM Justin
- Added new option "Use Own Image".
- Enhanced to support upload pos counter image by branch.

10/14/2013 4:11 PM Justin
- Bug fixed on get no file size while first to set up images for front end.
- Bug fixed on the dual screen image variables will missing after save it.

12/23/2013 1:39 PM Kee Kee
- Bug fixed for save "print_actual_quantiy"

12/24/2013 5:51 PM Andy
- Enhance to capture log when user update pos settings.

11/6/2014 5:26 PM DingRen
- Add Goods ReturnReason Settings

04/24/2015 11:06 PM Kee Kee
- Remove check receipt header image height

11/25/2016 17:10 Qiu Ying
- Fixed bug on data in database still exists when receipt logo has deleted

7/13/2017 9:05 AM Kee Kee
- Added "Company Logo" under Display Settings

7/14/2017 14:23 PM Kee Kee
- Filter Preset Receipt Remark Empty Value instead of save in database

04/18/2018 5:37 PM Brandon
- Add upload top right banner function.

6/20/2018 3:15 PM Andy
- Enhanced to able to override foreign currency rate by branch.

3/8/2019 4:43 PM Andy
- Added eWallet POS Settings.

4/4/2019 1:34 PM Justin
- Enhanced the eWallet settings to be able to load integrator list.

4/16/2019 2:43 PM Andy
- Enhanced to get eWallet List from posManager.

12/31/2020 4:11 PM Shane
- Added off_dayend setting, which apply to all branches

1/7/2021 1:26 PM Shane
- Added default design_mode and color_theme_name setting.

2/1/2021 5:00 PM Shane
- Added upload image function for POS Backend.

2/15/2021 5:41 PM Shane
- Added audio file upload function.

2/18/2021 12:48 PM Shane
- Added checking for audio file upload to accept .mp3 and .wav only.
- Added new Audio Display Point: "Every scan of valid item" and "Every scan of invalid item".
- Changed "First scan of new transaction" to "New Transaction".

2/24/2021 3:49 PM Shane
- Added new POS Counter Design "AD2".

2/26/2021 2:53 PM Shane
- Added version info for POS Counter Design.

4/14/2021 11:47 AM Shane
- Added "Force Privilege Override Popup" settings.

4/20/2021 11:22 AM Shane
- Added "Force Drawer Privilege popup" setting.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(159);

ini_set("display_errors",0);

class PosSettings extends Module
{
	var $branch_id,$audio_list;
	
	function __construct($title, $template='')
	{
		global $sessioninfo,$con,$smarty;
		if (BRANCH_CODE == 'HQ') $this->branch_id = intval($_REQUEST['branch_id']);
		if ($this->branch_id =='')
		{
			$this->branch_id = $sessioninfo['branch_id'];
		}
		
		$q1 = $con->sql_query("select id, code from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc($q1)){
			$branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("branch", $branch_list);

		//Audio List
		$this->audio_list = array(
			'new_transaction' => 'New Transaction',
			'end_transaction' => 'End of Transaction',
			'scan_item_success' => 'Every scan of valid item',
			'scan_item_failed' => 'Every scan of invalid item',
		);
		parent::__construct($title, $template='');
	}

	function _default()
	{
		global $con, $smarty,$config, $appCore;
		
		$con->sql_query("select * from pos_settings where branch_id = ".$this->branch_id);
		
		while($r = $con->sql_fetchrow())
		{
			if (preg_match("/^a:\d+/", $r[2])) $r[2] = unserialize($r[2]);
			$form[$r[1]] = $r[2];
		}
		unset($r);
		$con->sql_freeresult();
		$form['branch_id'] = $this->branch_id;
		$bcode =get_branch_code($this->branch_id);
		
		// get gst status
		$prms = array();
		$prms['branch_id'] = $form['branch_id'];
		$prms['date'] = date("Y-m-d");
		$form['branch_is_under_gst'] = check_gst_status($prms);
		
		if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_bg")){
			copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_bg-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_main_bg");
			$form['pos_main_bg_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_bg-default");
		}
		else{
			$form['pos_main_bg_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_bg");
		}
		$form['pos_main_bg'] = '/ui/pos_main_bg?'.time();
		
		if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_banner")){
			copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_banner-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_main_banner");
			$form['pos_main_banner_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_banner-default");
		}
		else{
			$form['pos_main_banner_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_banner");
		}
		$form['pos_main_banner'] = '/ui/pos_main_banner?'.time();
		
		if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_login_bg")){
			copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_login_bg-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_login_bg");
			$form['pos_login_bg_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_login_bg-default");
		}
		else{
			$form['pos_login_bg_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_login_bg");
		}
		$form['pos_login_bg'] = '/ui/pos_login_bg?'.time();
		
		/*
		if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_receipt_image"))
			copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_receipt_image-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_receipt_image");
		*/
		if (file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_receipt_image")){
			$form['pos_receipt_image_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_receipt_image");
		}
		$form['pos_receipt_image'] = '/ui/pos_receipt_image?'.time();
		
		if (file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_company_logo")){
			$form['pos_company_logo_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_company_logo");
		}
		$form['pos_company_logo'] = '/ui/pos_company_logo?'.time();

		if (file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_top_right_banner")){
			$form['pos_top_right_banner_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_top_right_banner");
		}
		$form['pos_top_right_banner'] = '/ui/pos_top_right_banner?'.time();

		if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pb_menu_body_image")){
			copy($_SERVER['DOCUMENT_ROOT']."/ui/pb_menu_body_image-default",$_SERVER['DOCUMENT_ROOT']."/ui/pb_menu_body_image");
			$form['pb_menu_body_image_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pb_menu_body_image-default");
		}
		else{
			$form['pb_menu_body_image_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pb_menu_body_image");
		}
		$form['pb_menu_body_image'] = '/ui/pb_menu_body_image?'.time();

		if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_top_right_banner_backend")){
			copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_top_right_banner_backend-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_top_right_banner_backend");
			$form['pos_top_right_banner_backend_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_top_right_banner_backend-default");
		}
		else{
			$form['pos_top_right_banner_backend_size'] = filesize($_SERVER['DOCUMENT_ROOT']."/ui/pos_top_right_banner_backend");
		}
		$form['pos_top_right_banner_backend'] = '/ui/pos_top_right_banner_backend?'.time();

		if($config['single_server_mode'] && get_branch_code($this->branch_id) != "HQ"){
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display")){
				mkdir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display", 0777, true);
				chmod($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display",0777);
			}
			
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos")){
				mkdir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos", 0777, true);
				chmod($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos",0777);
			}
			
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id)){
				mkdir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id, 0777, true);
				chmod($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id,0777);
			}

			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images")){
				mkdir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images", 0777, true);
				chmod($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images",0777);
			}
			
			/*
			if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_main_bg"))
				copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_bg-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_main_bg");
			*/
			$form['branch_pos_main_bg'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_main_bg?".time();
			
			/*
			if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_main_banner"))
				copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_main_banner-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_main_banner");
			*/
			$form['branch_pos_main_banner'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_main_banner?".time();
			
			/*if (!file_exists($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_login_bg"))
				copy($_SERVER['DOCUMENT_ROOT']."/ui/pos_login_bg-default",$_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_login_bg");
			*/
			$form['branch_pos_login_bg'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_login_bg?".time();

			$form['branch_pos_receipt_image'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_receipt_image?".time();
			
			$form['branch_pos_company_logo'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_company_logo?".time();

			$form['branch_pos_top_right_banner'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_top_right_banner?".time();

			$form['branch_pb_menu_body_image'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pb_menu_body_image?".time();
			$form['branch_pos_top_right_banner_backend'] = "/ui/pos_settings_display/pos/".$this->branch_id."/branch_pos_top_right_banner_backend?".time();
		}
		
		//new added data
		$con->sql_query("select receipt_header, receipt_footer from branch where id = ".$this->branch_id);
		while($r = $con->sql_fetchassoc())
		{
			$form['receipt_header'] = $r['receipt_header'];
			$form['receipt_footer'] = $r['receipt_footer'];
		}
		unset($r);
		$con->sql_freeresult();
		
		$images = scandir($_SERVER['DOCUMENT_ROOT']."/ui/dual_screen_images", 1);
		$form['dsi_list'] = array();
		foreach($images as $iname){
			if(!preg_match("/\.(jpg|png|gif)$/i",$iname)) continue;
			$form['dsi_list'][$iname] = "/ui/dual_screen_images/".$iname;
		}
		
		if($config['single_server_mode'] && get_branch_code($this->branch_id) != "HQ"){
			$images = scandir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images", 1);
			$form['branch_dsi_list'] = array();
			foreach($images as $iname){
				if(!preg_match("/\.(jpg|png|gif)$/i",$iname)) continue;
				$form['branch_dsi_list'][$iname] = "/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images/".$iname;
			}
		}
		
		// Foreign Currency
		if($config['foreign_currency']){
			$this->load_foreign_currency_settings();
		}
		
		// eWallet
		if($config['ewallet_list']){
			$ewallet_list = $appCore->posManager->getEwalletList($bcode);
			//print_r($ewallet_list);
			$smarty->assign('ewallet_list', $ewallet_list);
		}

		if($config['single_server_mode'] && get_branch_code($this->branch_id) != "HQ"){
			$con->sql_query("select * from pos_settings where branch_id = 1 and setting_name = 'off_dayend'");
			$r = $con->sql_fetchrow();
			$form[$r['setting_name']] = $r['setting_value'];
		}

		if(!isset($form['design_mode'])){
			$form['design_mode'] = '0';
		}

		if(!isset($form['color_theme_name'])){
			$form['color_theme_name'] = 'blue';
		}

		//Design mode option
		$design_mode_option = array(
			'208' => array(
					'0' => 'ui/pos_design_option/counter-design-1.png',
					'AD1' => 'ui/pos_design_option/counter-design-2.png'
				),
			'209' => array(
					'AD2' => 'ui/pos_design_option/counter-design-3.png'
				),
		);

		// $design_mode_option['0'] = 'ui/pos_design_option/counter-design-1.png';
		// $design_mode_option['AD1'] = 'ui/pos_design_option/counter-design-2.png';
		// $design_mode_option['AD2'] = 'ui/pos_design_option/counter-design-3.png';
		$smarty->assign("design_mode_option",$design_mode_option);

		$design_theme_color_option = array();
		$design_theme_color_option['blue'] = 'ui/pos_design_option/theme-design-1.png';
		$design_theme_color_option['orange'] = 'ui/pos_design_option/theme-design-2.png';
		$design_theme_color_option['green'] = 'ui/pos_design_option/theme-design-3.png';
		$smarty->assign("design_theme_color_option",$design_theme_color_option);

		//Force Override Privilege
		$force_override_privilege = array(
			'force_prune_receipt' => 'Force Prune Receipt Privilege popup',
			'force_cancel_bill' => 'Force Cancel Bill Privilege popup',
			'force_goods_return' => 'Force Goods Return Privilege popup',
			'force_drawer' => 'Force Drawer Privilege popup'
		);
		$smarty->assign("force_override_privilege",$force_override_privilege);

		//Audio List
		$smarty->assign("audio_list",$this->audio_list);
		foreach($this->audio_list as $audio_name => $audio_label){
			$setting_audio_name = 'audio_'.$audio_name;
			$con->sql_query("select setting_value from pos_settings where branch_id = ".$this->branch_id." and setting_name = ".ms($setting_audio_name));
			$r = $con->sql_fetchrow();
			$con->sql_freeresult();
			if($r['setting_value']){
				$form[$audio_name] = $r['setting_value'];
			}else{
				if(isset($form[$audio_name])) unset($form[$audio_name]);
			}
		}

		$smarty->assign("form",$form);
		
		$this->display();
	}
	
	function fupload($branch_upload=false){
		global $LANG,$con,$config;

		$fupload = array_keys($_FILES);
		$fname = $fupload[0];
		
		if($branch_upload){
			$dir = "/ui/pos_settings_display/pos/".$this->branch_id;
			$ds_dir = "/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images";
		}else{
			$dir = "/ui";
			$ds_dir = "/ui/dual_screen_images";
		}

		if ($_FILES[$fname]['error'] == 0 && preg_match("/\.(jpg|png|gif)$/i",$_FILES[$fname]['name'],$ext))
		{	
			$sz = getimagesize($_FILES[$fname]['tmp_name']);
			
			// change file name to follow timestamp
			$js_fname = $fname1 = $fpath = "";
			if(preg_match("/dual_screen_image/i", $fname)){
				if (!is_dir($_SERVER['DOCUMENT_ROOT'].$ds_dir)){
					mkdir($_SERVER['DOCUMENT_ROOT'].$ds_dir, 0777, true);
					@chmod($_SERVER['DOCUMENT_ROOT'].$ds_dir,0777);
				}
				
				$fname1 = time().$ext[0];
				
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/ui/dual_screen_images/$fname1")){
					$fname1 = time()+1;
					$fname1 .= $ext[0];
				}
			
				if($branch_upload){
					$fpath = $_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images/$fname1";
					$js_fname = "/pos_settings_display/pos/".$this->branch_id."/dual_screen_images/$fname1";
				}else{
					$fpath = $_SERVER['DOCUMENT_ROOT']."/ui/dual_screen_images/$fname1";
					$js_fname = "/dual_screen_images/$fname1";
				}
			}else{
				if (!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)){
					mkdir($_SERVER['DOCUMENT_ROOT'].$dir, 0777, true);
					@chmod($_SERVER['DOCUMENT_ROOT'].$dir,0777);
				}

				if($branch_upload){
					$fpath = $_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/".$fname;
					$js_fname = "/pos_settings_display/pos/".$this->branch_id."/$fname";
				}else{
					$fpath = $_SERVER['DOCUMENT_ROOT']."/ui/$fname";
					$js_fname = $fname;
				}
			}
			
		    if ($fname=='pos_receipt_image' && (($sz[0]<200 || $sz[0]>588))){
		        print "<script>parent.alert('".sprintf($LANG['POS_SETTINGS_INVALID_IMAGE_SIZE'],'200 or 588')."');</script>";
			}
		    elseif (preg_match("/dual_screen_image/i", $fname) && ($sz[0]>550 || $sz[1]>550)){
		        print "<script>parent.alert('".sprintf($LANG['POS_SETTINGS_INVALID_IMAGE_SIZE'],'550x550')."');</script>";
			}
			elseif ($_FILES[$fname]['tmp_name'])
			{
				if($fname == "pos_top_right_banner" || $fname == "branch_pos_top_right_banner")
				{
					$tmp_path = "/tmp/top_right_banner_".$_FILES[$fname]['name'];
					if(move_uploaded_file($_FILES[$fname]['tmp_name'], $tmp_path))
					{
						resize_photo($tmp_path, $fpath, "", 400, 100, 50); 
						unlink($tmp_path);
						$file_uploaded = true;
					}
					else
					{
						$file_uploaded = false;
					}	
				}
				else
				{
					$file_uploaded = (move_uploaded_file($_FILES[$fname]['tmp_name'], $fpath)) ? true : false;
				}

				if($file_uploaded)
				{
					@chmod($fpath, 0777);
					if(preg_match("/dual_screen_image/i", $fname)){
						if($branch_upload){
							$div_path = "div_branch_ds_image";
							$del_method = "branch_del_img";
						}else{
							$div_path = "div_ds_image";
							$del_method = "del_img";
						}
						
						$images = scandir($_SERVER['DOCUMENT_ROOT'].$ds_dir, 1);
						$ds_file_list = array();
					
						foreach($images as $iname){
							if(!preg_match("/\.(jpg|png|gif)$/i",$iname)) continue;
							$ds_file_list[] = $iname;
						}
						
						$upd['branch_id'] = $this->branch_id;
						$upd['setting_name'] = $_REQUEST['img'];
						$upd['setting_value'] = serialize($ds_file_list);
						$upd['last_update'] = "CURRENT_TIMESTAMP";
						$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));

						print "<script>parent.add_new_ds_image('ui$js_fname','$fname1','$div_path','$del_method','$branch_upload');</script>";
						exit;
					}
					else
					{
						$fsize = filesize($fpath);

						if ($config['single_server_mode'])
						{

							$upd['branch_id'] = $this->branch_id;
							$upd['setting_name'] = $fname."_size";
							$upd['setting_value'] = $fsize;
							$upd['last_update'] = "CURRENT_TIMESTAMP";
							$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));

							if(BRANCH_CODE == 'HQ' && $this->branch_id==1)
							{
								$r1=$con->sql_query("select id from branch");
								while($r=$con->sql_fetchrow($r1))
								{
									$upd['branch_id'] = $r['id'];
									$upd['setting_name'] = $fname."_size";
									$upd['setting_value'] = $fsize;
									$upd['last_update'] = "CURRENT_TIMESTAMP";
									//$con->sql_query("replace into pos_settings (branch_id,setting_name,setting_value) values(".mi().",".ms($fname."_size").",".mi(filesize($_SERVER['DOCUMENT_ROOT']."/ui/$fname")).")");
									$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));
								}
							}
						}
						else
						{
							$upd['branch_id'] = $this->branch_id;
							$upd['setting_name'] = $fname."_size";
							$upd['setting_value'] = $fsize;
							$upd['last_update'] = "CURRENT_TIMESTAMP";
							$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));
							//$con->sql_query("replace into pos_settings (branch_id,setting_name,setting_value) values(".$this->branch_id.",".ms($fname."_size").",".filesize($_SERVER['DOCUMENT_ROOT']."/ui/$fname").")");
						}
					
						print "<script>parent.$('".$fname."_size').value=".filesize($fpath).";parent.$('img_$fname').src = 'ui/$js_fname?".time()."';parent.$('img_$fname').width='200';alert('".$LANG['POS_SETTINGS_IMG_UPLOADED']."');</script>";
					}
				}
				else
				{
					print "<script>parent.alert('".$LANG['POS_SETTINGS_CANT_MOVE_FILE']."');</script>";
				}
			}
			else
			{
			    print "<script>parent.alert('".$LANG['POS_SETTINGS_UPLOAD_ERROR']."');</script>";
			}
		}
		elseif (!preg_match("/\.(jpg|png|gif)$/i",$_FILES[$fname]['name']))
			print "<script>parent.alert('".$LANG['POS_SETTINGS_INVALID_FORMAT']."');</script>";		
		else
			print "<script>parent.alert('".$LANG['POS_SETTINGS_UPLOAD_ERROR']."');</script>";		
	}
	
	function del_img($branch_del=false){
		global $LANG, $con;

		if($branch_del){
			$dir = "/ui/pos_settings_display/pos/".$this->branch_id;
			$ds_dir = "/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images";
		}else{
			$dir = "/ui";
			$ds_dir = "/ui/dual_screen_images";
		}
		
		$img = trim($_REQUEST['img']);
		$ds_img = trim($_REQUEST['ds_img']);
		
		if(!preg_match("/dual_screen_image/i", $img) && file_exists($_SERVER['DOCUMENT_ROOT'].$dir."/".$img))
		{
			unlink($_SERVER['DOCUMENT_ROOT'].$dir."/".$img);
			
			$con->sql_query("update pos_settings set setting_value='' where branch_id=".mi($this->branch_id)." and setting_name=".ms($img.'_size'));
			print "<script>parent.alert('".$LANG['POS_SETTINGS_IMG_DELETED']."');
							parent.$('".$img."_size').value='';
							parent.$('img_".$img."').src = '/ui/pixel.gif?".time()."';
							parent.$('img_".$img."').width='1';</script>";
					
			print "<script>parent.document.f_".$img.".a.value='fupload';</script>";
		}elseif(preg_match("/dual_screen_image/i", $img) && file_exists($_SERVER['DOCUMENT_ROOT'].$ds_dir."/".$ds_img)){
			unlink($_SERVER['DOCUMENT_ROOT'].$ds_dir."/".$ds_img);
			
			$images = scandir($_SERVER['DOCUMENT_ROOT'].$ds_dir, 1);
			$ds_file_list = array();
		
			foreach($images as $iname){
				if(!preg_match("/\.(jpg|png|gif)$/i",$iname)) continue;
				$ds_file_list[] = $iname;
			}
			$ds_file_list = serialize($ds_file_list);
			
			$con->sql_query("update pos_settings set setting_value='$ds_file_list' where branch_id=".mi($this->branch_id)." and setting_name=".ms($img));
			
			print "<script>parent.alert('".$LANG['POS_SETTINGS_IMG_DELETED']."')</script>";
			if($branch_del) print "<script>parent.document.f_".$img.".a.value='branch_fupload';</script>";
			else print "<script>parent.document.f_".$img.".a.value='fupload';</script>";
		}
	}
	
	function save()
	{
		global $con,$smarty,$LANG,$config,$sessioninfo;
		
		$form = $_REQUEST['form'];
		// save receipt footer and header
		$con->sql_query("update branch set receipt_header=".ms($form['receipt_header']).",receipt_footer=".ms($form['receipt_footer'])." where id=".$this->branch_id);
		unset($form['receipt_header'], $form['receipt_footer']);

		// clean up currency
		$form['currency'] = array();
		foreach($form['currency_name'] as $k=>$v)
		{
			if ($form['currency_name'][$k]!='' && $form['currency_rate'][$k]!='')
			{
				$form['currency'][$form['currency_name'][$k]] = $form['currency_rate'][$k];
			}
		}
		unset($form['currency_name']);
		unset($form['currency_rate']);
		
		if(!isset($form['print_actual_quantiy']['unit_code']))
		{
			$form['print_actual_quantiy']['unit_code'] = 0;
		}
		
		if(!isset($form['print_actual_quantiy']['price_code']))
		{
			$form['print_actual_quantiy']['price_code'] = 0;
		}
		
		if(!isset($form['print_actual_quantiy']['unit_code_unit_price']))
		{
			$form['print_actual_quantiy']['unit_code_unit_price'] = 0;
		}
		
		if(!isset($form['print_actual_quantiy']['unit_code_price_code']))
		{
			$form['print_actual_quantiy']['unit_code_price_code'] = 0;
		}
		
		if(!isset($form['service_charges_before_rdisc']))
		{
			$form['service_charges_before_rdisc'] = 0;
		}

		if($this->branch_id == 1 && !isset($form['off_dayend'])){
			$form['off_dayend'] = 0;
		}

		foreach($form['grr_settings']['code'] as $rid=>$grrs_code){
			$grrs_desc = $form['grr_settings']['description'][$rid];
			if(!trim($grrs_code) || !trim($grrs_desc)){
				unset($form['grr_settings']['code'][$rid]);
				unset($form['grr_settings']['description'][$rid]);
				continue;
			}
		}

		foreach($form['table_resit_remark'] as $rid=>$rval){
			if(!trim($rval) || !trim($rval)){
				unset($form['table_resit_remark'][$rid]);
				continue;
			}
		}

		//Audio
		$exclude_delete_setting = array();
		foreach($this->audio_list as $audio_name => $audio_label){
			if(!isset($form['use_audio_'.$audio_name])){
				$form['use_audio_'.$audio_name] = 0;
			}

			$exclude_delete_setting['audio_'.$audio_name] = 1;
		}

		$exclude_delete = " and setting_name not in (".implode(',',array_map('ms',array_keys($exclude_delete_setting))).")";

		$con->sql_query("delete from pos_settings where branch_id = ".$this->branch_id." $exclude_delete");
		
		foreach($form as $key => $value)
		{
			$empty_value = 0;
			if($key=="preset_receipt_footer")
			{
				if($value['option']==1)
				{
					$v = $value['receipt_footer'];
					for($i=0;$i<4;$i++)
					{
						if(trim($v[$i]['date_from'])=="" && trim($v[$i]['date_to'])=="" && trim($v[$i]['time_from'])=="" && trim($v[$i]['time_to'])=="" && trim($v[$i]['receipt_footer_description'])=="")
						{
							$empty_value += 1;
						}
					}
				
					if($empty_value==4)
					{
						$value['option'] = 0;
					}
				}
			}
/*			
			if ($value != '')
			{
*/				if (is_array($value))
				{
/*					$value['date_from'] = dmy_to_time($value['date_from']);
					$value['date_to'] = dmy_to_time($value['date_to']);
*/					$value = serialize($value);
				}
				else
				{
					if ($key == 'race') $value = ucase($value);
				}
				
				$upd['branch_id'] = $this->branch_id;
				$upd['setting_name'] = $key;
				$upd['setting_value'] = $value;
				$upd['last_update'] = "CURRENT_TIMESTAMP";
				//$upd['server_config'] = serialize($config);
				$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));
				//$con->sql_query("insert into pos_settings (branch_id, setting_name, setting_value) values(".$this->branch_id.", ".ms($key).", ".ms($value).") ");
				if ($con->sql_affectedrows()>0) $update++;
/*			}
*/		}

		// store path for dual screen image by branch
		$images = scandir($_SERVER['DOCUMENT_ROOT']."/ui/pos_settings_display/pos/".$this->branch_id."/dual_screen_images", 1);
		$ds_file_list = array();
	
		foreach($images as $iname){
			if(!preg_match("/\.(jpg|png|gif)$/i",$iname)) continue;
			$ds_file_list[] = $iname;
		}
		
		if($ds_file_list){
			$upd = array();
			$upd['branch_id'] = $this->branch_id;
			$upd['setting_name'] = "branch_pos_dual_screen_image";
			$upd['setting_value'] = serialize($ds_file_list);
			$upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));
		}
		
		// store path for dual screen image
		if($this->branch_id != 1) $images = scandir($_SERVER['DOCUMENT_ROOT']."/ui/dual_screen_images/".$this->branch_id, 1);
		else $images = scandir($_SERVER['DOCUMENT_ROOT']."/ui/dual_screen_images/", 1);
		$ds_file_list = array();
	
		foreach($images as $iname){
			if(!preg_match("/\.(jpg|png|gif)$/i",$iname)) continue;
			$ds_file_list[] = $iname;
		}
		
		if($ds_file_list){
			$upd = array();
			$upd['branch_id'] = $this->branch_id;
			$upd['setting_name'] = "pos_dual_screen_image";
			$upd['setting_value'] = serialize($ds_file_list);
			$upd['last_update'] = "CURRENT_TIMESTAMP";
			$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));
		}
		
		log_br($sessioninfo['id'], 'POS_SETTING', 0, "Update POS Setting for branch ID#".$this->branch_id);
		
		if ($update > 0) header("Location: ".$_SERVER['PHP_SELF']."?branch_id=".$this->branch_id."&msg=".urlencode($LANG['POS_SETTINGS_UPDATED']));
	}

	function branch_fupload(){
		$this->fupload(true);
	}
	
	function branch_del_img(){
		$this->del_img(true);
	}
	
	private function load_foreign_currency_settings(){
		global $config, $appCore, $smarty;
		
		$foreign_currency_data = array();
		$foreign_currency_data['list'] = $appCore->currencyManager->loadLatestCurrencyRate();
		
		$smarty->assign('foreign_currency_data', $foreign_currency_data);
	}

	function upload_audio(){
		global $con;

		$fupload = array_keys($_FILES);
		$fname = $fupload[0];

		$artype = explode("/",$_FILES[$fname]['type']);
		$mime_group = $artype[0];
		$mime_type = $artype[1];
		$ext = pathinfo($_FILES[$fname]['name'], PATHINFO_EXTENSION);
		$accepted_mime = array('mpeg','x-wav','wav');
		if ($_FILES[$fname]['error'] == 0 && $mime_group == 'audio' && in_array($mime_type,$accepted_mime))
		{	
			check_and_create_dir('attch');
			check_and_create_dir('attch/pos_audio');
			$dir = 'attch/pos_audio/'.$this->branch_id;
			check_and_create_dir($dir);
			$filename = $fname.'.'.$ext;
			$target = $dir.'/'.$filename;
			if(file_exists($target)){
				unlink($target);
			}
			if(move_uploaded_file($_FILES[$fname]['tmp_name'],$target)){
				$upd['branch_id'] = $this->branch_id;
				$upd['setting_name'] = 'audio_'.$fname;
				$upd['setting_value'] = $target;
				$upd['last_update'] = "CURRENT_TIMESTAMP";
				$con->sql_query("replace into pos_settings ".mysql_insert_by_field($upd));

				print "<script>parent.upload_audio_callback('$fname','$target');</script>";
			}else{
				print "<script>parent.alert('Fail to overwrite file, please contact adminstrator.');</script>";
			}
		}
		elseif ($mime_group != 'audio' || !in_array($mime_type,$accepted_mime))
			print "<script>parent.alert('Invalid file type (".$_FILES[$fname]['type']."). Please make sure uploaded file is .mp3 or .wav file.');</script>";
		else
			print "<script>parent.alert('File upload error, please contact adminstrator.');</script>";
	}

	function del_audio(){
		global $con;

		$audio_name = trim($_REQUEST['audio_file']);
		$setting_audio_name = 'audio_'.$audio_name;
		$con->sql_query("select * from pos_settings where branch_id = ".$this->branch_id." and setting_name = ".ms($setting_audio_name));
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();

		$target = $r['setting_value'];
		if(file_exists($target)){
			unlink($target);
		}

		$con->sql_query("delete from pos_settings where branch_id = ".$this->branch_id." and setting_name = ".ms($setting_audio_name));

		print "<script>parent.del_audio_callback('$audio_name');</script>";
	}
}

$pos_settings = new PosSettings ('POS Settings');

?>
