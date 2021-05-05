{*
7/31/2019 2:25 PM Andy
- Added Module "Member Mobile App Details".

9/24/2019 2:56 PM Andy
- Added "Profile Photo".
*}

{include file='header.tpl'}

<style>
{literal}
#div_profile_image{
	border: 3px outset black;
	float: right;
	background-color: #fff;
	padding: 3px;
}

#upload_image_popup {
	border:2px solid #000;
	background:#fff;
	width:300px;
	height:120px;
	padding:10px;
	position:absolute;
	text-align:center;
	z-index:10000;
}

{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MEMBER_MOBILE_DETAILS = {
	initialise: function(){
		CHANGE_PASS_DIALOG.initialize();
		UPLOAD_PROFILE_IMAGE_DIALOG.initialise();
	},
	// function when users click on profile image
	profile_image_clicked: function(){
		var profile_image_url = $('inp_profile_image_url').value;
		
		// No image
		if(!profile_image_url)	return;
		show_sku_image_div(profile_image_url);
	}
};

var CHANGE_PASS_DIALOG = {
	f: undefined,
	initialize: function(){
		this.f = document.f_change_pass;
	},
	open: function(){
		//$('div_cc_clone_dialog_content').update(_loading_);
		this.f.reset();
		
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_change_pass_dialog').show());
		
		this.f['new_p'].focus();
		
		/*var THIS = this;
		var params = {
			a: 'ajax_show_clone_cycle_count',
			branch_id: bid,
			id: cc_id
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Redirect to main page
						$('div_cc_clone_dialog_content').update(ret['html']);
						THIS.update_propose_st_date_list();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
				THIS.close();
			}
		});*/
	},
	close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	// function when user click on button confirm
	save_clicked: function(){
		if(!this.validate_form()){
			return false;
		}
		
		var params = $(this.f).serialize();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Reload
						location.reload(true);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function to validate form
	validate_form(){
		var p = this.f['new_p'].value.trim();
		
		// Min 6 char
		if(p.length < 6){
			alert('Password at least 6 characters');
			this.f['new_p'].focus();
			return false;
		}
		
		// a-z, 0-9 only
		if(!isAlphaNumeric(p)){
			alert('Please enter Alphanumeric only (A-z, 0-9).');
			this.f['new_p'].focus();
			return false;
		}
		
		// confirm password
		var p2 = this.f['new_p2'].value.trim();
		
		if(p != p2){
			alert('Password not match.');
			this.f['new_p2'].focus();
			return false;
		}
		
		return true;
	}
}

var UPLOAD_PROFILE_IMAGE_DIALOG = {
	f: undefined,
	initialise: function(){
		this.f = document.f_upload_profile_image;
	},
	open: function(){
		// reset the selected file path
		this.f['profile_image'].outerHTML = this.f['profile_image'].outerHTML;
		this.enable_submit_button(true);
		curtain(true, 'curtain2');
		center_div($('upload_image_popup').show());
	},
	close: function(){
		$('upload_image_popup').hide();
		curtain(false, 'curtain2');
	},
	// Core function to validate filename
	validate_file: function(){
		
		var inp_file = this.f['profile_image'];
		if(!inp_file){
			alert('File Input Not Found.');
			return false;
		}
		
		var filename = inp_file.value.trim();
		
		// Check File Extension
		if (filename == '') {
			alert('Please select a file to upload');
			return false;
		}else if (!/\.jpg|\.jpeg|\.png|\.gif/i.test(filename)){
			alert("Selected file must be a valid JPG/JPEG/PNG/GIF image");
			return false;
		}
		
		// File Size
		var oFile = inp_file.files[0];
		if (oFile.size > 1000000 ) // 1 mb for bytes.
		{
			alert("Image File Size is limited to a maximum of 1MB only.");
			return false;
		}
		
		return true;
	},
	// function to enable / disble action button
	enable_submit_button: function(is_enable){
		$$('#upload_image_popup p.p_submit input').each(function(inp){
			inp.disabled = !is_enable;
		});
	},
	// function when user click on upload button
	upload_clicked: function(){
		// Check File
		if(!this.validate_file()){
			return false;
		}
		
		this.enable_submit_button(false);
		
		// Upload
		this.f.submit();
	},
	// function when upload success
	upload_image_done: function(filepath){
		// Display Image Path
		//alert(filepath);
		
		//this.close();
		
		// Reload current page
		location.reload(true);
	},
	// function when upload failed
	upload_image_failed: function(errmsg){
		alert(errmsg);
		this.enable_submit_button(true);
	}
};
{/literal}
</script>

{* Change Password Dialog *}
<div id="div_change_pass_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:400px;height:200px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_change_pass_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Change Mobile Password</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="CHANGE_PASS_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_change_pass_dialog_content" style="padding:2px;;overflow-y:auto;">
	
		<form name="f_change_pass" onSubmit="return false;">
			<input type="hidden" name="a" value="ajax_change_pass" />
			<input type="hidden" name="nric" value="{$member.nric}" />
			
			<table width="100%">
				<tr>
					<td><b>New Password</b></td>
					<td>
						<input type="password" name="new_p" maxlength="10" />
						<img src="/ui/rq.gif" align="absmiddle" />
					</td>
				</tr>
				
				<tr>
					<td><b>Confirm Password</b></td>
					<td>
						<input type="password" name="new_p2" maxlength="10" />
						<img src="/ui/rq.gif" align="absmiddle" />
					</td>
				</tr>
			</table>
			
			<ul>
				<li> Alphanumeric only (A-z, 0-9).</li>
				<li> 6-10 Characters.</li>
			</ul>
			
			<p align="center" id="p_btn">
				<input type="button" value="Confirm" onClick="CHANGE_PASS_DIALOG.save_clicked();" />
				<input type="button" value="Cancel" onClick="CHANGE_PASS_DIALOG.close();" />
				<br />
				<span id="span_change_pass_processing"></span>
			</p>
		</form>
	</div>
</div>

{* popup Image div *}
<div id="upload_image_popup" style="display:none;">
	<form onsubmit="return false;" name="f_upload_profile_image" target="_ifs" enctype="multipart/form-data" method="post">
		<h4>Select an image to add</h4>
		<input type="hidden" name="a" value="upload_profile_image" />
		<input type="hidden" name="nric" value="{$member.nric}" />
		<input name="profile_image" type="file" />
		<br>
		<p class="p_submit">
			<input type="button" value="Upload" onClick="UPLOAD_PROFILE_IMAGE_DIALOG.upload_clicked();" />
			<input type="button" value="Cancel" onclick="UPLOAD_PROFILE_IMAGE_DIALOG.close()">
		</p>
	</form>
	<iframe name="_ifs" width="1" height="1" style="visibility:hidden"></iframe>
</div>

<h1>{$PAGE_TITLE}</h1>

<div class="stdframe">
	{* Profile Image *}
	<div id="div_profile_image">
		<div align="center">Profile Photo</div>
		<div align="center">
			<input type="hidden" id="inp_profile_image_url" name="profile_image_url" value="{$member.profile_image_url}" />
			<img {if $member.profile_image_url}src="thumb.php?img={$member.profile_image_url|urlencode}&h=100&w=100"{/if} onClick="MEMBER_MOBILE_DETAILS.profile_image_clicked();" height="100" width="100" />
		</div>
		<input type="button" value="Upload New Photo" onClick="UPLOAD_PROFILE_IMAGE_DIALOG.open();" />
	</div>
	
	{* Profile Info *}
	<table class="body">
		<tr>
			<td width="100"><b>NRIC<b></td>
			<td>{$member.nric}</td>
		</tr>
		
		<tr>
			<td><b>Card No<b></td>
			<td>{$member.card_no}</td>
		</tr>
		
		<tr>
			<td><b>Name<b></td>
			<td>{$member.name}</td>
		</tr>
	</table>
	
	

	{if !$member.mobile_registered}
		<div style="color: red;">
			* This member is not yet register with mobile.
		</div>
	{/if}
	
	<input type="button" value="{if $member.mobile_registered}Change{else}Set New{/if} Password" onClick="CHANGE_PASS_DIALOG.open();" />
	<br style="clear:both;" />
</div>

{if $member.mobile_registered}
	<h3>Device List</h3>
	<div class="stdframe">
		<table class="report_table" width="100%" style="background-color: #fff;">
			<tr class="header">
				<th>&nbsp;</th>
				<th>Device ID</th>
				<th>OS Type</th>
				<th>App Version</th>
				<th>Last Online</th>
			</tr>
			
			{foreach from=$member_app_session item=r name=f_device}
				<tr>
					<td align="center">{$smarty.foreach.f_device.iteration}</td>
					<td>{$r.device_id|default:'-'}</td>
					<td align="center">{$r.mobile_type|default:'-'}</td>
					<td align="center">{$r.app_version|default:'-'}</td>
					<td align="center">{$r.last_access|default:'-'}</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="5">* No Data *</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}

<script>MEMBER_MOBILE_DETAILS.initialise();</script>
{include file='footer.tpl'}